<?php

namespace App\Actions;

use App\Models\Block;
use App\Models\Content;
use App\Models\SocialAccount;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use App\Support\BlockTypeRegistry;
use App\Support\FormField;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedTenantDefaults
{
    use AsAction;

    public function handle(Tenant $tenant, ?BlockTypeRegistry $blockTypes = null): void
    {
        setCurrentTenant($tenant);

        $this->ensureContentUuids($tenant);

        if (data_get($tenant->meta, 'defaults_seeded')) {
            return;
        }

        $blockTypes ??= app(BlockTypeRegistry::class);

        $this->seedHeaderBlock($tenant);
        $contactForm = $this->seedContactForm($tenant);
        $this->seedCtaLink($tenant, $contactForm);
        $this->seedBlockLinks($tenant, $blockTypes);
        $this->seedDefaultPages($tenant);

        $tenant->meta->set('defaults_seeded', true);
        $tenant->save();
    }

    protected function ensureContentUuids(Tenant $tenant): void
    {
        Content::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->whereNull('uuid')
            ->orderBy('id')
            ->each(fn (Content $content) => $content->ensureUuid());
    }

    protected function seedHeaderBlock(Tenant $tenant): void
    {
        $headerBlock = Block::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->where('type', 'header')
            ->first();

        if (! $headerBlock) {
            return;
        }

        app(TenantProfileService::class)->saveBio($tenant, 'صفحة إقليم جديدة');

        $headerBlock->update([
            'data' => array_merge($headerBlock->data ?? [], [
                'show_avatar' => true,
                'show_verified_badge' => true,
            ]),
        ]);

        $networks = ['twitter', 'snapchat', 'tiktok'];
        $networkLabels = config('social-networks', []);

        foreach ($networks as $index => $network) {
            $exists = Content::query()
                ->where('block_id', $headerBlock->id)
                ->type('social-link')
                ->where('data->network', $network)
                ->exists();

            if ($exists) {
                continue;
            }

            Content::create([
                'tenant_id' => $tenant->id,
                'block_id' => $headerBlock->id,
                'type' => 'social-link',
                'title' => $networkLabels[$network]['label'] ?? $network,
                'slug' => $network.'-'.Str::lower(Str::random(8)),
                'data' => [
                    'network' => $network,
                    'url' => $this->socialNetworkUrl($tenant, $network, $networkLabels),
                ],
                'sort_order' => $index + 1,
                'active' => true,
                'status' => 'published',
                'published_at' => now(),
            ]);
        }
    }

    /**
     * @param  array<string, array{label?: string, icon?: string, url?: string}>  $networkLabels
     */
    protected function socialNetworkUrl(Tenant $tenant, string $network, array $networkLabels): string
    {
        $template = $networkLabels[$network]['url'] ?? '';

        if ($template === '') {
            return '';
        }

        $username = $this->socialUsername($tenant);

        return str_replace('{username}', $username, $template);
    }

    protected function socialUsername(Tenant $tenant): string
    {
        $meta = SocialAccount::query()
            ->where('user_id', $tenant->user_id)
            ->whereNotNull('meta')
            ->value('meta');

        if (is_array($meta) && filled($meta['nickname'] ?? null)) {
            return (string) $meta['nickname'];
        }

        return (string) $tenant->handle;
    }

    protected function seedContactForm(Tenant $tenant): Content
    {
        $existing = Content::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->type(contentTypeModel('forms'))
            ->where('slug', 'contact')
            ->first();

        if ($existing) {
            return $existing->ensureUuid();
        }

        $fields = FormField::forStorage([
            $this->contactField('text', 'الاسم', 'name', 'اكتب اسمك'),
            $this->contactField('email', 'البريد الإلكتروني', 'email', 'example@email.com'),
            $this->contactField('tel', 'رقم الجوال', 'phone', '05xxxxxxxx'),
            $this->contactField('textarea', 'الرسالة', 'message', 'اكتب رسالتك هنا...'),
        ]);

        return Content::create([
            'tenant_id' => $tenant->id,
            'type' => contentTypeModel('forms'),
            'title' => 'اتصل بنا',
            'slug' => 'contact',
            'data' => [
                'description' => 'يسعدنا تواصلك معنا. املأ النموذج وسنرد عليك في أقرب وقت.',
                'fields' => $fields,
                'submit_label' => 'إرسال',
                'success_message' => 'شكراً! تم استلام رسالتك بنجاح.',
            ],
            'active' => true,
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function contactField(string $type, string $label, string $name, string $placeholder): array
    {
        $field = FormField::make($type);

        $field['label'] = $label;
        $field['name'] = $name;
        $field['placeholder'] = $placeholder;
        $field['required'] = true;

        return $field;
    }

    protected function seedCtaLink(Tenant $tenant, Content $contactForm): void
    {
        $ctaBlock = Block::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->where('type', 'cta')
            ->first();

        if (! $ctaBlock) {
            return;
        }

        $exists = Content::query()
            ->where('block_id', $ctaBlock->id)
            ->type('cta-link')
            ->where('data->content_id', $contactForm->id)
            ->exists();

        if ($exists) {
            return;
        }

        Content::create([
            'tenant_id' => $tenant->id,
            'block_id' => $ctaBlock->id,
            'type' => 'cta-link',
            'title' => 'اتصل بنا',
            'slug' => 'cta-contact-'.Str::lower(Str::random(8)),
            'data' => [
                'link_type' => 'form',
                'content_type' => null,
                'label' => 'اتصل بنا',
                'url' => null,
                'icon' => null,
                'content_id' => $contactForm->id,
            ],
            'sort_order' => 1,
            'active' => true,
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    protected function seedBlockLinks(Tenant $tenant, BlockTypeRegistry $blockTypes): void
    {
        foreach (['blog', 'portfolio'] as $contentType) {
            EnsureSectionBlockLink::make()->handle($tenant->id, $contentType, $blockTypes);
        }
    }

    protected function seedDefaultPages(Tenant $tenant): void
    {
        $businessName = filled($tenant->name) ? (string) $tenant->name : (string) $tenant->handle;

        $pages = [
            [
                'template' => 'contact',
                'title' => 'اتصل بنا',
                'slug' => 'contact-us',
                'data' => Content::defaultContactPageData(),
            ],
            [
                'template' => 'faq',
                'title' => 'الأسئلة المتكررة',
                'slug' => 'faq',
                'data' => Content::defaultFaqPageData(),
            ],
            [
                'template' => 'about',
                'title' => 'من نحن',
                'slug' => 'about-us',
                'data' => Content::defaultAboutPageData(),
            ],
            [
                'template' => null,
                'title' => 'اتفاقية الاستخدام',
                'slug' => 'terms',
                'data' => Content::defaultTermsPageData($businessName),
            ],
            [
                'template' => null,
                'title' => 'سياسة الخصوصية',
                'slug' => 'privacy',
                'data' => Content::defaultPrivacyPageData($businessName),
            ],
        ];

        foreach ($pages as $page) {
            $this->seedDefaultPage($tenant, $page);
        }
    }

    /**
     * @param  array{template?: string|null, title: string, slug: string, data?: array<string, mixed>}  $page
     */
    protected function seedDefaultPage(Tenant $tenant, array $page): void
    {
        $template = $page['template'] ?? null;

        $existing = Content::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->type(contentTypeModel('pages'))
            ->when(
                filled($template),
                fn ($query) => $query->where('template', $template),
                fn ($query) => $query->where('slug', $page['slug']),
            )
            ->first();

        if ($existing) {
            $existing->ensureUuid();

            return;
        }

        Content::create([
            'tenant_id' => $tenant->id,
            'type' => contentTypeModel('pages'),
            'template' => $template,
            'title' => $page['title'],
            'slug' => $page['slug'],
            'data' => $page['data'] ?? [],
            'active' => true,
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
