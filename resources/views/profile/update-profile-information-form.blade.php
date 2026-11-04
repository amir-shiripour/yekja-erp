@php
    /** @noinspection SqlResolve */
    use App\Models\CustomUserField;
    use App\Models\UserCustomValue;
    use App\Tenancy\TenantManager;

    $user = auth()->user();
    $userRoles = $user->roles->pluck('name')->toArray();
    $userRolesDisplay = $user->roles->pluck('display_name')->toArray();
    $isSubAccount = !empty($user->tenant_id) || app(TenantManager::class)->hasTenant();

    $customFields = CustomUserField::query()
        ->when($isSubAccount, function ($query) use ($userRoles) {
            $query->where('entity_type', CustomUserField::ENTITY_SUB_ACCOUNT)
                  ->orWhereIn('role_name', $userRoles);
        }, function ($query) use ($userRoles) {
            $query->whereIn('role_name', $userRoles);
        })
        ->orderBy('id')
        ->get()
        ->unique('field_name');

    $customValues = UserCustomValue::where('user_id', $user->id)->get()->keyBy('field_name');

    if (!isset($this->state['custom']) || !is_array($this->state['custom'])) {
        $this->state['custom'] = [];
    }
    foreach ($customFields as $cf) {
        if (!array_key_exists($cf->field_name, $this->state['custom'])) {
            $val = $customValues->get($cf->field_name)?->value ?? '';
            if ($cf->field_type === 'checkbox') {
                if (is_string($val)) {
                    $decoded = json_decode($val, true);
                    $this->state['custom'][$cf->field_name] = is_array($decoded) ? $decoded : [];
                } else {
                    $this->state['custom'][$cf->field_name] = is_array($val) ? $val : [];
                }
            } else {
                $this->state['custom'][$cf->field_name] = $val;
            }
        }
    }

    $inputTypes = ['text','number','email','date','datetime-local','time','month','week','url','tel','password','color','range','hidden'];
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm mb-6";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4";
    $titleClass = "text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2";
    $descClass = "text-xs text-gray-500 dark:text-gray-400 mt-1";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2";
    $btnPrimaryClass = "inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed";
    $btnDangerClass = "inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-red-600 text-white font-bold text-sm shadow-lg shadow-red-500/30 hover:bg-red-700 hover:shadow-red-500/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed";
    $btnSecondaryClass = "inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white border border-gray-200 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white";
    $footerClass = "px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-end gap-4";
@endphp

<form wire:submit="updateProfileInformation" class="{{ $cardClass }}">
    {{-- هدر فرم --}}
    <div class="{{ $headerClass }}">
        <div>
            <h2 class="{{ $titleClass }}">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                {{ __('resellerhub::messages.profile.personal_info_title') }}
            </h2>
            <p class="{{ $descClass }}">
                {{ __('resellerhub::messages.profile.personal_info_desc') }}
            </p>
        </div>
    </div>

    {{-- محتوای فرم --}}
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{photoName: null, photoPreview: null}" class="md:col-span-2">
                <input type="file" id="photo" class="hidden"
                       wire:model.live="photo"
                       x-ref="photo"
                       x-on:change="
                            photoName = $refs.photo.files[0].name;
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                photoPreview = e.target.result;
                            };
                            reader.readAsDataURL($refs.photo.files[0]);
                    "/>

                <label class="{{ $labelClass }}">{{ __('resellerhub::messages.profile.photo_label') }}</label>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mt-2">
                    <!-- Current Profile Photo -->
                    <div x-show="! photoPreview">
                        <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}"
                             class="rounded-full h-20 w-20 object-cover ring-4 ring-gray-50 dark:ring-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm">
                    </div>

                    <!-- New Profile Photo Preview -->
                    <div x-show="photoPreview" style="display: none;">
                        <span
                            class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center ring-4 ring-gray-50 dark:ring-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm"
                            x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="{{ $btnSecondaryClass }}" x-on:click.prevent="$refs.photo.click()">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01v.01H14V8z"/>
                            </svg>
                            {{ __('resellerhub::messages.profile.select_new_photo') }}
                        </button>

                        @if ($this->user->profile_photo_path)
                            <button type="button"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 transition-colors border border-transparent dark:border-red-800 text-sm font-bold"
                                    wire:click="deleteProfilePhoto">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                {{ __('resellerhub::messages.profile.remove_photo') }}
                            </button>
                        @endif
                    </div>
                </div>
                <x-input-error for="photo" class="mt-2 text-xs text-red-500 font-bold"/>
            </div>
        @endif

        <!-- Name -->
        <div class="md:col-span-1">
            <label for="name" class="{{ $labelClass }}">{{ __('resellerhub::messages.profile.name_label') }} <span
                    class="text-red-500">*</span></label>
            <input id="name" type="text" class="{{ $inputClass }}" wire:model="state.name" required autocomplete="name"
                   placeholder="{{ __('resellerhub::messages.profile.name_placeholder') }}"/>
            <x-input-error for="name" class="mt-2 text-xs text-red-500 font-bold"/>
        </div>

        <!-- Email -->
        <div class="md:col-span-1">
            <label for="email" class="{{ $labelClass }}">{{ __('resellerhub::messages.profile.email_label') }} <span class="text-red-500">*</span></label>
            <div class="relative">
                <input id="email" type="email" class="{{ $inputClass }} dir-ltr text-left !pl-11"
                       wire:model="state.email" required autocomplete="username" placeholder="example@domain.com"/>
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <x-input-error for="email" class="mt-2 text-xs text-red-500 font-bold"/>

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                <div
                    class="p-3 mt-3 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-100 dark:border-amber-800 text-sm">
                    <p class="text-amber-800 dark:text-amber-300 font-medium">
                        {{ __('resellerhub::messages.profile.email_unverified') }}
                        <button type="button"
                                class="underline font-bold hover:text-amber-900 dark:hover:text-amber-100 focus:outline-none"
                                wire:click.prevent="sendEmailVerification">
                            {{ __('resellerhub::messages.profile.resend_verification') }}
                        </button>
                    </p>
                    @if ($this->verificationLinkSent)
                        <p class="mt-2 font-bold text-emerald-600 dark:text-emerald-400">
                            {{ __('resellerhub::messages.profile.verification_sent') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Mobile -->
        <div class="md:col-span-1">
            <label for="mobile" class="{{ $labelClass }}">{{ __('resellerhub::messages.profile.mobile_label') }}</label>
            <div class="relative">
                <input id="mobile" type="tel" class="{{ $inputClass }} dir-ltr text-left !pl-11"
                       wire:model="state.mobile" autocomplete="tel" placeholder="09123456789"/>
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
            </div>
            <x-input-error for="mobile" class="mt-2 text-xs text-red-500 font-bold"/>
        </div>

        <!-- Role (Readonly) -->
        <div class="md:col-span-1">
            <label class="{{ $labelClass }}">{{ __('resellerhub::messages.profile.roles_label') }}</label>
            <div class="flex flex-wrap gap-2 mt-1">
                @forelse($userRolesDisplay as $role)
                    <span
                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20">
                        {{ $role }}
                    </span>
                @empty
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('resellerhub::messages.profile.no_role') }}</span>
                @endforelse
            </div>
            <p class="mt-2 text-[11px] text-gray-400 dark:text-gray-500">{{ __('resellerhub::messages.profile.role_notice') }}</p>
        </div>

        <!-- Categorized Custom Fields Sections -->
        @if($customFields->isNotEmpty())
            @php
                $groupedFields = $customFields->groupBy(function($item) {
                    return CustomUserField::resolveCategoryGroup($item->field_name, $item->category_group ?? null);
                });

                $categoryMeta = [
                    'contact' => [
                        'title' => __('resellerhub::messages.profile.categories.contact_title'),
                        'desc' => __('resellerhub::messages.profile.categories.contact_desc'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />',
                        'badge_color' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300',
                    ],
                    'identity' => [
                        'title' => __('resellerhub::messages.profile.categories.identity_title'),
                        'desc' => __('resellerhub::messages.profile.categories.identity_desc'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />',
                        'badge_color' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-300',
                    ],
                    'business' => [
                        'title' => __('resellerhub::messages.profile.categories.business_title'),
                        'desc' => __('resellerhub::messages.profile.categories.business_desc'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />',
                        'badge_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300',
                    ],
                    'system' => [
                        'title' => __('resellerhub::messages.profile.categories.system_title'),
                        'desc' => __('resellerhub::messages.profile.categories.system_desc'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
                        'badge_color' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300',
                    ],
                    'general' => [
                        'title' => __('resellerhub::messages.profile.categories.general_title'),
                        'desc' => __('resellerhub::messages.profile.categories.general_desc'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
                        'badge_color' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-300',
                    ],
                ];
            @endphp

            @foreach($groupedFields as $grpKey => $fieldsInGroup)
                @php
                    $meta = $categoryMeta[$grpKey] ?? $categoryMeta['general'];
                @endphp
                <div class="md:col-span-2 pt-6 border-t border-gray-100 dark:border-gray-700 space-y-4">
                    <div
                        class="flex items-center justify-between gap-3 pb-2 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 rounded-xl {{ $meta['badge_color'] }} border shadow-2xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    {!! $meta['icon'] !!}
                                </svg>
                            </span>
                            <div>
                                <h3 class="{{ $titleClass }}">{{ $meta['title'] }}</h3>
                                <p class="{{ $descClass }}">{{ $meta['desc'] }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $meta['badge_color'] }} border">
                            {{ __('resellerhub::messages.profile.fields_count', ['count' => count($fieldsInGroup)]) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                        @foreach($fieldsInGroup as $field)
                            @php
                                $existing = $customValues->get($field->field_name)?->value ?? '';
                                $type = strtolower($field->field_type ?? 'text');
                                if ($type === 'file') continue;

                                $isFullWidth = ($type === 'textarea');
                                $opts = null;
                                if (is_array($field->meta)) {
                                    $opts = $field->meta['options'] ?? null;
                                } elseif (is_string($field->meta)) {
                                    $decodedMeta = json_decode($field->meta, true);
                                    $opts = is_array($decodedMeta) ? ($decodedMeta['options'] ?? null) : null;
                                }
                                $isLtrField = in_array($type, ['number', 'email', 'url', 'tel', 'color']) || in_array($field->field_name, ['kdmly', 'national_code', 'national_id', 'melli_code']);
                            @endphp

                            <div class="{{ $isFullWidth ? 'md:col-span-2' : 'md:col-span-1' }}">
                                <label for="custom_{{ $field->field_name }}" class="{{ $labelClass }}">
                                    {{ $field->label }}
                                    @if($field->is_required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>

                                @if($type === 'textarea')
                                    <textarea id="custom_{{ $field->field_name }}"
                                              wire:model="state.custom.{{ $field->field_name }}" rows="3"
                                              class="{{ $inputClass }} resize-none leading-relaxed"></textarea>

                                @elseif($type === 'file')
                                    <div
                                        class="flex items-center justify-center w-full p-4 border border-dashed rounded-xl border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/50">
                                        <div class="text-sm text-center text-gray-500 dark:text-gray-400">
                                            {{ __('resellerhub::messages.profile.file_readonly_notice') }}
                                            @if($existing && is_string($existing))
                                                <div
                                                    class="mt-2 text-indigo-600 dark:text-indigo-400 font-bold">{{ __('resellerhub::messages.profile.file_uploaded_notice') }}</div>
                                            @endif
                                        </div>
                                    </div>

                                @elseif($type === 'select' && is_array($opts))
                                    <div class="relative">
                                        <select id="custom_{{ $field->field_name }}"
                                                wire:model="state.custom.{{ $field->field_name }}"
                                                class="{{ $inputClass }} appearance-none cursor-pointer !pl-11">
                                            <option value="">{{ __('resellerhub::messages.profile.select_placeholder') }}</option>
                                            @foreach($opts as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                        <div
                                            class="pointer-events-none absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </div>

                                @elseif($type === 'radio' && is_array($opts))
                                    <div class="flex flex-wrap gap-4 pt-2">
                                        @foreach($opts as $opt)
                                            <label class="flex items-center gap-2 cursor-pointer group">
                                                <div class="relative flex items-center">
                                                    <input type="radio"
                                                           wire:model="state.custom.{{ $field->field_name }}"
                                                           value="{{ $opt }}" class="peer sr-only">
                                                    <div
                                                        class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all dark:border-gray-600"></div>
                                                    <div
                                                        class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                                </div>
                                                <span
                                                    class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ $opt }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                @elseif($type === 'checkbox' && is_array($opts))
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                        @foreach($opts as $opt)
                                            <label
                                                class="flex items-center gap-3 cursor-pointer p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-800 transition-colors">
                                                <input type="checkbox"
                                                       wire:model="state.custom.{{ $field->field_name }}"
                                                       value="{{ $opt }}"
                                                       class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <span
                                                    class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $opt }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                @else
                                    <input id="custom_{{ $field->field_name }}"
                                           type="{{ in_array($type, $inputTypes) ? $type : 'text' }}"
                                           class="{{ $inputClass }} {{ $isLtrField ? 'dir-ltr text-left font-mono' : '' }}"
                                           wire:model="state.custom.{{ $field->field_name }}"/>
                                @endif

                                <x-input-error for="custom.{{ $field->field_name }}"
                                               class="mt-2 text-xs text-red-500 font-bold"/>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- فوتر و ذخیره --}}
    <div class="{{ $footerClass }}">
        <x-action-message class="me-3 text-sm font-bold text-emerald-600 dark:text-emerald-400" on="saved">
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round"
                                                                                                 stroke-linejoin="round"
                                                                                                 stroke-width="2"
                                                                                                 d="M5 13l4 4L19 7"></path></svg>
                {{ __('resellerhub::messages.profile.saved_success') }}
            </span>
        </x-action-message>

        <button type="submit" wire:loading.attr="disabled" class="{{ $btnPrimaryClass }}">
            <span wire:loading.remove wire:target="updateProfileInformation" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round"
                                                                                                 stroke-linejoin="round"
                                                                                                 stroke-width="2"
                                                                                                 d="M5 13l4 4L19 7"/></svg>
                {{ __('resellerhub::messages.profile.save_changes') }}
            </span>
            <span wire:loading wire:target="updateProfileInformation" class="flex items-center gap-2">
                <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                {{ __('resellerhub::messages.profile.saving') }}
            </span>
        </button>
    </div>
</form>
