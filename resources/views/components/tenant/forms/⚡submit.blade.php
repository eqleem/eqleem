<div>
    @if ($submitted)
        <div class="space-y-3 text-right">
            <p class="text-sm font-semibold text-emerald-700">تم إرسال النموذج بنجاح.</p>
            <p class="text-sm leading-relaxed text-stone-600">شكراً لتواصلك، سنرد عليك في أقرب وقت.</p>
        </div>
    @elseif ($fields === [])
        <p class="text-sm leading-relaxed text-stone-500 text-right">هذا النموذج لا يحتوي على حقول بعد.</p>
    @else
        <form wire:submit="submit" class="space-y-4 text-right">
            @if (filled($description))
                <p class="text-sm leading-relaxed text-stone-600">{{ $description }}</p>
            @endif

            @foreach ($fields as $field)
                @php
                    $name = $field['name'];
                    $inputClass = 'w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none';
                @endphp

                <div class="space-y-1" wire:key="form-field-{{ $formContentId }}-{{ $field['id'] }}">
                    @if ($field['type'] !== 'checkbox')
                        <label for="field-{{ $formContentId }}-{{ $field['id'] }}" class="text-sm font-medium text-stone-700">
                            {{ $field['label'] ?: $field['name'] }}
                            @if ($field['required'])
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                    @endif

                    @switch($field['type'])
                        @case('textarea')
                            <textarea
                                id="field-{{ $formContentId }}-{{ $field['id'] }}"
                                wire:model="values.{{ $name }}"
                                rows="4"
                                @if (filled($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
                                class="{{ $inputClass }}"
                            ></textarea>
                            @break

                        @case('select')
                            <select
                                id="field-{{ $formContentId }}-{{ $field['id'] }}"
                                wire:model="values.{{ $name }}"
                                class="{{ $inputClass }}"
                            >
                                <option value="">اختر...</option>
                                @foreach ($field['options'] as $option)
                                    @if (filled($option))
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @break

                        @case('radio')
                            <div class="space-y-2">
                                @foreach ($field['options'] as $option)
                                    @if (filled($option))
                                        <label class="flex items-center gap-2 text-sm text-stone-700">
                                            <input
                                                type="radio"
                                                wire:model="values.{{ $name }}"
                                                value="{{ $option }}"
                                                class="text-primary-600 focus:ring-primary-500"
                                            >
                                            <span>{{ $option }}</span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                            @break

                        @case('checkbox')
                            <label class="flex items-center gap-2 text-sm text-stone-700">
                                <input
                                    id="field-{{ $formContentId }}-{{ $field['id'] }}"
                                    type="checkbox"
                                    wire:model="values.{{ $name }}"
                                    class="rounded border-stone-300 text-primary-600 focus:ring-primary-500"
                                >
                                <span>
                                    {{ $field['label'] ?: $field['name'] }}
                                    @if ($field['required'])
                                        <span class="text-red-500">*</span>
                                    @endif
                                </span>
                            </label>
                            @break

                        @case('file')
                            <input
                                id="field-{{ $formContentId }}-{{ $field['id'] }}"
                                type="file"
                                wire:model="values.{{ $name }}"
                                class="{{ $inputClass }} file:me-4 file:rounded-lg file:border-0 file:bg-stone-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-stone-700"
                            >
                            @break

                        @default
                            <input
                                id="field-{{ $formContentId }}-{{ $field['id'] }}"
                                type="{{ $field['type'] }}"
                                wire:model="values.{{ $name }}"
                                @if (filled($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
                                @if (in_array($field['type'], ['email', 'tel', 'number', 'date'], true)) dir="ltr" @endif
                                class="{{ $inputClass }}"
                            >
                    @endswitch

                    @if (filled($field['info']))
                        <p class="text-xs text-stone-400">{{ $field['info'] }}</p>
                    @endif

                    @error('values.'.$name)
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="submit"
                class="w-full rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-70"
            >
                <span wire:loading.remove wire:target="submit">إرسال</span>
                <span wire:loading wire:target="submit">جارٍ الإرسال...</span>
            </button>
        </form>
    @endif
</div>

<?php

use App\Models\Content;
use App\Models\FormSubmission;
use App\Support\FormField;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public int $formContentId;

    public ?int $blockId = null;

    public string $description = '';

    /** @var list<array<string, mixed>> */
    public array $fields = [];

    /** @var array<string, mixed> */
    public array $values = [];

    public bool $submitted = false;

    public function mount(
        int $formContentId,
        ?int $blockId = null,
        ?string $description = null,
        ?array $fields = null,
    ): void {
        $this->formContentId = $formContentId;
        $this->blockId = $blockId;

        if (is_array($fields)) {
            $this->hydrateFromPrepared($description ?? '', $fields);

            return;
        }

        $form = Content::query()
            ->type(contentTypeModel('forms'))
            ->whereKey($formContentId)
            ->where('active', true)
            ->first(['id', 'data']);

        if (! $form) {
            return;
        }

        $this->hydrateFromPrepared(
            (string) data_get($form->data, 'description', ''),
            FormField::normalize(data_get($form->data, 'fields')),
        );
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     */
    protected function hydrateFromPrepared(string $description, array $fields): void
    {
        $this->description = $description;
        $this->fields = FormField::normalize($fields);

        foreach ($this->fields as $field) {
            $this->values[(string) $field['name']] = $field['type'] === 'checkbox' ? false : null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = [];

        foreach ($this->fields as $field) {
            $name = (string) $field['name'];
            $key = 'values.'.$name;
            $fieldRules = [($field['required'] ?? false) ? 'required' : 'nullable'];

            $fieldRules = array_merge($fieldRules, match ($field['type']) {
                'email' => ['string', 'email', 'max:255'],
                'tel' => ['string', 'max:30'],
                'number' => ['numeric'],
                'date' => ['date'],
                'textarea' => ['string', 'max:5000'],
                'select', 'radio' => ['string', Rule::in(array_values(array_filter($field['options'] ?? [])))],
                'checkbox' => ['boolean'],
                'file' => ['file', 'max:10240'],
                default => ['string', 'max:255'],
            });

            $rules[$key] = $fieldRules;
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        $attributes = [];

        foreach ($this->fields as $field) {
            $label = filled($field['label'] ?? null) ? (string) $field['label'] : (string) $field['name'];
            $attributes['values.'.(string) $field['name']] = $label;
        }

        return $attributes;
    }

    public function submit(): void
    {
        if ($this->fields === []) {
            return;
        }

        $this->validate($this->rules(), [], $this->validationAttributes());

        $tenantId = currentTenantId();

        if (! $tenantId) {
            $this->addError('values', 'تعذّر إرسال النموذج حالياً.');

            return;
        }

        $storedValues = [];

        foreach ($this->fields as $field) {
            $name = (string) $field['name'];
            $value = $this->values[$name] ?? null;

            if ($field['type'] === 'file' && $value instanceof TemporaryUploadedFile) {
                $value = $value->store('form-submissions/'.$tenantId, 'public');
            }

            if ($field['type'] === 'checkbox') {
                $value = (bool) $value;
            }

            $storedValues[] = [
                'id' => (string) $field['id'],
                'name' => $name,
                'label' => (string) ($field['label'] ?? ''),
                'type' => (string) $field['type'],
                'value' => $value,
            ];
        }

        FormSubmission::query()->create([
            'tenant_id' => $tenantId,
            'content_id' => $this->formContentId,
            'block_id' => $this->blockId,
            'status' => 'new',
            'data' => ['fields' => $storedValues],
            'submitted_at' => now(),
        ]);

        $this->submitted = true;
        $this->reset('values');

        foreach ($this->fields as $field) {
            $this->values[(string) $field['name']] = $field['type'] === 'checkbox' ? false : null;
        }

        $this->dispatch('cta-form-submitted');
    }
}; ?>
