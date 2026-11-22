@extends('layouts.user')

@section('title', 'ویرایش پیگیری #' . ($followUp->id ?? ''))

@section('content')
    @php
        use Modules\Tasks\Entities\Task;
        use Illuminate\Support\Js;
        use Morilog\Jalali\Jalalian;

        $currentUser = auth()->user();

        $statuses   = $statuses   ?? Task::statusOptions();
        $priorities = $priorities ?? Task::priorityOptions();
        $users      = $users      ?? collect();
        $clients    = $clients    ?? collect();

        $faNum = function ($str) {
            $en = ['0','1','2','3','4','5','6','7','8','9'];
            $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            return str_replace($en, $fa, (string) $str);
        };

        $userSelectOptions = $users->map(function ($u) use ($faNum) {
            return [
                'id'        => (string) $u->id,
                'name'      => $u->name,
                'initial'   => mb_substr($u->name ?? 'ک', 0, 1),
                'email'     => $u->email ?? '',
                'mobile'    => $u->mobile ?? '',
                'mobile_fa' => $u->mobile ? $faNum($u->mobile) : '',
            ];
        })->values()->all();

        $clientSelectOptions = $clients->map(function ($c) use ($faNum) {
            $phone = $c->phone ?? '';
            $phoneLabel = 'شماره تماس';
            if ($phone) {
                if (str_starts_with($phone, '09') || str_starts_with($phone, '9') || str_starts_with($phone, '+989')) {
                    $phoneLabel = 'شماره همراه';
                } else {
                    $phoneLabel = 'تلفن ثابت / دفتر';
                }
            }

            $meta = is_array($c->meta) ? $c->meta : (json_decode($c->meta ?? '', true) ?: []);
            $officePhone = $meta['office_phone'] ?? ($meta['tell'] ?? ($meta['telephone'] ?? null));
            $companyName = $meta['company'] ?? ($meta['company_name'] ?? ($meta['organization'] ?? null));

            return [
                'id'                => (string) $c->id,
                'name'              => $c->full_name,
                'initial'           => mb_substr($c->full_name ?? 'م', 0, 1),
                'phone'             => $phone,
                'phone_fa'          => $phone ? $faNum($phone) : '',
                'phone_label'       => $phoneLabel,
                'email'             => $c->email ?? '',
                'national_code'     => $c->national_code ? $faNum($c->national_code) : '',
                'raw_national_code' => $c->national_code ?? '',
                'case_number'       => $c->case_number ? $faNum($c->case_number) : '',
                'raw_case_number'   => $c->case_number ?? '',
                'status_name'       => optional($c->status)->name ?? '',
                'office_phone'      => $officePhone ? $faNum($officePhone) : '',
                'raw_office_phone'  => $officePhone ?? '',
                'company'           => $companyName ?? '',
            ];
        })->values()->all();

        $relatedClient = $relatedClient ?? null;

        $initialRelatedId = old(
            'related_id',
            $relatedClient->id
                ?? ($followUp->related_type === Task::RELATED_TYPE_CLIENT ? $followUp->related_id : '')
        );

        $initialAssigneeId = old('assignee_id', $followUp->assignee_id ?? ($currentUser->id ?? ''));

        $initialDueAtView = old(
            'due_at_view',
            $followUp->due_at ? Jalalian::fromCarbon($followUp->due_at)->format('Y/m/d') : ''
        );

        $initialDueTime = old(
            'due_time',
            $followUp->due_at ? $followUp->due_at->format('H:i') : ''
        );

        $statusVariant = match($followUp->status) {
            Task::STATUS_DONE => 'emerald',
            Task::STATUS_CANCELED => 'rose',
            Task::STATUS_IN_PROGRESS => 'blue',
            default => 'slate',
        };
    @endphp

    <script>
        function assigneePicker(config) {
            return {
                open: false,
                search: '',
                options: config.options || [],
                selectedId: config.initialId ? String(config.initialId) : '',
                get selected() {
                    return this.options.find(o => String(o.id) === String(this.selectedId)) || null;
                },
                select(id) {
                    this.selectedId = String(id);
                    this.search = '';
                    this.open = false;
                },
                filtered() {
                    const term = (this.search || '').trim().toLowerCase();
                    if (!term) return this.options;
                    return this.options.filter(o =>
                        (o.name || '').toLowerCase().includes(term) ||
                        (o.email || '').toLowerCase().includes(term) ||
                        (o.mobile || '').toLowerCase().includes(term)
                    );
                }
            };
        }

        function clientPicker(config) {
            return {
                open: false,
                search: '',
                options: config.options || [],
                selectedId: config.initialId ? String(config.initialId) : '',
                get selected() {
                    return this.options.find(o => String(o.id) === String(this.selectedId)) || null;
                },
                select(id) {
                    this.selectedId = id ? String(id) : '';
                    this.search = '';
                    this.open = false;
                },
                clear() {
                    this.selectedId = '';
                    this.search = '';
                    this.open = false;
                },
                filtered() {
                    const term = (this.search || '').trim().toLowerCase();
                    if (!term) return this.options;
                    return this.options.filter(o =>
                        (o.name || '').toLowerCase().includes(term) ||
                        (o.phone || '').toLowerCase().includes(term) ||
                        (o.email || '').toLowerCase().includes(term) ||
                        (o.case_number || '').toLowerCase().includes(term) ||
                        (o.national_code || '').toLowerCase().includes(term)
                    );
                }
            };
        }
    </script>

    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 pb-32 md:pb-8"
         x-data="{ submitting: false }">

        <header
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-6 shadow-xs transition-all space-y-3 sm:space-y-4">
            <nav class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500"
                 aria-label="مسیر راهنما">
                <a href="{{ route('user.dashboard') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پیشخوان</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('user.followups.index') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">مدیریت پیگیری‌ها</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-black truncate max-w-xs sm:max-w-md inline-block"
                      title="ویرایش پیگیری شماره {{ $faNum($followUp->id) }}">
                    ویرایش: {{ $followUp->title }}
                </span>
            </nav>

            <div class="flex items-center justify-between gap-2.5 sm:gap-6">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <span
                        class="flex items-center justify-center w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-500/20 shrink-0">
                        <svg class="w-4.5 h-4.5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                            <h1 class="text-sm sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight truncate sm:whitespace-normal"
                                title="{{ $followUp->title }}">
                                ویرایش: {{ $followUp->title }}
                            </h1>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                    <a href="{{ route('user.followups.show', $followUp) }}"
                       class="inline-flex items-center gap-1 sm:gap-2 px-2.5 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span class="hidden sm:inline">مشاهده جزئیات</span>
                        <span class="sm:hidden">مشاهده</span>
                    </a>
                    <a href="{{ route('user.followups.index') }}"
                       class="inline-flex items-center gap-1 sm:gap-2 px-2.5 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span class="hidden sm:inline">فهرست پیگیری‌ها</span>
                        <span class="sm:hidden">بازگشت</span>
                    </a>
                </div>
            </div>
        </header>

        @if ($errors->any())
            <x-ui.alert variant="error" title="لطفاً خطاهای زیر را برطرف نمایید:" :dismissible="true">
                <ul class="list-disc list-inside space-y-1 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form method="POST"
              action="{{ route('user.followups.update', $followUp) }}"
              @submit="submitting = true"
              class="space-y-6 sm:space-y-8">
            @csrf
            @method('PUT')

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 sm:p-7 space-y-6">
                <div class="flex items-center gap-2.5 pb-3 border-b border-gray-100 dark:border-gray-700/40">
                    <span
                        class="w-2.5 h-2.5 rounded-full bg-indigo-600 ring-4 ring-indigo-100 dark:ring-indigo-950"></span>
                    <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">اطلاعات پایه پیگیری</h3>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                            عنوان پیگیری <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <input type="text"
                               name="title"
                               value="{{ old('title', $followUp->title) }}"
                               required
                               placeholder="عنوان پیگیری..."
                               class="w-full rounded-xl border border-gray-200/80 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700/60 px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500 outline-hidden">
                        @error('title')
                        <p class="mt-1.5 text-xs text-rose-500 font-bold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-1">
                        <div x-data="assigneePicker({
                                options: {{ Js::from($userSelectOptions) }},
                                initialId: '{{ $initialAssigneeId }}'
                             })"
                             class="space-y-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                کاربر مسئول انجام <span class="text-rose-500 font-bold">*</span>
                            </label>

                            <input type="hidden" name="assignee_id" :value="selectedId">

                            <div class="relative">
                                <div
                                    class="p-4 rounded-2xl bg-indigo-50/40 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-800/40 transition-all shadow-2xs space-y-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div
                                                class="w-11 h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-indigo-500/20 shrink-0">
                                                <span x-text="selected ? selected.initial : 'ک'"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="text-xs sm:text-sm font-black text-gray-900 dark:text-white truncate"
                                                        x-text="selected ? selected.name : 'انتخاب نشده'"></span>
                                                </div>
                                            </div>
                                        </div>

                                        @if(auth()->user()->can('followups.view.all') && $users->isNotEmpty())
                                            <button type="button"
                                                    @click="open = !open"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-indigo-200 dark:border-indigo-800/60 text-xs font-bold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-all shadow-2xs cursor-pointer shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                                <span>تغییر مسئول</span>
                                            </button>
                                        @endif
                                    </div>

                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2.5 border-t border-indigo-100/70 dark:border-indigo-800/30 text-xs">
                                        <div
                                            class="flex items-center gap-1.5 p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-indigo-100/70 dark:border-indigo-900/40 min-w-0">
                                            <span class="text-gray-400 font-bold shrink-0">ایمیل:</span>
                                            <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr truncate"
                                                  x-text="selected && selected.email ? selected.email : '—'"></span>
                                        </div>

                                        <div
                                            class="flex items-center gap-1.5 p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-indigo-100/70 dark:border-indigo-900/40 min-w-0">
                                            <span class="text-gray-400 font-bold shrink-0">تلفن تماس:</span>
                                            <span
                                                class="font-bold text-gray-800 dark:text-gray-200 dir-ltr text-right truncate"
                                                x-text="selected && selected.mobile_fa ? selected.mobile_fa : (selected && selected.mobile ? selected.mobile : '—')"></span>
                                        </div>
                                    </div>
                                </div>

                                @if(auth()->user()->can('followups.view.all') && $users->isNotEmpty())
                                    <div x-show="open"
                                         x-cloak
                                         @click.outside="open = false"
                                         class="absolute z-40 mt-2 w-full rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xl overflow-hidden">
                                        <div
                                            class="p-2.5 border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/30">
                                            <input type="text"
                                                   x-model="search"
                                                   placeholder="جستجوی نام، ایمیل یا شماره کاربر..."
                                                   class="w-full rounded-xl border border-gray-200/80 bg-white dark:bg-gray-900 dark:border-gray-700 px-3.5 py-2 text-xs text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-hidden">
                                        </div>
                                        <ul class="max-h-56 overflow-auto text-xs py-1 divide-y divide-gray-50 dark:divide-gray-700/30">
                                            <template x-for="opt in filtered()" :key="opt.id">
                                                <li>
                                                    <button type="button"
                                                            @click="select(opt.id)"
                                                            class="w-full text-right px-4 py-2.5 hover:bg-indigo-50/70 dark:hover:bg-indigo-950/40 flex items-center justify-between gap-3 transition-colors cursor-pointer"
                                                            :class="String(opt.id) === String(selectedId) ? 'bg-indigo-50/40 dark:bg-indigo-950/20 font-bold' : ''">
                                                        <div class="flex items-center gap-2.5 min-w-0">
                                                            <span
                                                                class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold text-xs shrink-0"
                                                                x-text="opt.initial"></span>
                                                            <div class="min-w-0">
                                                                <span
                                                                    class="block truncate text-gray-900 dark:text-white font-bold"
                                                                    x-text="opt.name"></span>
                                                                <span
                                                                    class="block text-[10px] text-gray-400 dir-ltr text-right truncate"
                                                                    x-text="opt.email || opt.mobile"></span>
                                                            </div>
                                                        </div>
                                                        <span x-show="String(opt.id) === String(selectedId)"
                                                              class="text-indigo-600 dark:text-indigo-400 shrink-0 font-black">✓</span>
                                                    </button>
                                                </li>
                                            </template>
                                            <template x-if="filtered().length === 0">
                                                <li class="px-4 py-3 text-xs text-gray-400 text-center">کاربری با این
                                                    مشخصات یافت نشد.
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div x-data="clientPicker({
                                options: {{ Js::from($clientSelectOptions) }},
                                initialId: '{{ $initialRelatedId }}'
                             })"
                             class="space-y-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                مشتری مرتبط <span class="text-gray-400 font-normal"></span>
                            </label>

                            <input type="hidden" name="related_type" value="{{ Task::RELATED_TYPE_CLIENT }}">
                            <input type="hidden" name="related_id" :value="selectedId">

                            <div class="relative">
                                <template x-if="selected">
                                    <div
                                        class="p-4 rounded-2xl bg-emerald-50/40 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-800/40 transition-all shadow-2xs space-y-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div
                                                    class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-emerald-500/20 shrink-0">
                                                    <span x-text="selected.initial"></span>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span
                                                            class="text-xs sm:text-sm font-black text-gray-900 dark:text-white truncate"
                                                            x-text="selected.name"></span>
                                                        <template x-if="selected.status_name">
                                                            <span
                                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200"
                                                                x-text="selected.status_name"></span>
                                                        </template>
                                                        <template x-if="selected.case_number">
                                                            <span
                                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200"
                                                                x-text="'پرونده: ' + selected.case_number"></span>
                                                        </template>
                                                    </div>
                                                    <span class="text-[11px] text-gray-400 font-bold block mt-0.5">پرونده مشتری انتخاب‌شده</span>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 shrink-0">
                                                <button type="button"
                                                        @click="open = !open"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-emerald-200 dark:border-emerald-800/60 text-xs font-bold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-all shadow-2xs cursor-pointer">
                                                    <span>تغییر</span>
                                                </button>
                                                <button type="button"
                                                        @click="clear()"
                                                        class="p-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-400 hover:text-rose-600 hover:border-rose-200 transition-colors cursor-pointer"
                                                        title="حذف اتصال به مشتری">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div
                                            class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2.5 border-t border-emerald-100/80 dark:border-emerald-800/40 text-xs">
                                            <template x-if="selected.phone">
                                                <div
                                                    class="flex items-center gap-1.5 p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-emerald-100 dark:border-emerald-900/40 min-w-0">
                                                    <span class="text-gray-400 font-bold shrink-0"
                                                          x-text="selected.phone_label + ':'"></span>
                                                    <span
                                                        class="font-bold text-gray-800 dark:text-gray-200 dir-ltr text-right truncate"
                                                        x-text="selected.phone_fa || selected.phone"></span>
                                                </div>
                                            </template>

                                            <template x-if="selected.office_phone">
                                                <div
                                                    class="flex items-center gap-1.5 p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-emerald-100 dark:border-emerald-900/40 min-w-0">
                                                    <span class="text-gray-400 font-bold shrink-0">تلفن دفتر:</span>
                                                    <span
                                                        class="font-bold text-gray-800 dark:text-gray-200 dir-ltr text-right truncate"
                                                        x-text="selected.office_phone"></span>
                                                </div>
                                            </template>

                                            <template x-if="selected.email">
                                                <div
                                                    class="flex items-center gap-1.5 p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-emerald-100 dark:border-emerald-900/40 min-w-0">
                                                    <span class="text-gray-400 font-bold shrink-0">ایمیل:</span>
                                                    <span
                                                        class="font-bold text-gray-800 dark:text-gray-200 dir-ltr truncate"
                                                        x-text="selected.email"></span>
                                                </div>
                                            </template>

                                            <template x-if="selected.national_code">
                                                <div
                                                    class="flex items-center gap-1.5 p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-emerald-100 dark:border-emerald-900/40 min-w-0">
                                                    <span class="text-gray-400 font-bold shrink-0">کد ملی:</span>
                                                    <span class="font-bold text-gray-800 dark:text-gray-200 truncate"
                                                          x-text="selected.national_code"></span>
                                                </div>
                                            </template>

                                            <template x-if="selected.company">
                                                <div
                                                    class="flex items-center gap-1.5 p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-emerald-100 dark:border-emerald-900/40 min-w-0">
                                                    <span class="text-gray-400 font-bold shrink-0">شرکت/سازمان:</span>
                                                    <span class="font-bold text-gray-800 dark:text-gray-200 truncate"
                                                          x-text="selected.company"></span>
                                                </div>
                                            </template>

                                            <template x-if="selected.case_number">
                                                <div
                                                    class="flex items-center gap-1.5 p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-emerald-100 dark:border-emerald-900/40 min-w-0">
                                                    <span class="text-gray-400 font-bold shrink-0">شماره پرونده:</span>
                                                    <span class="font-bold text-gray-800 dark:text-gray-200 truncate"
                                                          x-text="selected.case_number"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!selected">
                                    <div @click="open = !open"
                                         class="flex items-center justify-between p-4 rounded-2xl border border-dashed border-gray-200/90 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/20 hover:bg-gray-100/60 dark:hover:bg-gray-900/40 transition-all cursor-pointer">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-11 h-11 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center shrink-0">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">بدون اتصال به مشتری</span>
                                            </div>
                                        </div>

                                        <span
                                            class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                            <span>انتخاب مشتری</span>
                                            <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </span>
                                    </div>
                                </template>

                                <div x-show="open"
                                     x-cloak
                                     @click.outside="open = false"
                                     class="absolute z-40 mt-2 w-full rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xl overflow-hidden">
                                    <div
                                        class="p-2.5 border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/30">
                                        <input type="text"
                                               x-model="search"
                                               placeholder="جستجوی نام، شماره تلفن، کد ملی یا پرونده..."
                                               class="w-full rounded-xl border border-gray-200/80 bg-white dark:bg-gray-900 dark:border-gray-700 px-3.5 py-2 text-xs text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-hidden">
                                    </div>
                                    <ul class="max-h-56 overflow-auto text-xs py-1 divide-y divide-gray-50 dark:divide-gray-700/30">
                                        <li>
                                            <button type="button"
                                                    @click="clear()"
                                                    class="w-full text-right px-4 py-2.5 text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/40 text-xs font-bold flex items-center gap-2 transition-colors cursor-pointer">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                <span>بدون مشتری مرتبط (انصراف از انتساب)</span>
                                            </button>
                                        </li>
                                        <template x-for="opt in filtered()" :key="opt.id">
                                            <li>
                                                <button type="button"
                                                        @click="select(opt.id)"
                                                        class="w-full text-right px-4 py-2.5 hover:bg-emerald-50/70 dark:hover:bg-emerald-950/40 flex items-center justify-between gap-3 transition-colors cursor-pointer"
                                                        :class="String(opt.id) === String(selectedId) ? 'bg-emerald-50/40 dark:bg-emerald-950/20 font-bold' : ''">
                                                    <div class="flex items-center gap-2.5 min-w-0">
                                                        <span
                                                            class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold text-xs shrink-0"
                                                            x-text="opt.initial"></span>
                                                        <div class="min-w-0">
                                                            <div class="flex items-center gap-2">
                                                                <span
                                                                    class="truncate text-gray-900 dark:text-white font-bold"
                                                                    x-text="opt.name"></span>
                                                                <template x-if="opt.case_number">
                                                                    <span class="text-[10px] text-gray-400"
                                                                          x-text="'#' + opt.case_number"></span>
                                                                </template>
                                                            </div>
                                                            <div
                                                                class="flex items-center gap-2 text-[10px] text-gray-400 mt-0.5">
                                                                <span
                                                                    x-text="opt.phone_label + ': ' + (opt.phone_fa || opt.phone)"></span>
                                                                <template x-if="opt.national_code">
                                                                    <span
                                                                        x-text="'• کد ملی: ' + opt.national_code"></span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span x-show="String(opt.id) === String(selectedId)"
                                                          class="text-emerald-600 dark:text-emerald-400 shrink-0 font-black">✓</span>
                                                </button>
                                            </li>
                                        </template>
                                        <template x-if="filtered().length === 0">
                                            <li class="px-4 py-3 text-xs text-gray-400 text-center">مشتری با این مشخصات
                                                یافت نشد.
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                        <div>
                            <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                اولویت اهمیت <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <select name="priority"
                                    class="w-full rounded-xl border border-gray-200/80 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700/60 px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all cursor-pointer outline-hidden">
                                @foreach($priorities as $value => $label)
                                    <option
                                        value="{{ $value }}" @selected(old('priority', $followUp->priority) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                وضعیت پیگیری <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <select name="status"
                                    class="w-full rounded-xl border border-gray-200/80 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700/60 px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all cursor-pointer outline-hidden">
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $followUp->status) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                            توضیحات و شرح اقدام
                        </label>
                        <textarea name="description"
                                  rows="4"
                                  placeholder="هرگونه یادداشت یا توضیحات جدید را اینجا بنویسید..."
                                  class="w-full rounded-xl border border-gray-200/80 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700/60 px-4 py-3 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500 outline-hidden leading-relaxed">{{ old('description', $followUp->description) }}</textarea>
                        @error('description')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 sm:p-7 space-y-6">
                <div class="flex items-center gap-2.5 pb-3 border-b border-gray-100 dark:border-gray-700/40">
                    <span
                        class="w-2.5 h-2.5 rounded-full bg-indigo-600 ring-4 ring-indigo-100 dark:ring-indigo-950"></span>
                    <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">موعد انجام و سررسید
                        پیگیری</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                            تاریخ موعد
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="due_at_view"
                                   value="{{ $initialDueAtView }}"
                                   data-jdp
                                   data-jdp-only-date
                                   placeholder="1403/09/15"
                                   class="w-full rounded-xl border border-gray-200/80 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700/60 px-4 py-2.5 ps-10 text-xs sm:text-sm font-bold text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs outline-hidden text-center dir-ltr">
                            <div
                                class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        @error('due_at_view')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                            ساعت دقیق موعد
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="due_time"
                                   value="{{ $initialDueTime }}"
                                   data-jdp
                                   data-jdp-only-time
                                   placeholder="10:00"
                                   class="w-full rounded-xl border border-gray-200/80 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700/60 px-4 py-2.5 ps-10 text-xs sm:text-sm font-bold text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs outline-hidden text-center dir-ltr">
                            <div
                                class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        @error('due_time')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-gray-100 dark:border-gray-700/40">
                    <span
                        class="w-2.5 h-2.5 rounded-full bg-indigo-600 ring-4 ring-indigo-100 dark:ring-indigo-950"></span>
                    <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">اطلاعات سیستمی و متادیتای
                        رکورد</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- شناسه پیگیری --}}
                    <div
                        class="p-3.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700/40">
                        <span class="block text-[11px] text-gray-400 font-bold mb-0.5">شناسه سیستمی</span>
                        <span
                            class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">#{{ $faNum($followUp->id) }}</span>
                    </div>

                    {{-- ثبت‌کننده رکورد --}}
                    <div
                        class="p-3.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700/40">
                        <span class="block text-[11px] text-gray-400 font-bold mb-0.5">کاربر ثبت‌کننده</span>
                        <span
                            class="text-xs sm:text-sm font-black text-gray-900 dark:text-white truncate block">{{ optional($followUp->creator)->name ?? 'سیستم' }}</span>
                    </div>

                    {{-- تاریخ ثبت اولیه --}}
                    <div
                        class="p-3.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700/40">
                        <span class="block text-[11px] text-gray-400 font-bold mb-0.5">تاریخ ثبت اولیه</span>
                        <span
                            class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white dir-ltr block text-right">
                            {{ $followUp->created_at ? $faNum(Jalalian::fromCarbon($followUp->created_at)->format('Y/m/d H:i')) : '—' }}
                        </span>
                    </div>
                </div>
            </div>

            <div
                class="flex items-center justify-between relative md:sticky md:bottom-6 z-30 mb-6 md:mb-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-xl">
                <a href="{{ route('user.followups.index') }}"
                   class="px-5 py-2.5 text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                    انصراف
                </a>

                <button type="submit"
                        :disabled="submitting"
                        class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-black text-xs sm:text-sm shadow-lg shadow-indigo-600/30 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer">
                    <span x-show="submitting"
                          class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-show="!submitting">ذخیره تغییرات پیگیری</span>
                    <span x-show="submitting">در حال ذخیره‌سازی...</span>
                    <svg x-show="!submitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </div>
        </form>

        <div class="h-32 md:hidden" style="height: 8rem;" aria-hidden="true"></div>

        <x-ui.dock/>

        <x-ui.toast position="bottom-left"/>

    </div>
@endsection

@includeIf('partials.jalali-date-picker')
