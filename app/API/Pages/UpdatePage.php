<?php

namespace App\API\Pages;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Pages\Concerns\ResolvesPage;
use App\Http\Resources\PageResource;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates a content page (text, template settings, slug, and publish fields).
 */
class UpdatePage
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesPage;

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
        $fieldRules = [];

        foreach (Content::contactFormFieldKeys() as $field) {
            $fieldRules["form_fields.{$field}"] = ['sometimes', 'boolean'];
        }

        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:500'],
            'body' => ['sometimes', 'nullable', 'string'],
            'editor_mode' => ['sometimes', 'nullable', 'string', Rule::in(['html', 'markdown'])],
            'slug' => ['required', 'string', 'max:255'],
            'published' => ['required', 'boolean'],
            'show_form' => ['sometimes', 'boolean'],
            'form_fields' => ['sometimes', 'array'],
            ...$fieldRules,
            'show_social_links' => ['sometimes', 'boolean'],
            'show_contact_info' => ['sometimes', 'boolean'],
            'show_extra_links' => ['sometimes', 'boolean'],
            'success_message' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'faqs' => ['sometimes', 'array'],
            'faqs.*.id' => ['nullable', 'string', 'max:64'],
            'faqs.*.question' => ['required', 'string', 'max:500'],
            'faqs.*.answer' => ['nullable', 'string'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $booleans = [
            'published',
            'show_form',
            'show_social_links',
            'show_contact_info',
            'show_extra_links',
        ];

        foreach ($booleans as $key) {
            if ($request->exists($key)) {
                $request->merge([
                    $key => filter_var($request->input($key), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
                ]);
            }
        }

        if ($request->exists('form_fields') && is_array($request->input('form_fields'))) {
            $fields = [];

            foreach ($request->input('form_fields') as $key => $value) {
                $fields[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            }

            $request->merge(['form_fields' => $fields]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Tenant $tenant, string $uuid, array $data): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findPage($uuid);
        $payload = is_array($content->data) ? $content->data : [];

        if (array_key_exists('subtitle', $data)) {
            $payload['subtitle'] = (string) ($data['subtitle'] ?? '');
        }

        if (array_key_exists('body', $data)) {
            $payload['body'] = (string) ($data['body'] ?? '');
        }

        if (array_key_exists('editor_mode', $data)) {
            $payload['editor_mode'] = (string) ($data['editor_mode'] ?? data_get($payload, 'editor_mode', 'html'));
        }

        if ($content->template === 'contact') {
            $payload = $this->mergeContactSettings($payload, $data);
        }

        if ($content->template === 'faq' && array_key_exists('faqs', $data)) {
            $payload['faqs'] = $this->normalizeFaqs($data['faqs'] ?? []);
        }

        $slug = $this->uniquePageSlug(
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

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mergeContactSettings(array $payload, array $data): array
    {
        $defaults = Content::defaultContactPageData();

        foreach (['show_form', 'show_social_links', 'show_contact_info', 'show_extra_links', 'success_message'] as $key) {
            if (array_key_exists($key, $data)) {
                $payload[$key] = $key === 'success_message'
                    ? (string) ($data[$key] ?? '')
                    : (bool) $data[$key];
            } elseif (! array_key_exists($key, $payload)) {
                $payload[$key] = $defaults[$key];
            }
        }

        if (array_key_exists('form_fields', $data) && is_array($data['form_fields'])) {
            $fields = is_array(data_get($payload, 'form_fields'))
                ? $payload['form_fields']
                : $defaults['form_fields'];

            foreach (Content::contactFormFieldKeys() as $field) {
                if (array_key_exists($field, $data['form_fields'])) {
                    $fields[$field] = (bool) $data['form_fields'][$field];
                } elseif (! array_key_exists($field, $fields)) {
                    $fields[$field] = (bool) ($defaults['form_fields'][$field] ?? false);
                }
            }

            $payload['form_fields'] = $fields;
        } elseif (! array_key_exists('form_fields', $payload)) {
            $payload['form_fields'] = $defaults['form_fields'];
        }

        return $payload;
    }

    /**
     * @param  array<int, mixed>  $faqs
     * @return list<array{id: string, question: string, answer: string}>
     */
    protected function normalizeFaqs(array $faqs): array
    {
        $normalized = [];

        foreach ($faqs as $faq) {
            if (! is_array($faq)) {
                continue;
            }

            $question = trim((string) ($faq['question'] ?? ''));

            if ($question === '') {
                continue;
            }

            $id = trim((string) ($faq['id'] ?? ''));

            $normalized[] = [
                'id' => $id !== '' ? $id : (string) Str::uuid(),
                'question' => $question,
                'answer' => (string) ($faq['answer'] ?? ''),
            ];
        }

        return $normalized;
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, $validated);
    }

    public function jsonResponse(Content $content): PageResource
    {
        return (new PageResource($content, [
            'slug_prefix' => $this->slugPrefix(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
