<?php

namespace App\API\Forms;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Forms\Concerns\ResolvesForm;
use App\Http\Resources\FormResource;
use App\Models\Content;
use App\Models\Tenant;
use App\Support\FormField;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates a form (fields, publish state, and advanced settings).
 */
class UpdateForm
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesForm;

    /**
     * @return list<string>
     */
    public function getControllerMiddleware(): array
    {
        return [
            'auth:sanctum',
            'throttle:30,1',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'slug' => ['required', 'string', 'max:255'],
            'published' => ['required', 'boolean'],
            'submit_label' => ['nullable', 'string', 'max:100'],
            'success_message' => ['nullable', 'string', 'max:500'],
            'fields' => ['array'],
            'fields.*.id' => ['required', 'string', 'max:64'],
            'fields.*.type' => ['required', Rule::in(array_keys(FormField::typeOptions()))],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.name' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'fields.*.required' => ['boolean'],
            'fields.*.info' => ['nullable', 'string', 'max:500'],
            'fields.*.options' => ['nullable', 'array'],
            'fields.*.options.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($request->exists('published')) {
            $request->merge([
                'published' => filter_var($request->input('published'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }

        if ($request->exists('fields') && is_array($request->input('fields'))) {
            $request->merge([
                'fields' => collect($request->input('fields'))
                    ->map(function (mixed $field): array {
                        if (! is_array($field)) {
                            return [];
                        }

                        return [
                            'id' => (string) ($field['id'] ?? ''),
                            'type' => (string) ($field['type'] ?? 'text'),
                            'label' => (string) ($field['label'] ?? ''),
                            'name' => Str::lower(Str::replace('-', '_', (string) ($field['name'] ?? ''))),
                            'placeholder' => (string) ($field['placeholder'] ?? ''),
                            'required' => filter_var($field['required'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
                            'info' => (string) ($field['info'] ?? ''),
                            'options' => is_array($field['options'] ?? null) ? $field['options'] : [],
                        ];
                    })
                    ->filter(fn (array $field): bool => filled($field['id'] ?? null))
                    ->values()
                    ->all(),
            ]);
        }
    }

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     slug: string,
     *     published: bool,
     *     submit_label?: string|null,
     *     success_message?: string|null,
     *     fields?: list<array<string, mixed>>
     * }  $data
     */
    public function handle(Tenant $tenant, string $uuid, array $data): Content
    {
        setCurrentTenant($tenant);

        $fields = FormField::normalize($data['fields'] ?? []);
        $this->validateUniqueFieldNames($fields);

        $content = $this->findForm($uuid);
        $payload = $content->data ?? [];

        $payload['description'] = (string) ($data['description'] ?? '');
        $payload['fields'] = FormField::forStorage($fields);
        $payload['submit_label'] = filled($data['submit_label'] ?? null) ? (string) $data['submit_label'] : 'إرسال';
        $payload['success_message'] = (string) ($data['success_message'] ?? '');

        $slug = $this->uniqueFormSlug(
            filled($data['slug']) ? (string) $data['slug'] : Str::slug($data['title']),
            (int) $content->id,
        );

        $published = (bool) $data['published'];

        $content->update([
            'title' => $data['title'],
            'slug' => $slug,
            'status' => $published ? 'published' : 'draft',
            'data' => $payload,
            'published_at' => $published
                ? ($content->published_at ?? now())
                : null,
        ]);

        return $content->fresh();
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{
         *     title: string,
         *     description?: string|null,
         *     slug: string,
         *     published: bool,
         *     submit_label?: string|null,
         *     success_message?: string|null,
         *     fields?: list<array<string, mixed>>
         * } $validated
         */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, $validated);
    }

    public function jsonResponse(Content $content): FormResource
    {
        return (new FormResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'field_type_options' => $this->fieldTypeOptions(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
