<?php

namespace App\Support;

use App\Models\Content;
use Illuminate\Support\Collection;

class CtaLink
{
    /**
     * @return array<string, string>
     */
    public static function contentLinkTypeOptions(): array
    {
        $options = [];

        foreach (self::activeContentTypes() as $type) {
            $options['section:'.$type->slug] = 'رابط '.$type->name;
            $options['item:'.$type->slug] = config('cta-link-types.item_labels.'.$type->slug, 'محتوى محدد من '.$type->name);
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function blockLinkTypeOptions(): array
    {
        $options = [];

        foreach (self::activeContentTypes() as $type) {
            $options['section:'.$type->slug] = 'رابط '.$type->name;
        }

        $options['external'] = 'رابط خارجي';

        return $options;
    }

    /**
     * All link_type keys accepted when saving a block-link (sections, items, external).
     *
     * @return list<string>
     */
    public static function allowedBlockLinkTypeKeys(): array
    {
        $keys = [''];

        foreach (self::activeContentTypes() as $type) {
            $keys[] = 'section:'.$type->slug;
            $keys[] = 'item:'.$type->slug;
        }

        $keys[] = 'external';

        return $keys;
    }

    /**
     * Searchable picker options for the block-link editor (content types + external).
     *
     * @return list<array{
     *     key: string,
     *     label: string,
     *     icon_url: string,
     *     group: string,
     *     supports_section: bool,
     *     supports_item: bool,
     *     section_title: string,
     *     section_description: string,
     *     item_description: string
     * }>
     */
    public static function blockLinkPickerOptions(): array
    {
        $options = [];

        foreach (self::activeContentTypes() as $type) {
            $options[] = [
                'key' => $type->slug,
                'label' => 'رابط '.$type->name,
                'icon_url' => asset($type->icon),
                'group' => 'content',
                'supports_section' => self::contentTypeHasSectionRoute($type->slug),
                'supports_item' => self::contentTypeHasItemRoute($type->slug),
                'section_title' => (string) config(
                    "cta-link-types.block_link.sections.{$type->slug}.title",
                    $type->name
                ),
                'section_description' => (string) config(
                    "cta-link-types.block_link.sections.{$type->slug}.description",
                    ''
                ),
                'item_description' => (string) config(
                    "cta-link-types.block_link.items.{$type->slug}.description",
                    ''
                ),
            ];
        }

        $options[] = [
            'key' => 'external',
            'label' => 'رابط خارجي',
            'icon_url' => asset('assets/icons/tabler/ExternalLink.svg'),
            'group' => 'external',
            'supports_section' => false,
            'supports_item' => false,
            'section_title' => '',
            'section_description' => '',
            'item_description' => '',
        ];

        return $options;
    }

    /**
     * @return Collection<int, ContentType>
     */
    protected static function activeContentTypes(): Collection
    {
        return app(ContentTypeRegistry::class)->all();
    }

    public static function contentTypeHasSectionRoute(string $contentType): bool
    {
        $routeName = config('cta-link-types.routes.'.$contentType.'.index');

        return is_string($routeName) && filled($routeName);
    }

    public static function contentTypeHasItemRoute(string $contentType): bool
    {
        $routeName = config('cta-link-types.routes.'.$contentType.'.detail');

        return is_string($routeName) && filled($routeName);
    }

    /**
     * @return array<string, string>
     */
    public static function navLinkTypeOptions(): array
    {
        return [
            'external' => 'رابط خارجي',
        ] + self::blockLinkTypeOptions();
    }

    /**
     * @return array<string, string>
     */
    public static function linkTypeOptions(string $profile): array
    {
        return match ($profile) {
            'block' => ['' => 'اختر نوع الرابط...'] + self::blockLinkTypeOptions(),
            'nav' => self::navLinkTypeOptions(),
            default => self::navLinkTypeOptions(),
        };
    }

    public static function defaultTypeKey(string $profile): string
    {
        return match ($profile) {
            'block' => '',
            'nav' => 'external',
            default => 'external',
        };
    }

    public static function isExternalLink(string $typeKey): bool
    {
        return $typeKey === 'external';
    }

    public static function needsContentPicker(string $typeKey): bool
    {
        return str_starts_with($typeKey, 'item:');
    }

    public static function contentPickerLabel(string $typeKey): string
    {
        if (! str_starts_with($typeKey, 'item:')) {
            return 'اختر المحتوى';
        }

        $contentType = substr($typeKey, 5);

        if ($contentType === 'pages') {
            return 'اختر الصفحة';
        }

        return (string) config('cta-link-types.item_labels.'.$contentType, 'اختر المحتوى');
    }

    public static function linkNamePlaceholder(string $typeKey, string $profile): string
    {
        if (self::isExternalLink($typeKey)) {
            return 'مثال: تواصل معنا';
        }

        if (str_starts_with($typeKey, 'section:')) {
            $contentType = substr($typeKey, 8);

            return (string) config('content-types.'.$contentType.'.name', $profile === 'block' ? 'عنوان الرابط' : 'اسم الرابط');
        }

        if (str_starts_with($typeKey, 'item:')) {
            return 'اتركه فارغاً لاستخدام عنوان المحتوى';
        }

        return $profile === 'block' ? 'عنوان الرابط' : 'اسم الرابط';
    }

    public static function linkNameHint(string $typeKey, string $profile): string
    {
        if (self::isExternalLink($typeKey)) {
            return 'مطلوب للروابط الخارجية.';
        }

        if (str_starts_with($typeKey, 'item:')) {
            return 'اختياري — يُستخدم عنوان المحتوى المحدد إذا تُرك فارغاً.';
        }

        if ($profile === 'block') {
            return 'يُعبّأ تلقائياً من نوع الرابط ويمكنك تعديله.';
        }

        return 'اختياري — يُستخدم اسم المحتوى أو القسم تلقائياً إذا تُرك فارغاً.';
    }

    /**
     * @return array<string, string>
     */
    public static function allTypeLabels(): array
    {
        return self::navLinkTypeOptions() + self::contentLinkTypeOptions() + [
            'form' => 'نموذج',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function typeKeyFromStoredData(array $data): string
    {
        $linkType = $data['link_type'] ?? 'external';

        if (in_array($linkType, ['external', 'form'], true)) {
            return $linkType;
        }

        return self::typeKeyFromData($data);
    }

    public static function blockLinkTitleFromTypeKey(string $typeKey): string
    {
        $parsed = self::parseTypeKey($typeKey);
        $contentType = $parsed['content_type'] ?? '';

        if (! filled($contentType)) {
            return '';
        }

        $group = $parsed['link_type'] === 'item' ? 'items' : 'sections';

        return (string) config(
            "cta-link-types.block_link.{$group}.{$contentType}.title",
            config("content-types.{$contentType}.name", '')
        );
    }

    public static function blockLinkDescriptionFromTypeKey(string $typeKey): string
    {
        $parsed = self::parseTypeKey($typeKey);
        $contentType = $parsed['content_type'] ?? '';

        if (! filled($contentType)) {
            return '';
        }

        $group = $parsed['link_type'] === 'item' ? 'items' : 'sections';

        return (string) config("cta-link-types.block_link.{$group}.{$contentType}.description", '');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function typeKeyFromData(array $data): string
    {
        $linkType = $data['link_type'] ?? 'section';
        $contentType = $data['content_type'] ?? '';

        if ($linkType === 'section' || $linkType === 'item') {
            return $linkType.':'.$contentType;
        }

        return 'section:'.$contentType;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function titleFromData(array $data): string
    {
        if (filled($data['title'] ?? null)) {
            return (string) $data['title'];
        }

        $linkType = $data['link_type'] ?? 'section';

        if ($linkType === 'section') {
            return (string) config('content-types.'.$data['content_type'].'.name', '');
        }

        if ($linkType === 'item' && filled($data['content_id'] ?? null)) {
            return Content::query()->find($data['content_id'])?->title ?? '';
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function descriptionFromData(array $data): string
    {
        return (string) ($data['description'] ?? '');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function urlFromData(array $data): ?string
    {
        $linkType = $data['link_type'] ?? 'section';

        return match ($linkType) {
            'section' => self::sectionUrl($data['content_type'] ?? ''),
            'item' => self::itemUrl($data['content_type'] ?? '', (int) ($data['content_id'] ?? 0)),
            'external' => filled($data['url'] ?? null) ? (string) $data['url'] : null,
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function iconFromData(array $data): string
    {
        if (($data['link_type'] ?? '') === 'external') {
            return (string) ($data['icon'] ?? config('cta-link-types.icons.external'));
        }

        $contentType = $data['content_type'] ?? '';

        return config('cta-link-types.icons.'.$contentType, config('cta-link-types.icons.external'));
    }

    public static function typeOptions(): array
    {
        return self::navLinkTypeOptions();
    }

    public static function typeKey(Content $link): string
    {
        $data = $link->data ?? [];
        $linkType = $data['link_type'] ?? 'external';

        if ($linkType === 'external') {
            return 'external';
        }

        if ($linkType === 'form') {
            return 'form';
        }

        $contentType = $data['content_type'] ?? '';

        return $linkType.':'.$contentType;
    }

    public static function typeLabel(Content $link): string
    {
        return self::allTypeLabels()[self::typeKey($link)] ?? self::typeKey($link);
    }

    public static function label(Content $link): string
    {
        $data = $link->data ?? [];

        if (filled($data['label'] ?? null)) {
            return (string) $data['label'];
        }

        $linkType = $data['link_type'] ?? 'external';

        if ($linkType === 'section') {
            return config('content-types.'.$data['content_type'].'.name', $data['content_type'] ?? '');
        }

        if (in_array($linkType, ['item', 'form'], true) && filled($data['content_id'] ?? null)) {
            return Content::query()->find($data['content_id'])?->title ?? '';
        }

        return (string) ($link->title ?? '');
    }

    public static function icon(Content $link): string
    {
        $data = $link->data ?? [];
        $linkType = $data['link_type'] ?? 'external';

        if ($linkType === 'external') {
            return (string) ($data['icon'] ?? config('cta-link-types.icons.external'));
        }

        if ($linkType === 'form') {
            return config('cta-link-types.icons.form', 'hugeicons:file-01');
        }

        $contentType = $data['content_type'] ?? '';

        return config('cta-link-types.icons.'.$contentType, config('cta-link-types.icons.external'));
    }

    public static function url(Content $link): ?string
    {
        $data = $link->data ?? [];
        $linkType = $data['link_type'] ?? 'external';

        return match ($linkType) {
            'external' => filled($data['url'] ?? null) ? (string) $data['url'] : null,
            'section' => self::sectionUrl($data['content_type'] ?? ''),
            'item' => self::itemUrl($data['content_type'] ?? '', (int) ($data['content_id'] ?? 0)),
            'form' => '#',
            default => null,
        };
    }

    public static function summary(Content $link): string
    {
        $data = $link->data ?? [];
        $linkType = $data['link_type'] ?? 'external';

        return match ($linkType) {
            'external' => (string) ($data['url'] ?? ''),
            'section' => config('content-types.'.$data['content_type'].'.name', ''),
            'item', 'form' => Content::query()->find($data['content_id'] ?? null)?->title ?? '',
            default => '',
        };
    }

    public static function opensInNewTab(Content $link): bool
    {
        return ($link->data['link_type'] ?? 'external') === 'external';
    }

    public static function isForm(Content $link): bool
    {
        $data = $link->data ?? [];
        $linkType = $data['link_type'] ?? '';

        if ($linkType === 'form') {
            return true;
        }

        return $linkType === 'item' && ($data['content_type'] ?? '') === 'forms';
    }

    public static function formContentId(Content $link): ?int
    {
        if (! self::isForm($link)) {
            return null;
        }

        $contentId = $link->data['content_id'] ?? null;

        return filled($contentId) ? (int) $contentId : null;
    }

    /**
     * @return array{link_type: string, content_type: ?string, label: string, url: string, icon: string, content_id: ?int}
     */
    public static function parseTypeKey(string $typeKey): array
    {
        if ($typeKey === 'external') {
            return [
                'link_type' => 'external',
                'content_type' => null,
            ];
        }

        if ($typeKey === 'form') {
            return [
                'link_type' => 'form',
                'content_type' => null,
            ];
        }

        if (str_starts_with($typeKey, 'section:')) {
            return [
                'link_type' => 'section',
                'content_type' => substr($typeKey, 8),
            ];
        }

        if (str_starts_with($typeKey, 'item:')) {
            return [
                'link_type' => 'item',
                'content_type' => substr($typeKey, 5),
            ];
        }

        return [
            'link_type' => 'external',
            'content_type' => null,
        ];
    }

    public static function modelType(string $contentTypeSlug): string
    {
        return contentTypeModel($contentTypeSlug);
    }

    /**
     * @return Collection<int, Content>
     */
    public static function searchContents(string $linkTypeKey, string $search, int $limit = 8): Collection
    {
        $parsed = self::parseTypeKey($linkTypeKey);

        if ($parsed['link_type'] === 'form') {
            return self::searchByType('form', $search, $limit);
        }

        if ($parsed['link_type'] === 'item' && filled($parsed['content_type'])) {
            return self::searchByType($parsed['content_type'], $search, $limit);
        }

        return collect();
    }

    /**
     * @return Collection<int, Content>
     */
    public static function recentContents(string $linkTypeKey, int $limit = 5): Collection
    {
        $parsed = self::parseTypeKey($linkTypeKey);

        if ($parsed['link_type'] === 'form') {
            return self::recentByType('form', $limit);
        }

        if ($parsed['link_type'] === 'item' && filled($parsed['content_type'])) {
            return self::recentByType($parsed['content_type'], $limit);
        }

        return collect();
    }

    /**
     * @return Collection<int, Content>
     */
    protected static function searchByType(string $type, string $search, int $limit): Collection
    {
        $search = trim($search);

        if ($search === '') {
            return collect();
        }

        return Content::query()
            ->type(self::modelType($type))
            ->where(function ($query) use ($search): void {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%');
            })
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'type']);
    }

    /**
     * @return Collection<int, Content>
     */
    protected static function recentByType(string $type, int $limit): Collection
    {
        return Content::query()
            ->type(self::modelType($type))
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'type']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{tab: string, item?: string}|null
     */
    public static function adminManageParamsFromData(array $data): ?array
    {
        $linkType = $data['link_type'] ?? null;
        $contentType = $data['content_type'] ?? '';

        if (! in_array($linkType, ['section', 'item'], true) || ! filled($contentType)) {
            return null;
        }

        if (! is_array(config("content-types.{$contentType}"))) {
            return null;
        }

        $params = ['tab' => 'content-'.$contentType];

        if ($linkType === 'item' && filled($data['content_id'] ?? null)) {
            $uuid = Content::query()->find($data['content_id'])?->uuid;

            if (! filled($uuid)) {
                return null;
            }

            $params['item'] = (string) $uuid;
        }

        return $params;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function adminManageUrlFromData(array $data): ?string
    {
        $params = self::adminManageParamsFromData($data);

        return $params !== null ? route('admin.page.home', $params) : null;
    }

    protected static function sectionUrl(string $contentType): ?string
    {
        $routeName = config('cta-link-types.routes.'.$contentType.'.index');

        if (! is_string($routeName) || ! filled($routeName)) {
            return null;
        }

        return route($routeName);
    }

    protected static function itemUrl(string $contentType, int $contentId): ?string
    {
        if ($contentId <= 0) {
            return null;
        }

        $content = Content::query()->find($contentId);

        if (! $content || ! filled($content->slug)) {
            return null;
        }

        $routeName = config('cta-link-types.routes.'.$contentType.'.detail');

        if (! is_string($routeName) || ! filled($routeName)) {
            return null;
        }

        return route($routeName, $content->slug);
    }
}
