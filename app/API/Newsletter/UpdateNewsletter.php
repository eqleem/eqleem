<?php

namespace App\API\Newsletter;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Newsletter\Concerns\ResolvesNewsletter;
use App\Http\Resources\NewsletterResource;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates a newsletter issue (content, mail, and publish fields).
 */
class UpdateNewsletter
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesNewsletter;

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
            'subject' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'editor_mode' => ['sometimes', 'nullable', 'string', Rule::in(['html', 'markdown'])],
            'slug' => ['required', 'string', 'max:255'],
            'mail_status' => ['required', Rule::in(array_keys(Content::newsletterMailStatusOptions()))],
            'scheduled_date' => ['nullable', 'required_if:mail_status,scheduled', 'date'],
            'scheduled_time' => ['nullable', 'required_if:mail_status,scheduled', 'date_format:H:i'],
            'recipients_count' => ['nullable', 'integer', 'min:0'],
            'published' => ['required', 'boolean'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($request->exists('published')) {
            $request->merge([
                'published' => filter_var($request->input('published'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }

    /**
     * @param  array{
     *     title: string,
     *     subject?: string|null,
     *     subtitle?: string|null,
     *     body?: string|null,
     *     editor_mode?: string,
     *     slug: string,
     *     mail_status: string,
     *     scheduled_date?: string|null,
     *     scheduled_time?: string|null,
     *     recipients_count?: int|null,
     *     published: bool
     * }  $data
     */
    public function handle(Tenant $tenant, string $uuid, array $data): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findNewsletter($uuid);
        $payload = $content->data ?? [];

        $payload['subject'] = (string) ($data['subject'] ?? '');
        $payload['subtitle'] = (string) ($data['subtitle'] ?? '');
        $payload['body'] = (string) ($data['body'] ?? '');
        $payload['editor_mode'] = (string) ($data['editor_mode'] ?? data_get($payload, 'editor_mode', 'html'));
        $payload['mail_status'] = $data['mail_status'];
        $payload['recipients_count'] = isset($data['recipients_count']) ? (int) $data['recipients_count'] : 0;

        if ($data['mail_status'] === 'scheduled'
            && filled($data['scheduled_date'] ?? null)
            && filled($data['scheduled_time'] ?? null)
        ) {
            $payload['scheduled_at'] = Carbon::parse($data['scheduled_date'].' '.$data['scheduled_time'])->toIso8601String();
        } else {
            unset($payload['scheduled_at']);
        }

        if ($data['mail_status'] === 'sent') {
            $payload['sent_at'] = data_get($content->data, 'sent_at') ?? now()->toIso8601String();
        } else {
            unset($payload['sent_at']);
        }

        $slug = $this->uniqueNewsletterSlug(
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
         *     subject?: string|null,
         *     subtitle?: string|null,
         *     body?: string|null,
         *     editor_mode?: string,
         *     slug: string,
         *     mail_status: string,
         *     scheduled_date?: string|null,
         *     scheduled_time?: string|null,
         *     recipients_count?: int|null,
         *     published: bool
         * } $validated
         */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, $validated);
    }

    public function jsonResponse(Content $content): NewsletterResource
    {
        return (new NewsletterResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'mail_status_options' => Content::newsletterMailStatusOptions(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
