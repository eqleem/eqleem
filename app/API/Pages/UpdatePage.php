<?php

namespace App\API\Pages;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Pages\Concerns\ResolvesPage;
use App\Http\Resources\PageResource;
use App\Models\Branch;
use App\Models\Calendar;
use App\Models\Content;
use App\Models\Tenant;
use App\Support\BlockBrandMark;
use App\Support\CtaBooking;
use App\Support\CtaLink;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
            'primary_button' => ['sometimes', 'nullable', 'array'],
            'primary_button.label' => ['nullable', 'string', 'max:120'],
            'primary_button.link_type' => ['nullable', 'string', Rule::in(CtaLink::allowedBlockLinkTypeKeys())],
            'primary_button.url' => ['nullable', 'string', 'max:2000'],
            'primary_button.content_id' => ['nullable', 'integer'],
            'primary_button.branch_ids' => ['sometimes', 'array'],
            'primary_button.branch_ids.*' => ['integer'],
            'primary_button.calendar_ids' => ['sometimes', 'array'],
            'primary_button.calendar_ids.*' => ['integer'],
            'primary_button.allow_client_choice' => ['sometimes', 'boolean'],
            'primary_button.duration_minutes' => ['sometimes', 'integer', 'min:1', 'max:480'],
            'stats' => ['sometimes', 'array'],
            'stats.*.id' => ['nullable', 'string', 'max:64'],
            'stats.*.value' => ['required', 'string', 'max:50'],
            'stats.*.label' => ['nullable', 'string', 'max:255'],
            'features_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'features_description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'features' => ['sometimes', 'array'],
            'features.*.id' => ['nullable', 'string', 'max:64'],
            'features.*.title' => ['required', 'string', 'max:255'],
            'features.*.description' => ['nullable', 'string', 'max:1000'],
            'features.*.brand_mark' => ['nullable', 'array'],
            'features.*.brand_mark.type' => ['nullable', 'string', Rule::in(['emoji', 'icon', 'image', 'none'])],
            'features.*.brand_mark.value' => ['nullable', 'string', 'max:255'],
            'features.*.brand_mark.color' => ['nullable', 'string', 'max:32'],
            'features.*.brand_mark.path' => ['nullable', 'string', 'max:500'],
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

        if ($request->exists('primary_button') && is_array($request->input('primary_button'))) {
            $button = $request->input('primary_button');

            if (array_key_exists('allow_client_choice', $button)) {
                $button['allow_client_choice'] = filter_var(
                    $button['allow_client_choice'],
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                ) ?? true;
            }

            $request->merge(['primary_button' => $button]);
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

        if ($content->template === 'about') {
            $payload = $this->mergeAboutSettings($payload, $data);
        }

        $slug = $this->uniquePageSlug(
            filled($data['slug']) ? (string) $data['slug'] : Str::slug($data['title']),
            (int) $content->id,
        );

        $published = (bool) $data['published'];

        $content->update([
            'title' => $data['title'],
            'slug' => $slug,
            'active' => $published,
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
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mergeAboutSettings(array $payload, array $data): array
    {
        $defaults = Content::defaultAboutPageData();

        foreach (['features_title', 'features_description'] as $key) {
            if (array_key_exists($key, $data)) {
                $payload[$key] = (string) ($data[$key] ?? '');
            } elseif (! array_key_exists($key, $payload)) {
                $payload[$key] = $defaults[$key];
            }
        }

        if (array_key_exists('primary_button', $data)) {
            $payload['primary_button'] = $this->normalizePrimaryButton(
                is_array($data['primary_button'] ?? null) ? $data['primary_button'] : []
            );
        } elseif (! array_key_exists('primary_button', $payload)) {
            $payload['primary_button'] = $defaults['primary_button'];
        }

        if (array_key_exists('stats', $data)) {
            $payload['stats'] = $this->normalizeStats($data['stats'] ?? []);
        } elseif (! array_key_exists('stats', $payload)) {
            $payload['stats'] = $defaults['stats'];
        }

        if (array_key_exists('features', $data)) {
            $payload['features'] = $this->normalizeFeatures($data['features'] ?? []);
        } elseif (! array_key_exists('features', $payload)) {
            $payload['features'] = $defaults['features'];
        }

        if (! array_key_exists('hero_image', $payload)) {
            $payload['hero_image'] = $defaults['hero_image'];
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $button
     * @return array<string, mixed>
     */
    protected function normalizePrimaryButton(array $button): array
    {
        $defaults = Content::defaultAboutPrimaryButton();
        $label = trim((string) ($button['label'] ?? ''));
        $typeKey = trim((string) ($button['link_type'] ?? 'external'));

        if ($typeKey === '' || ! in_array($typeKey, CtaLink::allowedBlockLinkTypeKeys(), true)) {
            $typeKey = 'external';
        }

        $parsed = CtaLink::parseTypeKey($typeKey);
        $contentId = filled($button['content_id'] ?? null) ? (int) $button['content_id'] : null;
        $url = filled($button['url'] ?? null) ? trim((string) $button['url']) : null;

        if ($parsed['link_type'] === 'external' && $label !== '' && ! filled($url)) {
            throw ValidationException::withMessages([
                'primary_button.url' => 'رابط الزر مطلوب.',
            ]);
        }

        if (CtaLink::needsContentPicker($typeKey) && $label !== '' && ! $contentId) {
            throw ValidationException::withMessages([
                'primary_button.content_id' => 'اختر عنصراً للزر.',
            ]);
        }

        if ($contentId && CtaLink::needsContentPicker($typeKey)) {
            $content = Content::query()->type(CtaLink::modelType((string) $parsed['content_type']))->whereKey($contentId)->first();

            if (! $content) {
                throw ValidationException::withMessages([
                    'primary_button.content_id' => 'العنصر المحدد غير موجود.',
                ]);
            }
        }

        $payload = [
            'label' => $label,
            'link_type' => $parsed['link_type'],
            'content_type' => $parsed['content_type'],
            'content_id' => $parsed['link_type'] === 'external' || $parsed['link_type'] === 'booking' || $parsed['link_type'] === 'section'
                ? null
                : $contentId,
            'url' => $parsed['link_type'] === 'external' ? $url : null,
            'branch_ids' => $defaults['branch_ids'],
            'calendar_ids' => $defaults['calendar_ids'],
            'allow_client_choice' => $defaults['allow_client_choice'],
            'duration_minutes' => $defaults['duration_minutes'],
        ];

        if ($parsed['link_type'] === 'booking' && $label !== '') {
            $booking = $this->validatedBookingConfig($button);
            $payload = array_merge($payload, $booking);
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     branch_ids: list<int>,
     *     calendar_ids: list<int>,
     *     allow_client_choice: bool,
     *     duration_minutes: int
     * }
     */
    protected function validatedBookingConfig(array $data): array
    {
        $config = CtaBooking::configFromData($data);

        if ($config['branch_ids'] === [] && $config['calendar_ids'] === []) {
            throw ValidationException::withMessages([
                'primary_button.branch_ids' => 'اختر فرعاً واحداً على الأقل أو تقويماً للحجز.',
            ]);
        }

        if ($config['branch_ids'] !== []) {
            $validBranchCount = Branch::query()
                ->whereIn('id', $config['branch_ids'])
                ->count();

            if ($validBranchCount !== count($config['branch_ids'])) {
                throw ValidationException::withMessages([
                    'primary_button.branch_ids' => 'أحد الفروع المحددة غير صالح.',
                ]);
            }
        }

        if ($config['calendar_ids'] !== []) {
            $validCalendarCount = Calendar::query()
                ->whereIn('id', $config['calendar_ids'])
                ->where('active', true)
                ->count();

            if ($validCalendarCount !== count($config['calendar_ids'])) {
                throw ValidationException::withMessages([
                    'primary_button.calendar_ids' => 'أحد التقاويم المحددة غير صالح.',
                ]);
            }
        }

        $calendars = CtaBooking::resolveCalendars($config['branch_ids'], $config['calendar_ids']);

        if ($calendars->isEmpty()) {
            throw ValidationException::withMessages([
                'primary_button.branch_ids' => 'لا توجد تقاويم نشطة تطابق الفروع أو التقاويم المحددة.',
            ]);
        }

        return $config;
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

    /**
     * @param  array<int, mixed>  $stats
     * @return list<array{id: string, value: string, label: string}>
     */
    protected function normalizeStats(array $stats): array
    {
        $normalized = [];

        foreach ($stats as $stat) {
            if (! is_array($stat)) {
                continue;
            }

            $value = trim((string) ($stat['value'] ?? ''));

            if ($value === '') {
                continue;
            }

            $id = trim((string) ($stat['id'] ?? ''));

            $normalized[] = [
                'id' => $id !== '' ? $id : (string) Str::uuid(),
                'value' => $value,
                'label' => trim((string) ($stat['label'] ?? '')),
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<int, mixed>  $features
     * @return list<array{id: string, title: string, description: string, brand_mark: array<string, mixed>|null}>
     */
    protected function normalizeFeatures(array $features): array
    {
        $normalized = [];

        foreach ($features as $feature) {
            if (! is_array($feature)) {
                continue;
            }

            $title = trim((string) ($feature['title'] ?? ''));

            if ($title === '') {
                continue;
            }

            $id = trim((string) ($feature['id'] ?? ''));
            $brandMark = null;

            if (is_array($feature['brand_mark'] ?? null)) {
                $type = (string) ($feature['brand_mark']['type'] ?? '');

                if ($type === 'none' || $type === '') {
                    $brandMark = null;
                } else {
                    $brandMark = BlockBrandMark::normalizeStored($feature['brand_mark']);
                }
            }

            $normalized[] = [
                'id' => $id !== '' ? $id : (string) Str::uuid(),
                'title' => $title,
                'description' => trim((string) ($feature['description'] ?? '')),
                'brand_mark' => $brandMark,
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
