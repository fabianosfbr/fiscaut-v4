@php
    use Filament\Support\Enums\GridDirection;

    $fieldWrapperView = $getFieldWrapperView();
    $extraInputAttributeBag = $getExtraInputAttributeBag();
    $isHtmlAllowed = $isHtmlAllowed();
    $gridDirection = $getGridDirection() ?? GridDirection::Column;
    $isBulkToggleable = $isBulkToggleable();
    $isDisabled = $isDisabled();
    $isSearchable = $isSearchable();
    $statePath = $getStatePath();
    $options = $getOptions();
    $livewireKey = $getLivewireKey();
    $wireModelAttribute = $applyStateBindingModifiers('wire:model');
    $normalizedOptions = $getNormalizedOptions();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            container: null,
            componentId: @js($this->getId()),
            areAllCheckboxesChecked: false,
            checkboxListOptions: [],
            search: '',
            visibleCheckboxListOptions: [],

            init() {
                this.container = this.$root
                this.refreshCheckboxListOptions()
                this.updateVisibleCheckboxListOptions()

                this.$nextTick(() => {
                    this.checkIfAllCheckboxesAreChecked()
                })

                Livewire.hook('commit', ({ component, succeed }) => {
                    succeed(() => {
                        this.$nextTick(() => {
                            if (component.id !== this.componentId) {
                                return
                            }

                            this.refreshCheckboxListOptions()
                            this.updateVisibleCheckboxListOptions()
                            this.checkIfAllCheckboxesAreChecked()
                        })
                    })
                })

                this.$watch('search', () => {
                    this.updateVisibleCheckboxListOptions()
                    this.checkIfAllCheckboxesAreChecked()
                })
            },

            refreshCheckboxListOptions() {
                this.checkboxListOptions = Array.from(
                    this.container.querySelectorAll('[data-checkbox-list-option]'),
                )
            },

            matchesSearch(checkboxListItem) {
                if (! this.search) {
                    return true
                }

                const searchTerm = this.search.toLowerCase()
                const searchableText = [
                    checkboxListItem.dataset.searchLabel ?? '',
                    checkboxListItem.dataset.searchDescription ?? '',
                    checkboxListItem.dataset.categoryLabel ?? '',
                ]
                    .join(' ')
                    .toLowerCase()

                return searchableText.includes(searchTerm)
            },

            checkIfAllCheckboxesAreChecked: function () {
                this.areAllCheckboxesChecked =
                    this.visibleCheckboxListOptions.length > 0 &&
                    this.visibleCheckboxListOptions.length ===
                    this.visibleCheckboxListOptions.filter((checkboxLabel) =>
                        checkboxLabel.querySelector(
                            'input[type=checkbox]:checked, input[type=checkbox]:disabled',
                        ),
                    ).length
            },

            toggleAllCheckboxes: function () {
                const state = ! this.areAllCheckboxesChecked

                this.visibleCheckboxListOptions.forEach((checkboxLabel) => {
                    const checkbox = checkboxLabel.querySelector('input[type=checkbox]')

                    if (checkbox.disabled) {
                        return
                    }

                    checkbox.checked = state
                    checkbox.dispatchEvent(new Event('change', { bubbles: true }))
                })

                this.areAllCheckboxesChecked = state
            },

            updateVisibleCheckboxListOptions: function () {
                this.visibleCheckboxListOptions = this.checkboxListOptions.filter(
                    (checkboxListItem) => this.matchesSearch(checkboxListItem),
                )
            },

            getCategoryOptions(categoryKey) {
                return Array.from(
                    this.container.querySelectorAll(
                        `[data-checkbox-list-option][data-category-key='${categoryKey}']`,
                    ),
                )
            },

            areAllCategoryCheckboxesChecked(categoryKey) {
                const categoryOptions = this.getCategoryOptions(categoryKey).filter((checkboxListItem) =>
                    this.matchesSearch(checkboxListItem),
                )

                return (
                    categoryOptions.length > 0 &&
                    categoryOptions.every((checkboxLabel) => {
                        const checkbox = checkboxLabel.querySelector('input[type=checkbox]')

                        return checkbox && (checkbox.checked || checkbox.disabled)
                    })
                )
            },

            toggleCategoryCheckboxes(categoryKey) {
                const categoryOptions = this.getCategoryOptions(categoryKey).filter((checkboxListItem) =>
                    this.matchesSearch(checkboxListItem),
                )

                if (! categoryOptions.length) {
                    return
                }

                const state = ! this.areAllCategoryCheckboxesChecked(categoryKey)

                categoryOptions.forEach((checkboxLabel) => {
                    const checkbox = checkboxLabel.querySelector('input[type=checkbox]')

                    if (! checkbox || checkbox.disabled) {
                        return
                    }

                    checkbox.checked = state
                    checkbox.dispatchEvent(new Event('change', { bubbles: true }))
                })

                this.checkIfAllCheckboxesAreChecked()
            },

            isCategoryVisible(categoryKey) {
                return this.getCategoryOptions(categoryKey).some((checkboxListItem) =>
                    this.matchesSearch(checkboxListItem),
                )
            },
        }"
    >

        
        @if (! $isDisabled)
            @if ($isSearchable)
                <x-filament::input.wrapper
                    inline-prefix
                    prefix-icon="heroicon-m-magnifying-glass"
                    prefix-icon-alias="forms:components.checkbox-list.search-field"
                    class="mb-4"
                >
                    <x-filament::input
                        inline-prefix
                        :placeholder="$getSearchPrompt()"
                        type="search"
                        :attributes="
                            \Filament\Support\prepare_inherited_attributes(
                                new \Illuminate\View\ComponentAttributeBag([
                                    'x-model.debounce.' . $getSearchDebounce() => 'search',
                                ])
                            )
                        "
                    />
                </x-filament::input.wrapper>
            @endif

            @if ($isBulkToggleable && count($normalizedOptions))
                <div
                    x-cloak
                    class="mb-2"
                    wire:key="{{ $this->getId() }}.{{ $getStatePath() }}.{{ $field::class }}.actions"
                >
                    <span
                        x-show="! areAllCheckboxesChecked"
                        x-on:click="toggleAllCheckboxes()"
                        wire:key="{{ $this->getId() }}.{{ $statePath }}.{{ $field::class }}.actions.select-all"
                    >
                        {{ $getAction('selectAll') }}
                    </span>

                    <span
                        x-show="areAllCheckboxesChecked"
                        x-on:click="toggleAllCheckboxes()"
                        wire:key="{{ $this->getId() }}.{{ $statePath }}.{{ $field::class }}.actions.deselect-all"
                    >
                        {{ $getAction('deselectAll') }}
                    </span>
                </div>
            @endif
        @endif

         <div
            {{
                $getExtraAttributeBag()
                    ->grid($getColumns(), $gridDirection)
                    ->merge([
                        'x-show' => $isSearchable ? 'visibleCheckboxListOptions.length' : null,
                    ], escape: false)
                    ->class([
                        'fi-fo-checkbox-list-options',
                    ])
            }}
        >
             @forelse ($normalizedOptions as $group)
                
                <div
                    wire:key="{{ $this->getId() }}.{{ $statePath }}.{{ $field::class }}.group.{{ $group['key'] }}"
                    data-checkbox-list-category="{{ $group['key'] }}"
                    data-category-label="{{ $group['label'] ?? '' }}"
                    @if ($isSearchable && filled($group['label']))
                        x-show="isCategoryVisible(@js($group['key']))"
                    @endif
                    x-cloak
                    @class([
                        'break-inside-avoid' => $gridDirection === 'column',
                        'p-4' => filled($group['label']),
                        'pt-4' => $gridDirection === 'column' && blank($group['label']),
                    ])
                >
                    @if (filled($group['label']))
                        <button
                            type="button"
                            class="mb-4 flex w-full items-start justify-between gap-3 rounded-lg bg-gray-50 px-3 py-2 text-left dark:bg-black/20"
                            @disabled($isDisabled)
                            x-on:click="toggleCategoryCheckboxes(@js($group['key']))"
                        >
                            <span class="grid text-sm leading-5">
                                <span class="font-semibold text-gray-950 dark:text-white">
                                    {{ $group['label'] }}
                                </span>                     
                            </span>

                            <span
                                class="shrink-0 text-xs font-medium text-primary-600 dark:text-primary-400"
                                x-text="
                                    areAllCategoryCheckboxesChecked(@js($group['key']))
                                        ? 'X'
                                        : 'X'
                                "
                            >
                            </span>
                        </button>
                    @endif

                    <div class="space-y-3">
                        @foreach ($group['children'] as $option)
                            <div
                                wire:key="{{ $this->getId() }}.{{ $statePath }}.{{ $field::class }}.options.{{ $group['key'] }}.{{ $option['value'] }}"
                                data-checkbox-list-option
                                data-category-key="{{ $group['key'] }}"
                                data-category-label="{{ $group['label'] ?? '' }}"
                                data-search-label="{{ $option['label'] }}"
                                data-search-description="{{ $hasDescription($option['value']) ? $getDescription($option['value']) : '' }}"
                                @if ($isSearchable)
                                    x-show="matchesSearch($el)"
                                @endif
                                x-cloak
                            >
                                <label class="fi-fo-checkbox-list-option flex items-start gap-3">
                                    <span class="pt-0.5">
                                        <x-filament::input.checkbox
                                        :valid="! $errors->has($statePath)"
                                        :attributes="
                                            \Filament\Support\prepare_inherited_attributes($getExtraInputAttributeBag())
                                                ->merge([
                                                    'disabled' => $isDisabled || $isOptionDisabled($option['value'], $option['label']),
                                                    'value' => $option['value'],
                                                    'wire:loading.attr' => 'disabled',
                                                    $applyStateBindingModifiers('wire:model') => $statePath,
                                                    'x-on:change' => ($isBulkToggleable || filled($group['label'])) ? 'checkIfAllCheckboxesAreChecked()' : null,
                                                ], escape: false)
                                                ->class([
                                                    'fi-checkbox-input',
                                                    'fi-valid' => ! $errors->has($statePath),
                                                    'fi-invalid' => $errors->has($statePath),
                                                ])
                                        "
                                    />
                                    </span>

                                    <div class="fi-fo-checkbox-list-option-text">
                                         <span class="fi-fo-checkbox-list-option-label">

                                            @if ($isHtmlAllowed)
                                                {!! $option['label'] !!}
                                            @else
                                                {{ $option['label'] }}
                                            @endif
                                        
                                                                                           
                                        </span>

                                        @if ($hasDescription($option['value']))
                                            <p
                                                class="fi-fo-checkbox-list-option-description"
                                            >
                                                {{ $getDescription($option['value']) }}
                                            </p>
                                        @endif
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>            

             @empty
                <div
                    wire:key="{{ $this->getId() }}.{{ $statePath }}.{{ $field::class }}.empty"
                ></div>
            @endforelse

        </div>

        @if ($isSearchable)
            <div
                x-cloak
                x-show="search && ! visibleCheckboxListOptions.length"
                class="fi-fo-checkbox-list-no-search-results-message"
            >
                {{ $getNoSearchResultsMessage() }}
            </div>
        @endif
    </div>
</x-dynamic-component>
