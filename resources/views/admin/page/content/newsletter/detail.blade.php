<div class="bg-white rounded-2xl overflow-hidden" x-data="{ formTab: 'edit' }">
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
                <span class="text-gray-600 truncate">تحرير النشرة</span>
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
                x-on:click="formTab = 'send'"
                x-bind:class="formTab === 'send'
                    ? 'bg-white text-gray-900 shadow-sm font-semibold'
                    : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition"
            >
                <ui:icon name="mail" class="!w-4 !h-4 shrink-0" />
                الإرسال
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
        <div x-cloak x-show="formTab === 'edit'" class="space-y-2">
            <ui:input name="title" placeholder="عنوان النشرة" />

            <ui:input
                name="subject"
                label="موضوع البريد"
                placeholder="الموضوع الذي يظهر في صندوق الوارد"
                info="يُستخدم كعنوان رسالة البريد الإلكتروني عند الإرسال."
            />

            <ui:textarea
                name="subtitle"
                placeholder="نص معاينة"
                info="نص قصير يظهر تحت العنوان في أرشيف النشرة وفي معاينة البريد."
            />

            <ui:file name="image" label="صورة الغلاف" upload-label="رفع الصورة">
                @if ($image)
                    <img src="{{ $image->temporaryUrl() }}" class="w-full max-w-sm rounded-xl mb-2 object-cover" alt="">
                @elseif ($currentImage)
                    <img src="{{ $currentImage }}" class="w-full max-w-sm rounded-xl mb-2 object-cover" alt="">
                @endif
            </ui:file>

            <ui:ck
                name="body"
                :value="$body"
                :model-id="$content->id"
                model-type="content"
            />
        </div>

        <div x-cloak x-show="formTab === 'send'" class="space-y-2">
            <ui:select
                name="mailStatus"
                label="حالة الإرسال"
                :options="$mailStatusOptions"
                live
            />

            @if ($mailStatus === 'scheduled')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <ui:input name="scheduledDate" label="تاريخ الجدولة" type="date" dir="ltr" />
                    <ui:input name="scheduledTime" label="وقت الجدولة" type="time" dir="ltr" />
                </div>
            @endif

            @if ($mailStatus === 'sent' && $sentAt)
                <ui:alert
                    type="success"
                    title="تم الإرسال"
                    :text="'أُرسلت النشرة في '.e($sentAt)"
                />
            @endif

            <ui:input
                name="recipientsCount"
                label="عدد المستلمين"
                type="number"
                dir="ltr"
                min="0"
                step="1"
                placeholder="0"
                info="يُحدَّث تلقائياً عند ربط نظام الإرسال، أو يمكن إدخاله يدوياً بعد الإرسال."
            />
        </div>

        <div x-cloak x-show="formTab === 'advanced'" class="space-y-2">
            <ui:input
                name="slug"
                label="نص الرابط"
                dir="ltr"
                :prefix="$slugPrefix"
            />

            <ui:toggle name="published" label="نشر في أرشيف الموقع" live />
        </div>

        <x-slot:footer>
            <div class="flex items-center gap-2">
                <ui:button wire:click="saveAndClose" type="button" target="saveAndClose" label="حفظ وإغلاق" />
                <ui:button type="submit" target="save" label="{{ __('Save') }}" />
            </div>
        </x-slot:footer>
    </ui:form>
</div>

<?php

use App\Models\Content;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends \Livewire\Component
{
    use WithFileUploads;

    /** @var array<string, mixed> */
    public array $contentType = [];

    public string $itemId = '';

    public string $title = '';

    public string $subject = '';

    public string $subtitle = '';

    public string $body = '';

    public string $editorMode = 'html';

    public string $slug = '';

    public string $mailStatus = 'draft';

    public string $scheduledDate = '';

    public string $scheduledTime = '';

    public string $recipientsCount = '';

    public ?string $sentAt = null;

    public bool $published = false;

    public $image = null;

    public ?string $currentImage = null;

    public function mount(): void
    {
        $content = $this->content();

        $this->title = $content->title;
        $this->subject = (string) data_get($content->data, 'subject', '');
        $this->subtitle = (string) data_get($content->data, 'subtitle', '');
        $this->body = (string) data_get($content->data, 'body', '');
        $this->editorMode = (string) data_get($content->data, 'editor_mode', 'html');
        $this->slug = $content->slug;
        $this->mailStatus = $content->newsletterMailStatus();
        $this->recipientsCount = $this->formatCount(data_get($content->data, 'recipients_count'));
        $this->published = $content->status === 'published';
        $this->currentImage = contentImageUrl(data_get($content->data, 'image'));

        $scheduledAt = $content->newsletterScheduledAt();
        $this->scheduledDate = $scheduledAt?->format('Y-m-d') ?? '';
        $this->scheduledTime = $scheduledAt?->format('H:i') ?? '';

        $sentAt = $content->newsletterSentAt();
        $this->sentAt = $sentAt?->translatedFormat('j F Y، H:i');
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
    public function mailStatusOptions(): array
    {
        return Content::newsletterMailStatusOptions();
    }

    public function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/newsletter/';
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => 'required|min:1|max:255',
            'subject' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'body' => 'nullable|string',
            'editorMode' => 'required|in:html,markdown',
            'slug' => 'required|string|max:255',
            'mailStatus' => ['required', Rule::in(array_keys(Content::newsletterMailStatusOptions()))],
            'scheduledDate' => 'nullable|required_if:mailStatus,scheduled|date',
            'scheduledTime' => 'nullable|required_if:mailStatus,scheduled|date_format:H:i',
            'recipientsCount' => 'nullable|integer|min:0',
            'published' => 'boolean',
            'image' => 'nullable|image|max:15360',
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

        $content = $this->content();
        $data = $content->data ?? [];

        $data['subject'] = $this->subject;
        $data['subtitle'] = $this->subtitle;
        $data['body'] = $this->body;
        $data['editor_mode'] = $this->editorMode;
        $data['mail_status'] = $this->mailStatus;
        $data['recipients_count'] = filled($this->recipientsCount) ? (int) $this->recipientsCount : 0;

        if ($this->mailStatus === 'scheduled' && filled($this->scheduledDate) && filled($this->scheduledTime)) {
            $data['scheduled_at'] = Carbon::parse($this->scheduledDate.' '.$this->scheduledTime)->toIso8601String();
        } else {
            unset($data['scheduled_at']);
        }

        if ($this->mailStatus === 'sent') {
            $data['sent_at'] = data_get($content->data, 'sent_at') ?? now()->toIso8601String();
        } else {
            unset($data['sent_at']);
        }

        if ($this->image instanceof TemporaryUploadedFile) {
            $tenantUuid = tenant('uuid') ?? 'shared';
            $path = $this->image->storePublicly('tenant-media/'.$tenantUuid.'/newsletter', 'spaces');
            $data['image'] = $path;
            $this->currentImage = contentImageUrl($path);
            $this->image = null;
        }

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
        $sentAt = $content->fresh()->newsletterSentAt();
        $this->sentAt = $sentAt?->translatedFormat('j F Y، H:i');
        $this->dispatch('updateNewsletterList');
        $this->dispatch('notify', text: __('Saved'));

        if ($close) {
            return $this->redirect(route('admin.page.home', [
                'tab' => $this->contentType['tab_id'],
            ]), navigate: true);
        }

        return null;
    }

    private function uniqueSlug(string $baseSlug, int $exceptId): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'newsletter';
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

    private function formatCount(mixed $value): string
    {
        $count = (int) $value;

        return $count > 0 ? (string) $count : '';
    }

    public function render()
    {
        return $this->view([
            'content' => $this->content(),
            'mailStatusOptions' => $this->mailStatusOptions(),
            'slugPrefix' => $this->slugPrefix(),
        ]);
    }
}; ?>
