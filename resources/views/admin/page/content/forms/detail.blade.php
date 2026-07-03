<div class="bg-white rounded-2xl overflow-hidden" x-data="{ formTab: 'edit', fieldModal: false }" x-on:form-field-saved.window="fieldModal = false" x-on:open-field-editor.window="fieldModal = true">
    <div class="bg-stone-200/70 flex items-center justify-between gap-4 px-4 py-3 border-b border-stone-200">
        <div class="flex items-center gap-3 min-w-0">
            <a
                href="{{ route('admin.page.home', ['tab' => $contentType['tab_id']]) }}"
                wire:navigate
                title="{{ __('Back') }}"
                class="bg-white p-2 rounded-lg shadow-sm hover:bg-gray-50 flex items-center justify-center shrink-0"
            >
                <ui:icon name="arrow-right" class="!w-5 !h-5 text-gray-600" />
            </a>
            <div class="flex items-center gap-2 min-w-0 text-sm text-gray-700">
                <img src="{{ asset($contentType['icon']) }}" class="w-5 h-5 shrink-0" alt="">
                <span class="font-semibold truncate">{{ $contentType['name'] }}</span>
                <span class="text-gray-400">/</span>
                <span class="text-gray-600 truncate">تحرير النموذج</span>
            </div>
        </div>

        <nav class="flex items-center gap-1 shrink-0 bg-gray-300/40 rounded-xl p-0.5">
            <button
                type="button"
                x-on:click="formTab = 'edit'"
                x-bind:class="formTab === 'edit'
                    ? 'bg-white text-gray-900 shadow-sm font-semibold'
                    : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition"
            >
                <ui:icon name="Pencil" class="!w-4 !h-4 shrink-0" />
                تحرير
            </button>
            <button
                type="button"
                x-on:click="formTab = 'advanced'"
                x-bind:class="formTab === 'advanced'
                    ? 'bg-white text-gray-900 shadow-sm font-semibold'
                    : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition"
            >
                <ui:icon name="adjustments-horizontal" class="!w-4 !h-4 shrink-0" />
                متقدم
            </button>
        </nav>
    </div>

    <ui:form wire:submit="save" class="!p-4 md:!p-6 !rounded-none">
        <div x-cloak x-show="formTab === 'edit'" class="space-y-4">
            <ui:input name="title" label="اسم النموذج" placeholder="مثال: نموذج تواصل" />

            <ui:textarea
                name="description"
                label="وصف النموذج"
                placeholder="وصف مختصر يظهر للمستخدم قبل تعبئة النموذج"
                info="يُحفظ في data.description ويمكن استخدامه عند عرض النموذج في الموقع."
            />

            <div class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">حقول النموذج</p>
                        <p class="text-xs text-gray-400 mt-0.5">رتّب الحقول بالسحب والإفلات.</p>
                    </div>

                    <div x-data="{ addFieldOpen: false }" class="relative shrink-0">
                        <button
                            type="button"
                            x-on:click="addFieldOpen = !addFieldOpen"
                            class="inline-flex items-center cursor-pointer transition-all duration-300 justify-center gap-2 whitespace-nowrap rounded-md text-sm h-9 px-4 py-2 bg-primary-600 text-white hover:bg-primary-700"
                        >
                            <ui:icon name="square-rounded-plus" class="!w-4 !h-4" />
                            إضافة حقل
                            <ui:icon name="chevron-down" class="!w-4 !h-4 opacity-75" />
                        </button>

                        <div
                            x-show="addFieldOpen"
                            x-on:click.outside="addFieldOpen = false"
                            x-cloak
                            x-transition.scale.origin.top
                            class="absolute z-50 mt-2 min-w-48 rounded-lg border border-gray-100 bg-white p-1 shadow-lg ltr:right-0 rtl:left-0"
                        >
                            @foreach ($fieldTypeOptions as $type => $typeLabel)
                                <button
                                    type="button"
                                    wire:click="addField('{{ $type }}')"
                                    x-on:click="addFieldOpen = false"
                                    class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 transition text-start"
                                >
                                    <ui:icon name="{{ \App\Support\FormField::typeIcon($type) }}" class="!w-4 !h-4 text-gray-400 shrink-0" />
                                    {{ $typeLabel }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if ($fields === [])
                    <ui:empty subtitle="أضف حقولاً لبناء النموذج.">
                        لا توجد حقول بعد.
                        <x-slot:icon>
                            <ui:icon name="clipboard-list" class="!w-12 !h-12 opacity-40" />
                        </x-slot:icon>
                    </ui:empty>
                @else
                    <ul
                        wire:sortable="updateFieldOrder"
                        wire:sortable.options="{ animation: 150 }"
                        class="space-y-1.5"
                    >
                        @foreach ($fields as $index => $field)
                            <li
                                wire:sortable.item="{{ $field['id'] }}"
                                wire:key="form-field-{{ $field['id'] }}"
                                class="group flex items-center gap-2 rounded-lg border border-gray-100 bg-white px-2 py-2 hover:border-gray-200 transition"
                            >
                                <button
                                    type="button"
                                    wire:sortable.handle
                                    class="cursor-grab active:cursor-grabbing rounded-md p-1 text-gray-300 hover:bg-gray-100 hover:text-gray-500 transition"
                                    aria-label="سحب لإعادة الترتيب"
                                >
                                    <ui:icon name="grip-vertical" class="!w-4 !h-4" />
                                </button>

                                <ui:icon name="{{ \App\Support\FormField::typeIcon($field['type']) }}" class="!w-5 !h-5 text-gray-400 shrink-0" />

                                <button
                                    type="button"
                                    wire:click="openFieldEditor('{{ $field['id'] }}')"
                                    x-on:click="fieldModal = true"
                                    class="flex flex-1 min-w-0 flex-col items-start text-start hover:text-primary-600 transition"
                                >
                                    <span class="text-sm font-medium text-gray-800 truncate">
                                        {{ filled($field['label']) ? $field['label'] : 'حقل بدون عنوان' }}
                                    </span>
                                    <span class="text-xs text-gray-500 truncate font-mono" dir="ltr">
                                        {{ $field['name'] }}
                                    </span>
                                    <span class="text-xs text-gray-400 truncate">
                                        {{ \App\Support\FormField::typeLabel($field['type']) }}
                                        @if ($field['required'])
                                            · مطلوب
                                        @endif
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    wire:click="deleteField('{{ $field['id'] }}')"
                                    wire:confirm="هل أنت متأكد من حذف هذا الحقل؟"
                                    class="shrink-0 rounded-lg p-1 text-red-400/80 hover:bg-red-50 hover:text-red-500 transition"
                                    aria-label="حذف الحقل"
                                >
                                    <ui:icon name="trash" class="!w-4 !h-4" />
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div x-cloak x-show="formTab === 'advanced'" class="space-y-2">
            <ui:input
                name="slug"
                label="نص الرابط"
                dir="ltr"
                :prefix="$slugPrefix"
            />

            <ui:toggle name="published" label="حالة النشر" />

            <ui:input
                name="submitLabel"
                label="نص زر الإرسال"
                placeholder="إرسال"
                info="يُحفظ في data.submit_label"
            />

            <ui:textarea
                name="successMessage"
                label="رسالة النجاح"
                placeholder="شكراً! تم استلام طلبك بنجاح."
                info="يُحفظ في data.success_message"
            />
        </div>

        <x-slot:footer>
            <div class="flex items-center gap-2">
                <ui:button wire:click="saveAndClose" type="button" target="saveAndClose" label="حفظ وإغلاق" />
                <ui:button type="submit" target="save" label="{{ __('Save') }}" />
            </div>
        </x-slot:footer>
    </ui:form>

    <div
        x-show="fieldModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition.opacity
    >
        <div class="absolute inset-0 bg-gray-800/75" x-on:click="fieldModal = false"></div>

        <div class="relative w-full max-w-lg rounded-xl bg-white shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 p-3 px-4 sticky top-0 bg-white z-10">
                <p class="text-sm font-semibold text-gray-600">تعديل الحقل</p>
                <button type="button" x-on:click="fieldModal = false" class="rounded-md bg-gray-100 p-1 text-gray-400 hover:bg-gray-200">
                    <ui:icon name="x" class="!w-4 !h-4" />
                </button>
            </div>

            @if ($editingFieldIndex !== null)
                @php($field = $fields[$editingFieldIndex])
                <div class="space-y-3 p-4">
                    <ui:select
                        name="fields.{{ $editingFieldIndex }}.type"
                        label="نوع الحقل"
                        :options="$fieldTypeOptions"
                      
                    />

                    <ui:input
                        name="fields.{{ $editingFieldIndex }}.label"
                        label="عنوان الحقل"
                        placeholder="مثال: الاسم الكامل"
                        live.debounce.500ms
                    />

                    <ui:input
                        name="fields.{{ $editingFieldIndex }}.name"
                        label="اسم الحقل (name)"
                        placeholder="full_name"
                        dir="ltr"
                        
                        info="يُستخدم كمفتاح الحقل عند بناء النموذج. أحرف إنجليزية صغيرة وأرقام و _ فقط."
                    />

                    @if ($field['type'] !== 'checkbox')
                        <ui:input
                            name="fields.{{ $editingFieldIndex }}.placeholder"
                            label="نص توضيحي"
                            placeholder="اكتب هنا..."
                        />
                    @endif

                    <ui:toggle
                        name="fields.{{ $editingFieldIndex }}.required"
                        label="حقل مطلوب"
                     
                    />

                    <ui:textarea
                        name="fields.{{ $editingFieldIndex }}.info"
                        label="نص مساعد"
                        placeholder="تعليمات إضافية تظهر تحت الحقل"
                    />

                    @if (\App\Support\FormField::hasOptions($field['type']))
                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-gray-600 px-1">الخيارات</p>

                            @foreach ($field['options'] as $optionIndex => $option)
                                <div wire:key="field-option-{{ $field['id'] }}-{{ $optionIndex }}" class="flex items-center gap-2">
                                    <ui:input
                                        name="fields.{{ $editingFieldIndex }}.options.{{ $optionIndex }}"
                                        placeholder="خيار {{ $optionIndex + 1 }}"
                                        class="flex-1"
                                    />
                                    <button
                                        type="button"
                                        wire:click="removeOption('{{ $field['id'] }}', {{ $optionIndex }})"
                                        class="shrink-0 rounded-lg p-2 text-red-400 hover:bg-red-50 hover:text-red-500 transition"
                                    >
                                        <ui:icon name="x" class="!w-4 !h-4" />
                                    </button>
                                </div>
                            @endforeach

                            <ui:button
                                type="button"
                                wire:click="addOption('{{ $field['id'] }}')"
                                variant="secondary"
                                icon="plus"
                                label="إضافة خيار"
                                class="w-full"
                            />
                        </div>
                    @endif

                    <div class="flex justify-end pt-2">
                        <ui:button
                            type="button"
                            x-on:click="fieldModal = false; $dispatch('form-field-saved')"
                            label="تم"
                        />
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<?php

use App\Models\Content;
use App\Support\FormField;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

new class extends \Livewire\Component
{
    /** @var array<string, mixed> */
    public array $contentType = [];

    public string $itemId = '';

    public string $title = '';

    public string $description = '';

    public string $slug = '';

    public bool $published = false;

    public string $submitLabel = 'إرسال';

    public string $successMessage = '';

    /** @var list<array<string, mixed>> */
    public array $fields = [];

    public ?string $editingFieldId = null;

    public function mount(): void
    {
        $content = $this->content();

        $this->title = $content->title;
        $this->description = (string) data_get($content->data, 'description', '');
        $this->slug = $content->slug;
        $this->published = $content->status === 'published';
        $this->submitLabel = (string) data_get($content->data, 'submit_label', 'إرسال');
        $this->successMessage = (string) data_get($content->data, 'success_message', '');
        $this->fields = FormField::normalize(data_get($content->data, 'fields'));
    }

    public function content(): Content
    {
        return Content::query()
            ->type(contentTypeModel($this->contentType['slug']))
            ->where('uuid', $this->itemId)
            ->firstOrFail();
    }

    /**
     * @return array<string, string>
     */
    public function fieldTypeOptions(): array
    {
        return FormField::typeOptions();
    }

    public function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/forms/';
    }

    public function editingFieldIndex(): ?int
    {
        if ($this->editingFieldId === null) {
            return null;
        }

        foreach ($this->fields as $index => $field) {
            if ($field['id'] === $this->editingFieldId) {
                return $index;
            }
        }

        return null;
    }

    public function updated(string $property, mixed $value): void
    {
        if (preg_match('/^fields\.(\d+)\.type$/', $property, $matches)) {
            $index = (int) $matches[1];

            if (! isset($this->fields[$index])) {
                return;
            }

            if (FormField::hasOptions((string) $value) && $this->fields[$index]['options'] === []) {
                $this->fields[$index]['options'] = ['', ''];
            }

            if (! FormField::hasOptions((string) $value)) {
                $this->fields[$index]['options'] = [];
            }
        }

        if (preg_match('/^fields\.(\d+)\.label$/', $property, $matches)) {
            $index = (int) $matches[1];

            if (! isset($this->fields[$index])) {
                return;
            }

            $field = $this->fields[$index];

            if ($field['name'] === $field['id'] || blank($field['name'])) {
                $slug = Str::slug((string) $value);
                $this->fields[$index]['name'] = $slug !== '' ? $slug : $field['id'];
            }
        }
        if (preg_match('/^fields\.(\d+)\.name$/', $property, $matches)) {
            $index = (int) $matches[1];

            if (! isset($this->fields[$index])) {
                return;
            }

            $this->fields[$index]['name'] = Str::lower(Str::replace('-', '_', (string) $value));
        }
    }

    public function addField(string $type): void
    {
        if (! array_key_exists($type, FormField::typeOptions())) {
            return;
        }

        $field = FormField::make($type);
        $this->fields[] = $field;
        $this->editingFieldId = $field['id'];
        $this->dispatch('open-field-editor');
    }

    public function openFieldEditor(string $fieldId): void
    {
        $this->editingFieldId = $fieldId;
    }

    public function deleteField(string $fieldId): void
    {
        $this->fields = collect($this->fields)
            ->reject(fn (array $field): bool => $field['id'] === $fieldId)
            ->values()
            ->all();

        if ($this->editingFieldId === $fieldId) {
            $this->editingFieldId = null;
        }
    }

    /**
     * @param  list<array{value: string, order: int}>  $items
     */
    public function updateFieldOrder(array $items): void
    {
        $orderedIds = collect($items)
            ->sortBy('order')
            ->pluck('value')
            ->all();

        $fieldsById = collect($this->fields)->keyBy('id');

        $this->fields = collect($orderedIds)
            ->map(fn (string $id): ?array => $fieldsById->get($id))
            ->filter()
            ->values()
            ->all();
    }

    public function addOption(string $fieldId): void
    {
        $index = $this->fieldIndex($fieldId);

        if ($index === null) {
            return;
        }

        $this->fields[$index]['options'][] = '';
    }

    public function removeOption(string $fieldId, int $optionIndex): void
    {
        $index = $this->fieldIndex($fieldId);

        if ($index === null) {
            return;
        }

        unset($this->fields[$index]['options'][$optionIndex]);
        $this->fields[$index]['options'] = array_values($this->fields[$index]['options']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => 'required|min:1|max:255',
            'description' => 'nullable|string|max:1000',
            'slug' => 'required|string|max:255',
            'published' => 'boolean',
            'submitLabel' => 'nullable|string|max:100',
            'successMessage' => 'nullable|string|max:500',
            'fields' => 'array',
            'fields.*.type' => ['required', Rule::in(array_keys(FormField::typeOptions()))],
            'fields.*.label' => 'required|string|max:255',
            'fields.*.name' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/'],
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.required' => 'boolean',
            'fields.*.info' => 'nullable|string|max:500',
            'fields.*.options' => 'nullable|array',
            'fields.*.options.*' => 'nullable|string|max:255',
        ];
    }

    public function save(): void
    {
        $this->persist(close: false);
    }

    public function saveAndClose(): void
    {
        $this->persist(close: true);
    }

    public function persist(bool $close = false): mixed
    {
        $this->validate();
        $this->validateUniqueFieldNames();

        $content = $this->content();
        $data = $content->data ?? [];

        $data['description'] = $this->description;
        $data['fields'] = FormField::forStorage($this->fields);
        $data['submit_label'] = $this->submitLabel !== '' ? $this->submitLabel : 'إرسال';
        $data['success_message'] = $this->successMessage;

        $slug = $this->uniqueSlug(
            filled($this->slug) ? $this->slug : Str::slug($this->title),
            (int) $content->id,
        );

        $status = $this->published ? 'published' : 'draft';

        $content->update([
            'title' => $this->title,
            'slug' => $slug,
            'status' => $status,
            'data' => $data,
            'published_at' => $this->published
                ? ($content->published_at ?? now())
                : null,
        ]);

        $this->slug = $slug;
        $this->dispatch('updateFormsList');
        $this->dispatch('notify', text: __('Saved'));

        if ($close) {
            return $this->redirect(route('admin.page.home', [
                'tab' => $this->contentType['tab_id'],
            ]), navigate: true);
        }

        return null;
    }

    private function validateUniqueFieldNames(): void
    {
        $names = collect($this->fields)->pluck('name')->filter();

        if ($names->count() !== $names->unique()->count()) {
            $this->addError('fields', 'يجب أن تكون أسماء الحقول (name) فريدة داخل النموذج.');

            throw \Illuminate\Validation\ValidationException::withMessages([
                'fields' => 'يجب أن تكون أسماء الحقول (name) فريدة داخل النموذج.',
            ]);
        }
    }

    private function uniqueSlug(string $baseSlug, int $exceptId): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'form';
        $counter = 1;

        while (
            Content::query()
                ->where('slug', $slug)
                ->whereKeyNot($exceptId)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function fieldIndex(string $fieldId): ?int
    {
        foreach ($this->fields as $index => $field) {
            if ($field['id'] === $fieldId) {
                return $index;
            }
        }

        return null;
    }

    public function render()
    {
        return $this->view([
            'content' => $this->content(),
            'slugPrefix' => $this->slugPrefix(),
            'fieldTypeOptions' => $this->fieldTypeOptions(),
            'editingFieldIndex' => $this->editingFieldIndex(),
        ]);
    }
}; ?>
