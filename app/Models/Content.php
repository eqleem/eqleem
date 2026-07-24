<?php

namespace App\Models;

use Aliziodev\LaravelTaxonomy\Traits\HasTaxonomy;
use App\Actions\EnsureSectionBlockLink;
use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'tenant_id',
    'user_id',
    'block_id',
    'parent_id',
    'type',
    'template',
    'title',
    'slug',
    'data',
    'price',
    'meta',
    'active',
    'status',
    'sort_order',
    'published_at',
])]
class Content extends Model implements HasMedia
{
    use BelongsToTenant, HasTaxonomy, HasUuid, InteractsWithMedia, SoftDeletes;

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'price' => 'integer',
            'meta' => 'array',
            'active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            if ($tenantId = currentTenantId()) {
                $builder->where('tenant_id', $tenantId);
            }
        });

        static::creating(function (Content $content): void {
            if (filled($content->user_id)) {
                return;
            }

            $userId = auth()->id();

            if ($userId) {
                $content->user_id = $userId;

                return;
            }

            if ($content->tenant_id) {
                $content->user_id = Tenant::query()
                    ->whereKey($content->tenant_id)
                    ->value('user_id');
            }
        });

        static::created(function (Content $content): void {
            if ($content->block_id !== null) {
                return;
            }

            $contentTypeSlug = contentTypeSlugFromModel($content->type);

            if ($contentTypeSlug === null || ! $content->tenant_id) {
                return;
            }

            EnsureSectionBlockLink::run($content->tenant_id, $contentTypeSlug);
        });

        static::deleting(function (Content $content): bool {
            return ! $content->isSystemPage();
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'content_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeTemplate(Builder $query, string $template): Builder
    {
        return $query->where('template', $template);
    }

    /**
     * @return array<string, string>
     */
    public static function pageTemplateOptions(): array
    {
        return [
            'default' => 'صفحة عادية',
            'contact' => 'اتصل بنا',
            'faq' => 'الأسئلة المتكررة',
            'about' => 'من نحن',
            'features' => 'المزايا',
            'pricing' => 'الباقات والأسعار',
        ];
    }

    /**
     * @return list<string>
     */
    public static function systemPageTemplates(): array
    {
        return ['contact', 'faq', 'about'];
    }

    /**
     * Templates that can be chosen when creating a page from the dashboard.
     *
     * @return list<string>
     */
    public static function creatablePageTemplates(): array
    {
        return ['contact', 'faq', 'about'];
    }

    /**
     * @return list<string>
     */
    public static function contactFormFieldKeys(): array
    {
        return ['name', 'email', 'phone', 'message', 'address'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultContactPageData(): array
    {
        return [
            'subtitle' => 'يسعدنا تواصلك معنا. املأ النموذج وسنرد عليك في أقرب وقت.',
            'show_form' => true,
            'form_fields' => [
                'name' => true,
                'email' => true,
                'phone' => true,
                'message' => true,
                'address' => false,
            ],
            'show_social_links' => true,
            'show_contact_info' => true,
            'show_extra_links' => true,
            'success_message' => 'شكراً لتواصلك معنا. سنرد عليك في أقرب وقت.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultFaqPageData(): array
    {
        return [
            'subtitle' => 'إجابات على أكثر الأسئلة شيوعاً حول خدماتنا.',
            'faqs' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultAboutPageData(): array
    {
        return [
            'subtitle' => 'تعرف على قصتنا ورؤيتنا وما يميزنا.',
            'hero_image' => null,
            'primary_button' => self::defaultAboutPrimaryButton(),
            'stats' => [
                [
                    'id' => 'stat_1',
                    'value' => '95%',
                    'label' => 'رضا العملاء',
                ],
                [
                    'id' => 'stat_2',
                    'value' => '10+',
                    'label' => 'سنوات من الخبرة',
                ],
                [
                    'id' => 'stat_3',
                    'value' => '500+',
                    'label' => 'مشروع منجز',
                ],
            ],
            'features_title' => 'لماذا تختارنا؟',
            'features_description' => 'نقدم قيمة حقيقية عبر تجربة واضحة وخدمات موثوقة.',
            'features' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultAboutPrimaryButton(): array
    {
        return [
            'label' => '',
            'link_type' => 'external',
            'content_type' => null,
            'content_id' => null,
            'url' => null,
            'branch_ids' => [],
            'calendar_ids' => [],
            'allow_client_choice' => true,
            'duration_minutes' => 30,
        ];
    }

    /**
     * Generic terms-of-use content suitable for most online stores and pages.
     *
     * @return array{subtitle: string, body: string, editor_mode: string}
     */
    public static function defaultTermsPageData(string $businessName = 'هذه الصفحة'): array
    {
        $name = filled(trim($businessName)) ? trim($businessName) : 'هذه الصفحة';

        return [
            'subtitle' => 'يرجى قراءة هذه الاتفاقية بعناية قبل استخدام '.$name.' أو طلب أي منتج أو خدمة.',
            'editor_mode' => 'html',
            'body' => <<<HTML
<p>آخر تحديث: يمكن تحديث هذه الاتفاقية عند الحاجة. استمرارك في استخدام الصفحة يعني موافقتك على النسخة السارية.</p>
<h2>1. القبول بالشروط</h2>
<p>باستخدامك لـ {$name} أو تصفّحك لمحتواها أو طلبك لأي منتج أو خدمة معروضة، فإنك توافق على الالتزام بهذه الاتفاقية. إذا لم توافق على أي جزء منها، يرجى التوقف عن استخدام الصفحة.</p>
<h2>2. طبيعة الصفحة والخدمات</h2>
<p>قد تعرض {$name} معلومات، منتجات، خدمات، حجوزات، محتوى رقمياً، أو وسائل تواصل حسب ما يتيحه صاحب الصفحة. المحتوى والأسعار والتوفر قابلة للتغيير دون إشعار مسبق ما لم يُنص على خلاف ذلك.</p>
<h2>3. الاستخدام المقبول</h2>
<p>تتعهد باستخدام الصفحة لأغراض مشروعة فقط، وعدم محاولة الإضرار بها أو تعطيلها أو الوصول غير المصرح به إلى أنظمتها أو بيانات الآخرين، وعدم نشر محتوى مخالف للأنظمة أو مضلل أو مسيء.</p>
<h2>4. الطلبات والحجوزات والمنتجات</h2>
<p>عند تقديم طلب أو حجز أو شراء، تتحمل مسؤولية صحة البيانات التي تقدمها. قبول الطلب أو تأكيده يخضع لسياسات البيع والتوفر والشحن أو التنفيذ المعتمدة لدى {$name}. قد تُرفض أو تُلغى الطلبات عند وجود خطأ واضح في السعر أو التوفر أو الاشتباه في احتيال.</p>
<h2>5. الأسعار والمدفوعات</h2>
<p>الأسعار المعروضة هي الأسعار السارية وقت العرض ما لم يُذكر غير ذلك. عند إتمام عملية دفع عبر مزوّد دفع إلكتروني، تخضع العملية أيضاً لشروط ذلك المزوّد. أي استرداد أو إلغاء يتم وفق السياسة المعلنة أو ما يُتفق عليه عند إتمام الطلب.</p>
<h2>6. الملكية الفكرية</h2>
<p>جميع النصوص والصور والشعارات والتصاميم والمحتوى المعروض على {$name} محمية بحقوق الملكية الفكرية الخاصة بصاحب الصفحة أو مرخّصيها، ولا يجوز نسخها أو إعادة استخدامها لأغراض تجارية دون إذن مسبق.</p>
<h2>7. روابط ومحتوى الأطراف الثالثة</h2>
<p>قد تتضمن الصفحة روابط أو تكاملات مع مواقع أو خدمات خارجية. لسنا مسؤولين عن محتوى تلك الأطراف أو سياساتها، واستخدامك لها يكون على مسؤوليتك ووفقاً لشروطها.</p>
<h2>8. إخلاء المسؤولية</h2>
<p>تُقدَّم الصفحة ومحتواها «كما هي». ضمن الحدود التي يسمح بها النظام، لا نضمن خلو الخدمة من الانقطاع أو الأخطاء، ولا نتحمل مسؤولية الأضرار غير المباشرة الناتجة عن الاعتماد على المحتوى أو عن تأخير أو تعذر تنفيذ طلب بسبب ظروف خارجة عن السيطرة المعقولة.</p>
<h2>9. التعديلات</h2>
<p>يحق لصاحب {$name} تعديل هذه الاتفاقية في أي وقت بنشر النسخة المحدّثة على هذه الصفحة. ننصح بمراجعتها بشكل دوري.</p>
<h2>10. التواصل</h2>
<p>لأي استفسار حول اتفاقية الاستخدام، يرجى التواصل عبر صفحة اتصل بنا أو عبر وسائل التواصل المتاحة على الصفحة.</p>
HTML,
        ];
    }

    /**
     * Generic privacy-policy content suitable for most online stores and pages.
     *
     * @return array{subtitle: string, body: string, editor_mode: string}
     */
    public static function defaultPrivacyPageData(string $businessName = 'هذه الصفحة'): array
    {
        $name = filled(trim($businessName)) ? trim($businessName) : 'هذه الصفحة';

        return [
            'subtitle' => 'نوضح هنا كيف نجمع بياناتك ونستخدمها ونحميها عند تفاعلك مع '.$name.'.',
            'editor_mode' => 'html',
            'body' => <<<HTML
<p>آخر تحديث: قد نحدّث هذه السياسة من وقت لآخر. سننشر أي تعديل على هذه الصفحة.</p>
<h2>1. المقدمة</h2>
<p>تحترم {$name} خصوصيتك. تشرح هذه السياسة أنواع المعلومات التي قد نجمعها عند زيارتك أو تواصلك أو تقديمك لطلب، وكيف نستخدمها، والخيارات المتاحة لك.</p>
<h2>2. البيانات التي قد نجمعها</h2>
<p>قد نجمع أنواعاً من البيانات تشمل:</p>
<ul>
<li>بيانات التعريف والتواصل مثل الاسم والبريد الإلكتروني ورقم الجوال والعنوان عند تقديمها طوعاً.</li>
<li>بيانات الطلبات والحجوزات والمدفوعات اللازمة لتنفيذ الخدمة، مع العلم أن بيانات البطاقة الكاملة غالباً تُعالج عبر مزوّدي دفع معتمدين.</li>
<li>محتوى الرسائل أو النماذج التي ترسلها إلينا.</li>
<li>بيانات تقنية أساسية مثل نوع الجهاز أو المتصفح وعنوان IP وسجلات الاستخدام لتحسين الأداء والأمان.</li>
</ul>
<h2>3. كيف نستخدم البيانات</h2>
<p>نستخدم البيانات من أجل:</p>
<ul>
<li>تشغيل الصفحة وتقديم المنتجات أو الخدمات والرد على الاستفسارات.</li>
<li>معالجة الطلبات والمدفوعات والتواصل بشأن حالة الطلب أو الدعم.</li>
<li>تحسين التجربة ومنع الاحتيال وضمان أمان الصفحة.</li>
<li>الامتثال للمتطلبات النظامية عند الاقتضاء.</li>
</ul>
<h2>4. مشاركة البيانات</h2>
<p>لا نبيع بياناتك الشخصية. قد نشارك بيانات محدودة مع مزوّدي خدمات يساعدوننا في التشغيل (مثل الاستضافة، البريد، والشحن، والمدفوعات) وبالقدر اللازم لتقديم الخدمة فقط، أو عند وجود التزام نظامي أو لحماية حقوقنا وحقوق المستخدمين.</p>
<h2>5. ملفات تعريف الارتباط</h2>
<p>قد نستخدم ملفات تعريف الارتباط أو تقنيات مشابهة لتشغيل الجلسات، وتذكر التفضيلات، وتحسين تجربة الاستخدام. يمكنك التحكم فيها من إعدادات متصفحك، وقد يؤثر تعطيلها على بعض الوظائف.</p>
<h2>6. الاحتفاظ بالبيانات وأمانها</h2>
<p>نحتفظ بالبيانات للمدة اللازمة لتقديم الخدمة والوفاء بالالتزامات التشغيلية والنظامية. نتخذ إجراءات تقنية وتنظيمية معقولة لحماية بياناتك، مع العلم أنه لا توجد وسيلة نقل أو تخزين إلكتروني آمنة بنسبة كاملة.</p>
<h2>7. حقوقك</h2>
<p>وفق الأنظمة المعمول بها، قد يحق لك طلب الاطلاع على بياناتك أو تصحيحها أو حذفها أو تقييد معالجتها، ضمن الحدود النظامية وما يلزم لتشغيل الطلبات والحسابات ذات الصلة. لتقديم طلب متعلق بخصوصيتك، تواصل معنا عبر وسائل الاتصال المتاحة.</p>
<h2>8. خصوصية الأطفال</h2>
<p>الصفحة غير موجّهة للأطفال دون السن النظامي المناسب دون موافقة ولي الأمر عند الحاجة. إذا وصلنا إلى علمنا بجمع بيانات طفل بالمخالفة لذلك، سنعمل على حذفها وفق الإجراءات المناسبة.</p>
<h2>9. التواصل</h2>
<p>لأي أسئلة حول سياسة الخصوصية، راسلنا عبر صفحة اتصل بنا أو عبر قنوات التواصل المعروضة على {$name}.</p>
HTML,
        ];
    }

    public function isSystemPage(): bool
    {
        return $this->type === contentTypeModel('pages')
            && in_array((string) $this->template, self::systemPageTemplates(), true);
    }

    public function getAvatarAttribute(): string
    {
        return 'https://api.dicebear.com/9.x/shapes/svg?seed='.$this->id;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'published' => 'منشور',
            default => 'مسودة',
        };
    }

    public function registerMediaCollections(): void
    {
        $disk = config('media-library.disk_name');

        foreach ([
            'editor-images',
            'portfolio-media',
            'store-media',
            'service-media',
            'digital-product-media',
            'digital-product-downloads',
            'digital-service-media',
            'on-demand-service-media',
            'menu-media',
            'course-media',
            'course-lesson-files',
            'unit-media',
        ] as $collection) {
            $this->addMediaCollection($collection)->useDisk($disk);
        }
    }

    public function hasMediaAtPath(string $collection, string $path): bool
    {
        if (! filled($path)) {
            return false;
        }

        return $this->getMedia($collection)
            ->contains(fn (Media $media): bool => $media->getPathRelativeToRoot() === $path
                || $media->getUrl() === $path);
    }

    public function attachMediaFromDiskIfNeeded(string $collection, string $path): void
    {
        if (! filled($path) || $this->hasMediaAtPath($collection, $path)) {
            return;
        }

        $disk = config('media-library.disk_name');

        if (! Storage::disk($disk)->exists($path)) {
            return;
        }

        $this->addMediaFromDisk($path, $disk)
            ->preservingOriginal()
            ->toMediaCollection($collection);
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function portfolioImages(): array
    {
        return $this->mediaIdUrlList('portfolio-media');
    }

    /**
     * @return array<int, string>
     */
    public function portfolioImageUrls(): array
    {
        return $this->mediaUrls($this->portfolioImages());
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function storeImages(): array
    {
        return $this->mediaIdUrlList('store-media');
    }

    /**
     * @return array<int, string>
     */
    public function storeImageUrls(): array
    {
        return $this->mediaUrls($this->storeImages());
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function serviceImages(): array
    {
        return $this->mediaIdUrlList('service-media');
    }

    /**
     * @return array<int, string>
     */
    public function serviceImageUrls(): array
    {
        return $this->mediaUrls($this->serviceImages());
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function unitImages(): array
    {
        return $this->mediaIdUrlList('unit-media');
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function digitalProductImages(): array
    {
        return $this->mediaIdUrlList('digital-product-media');
    }

    /**
     * @return array<int, array{id: int, name: string, url: string, size: int}>
     */
    public function digitalProductDownloadFiles(): array
    {
        return $this->getMedia('digital-product-downloads')
            ->map(fn (Media $media): array => [
                'id' => (int) $media->id,
                'name' => $media->file_name,
                'url' => $media->getUrl(),
                'size' => (int) $media->size,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function menuImages(): array
    {
        return $this->mediaIdUrlList('menu-media');
    }

    /**
     * @return array<int, string>
     */
    public function menuImageUrls(): array
    {
        return $this->mediaUrls($this->menuImages());
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function digitalServiceImages(): array
    {
        return $this->mediaIdUrlList('digital-service-media');
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function onDemandServiceImages(): array
    {
        return $this->mediaIdUrlList('on-demand-service-media');
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    protected function mediaIdUrlList(string $collection): array
    {
        return $this->getMedia($collection)
            ->map(fn (Media $media): array => [
                'id' => (int) $media->id,
                'url' => $media->getUrl(),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{id: int, url: string}>  $images
     * @return array<int, string>
     */
    protected function mediaUrls(array $images): array
    {
        return collect($images)
            ->pluck('url')
            ->values()
            ->all();
    }

    public function calendars(): MorphToMany
    {
        return $this->morphToMany(Calendar::class, 'bookable', 'bookables')
            ->withPivot(['type', 'active'])
            ->withTimestamps();
    }

    public function migrateLegacyPortfolioImagesIfNeeded(): void
    {
        if ($this->type !== 'portfolio') {
            return;
        }

        $legacyImages = data_get($this->data, 'images', []);

        if (! is_array($legacyImages) || $legacyImages === []) {
            return;
        }

        if ($this->getMedia('portfolio-media')->isEmpty()) {
            foreach ($legacyImages as $path) {
                if (! filled($path) || ! is_string($path)) {
                    continue;
                }

                $disk = config('media-library.disk_name');

                if (! Storage::disk($disk)->exists($path)) {
                    continue;
                }

                $this->addMediaFromDisk($path, $disk)
                    ->preservingOriginal()
                    ->toMediaCollection('portfolio-media');
            }
        }

        $data = $this->data ?? [];
        unset($data['images']);
        $this->forceFill(['data' => $data])->save();
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class)->orderBy('sort_order');
    }

    /**
     * @return array<string, string>
     */
    public static function newsletterMailStatusOptions(): array
    {
        return [
            'draft' => 'مسودة',
            'scheduled' => 'مجدولة',
            'sent' => 'تم الإرسال',
        ];
    }

    public function newsletterMailStatus(): string
    {
        $status = (string) data_get($this->data, 'mail_status', 'draft');

        return array_key_exists($status, self::newsletterMailStatusOptions())
            ? $status
            : 'draft';
    }

    public function getNewsletterMailStatusLabelAttribute(): string
    {
        return self::newsletterMailStatusOptions()[$this->newsletterMailStatus()] ?? 'مسودة';
    }

    public function newsletterSentAt(): ?Carbon
    {
        $value = data_get($this->data, 'sent_at');

        return filled($value) ? Carbon::parse($value) : null;
    }

    public function newsletterScheduledAt(): ?Carbon
    {
        $value = data_get($this->data, 'scheduled_at');

        return filled($value) ? Carbon::parse($value) : null;
    }

    public function newsletterRecipientsCount(): int
    {
        return (int) data_get($this->data, 'recipients_count', 0);
    }

    public function orderItemType(): string
    {
        return self::orderItemTypeFor((string) $this->type);
    }

    public function isShippable(): bool
    {
        return Order::isShippableItemType($this->orderItemType());
    }

    public static function orderItemTypeFor(string $contentType): string
    {
        return match ($contentType) {
            'product' => 'product',
            'service' => 'service',
            'course' => 'course',
            'digital-product' => 'digital_product',
            'digital-service' => 'digital_service',
            'on-demand-service' => 'on_demand_service',
            'menu' => 'menu',
            'unit' => 'unit_rental',
            default => 'other',
        };
    }

    public function cartImageUrl(): ?string
    {
        $url = match ($this->type) {
            'product' => $this->getFirstMediaUrl('store-media'),
            'service' => $this->getFirstMediaUrl('service-media'),
            'course' => $this->getFirstMediaUrl('course-media'),
            'digital-product' => $this->getFirstMediaUrl('digital-product-media'),
            'digital-service' => $this->getFirstMediaUrl('digital-service-media'),
            'on-demand-service' => $this->getFirstMediaUrl('on-demand-service-media'),
            'menu' => $this->getFirstMediaUrl('menu-media'),
            'unit' => $this->getFirstMediaUrl('unit-media'),
            default => null,
        };

        if (filled($url)) {
            return $url;
        }

        return contentImageUrl($this->avatar);
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function courseImages(): array
    {
        return $this->mediaIdUrlList('course-media');
    }

    /**
     * @return array<int, array{id: int, name: string, url: string, size: int, chapter_id: ?string, lesson_id: ?string}>
     */
    public function courseLessonFiles(): array
    {
        return $this->getMedia('course-lesson-files')
            ->map(fn (Media $media): array => [
                'id' => (int) $media->id,
                'name' => $media->file_name,
                'url' => $media->getUrl(),
                'size' => (int) $media->size,
                'chapter_id' => $media->getCustomProperty('chapter_id'),
                'lesson_id' => $media->getCustomProperty('lesson_id'),
            ])
            ->values()
            ->all();
    }

    public function courseLessonCount(): int
    {
        return collect(data_get($this->data, 'chapters', []))
            ->flatMap(fn (mixed $chapter): array => is_array($chapter) ? ($chapter['lessons'] ?? []) : [])
            ->count();
    }

    /**
     * @return array<string, string>
     */
    public static function courseLevelOptions(): array
    {
        return [
            'beginner' => 'مبتدئ',
            'intermediate' => 'متوسط',
            'advanced' => 'متقدم',
            'none' => 'بدون تصنيف',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function courseTypeOptions(): array
    {
        return [
            'recorded' => 'مسجلة',
            'live' => 'مباشرة',
            'hybrid' => 'مختلطة',
        ];
    }

    public function courseLevelLabel(): string
    {
        $level = (string) data_get($this->data, 'level', 'none');

        return self::courseLevelOptions()[$level] ?? self::courseLevelOptions()['none'];
    }
}
