<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field">
    <div class="fi-form-component-select-tag-grouped">
        <div
            x-data="{
                state: $wire.$entangle(@js($getStatePath())),
                options: @js($field->getProcessedOptions() ?? []),
                placeholder: @js($field->getPlaceholder() ?? 'Selecione as etiquetas...'),
                searchPlaceholder: @js($field->getSearchPlaceholder()),
                multiple: @js($field->getMultiple()),
                searchable: @js($field->getSearchable()),
                clearable: @js($field->getClearable()),
                disabled: @js($field->isDisabled()),
                tomSelect: null,
                updating: false,
                debounceTimeout: null,
                init() {

                     this.$nextTick(() => {
                        this.initTomSelect();                       
                        const tomSelectInstance = this.tomSelect || (this.$refs.tomselect && this.$refs.tomselect.tomselect);
                        if (tomSelectInstance) {
                            if (this.disabled) {
                                tomSelectInstance.disable();
                            } else {
                                tomSelectInstance.enable();
                            }
                        }
                    });
                },
                initTomSelect() {
                if (this.tomSelect || (this.$refs.tomselect && this.$refs.tomselect.tomselect)) {
                        return;
                    }

                    if (typeof TomSelect === 'undefined') {
                        console.error('❌ TomSelect library not loaded');
                        return;
                    }

                    const selectElement = this.$refs.tomselect;

                    if (!selectElement) {
                        console.error('❌ TomSelect element not found');
                        return;
                    }

                    if (!this.options || this.options.length === 0) {
                        console.warn('⚠️ No options available for SelectTagGrouped');
                        return;
                    }
                    
                    const formattedOptions = [];
                    const optgroups = [];

                    this.options.forEach((category, index) => {
                        optgroups.push({
                            value: category.text,
                            label: category.text
                        });

                        if (category.children && category.children.length > 0) {
                            category.children.forEach(tag => {
                                formattedOptions.push({
                                    value: tag.id,
                                    text: tag.display,
                                    code: tag.code,
                                    name: tag.name,
                                    color: tag.color,
                                    optgroup: category.text,
                                    category_id: tag.category_id,
                                    category_name: tag.category_name
                                });
                            });
                        }
                    });
       
                    this.tomSelect = new TomSelect(selectElement, {
                        plugins: this.multiple ? ['remove_button', 'clear_button'] : [],
                        items: Array.isArray(this.state) ? this.state : (this.state ? [this.state] : []),
                        options: formattedOptions,
                        optgroups: optgroups,
                        optgroupField: 'optgroup',
                        valueField: 'value',
                        labelField: 'text',
                        searchField: this.searchable ? ['code', 'name', 'text'] : [],
                        placeholder: this.placeholder,
                        hidePlaceholder: this.multiple,
                        maxItems: this.multiple ? null : 1,
                        maxOptions: 500,
                        create: false,
                        closeAfterSelect: !this.multiple,
                        allowEmptyOption: this.clearable,
                        onChange: (value) => {
                            if (!this.updating) {
                                this.updating = true;
                                const selectedValues = this.tomSelect.getValue();
                                if (!selectedValues || selectedValues === '') {
                                    this.state = this.multiple ? [] : null;
                                } else if (this.multiple) {
                                    let arrayValues = [];
                                    if (typeof selectedValues === 'string') {
                                        arrayValues = selectedValues.split(',').filter(v => v !== '');
                                    } else if (Array.isArray(selectedValues)) {
                                        arrayValues = selectedValues;
                                    } else {
                                        arrayValues = [selectedValues];
                                    }
                                    this.state = arrayValues.map(v => String(v));
                                } else {
                                    this.state = Array.isArray(selectedValues) ? String(selectedValues[0]) : String(selectedValues);
                                }
                                this.$nextTick(() => {
                                    this.updating = false;
                                });
                            }
                        },
                        onInitialize: () => {
                            setTimeout(() => {
                                if (this.tomSelect && this.tomSelect.control_input && this.searchPlaceholder) {
                                    this.tomSelect.control_input.placeholder = this.searchPlaceholder;
                                }
                            }, 100);
                        },                        
                    });

                    this.$watch('state', (newValue) => {
                        if (!this.updating && this.tomSelect) {
                            this.updating = true;
                            this.tomSelect.setValue(newValue || []);
                            this.$nextTick(() => {
                                this.updating = false;
                            });
                        }
                    });
                },
            }"
            x-init="init()"
            {{ $getExtraAttributeBag() }}>

            <div wire:ignore>
                <select
                    x-ref="tomselect"
                    :id="$id('tom-select')"
                    {{ $getMultiple() ? 'multiple' : '' }}
                    {{ $isDisabled() ? 'disabled' : '' }}
                    style="display: none;">
                </select>
            </div>
        </div>
    </div>

</x-dynamic-component>