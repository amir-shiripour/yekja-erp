@extends('layouts.user')

@php
    use Modules\Tasks\Entities\Task;
    use Illuminate\Support\Js;
    use Morilog\Jalali\Jalalian;

    /** @var Task $task */

    $currentUser    = auth()->user();
    $canAssign      = $canAssign ?? ($currentUser && ($currentUser->can('tasks.assign') || $currentUser->can('tasks.manage') || $currentUser->hasRole('super-admin')));

    $types          = $types ?? Task::typeOptions();
    $statuses       = $statuses ?? Task::statusOptions();
    $priorities     = $priorities ?? Task::priorityOptions();
    $users          = $users ?? collect();
    $roles          = $roles ?? collect();
    $clients        = $clients ?? collect();
    $clientStatuses = $clientStatuses ?? collect();

    $meta           = $task->meta ?? [];

    // تبدیل ارقام به فارسی
    $faNum = function ($str) {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace($en, $fa, (string) $str);
    };

    // تاریخ سررسید برای نمایش شمسی
    $dueAtView = old('due_at_view');
    if (! $dueAtView && $task->due_at) {
        try {
            $dueAtView = Jalalian::fromCarbon($task->due_at)->format('Y/m/d');
        } catch (\Throwable $e) {
            $dueAtView = $task->due_at->format('Y-m-d');
        }
    }

    // ساعت سررسید برای نمایش
    $dueTimeView = old('due_time');
    if (! $dueTimeView && $task->due_at) {
        $dueTimeView = $task->due_at->format('H:i');
    }

    $userOptions = $users->map(function ($u) use ($faNum) {
        return [
            'id'       => (string) $u->id,
            'name'     => $u->name,
            'email'    => $u->email ?? '',
            'mobile'   => $u->mobile ? $faNum($u->mobile) : '',
            'role_ids' => $u->roles ? $u->roles->pluck('id')->map(fn ($id) => (string) $id)->values()->all() : [],
        ];
    })->values()->all();

    $clientOptions = $clients->map(function ($c) use ($faNum) {
        return [
            'id'        => (string) $c->id,
            'name'      => $c->full_name,
            'phone'     => $c->phone ? $faNum($c->phone) : '',
            'status_id' => $c->status_id ? (string) $c->status_id : null,
            'status'    => optional($c->status)->name ?? '',
        ];
    })->values()->all();

    $roleOptions = $roles->map(fn($r) => [
        'value' => (string) $r->id,
        'label' => $r->name,
    ])->values()->all();

    $userSelectOptions = collect($userOptions)->map(function ($u) {
        return [
            'value'    => (string) $u['id'],
            'label'    => $u['name'],
            'initial'  => mb_substr($u['name'], 0, 1),
            'subtitle' => $u['email'] ?: ($u['mobile'] ?? ''),
            'email'    => $u['email'] ?? '',
            'phone'    => $u['mobile'] ?? '',
            'role_ids' => $u['role_ids'] ?? [],
        ];
    })->all();

    $relatedUserSelectOptions = collect($userOptions)->map(function ($u) {
        return [
            'value'    => (string) $u['id'],
            'label'    => $u['name'],
            'initial'  => mb_substr($u['name'], 0, 1),
            'subtitle' => $u['email'] ?: ($u['mobile'] ?? ''),
            'email'    => $u['email'] ?? '',
            'phone'    => $u['mobile'] ?? '',
            'role_ids' => $u['role_ids'] ?? [],
        ];
    })->all();

    $clientSelectOptions = collect($clientOptions)->map(function ($c) {
        return [
            'value'     => (string) $c['id'],
            'label'     => $c['name'],
            'initial'   => mb_substr($c['name'], 0, 1),
            'subtitle'  => $c['phone'] ?? '',
            'phone'     => $c['phone'] ?? '',
            'badge'     => $c['status'] ?? null,
            'status_id' => $c['status_id'] ?? null,
        ];
    })->all();

    $clientStatusOptions = $clientStatuses->map(fn($st) => [
        'value' => (string) $st->id,
        'label' => $st->label ?? $st->key,
    ])->values()->all();

    // مقادیر اولیه برای انتخاب‌ها
    $initialAssigneeMode = old('assignee_mode', $meta['assignee_mode'] ?? 'single_user');
    $initialAssigneeUserIds = old('assignee_user_ids', $meta['assignee_user_ids'] ?? ($task->assignee_id ? [$task->assignee_id] : []));
    $initialAssigneeRoleIds = old('assignee_role_ids', $meta['assignee_role_ids'] ?? []);

    $initialRelatedTarget = old('related_target', $meta['related_target'] ?? (
        $task->related_type === Task::RELATED_TYPE_USER ? 'user' : ($task->related_type === Task::RELATED_TYPE_CLIENT ? 'client' : 'none')
    ));
    $initialRelatedUserRoleIds = old('related_user_role_ids', $meta['related_user_role_ids'] ?? []);
    $initialRelatedUserIds = old('related_user_ids', $meta['related_user_ids'] ?? (
        $task->related_type === Task::RELATED_TYPE_USER && $task->related_id ? [$task->related_id] : []
    ));
    $initialRelatedClientStatusIds = old('related_client_status_ids', $meta['related_client_status_ids'] ?? []);
    $initialRelatedClientIds = old('related_client_ids', $meta['related_client_ids'] ?? (
        $task->related_type === Task::RELATED_TYPE_CLIENT && $task->related_id ? [$task->related_id] : []
    ));

    $statusVariant = match($task->status) {
        Task::STATUS_DONE => 'success',
        Task::STATUS_IN_PROGRESS => 'info',
        Task::STATUS_CANCELED => 'danger',
        default => 'neutral'
    };

    $alpineMainData = [
        'taskType'      => old('task_type', $task->task_type ?? Task::TYPE_GENERAL),
        'assigneeMode'  => $initialAssigneeMode,
        'relatedTarget' => $initialRelatedTarget,
        'canAssign'     => (bool) $canAssign,
        'users'         => $userOptions,
        'clients'       => $clientOptions,
        'selectedRelatedUserRoleIds'    => collect($initialRelatedUserRoleIds)->map(fn ($id) => (string) $id)->values()->all(),
        'selectedRelatedClientStatusIds'=> collect($initialRelatedClientStatusIds)->map(fn ($id) => (string) $id)->values()->all(),
    ];
@endphp

@section('title', 'ویرایش وظیفه شماره ' . $faNum($task->id))

@section('content')
    <script>
        function setupTaskMultiSelect(config) {
            return {
                name: config.name,
                allLabel: config.allLabel || null,
                options: config.options || [],
                selectedValues: [],
                search: '',
                open: false,
                formContext: null,

                init() {
                    const oldValues = Array.isArray(config.oldValues) ? config.oldValues : [];
                    this.selectedValues = oldValues.map(String);
                    if (this.name) {
                        this.$dispatch(this.name + '-changed', this.selectedValues);
                    }
                },

                isSelected(value) {
                    value = String(value);
                    return this.selectedValues.includes(value);
                },

                toggle(value) {
                    value = String(value);
                    if (value === '__all__') {
                        this.selectedValues = this.isSelected('__all__') ? [] : ['__all__'];
                    } else {
                        this.selectedValues = this.selectedValues.filter(v => v !== '__all__');
                        if (this.isSelected(value)) {
                            this.selectedValues = this.selectedValues.filter(v => v !== value);
                        } else {
                            this.selectedValues.push(value);
                        }
                    }
                    if (this.name) {
                        this.$dispatch(this.name + '-changed', this.selectedValues);
                    }
                },

                clearValue(value) {
                    value = String(value);
                    this.selectedValues = this.selectedValues.filter(v => v !== value);
                    if (this.name) {
                        this.$dispatch(this.name + '-changed', this.selectedValues);
                    }
                },

                filteredOptions() {
                    const term = (this.search || '').toLowerCase();
                    let baseOpts = this.options || [];
                    const ctx = this.formContext || window.taskFormContext || {};

                    if (this.name === 'related_user_ids' && ctx.users && ctx.selectedRelatedUserRoleIds) {
                        const selectedRoles = (ctx.selectedRelatedUserRoleIds || []).filter(v => v !== '__all__').map(String);
                        if (selectedRoles.length > 0) {
                            const allUsers = ctx.users || [];
                            baseOpts = baseOpts.filter(opt => {
                                const user = allUsers.find(u => String(u.id) === String(opt.value));
                                if (!user) return false;
                                const roleIds = (user.role_ids || []).map(String);
                                return selectedRoles.some(rid => roleIds.includes(rid));
                            });
                        }
                    }

                    if (this.name === 'related_client_ids' && ctx.clients && ctx.selectedRelatedClientStatusIds) {
                        const selectedStatuses = (ctx.selectedRelatedClientStatusIds || []).filter(v => v !== '__all__').map(String);
                        if (selectedStatuses.length > 0) {
                            const allClients = ctx.clients || [];
                            baseOpts = baseOpts.filter(opt => {
                                const client = allClients.find(c => String(c.id) === String(opt.value));
                                if (!client) return false;
                                const clientStatusId = client.status_id != null ? String(client.status_id) : null;
                                if (!clientStatusId) return false;
                                return selectedStatuses.includes(clientStatusId);
                            });
                        }
                    }

                    return baseOpts.filter(o => (o.label || '').toLowerCase().includes(term));
                }
            };
        }
    </script>

    {{-- کانتینر اصلی با پهنای استاندارد (max-w-screen-2xl) --}}
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 pb-32 md:pb-8"
         x-data="{ submitting: false }">

        {{-- هدر صفحه --}}
        <header
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-6 shadow-xs transition-all space-y-3 sm:space-y-4">
            {{-- بردکرامب ناوبری --}}
            <nav class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500"
                 aria-label="مسیر راهنما">
                <a href="{{ route('user.dashboard') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پیشخوان</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('user.tasks.index') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">مدیریت وظایف</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span
                    class="text-indigo-600 dark:text-indigo-400 font-black truncate max-w-xs sm:max-w-md inline-block">ویرایش: {{ $task->title }}</span>
            </nav>

            {{-- ردیف هدر: آیکون، عنوان و دکمه‌های عملیاتی در یک راستا هم روی موبایل و هم روی دسکتاپ --}}
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
                                title="{{ $task->title }}">
                                ویرایش: {{ $task->title }}
                            </h1>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('user.tasks.show', $task) }}"
                       class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/80 text-xs sm:text-sm font-bold shadow-2xs transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span class="hidden sm:inline">مشاهده جزئیات</span>
                        <span class="sm:hidden">مشاهده</span>
                    </a>
                </div>
            </div>
        </header>

        {{-- هشدارهای خطا --}}
        @if ($errors->any())
            <x-ui.alert variant="danger" title="لطفاً خطاهای زیر را برطرف نمایید:" :dismissible="true">
                <ul class="list-disc list-inside space-y-1 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        {{-- فرم ویرایش وظیفه --}}
        <form method="POST"
              action="{{ route('user.tasks.update', $task) }}"
              class="space-y-6 sm:space-y-8"
              x-data='@json($alpineMainData)'
              data-task-form-root
              x-init="window.taskFormContext = $data"
              @submit="submitting = true"
              @task_type-changed="taskType = $event.detail"
              @related_user_role_ids-changed="selectedRelatedUserRoleIds = $event.detail || []"
              @related_client_status_ids-changed="selectedRelatedClientStatusIds = $event.detail || []">
            @csrf
            @method('PUT')

            {{-- کارت اطلاعات پایه وظیفه --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-5 sm:p-7 shadow-xs space-y-6">
                <div class="flex items-center gap-2.5 pb-4 border-b border-gray-100 dark:border-gray-700/40">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shrink-0"></span>
                    <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">اطلاعات پایه وظیفه</h2>
                </div>

                <div class="space-y-5">
                    {{-- عنوان وظیفه --}}
                    <div>
                        <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                            عنوان وظیفه <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <input type="text"
                               name="title"
                               value="{{ old('title', $task->title) }}"
                               required
                               placeholder="عنوان وظیفه..."
                               class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 transition-all outline-hidden @error('title') border-rose-400 ring-rose-500/10 @enderror">
                        @error('title')
                        <p class="mt-1 text-xs text-rose-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- بخش دو ستونه تخصیص مسئول و موجودیت مرتبط (طراحی یکپارچه مشابه پیگیری با باتن سگمنتد) --}}
                    @php
                        $assigneeModeOptions = [
                            [
                                'value' => 'single_user',
                                'label' => 'کاربر مشخص',
                                'icon' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'
                            ],
                            [
                                'value' => 'by_roles',
                                'label' => 'بر اساس نقش‌ها',
                                'icon' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
                            ]
                        ];

                        $relatedTargetOptions = [
                            [
                                'value' => 'none',
                                'label' => 'مستقل',
                                'icon' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>'
                            ],
                            [
                                'value' => 'client',
                                'label' => 'مشتری پرونده',
                                'icon' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'
                            ],
                            [
                                'value' => 'user',
                                'label' => 'کاربر همکار',
                                'icon' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'
                            ]
                        ];
                    @endphp
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-1">
                        {{-- ستون ۱: کاربر مسئول انجام --}}
                        <div
                            class="p-4 sm:p-5 rounded-2xl bg-indigo-50/40 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-800/40 space-y-4 shadow-2xs">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 border-b border-indigo-100/70 dark:border-indigo-800/30 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                                    <label class="text-xs font-black text-gray-800 dark:text-gray-200">
                                        کاربر مسئول انجام <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                </div>

                                {{-- دکمه سگمنتد انتخاب روش تخصیص --}}
                                <div
                                    x-show="taskType === '{{ Task::TYPE_GENERAL }}' || (taskType === '{{ Task::TYPE_FOLLOW_UP }}' && canAssign)"
                                    x-cloak>
                                    <x-ui.segmented-button
                                        name="assignee_mode"
                                        model="assigneeMode"
                                        size="xs"
                                        :options="$assigneeModeOptions"
                                    />
                                </div>
                            </div>

                            {{-- پیام اتوماتیک در صورتی که کاربر دسترسی تغییر مسئول را نداشته باشد --}}
                            @if($currentUser)
                                <div x-show="taskType === '{{ Task::TYPE_FOLLOW_UP }}' && !canAssign" x-cloak
                                     class="flex items-center gap-3 p-3.5 rounded-xl bg-white/80 dark:bg-gray-800/80 border border-indigo-100 dark:border-indigo-800/40">
                                    <div
                                        class="w-9 h-9 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-black text-xs shrink-0">
                                        {{ mb_substr($currentUser->name ?? 'ک', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-indigo-900 dark:text-indigo-200">
                                            مسئول این وظیفه: <span
                                                class="text-indigo-600 dark:text-indigo-400">{{ $task->assignee_name }}</span>
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <div
                                x-show="taskType === '{{ Task::TYPE_GENERAL }}' || (taskType === '{{ Task::TYPE_FOLLOW_UP }}' && canAssign)"
                                x-cloak class="space-y-3">
                                {{-- حالت انتخاب کاربران مشخص --}}
                                <div x-show="assigneeMode === 'single_user'" x-cloak class="space-y-1.5">
                                    <x-ui.select
                                        name="assignee_user_ids"
                                        :options="$userSelectOptions"
                                        :value="$initialAssigneeUserIds"
                                        :multiple="true"
                                        placeholder="انتخاب کاربران مسئول..."
                                        searchPlaceholder="جستجوی نام، ایمیل یا شماره..."
                                        color="indigo"
                                    />
                                    @error('assignee_user_ids') <p
                                        class="mt-1 text-xs text-rose-500 font-semibold">{{ $message }}</p> @enderror
                                </div>

                                {{-- حالت انتخاب نقش‌ها --}}
                                <div x-show="assigneeMode === 'by_roles'" x-cloak class="space-y-1.5">
                                    <x-ui.select
                                        name="assignee_role_ids"
                                        :options="$roleOptions"
                                        :value="$initialAssigneeRoleIds"
                                        :multiple="true"
                                        allLabel="همه نقش‌ها"
                                        placeholder="انتخاب نقش‌های مسئول..."
                                        searchPlaceholder="جستجوی نقش..."
                                        color="indigo"
                                    />
                                </div>
                            </div>
                        </div>

                        {{-- ستون ۲: موجودیت و مشتری مرتبط --}}
                        <div
                            class="p-4 sm:p-5 rounded-2xl bg-emerald-50/40 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-800/40 space-y-4 shadow-2xs">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 border-b border-emerald-100/70 dark:border-emerald-800/30 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                                    <label class="text-xs font-black text-gray-800 dark:text-gray-200">
                                        مشتری مرتبط
                                    </label>
                                </div>

                                {{-- دکمه سگمنتد انتخاب نوع موجودیت --}}
                                <x-ui.segmented-button
                                    name="related_target"
                                    model="relatedTarget"
                                    size="xs"
                                    :options="$relatedTargetOptions"
                                />
                            </div>

                            {{-- حالت مستقل (بدون موجودیت) --}}
                            <div x-show="relatedTarget === 'none'" x-cloak
                                 class="p-3.5 rounded-xl bg-white/70 dark:bg-gray-800/70 border border-emerald-100/60 dark:border-emerald-900/30 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                این وظیفه مستقل بوده و به پرونده مشتری یا کاربر دیگری پیوست نمی‌شود.
                            </div>

                            {{-- ارتباط با مشتری --}}
                            <div x-show="relatedTarget === 'client'" x-cloak class="space-y-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1">
                                        فیلتر وضعیت‌های مشتری (اختیاری)
                                    </label>
                                    <x-ui.select
                                        name="related_client_status_ids"
                                        :options="$clientStatusOptions"
                                        :value="$initialRelatedClientStatusIds"
                                        :multiple="true"
                                        allLabel="همه وضعیت‌ها"
                                        placeholder="همه وضعیت‌ها (اختیاری)..."
                                        searchPlaceholder="جستجوی وضعیت..."
                                        color="emerald"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1">
                                        انتخاب مشتریان مرتبط <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <x-ui.select
                                        name="related_client_ids"
                                        :options="$clientSelectOptions"
                                        :value="$initialRelatedClientIds"
                                        :multiple="true"
                                        placeholder="انتخاب مشتریان پرونده..."
                                        searchPlaceholder="جستجوی نام یا تلفن مشتری..."
                                        color="emerald"
                                    />
                                    @error('related_client_ids') <p
                                        class="mt-1 text-xs text-rose-500 font-semibold">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- ارتباط با کاربر --}}
                            <div x-show="relatedTarget === 'user'" x-cloak class="space-y-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1">
                                        فیلتر نقش‌های کاربر (اختیاری)
                                    </label>
                                    <x-ui.select
                                        name="related_user_role_ids"
                                        :options="$roleOptions"
                                        :value="$initialRelatedUserRoleIds"
                                        :multiple="true"
                                        allLabel="همه نقش‌ها"
                                        placeholder="همه نقش‌ها (اختیاری)..."
                                        searchPlaceholder="جستجوی نقش..."
                                        color="indigo"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1">
                                        انتخاب کاربران مرتبط <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <x-ui.select
                                        name="related_user_ids"
                                        :options="$relatedUserSelectOptions"
                                        :value="$initialRelatedUserIds"
                                        :multiple="true"
                                        placeholder="انتخاب همکاران مرتبط..."
                                        searchPlaceholder="جستجوی نام یا ایمیل همکار..."
                                        color="indigo"
                                    />
                                    @error('related_user_ids') <p
                                        class="mt-1 text-xs text-rose-500 font-semibold">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ردیف ۳ تایی: نوع وظیفه، اولویت، وضعیت --}}
                    @php
                        $availableTypes = collect($types)
                            ->reject(fn($lbl, $key) => $key === Task::TYPE_SYSTEM && $task->task_type !== Task::TYPE_SYSTEM)
                            ->all();
                    @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- نوع وظیفه --}}
                        <div>
                            <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                نوع وظیفه <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <x-ui.select
                                name="task_type"
                                :options="$availableTypes"
                                :value="old('task_type', $task->task_type)"
                                :searchable="false"
                                :clearable="false"
                            />
                            @error('task_type')
                            <p class="mt-1 text-xs text-rose-500 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- اولویت اهمیت --}}
                        <div>
                            <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                اولویت اهمیت <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <x-ui.select
                                name="priority"
                                :options="$priorities"
                                :value="old('priority', $task->priority)"
                                :searchable="false"
                                :clearable="false"
                            />
                            @error('priority')
                            <p class="mt-1 text-xs text-rose-500 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- وضعیت --}}
                        <div>
                            <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                                وضعیت <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <x-ui.select
                                name="status"
                                :options="$statuses"
                                :value="old('status', $task->status)"
                                :searchable="false"
                                :clearable="false"
                            />
                            @error('status')
                            <p class="mt-1 text-xs text-rose-500 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- زمان‌بندی و سررسید وظیفه --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700/30">
                        <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                            زمان سررسید و موعد اقدام
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- تاریخ سررسید (شمسی) --}}
                            <div class="relative">
                                <input id="due_at_view"
                                       name="due_at_view"
                                       type="text"
                                       data-jdp-only-date
                                       autocomplete="off"
                                       placeholder="انتخاب تاریخ سررسید (شمسی)..."
                                       value="{{ $dueAtView }}"
                                       class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-2.5 pe-10 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-hidden cursor-pointer text-center">
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3.5 text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- ساعت سررسید --}}
                            <div class="relative">
                                <input type="text"
                                       data-jdp-only-time
                                       placeholder="ساعت (مثال: ۱۴:۳۰)"
                                       name="due_time"
                                       value="{{ $dueTimeView }}"
                                       class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-2.5 pe-10 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-hidden cursor-pointer text-center dir-ltr">
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3.5 text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- شرح و توضیحات تکمیلی --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700/30">
                        <label class="block text-xs font-bold mb-1.5 text-gray-700 dark:text-gray-300">
                            شرح و توضیحات تکمیلی
                        </label>
                        <textarea name="description"
                                  rows="4"
                                  placeholder="توضیحات تکمیلی، چک‌لیست یا نکات لازم برای این وظیفه..."
                                  class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 p-3.5 text-xs sm:text-sm text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 transition-all outline-hidden leading-relaxed @error('description') border-rose-400 ring-rose-500/10 @enderror">{{ old('description', $task->description) }}</textarea>
                        @error('description')
                        <p class="mt-1 text-xs text-rose-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- کارت اطلاعات سیستمی و متادیتای رکورد --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-gray-100 dark:border-gray-700/40">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shrink-0"></span>
                    <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">اطلاعات سیستمی و متادیتای
                        رکورد</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- شناسه وظیفه --}}
                    <div
                        class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/40">
                        <span class="text-[10px] font-bold text-gray-400 block mb-1">شناسه یکتا وظیفه</span>
                        <span
                            class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">#{{ $faNum($task->id) }}</span>
                    </div>

                    {{-- ثبت‌کننده --}}
                    <div
                        class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/40">
                        <span class="text-[10px] font-bold text-gray-400 block mb-1">ایجادکننده وظیفه</span>
                        <span
                            class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">{{ $task->creator_name ?? 'سیستم' }}</span>
                    </div>

                    {{-- زمان ایجاد --}}
                    <div
                        class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/40">
                        <span class="text-[10px] font-bold text-gray-400 block mb-1">تاریخ و زمان ایجاد</span>
                        <span class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">
                            {{ $task->created_at ? $faNum(Jalalian::fromCarbon($task->created_at)->format('Y/m/d H:i')) : '—' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- نوار شناور و چسبان عملیات فرم (ثبت / انصراف) --}}
            <div
                class="relative md:sticky md:bottom-6 z-30 mb-6 md:mb-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-xl shadow-gray-200/50 dark:shadow-black/30 flex items-center justify-between gap-4 transition-all">
                <div class="flex items-center gap-3">
                    <a href="{{ route('user.tasks.index') }}"
                       class="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 hover:bg-gray-100 dark:bg-gray-700/50 dark:hover:bg-gray-700 text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300 transition-all shadow-2xs active:scale-95">
                        <svg class="w-4 h-4 rtl:rotate-180 text-gray-400 dark:text-gray-500" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>انصراف و بازگشت</span>
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                            :disabled="submitting"
                            class="inline-flex items-center justify-center gap-2.5 px-6 sm:px-8 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-black text-xs sm:text-sm shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 active:scale-95 transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                        <span x-show="submitting"
                              class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin shrink-0"></span>
                        <svg x-show="!submitting" class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-show="!submitting">بروزرسانی وظیفه</span>
                        <span x-show="submitting">در حال ذخیره‌سازی...</span>
                    </button>
                </div>
            </div>
        </form>

        <div class="h-32 md:hidden" style="height: 8rem;" aria-hidden="true"></div>

        <x-ui.dock/>
    </div>
@endsection

@includeIf('partials.jalali-date-picker')
