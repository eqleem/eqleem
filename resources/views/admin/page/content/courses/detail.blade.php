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
                <span class="text-gray-600 truncate">{{ __('Edit course') }}</span>
            </div>
        </div>

        <nav class="flex items-center gap-1 shrink-0 bg-gray-300/40 rounded-xl p-0.5 overflow-x-auto">
            <button
                type="button"
                x-on:click="formTab = 'edit'"
                x-bind:class="formTab === 'edit'
                    ? 'bg-white text-gray-900 shadow-sm font-semibold'
                    : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition whitespace-nowrap"
            >
                <ui:icon name="Pencil" class="!w-4 !h-4 shrink-0" />
                تحرير
            </button>
            <button
                type="button"
                x-on:click="formTab = 'curriculum'"
                x-bind:class="formTab === 'curriculum'
                    ? 'bg-white text-gray-900 shadow-sm font-semibold'
                    : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition whitespace-nowrap"
            >
                <ui:icon name="list-details" class="!w-4 !h-4 shrink-0" />
                المحتوى التعليمي
            </button>
            <button
                type="button"
                x-on:click="formTab = 'advanced'"
                x-bind:class="formTab === 'advanced'
                    ? 'bg-white text-gray-900 shadow-sm font-semibold'
                    : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition whitespace-nowrap"
            >
                <ui:icon name="adjustments-horizontal" class="!w-4 !h-4 shrink-0" />
                متقدم
            </button>
        </nav>
    </div>

    <ui:form wire:submit="save" class="!p-4 md:!p-6 !rounded-none">
        <div x-cloak x-show="formTab === 'edit'" class="space-y-2">
            <ui:input
                name="title"
                label="اسم الدورة"
                placeholder="مثال: مهارات المحادثة باللغة الإنجليزية للمبتدئين"
            />

            <ui:textarea
                name="subtitle"
                label="عنوان ترويجي"
                placeholder="مثال: مقدمة شاملة تأخذك من ما قبل البداية إلى مرحلة الإنطلاقة"
                info="عنوان فرعي لا يزيد عن 300 حرف، يظهر تحت العنوان بصفحة الدورة. يمكنك كتابة وصف مفصل لاحقاً."
                maxlength="300"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <ui:input
                    name="price"
                    label="السعر"
                    type="number"
                    dir="ltr"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    suffix="ر.س"
                />

                <ui:input
                    name="comparePrice"
                    label="سعر المقارنة"
                    type="number"
                    dir="ltr"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    suffix="ر.س"
                />
            </div>

            <ui:upload
                name="images"
                :value="$images"
                label="الصورة"
                :block="true"
                :multiple="true"
                :max-files="1"
                :sortable="false"
                collection="course-media"
                :model-id="$content->id"
                model-type="content"
                add-method="addImage"
                remove-method="removeImage"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <ui:select
                    name="courseType"
                    label="نوع الدورة"
                    :options="$courseTypeOptions"
                />

                <ui:input
                    name="hours"
                    label="عدد ساعات الدورة"
                    type="number"
                    dir="ltr"
                    step="0.5"
                    min="0"
                    placeholder="0"
                    suffix="ساعات"
                />
            </div>

            <ui:radio
                name="level"
                label="المستوى"
                :options="$levelOptions"
            />

            <ui:ck
                name="body"
                label="الوصف"
                :value="$body"
                :model-id="$content->id"
                model-type="content"
            />
        </div>

        <div x-cloak x-show="formTab === 'curriculum'" class="space-y-4">
            <ui:alert
                type="info"
                title="المحتوى التعليمي"
                text="نظّم الدورة في فصول (أقسام) ودروس. لكل درس يمكنك رفع ملف (فيديو، صوت، مستند) أو إضافة رابط خارجي مثل يوتيوب."
            />

            <div class="flex items-center justify-between gap-3">
                <p class="text-sm text-gray-600">
                    {{ count($chapters) }} {{ count($chapters) === 1 ? 'فصل' : 'فصول' }}
                    —
                    {{ $this->totalLessons() }} {{ $this->totalLessons() === 1 ? 'درس' : 'دروس' }}
                </p>
                <ui:button type="button" wire:click="addChapter" label="إضافة فصل" icon="square-rounded-plus" />
            </div>

            @if ($chapters === [])
                <ui:empty subtitle="أضف فصولاً ودروساً لبناء محتوى الدورة.">
                    لا يوجد محتوى تعليمي بعد.
                    <x-slot:icon>
                        <ui:icon name="list-details" class="w-12 h-12 opacity-50" />
                    </x-slot:icon>
                </ui:empty>
            @else
                <div class="space-y-3">
                    @foreach ($chapters as $chapterIndex => $chapter)
                        <div
                            wire:key="course-chapter-{{ $chapter['id'] }}"
                            class="rounded-xl border border-stone-200 bg-stone-50/60 overflow-hidden"
                            x-data="{ open: true }"
                        >
                            <div class="flex items-center justify-between gap-3 bg-white px-4 py-3 border-b border-stone-200">
                                <button
                                    type="button"
                                    x-on:click="open = !open"
                                    class="flex items-center gap-2 min-w-0 text-start"
                                >
                                    <ui:icon
                                        name="chevron-down"
                                        class="!w-4 !h-4 text-gray-400 transition"
                                        x-bind:class="open ? '' : '-rotate-90'"
                                    />
                                    <span class="text-sm font-semibold text-gray-800 truncate">
                                        فصل {{ $chapterIndex + 1 }}
                                        @if (filled($chapter['title']))
                                            — {{ $chapter['title'] }}
                                        @endif
                                    </span>
                                    <span class="text-xs text-gray-500 shrink-0">
                                        {{ count($chapter['lessons'] ?? []) }} دروس
                                    </span>
                                </button>

                                <div class="flex items-center gap-1 shrink-0">
                                    <button
                                        type="button"
                                        wire:click="addLesson({{ $chapterIndex }})"
                                        class="rounded-lg px-2.5 py-1.5 text-sm text-gray-600 hover:bg-primary-50 hover:text-primary-600 flex items-center gap-1.5 transition"
                                        title="إضافة درس"
                                    >
                                        <ui:icon name="square-rounded-plus" class="!w-4 !h-4" />
                                        <span>إضافة درس</span>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="removeChapter({{ $chapterIndex }})"
                                        wire:confirm="هل أنت متأكد من حذف هذا الفصل وجميع دروسه؟"
                                        class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600"
                                        title="حذف الفصل"
                                    >
                                        <ui:icon name="trash" class="!w-4 !h-4" />
                                    </button>
                                </div>
                            </div>

                            <div x-show="open" x-collapse class="p-4 space-y-3">
                                <ui:input
                                    name="chapters.{{ $chapterIndex }}.title"
                                    label="عنوان الفصل"
                                    placeholder="مثال: الأساسيات والتحضير"
                                />

                                <ui:textarea
                                    name="chapters.{{ $chapterIndex }}.description"
                                    label="وصف الفصل"
                                    placeholder="وصف بسيط عن محتوى هذا الفصل"
                                    rows="2"
                                />

                                @if (($chapter['lessons'] ?? []) === [])
                                    <p class="text-sm text-gray-500 py-2">لا توجد دروس في هذا الفصل بعد.</p>
                                @else
                                    <div class="space-y-2">
                                        @foreach ($chapter['lessons'] as $lessonIndex => $lesson)
                                            <div
                                                wire:key="course-lesson-{{ $lesson['id'] }}"
                                                class="rounded-lg border border-stone-200 bg-white overflow-hidden"
                                                x-data="{ open: false }"
                                            >
                                                <div class="flex items-center justify-between gap-3 bg-stone-50 px-3 py-2.5 border-b border-stone-100">
                                                    <button
                                                        type="button"
                                                        x-on:click="open = !open"
                                                        class="flex items-center gap-2 min-w-0 text-start flex-1"
                                                    >
                                                        <ui:icon
                                                            name="chevron-down"
                                                            class="!w-4 !h-4 text-gray-400 transition shrink-0"
                                                            x-bind:class="open ? '' : '-rotate-90'"
                                                        />
                                                        <span class="text-sm font-semibold text-gray-700 truncate">
                                                            درس {{ $lessonIndex + 1 }}
                                                            @if (filled($lesson['title']))
                                                                — {{ $lesson['title'] }}
                                                            @endif
                                                        </span>
                                                        @if (($lesson['source'] ?? 'file') === 'link' && filled($lesson['link'] ?? null))
                                                            <span class="text-xs text-primary-600 bg-primary-50 px-2 py-0.5 rounded-md shrink-0">رابط</span>
                                                        @elseif (filled($lesson['file_name'] ?? null))
                                                            <span class="text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md shrink-0">ملف</span>
                                                        @endif
                                                    </button>

                                                    <button
                                                        type="button"
                                                        wire:click="removeLesson({{ $chapterIndex }}, {{ $lessonIndex }})"
                                                        wire:confirm="هل أنت متأكد من حذف هذا الدرس؟"
                                                        class="rounded-lg p-1 text-gray-400 hover:bg-red-50 hover:text-red-600 shrink-0"
                                                        title="حذف الدرس"
                                                    >
                                                        <ui:icon name="trash" class="!w-4 !h-4" />
                                                    </button>
                                                </div>

                                                <div x-show="open" x-collapse class="p-4 space-y-3">
                                                <ui:input
                                                    name="chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.title"
                                                    label="عنوان الدرس"
                                                    placeholder="مثال: مقدمة الدورة وخارطة التعلم"
                                                />

                                                <ui:textarea
                                                    name="chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.description"
                                                    label="وصف الدرس"
                                                    placeholder="وصف بسيط عن محتوى الدرس"
                                                    rows="2"
                                                />

                                                <ui:select
                                                    name="chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.source"
                                                    label="مصدر المحتوى"
                                                    :options="['file' => 'رفع ملف', 'link' => 'رابط خارجي']"
                                                    live
                                                />

                                                @if (($lesson['source'] ?? 'file') === 'link')
                                                    <ui:input
                                                        name="chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.link"
                                                        label="رابط الدرس"
                                                        placeholder="https://www.youtube.com/watch?v=..."
                                                        dir="ltr"
                                                        info="يمكنك إضافة رابط يوتيوب أو أي منصة فيديو أخرى."
                                                    />
                                                @else
                                                    <div class="space-y-2">
                                                        <ui:field label="ملف الدرس">
                                                            @if (filled($lesson['file_name'] ?? null))
                                                                <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                                                    <ui:icon name="file-download" class="!w-5 !h-5 text-primary-500 shrink-0" />
                                                                    <div class="min-w-0 flex-1">
                                                                        <p class="truncate text-sm font-medium text-gray-800">
                                                                            {{ $lesson['file_name'] }}
                                                                        </p>
                                                                        @if (filled($lesson['file_url'] ?? null))
                                                                            <a
                                                                                href="{{ $lesson['file_url'] }}"
                                                                                target="_blank"
                                                                                class="text-xs text-primary-600 hover:underline"
                                                                            >
                                                                                معاينة الملف
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                    @if (filled($lesson['media_id'] ?? null))
                                                                        <button
                                                                            type="button"
                                                                            wire:click="removeLessonFile({{ $chapterIndex }}, {{ $lessonIndex }})"
                                                                            class="rounded-lg p-1 text-gray-400 hover:bg-red-50 hover:text-red-600"
                                                                            title="حذف الملف"
                                                                        >
                                                                            <ui:icon name="trash" class="!w-4 !h-4" />
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <p class="text-sm text-gray-500 mb-2">
                                                                    لم يتم رفع ملف بعد. يدعم الفيديو والصوت والمستندات.
                                                                </p>
                                                            @endif

                                                            <button
                                                                type="button"
                                                                wire:click="prepareLessonUpload({{ $chapterIndex }}, {{ $lessonIndex }})"
                                                                class="text-gray-700 cursor-pointer hover:bg-primary-100 bg-white border shadow-sm p-2 px-3 rounded-lg flex items-center gap-x-2 text-sm w-fit"
                                                            >
                                                                <ui:icon name="upload" class="!w-5 !h-5" />
                                                                <span>
                                                                    @if (filled($lesson['file_name'] ?? null))
                                                                        استبدال الملف
                                                                    @else
                                                                        رفع ملف الدرس
                                                                    @endif
                                                                </span>
                                                            </button>
                                                        </ui:field>
                                                    </div>
                                                @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <ui:button
                                    type="button"
                                    wire:click="addLesson({{ $chapterIndex }})"
                                    label="إضافة درس"
                                    icon="square-rounded-plus"
                                    class="!bg-white"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div x-cloak x-show="formTab === 'advanced'" class="space-y-2">
            <ui:checkbox-select
                name="categoryIds"
                label="القسم"
                :options="$categories"
                :selected="$categoryIds"
                placeholder="اختر الأقسام"
            />

            <ui:input
                name="slug"
                label="نص الرابط"
                dir="ltr"
                :prefix="$slugPrefix"
            />

            <ui:toggle name="published" label="حالة النشر" live />
        </div>

        <x-slot:footer>
            <div class="flex items-center gap-2">
                <ui:button wire:click="saveAndClose" type="button" target="saveAndClose" label="حفظ وإغلاق" />
                <ui:button type="submit" target="save" label="{{ __('Save') }}" />
            </div>
        </x-slot:footer>
    </ui:form>

    <ui:modal :title="$this->lessonUploadModalTitle()" size="lg" name="course-lesson-file-upload">
        @if ($activeUploadChapterIndex !== null && $activeUploadLessonIndex !== null)
            <div class="p-4 space-y-3">
                <p class="text-sm text-gray-600">
                    {{ $this->lessonUploadModalSubtitle() }}
                </p>

                <ui:upload-files
                    name="lessonFileUpload"
                    :value="[]"
                    label="ملف الدرس"
                    :block="true"
                    :multiple="false"
                    :max-files="1"
                    :sortable="false"
                    collection="course-lesson-files"
                    :model-id="$content->id"
                    model-type="content"
                    add-method="addLessonFile"
                    remove-method="removeActiveLessonFile"
                    reorder-method="noopReorderLessonFiles"
                    upload-label="اختر الملف وارفعه"
                    empty-text="ارفع فيديو أو صوت أو مستند للدرس. يُغلق النافذة تلقائياً بعد اكتمال الرفع."
                    :key="'course-lesson-uploader-'.$activeUploadChapterIndex.'-'.$activeUploadLessonIndex"
                />
            </div>
        @endif
    </ui:modal>
</div>

<?php

use App\Models\Content;
use App\Models\Media;
use App\Models\Taxonomy;
use App\Support\Money;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

new class extends \Livewire\Component
{
    /** @var array<string, mixed> */
    public array $contentType = [];

    public string $itemId = '';

    public string $title = '';

    public string $subtitle = '';

    public string $body = '';

    public string $editorMode = 'html';

    public string $slug = '';

    public string $price = '';

    public string $comparePrice = '';

    public string $hours = '0';

    public string $level = 'beginner';

    public string $courseType = 'recorded';

    /** @var array<int, string> */
    public array $categoryIds = [];

    /** @var array<int, array{id: int, url: string}> */
    public array $images = [];

    public bool $published = false;

    /** @var array<int, array{id: string, title: string, description: string, lessons: array<int, array<string, mixed>>}> */
    public array $chapters = [];

    public ?int $activeUploadChapterIndex = null;

    public ?int $activeUploadLessonIndex = null;

    public function mount(): void
    {
        $content = $this->content();

        $this->title = $content->title;
        $this->subtitle = (string) data_get($content->data, 'subtitle', '');
        $this->body = (string) data_get($content->data, 'body', '');
        $this->editorMode = (string) data_get($content->data, 'editor_mode', 'html');
        $this->slug = $content->slug;
        $this->price = $this->decimalFromMinor(data_get($content->data, 'price'));
        $this->comparePrice = $this->decimalFromMinor(data_get($content->data, 'compare_price'));
        $this->hours = (string) data_get($content->data, 'hours', 0);
        $this->level = (string) data_get($content->data, 'level', 'beginner');
        $this->courseType = (string) data_get($content->data, 'course_type', 'recorded');
        $this->categoryIds = $content->taxonomiesOfType('course_category')
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->values()
            ->all();
        $this->published = $content->status === 'published';
        $this->images = $content->fresh()->courseImages();
        $this->chapters = $this->normalizeChapters(data_get($content->data, 'chapters', []));
    }

    public function content(): Content
    {
        return Content::query()
            ->type(contentTypeModel($this->contentType['slug']))
            ->where('uuid', $this->itemId)
            ->firstOrFail();
    }

    public function totalLessons(): int
    {
        return collect($this->chapters)
            ->sum(fn (array $chapter): int => count($chapter['lessons'] ?? []));
    }

    /**
     * @return array<string, string>
     */
    public function levelOptions(): array
    {
        return Content::courseLevelOptions();
    }

    /**
     * @return array<string, string>
     */
    public function courseTypeOptions(): array
    {
        return Content::courseTypeOptions();
    }

    /**
     * @return array<int, array{id: string, label: string, selectable: bool}>
     */
    public function categories(): array
    {
        $parentIds = Taxonomy::query()
            ->type('course_category')
            ->whereNotNull('parent_id')
            ->pluck('parent_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->flip();

        return Taxonomy::flatTree('course_category')
            ->map(fn (Taxonomy $item): array => [
                'id' => (string) $item->id,
                'label' => str_repeat('— ', (int) ($item->depth ?? 0)).$item->name,
                'selectable' => ! $parentIds->has((int) $item->id),
            ])
            ->all();
    }

    public function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/courses/';
    }

    public function addChapter(): void
    {
        $this->chapters[] = [
            'id' => (string) Str::uuid(),
            'title' => '',
            'description' => '',
            'lessons' => [],
        ];
    }

    public function removeChapter(int $chapterIndex): void
    {
        if (! isset($this->chapters[$chapterIndex])) {
            return;
        }

        $chapterId = $this->chapters[$chapterIndex]['id'] ?? null;

        if (is_string($chapterId)) {
            $this->deleteLessonMediaForChapter($chapterId);
        }

        unset($this->chapters[$chapterIndex]);
        $this->chapters = array_values($this->chapters);
        $this->clearActiveUpload();
    }

    public function addLesson(int $chapterIndex): void
    {
        if (! isset($this->chapters[$chapterIndex])) {
            return;
        }

        $this->chapters[$chapterIndex]['lessons'][] = $this->blankLesson();
    }

    public function removeLesson(int $chapterIndex, int $lessonIndex): void
    {
        if (! isset($this->chapters[$chapterIndex]['lessons'][$lessonIndex])) {
            return;
        }

        $lesson = $this->chapters[$chapterIndex]['lessons'][$lessonIndex];
        $mediaId = isset($lesson['media_id']) ? (int) $lesson['media_id'] : null;

        if ($mediaId) {
            $this->deleteLessonMedia($mediaId);
        }

        unset($this->chapters[$chapterIndex]['lessons'][$lessonIndex]);
        $this->chapters[$chapterIndex]['lessons'] = array_values($this->chapters[$chapterIndex]['lessons']);
        $this->clearActiveUpload();
    }

    public function prepareLessonUpload(int $chapterIndex, int $lessonIndex): void
    {
        if (! isset($this->chapters[$chapterIndex]['lessons'][$lessonIndex])) {
            return;
        }

        $this->activeUploadChapterIndex = $chapterIndex;
        $this->activeUploadLessonIndex = $lessonIndex;
        $this->dispatch('openmodal', modal: 'course-lesson-file-upload');
    }

    public function lessonUploadModalTitle(): string
    {
        return 'رفع ملف الدرس';
    }

    public function lessonUploadModalSubtitle(): string
    {
        if ($this->activeUploadChapterIndex === null || $this->activeUploadLessonIndex === null) {
            return '';
        }

        $chapterTitle = trim((string) data_get($this->chapters, $this->activeUploadChapterIndex.'.title', ''));
        $lessonTitle = trim((string) data_get(
            $this->chapters,
            $this->activeUploadChapterIndex.'.lessons.'.$this->activeUploadLessonIndex.'.title',
            ''
        ));

        $chapterLabel = $chapterTitle !== ''
            ? $chapterTitle
            : 'فصل '.($this->activeUploadChapterIndex + 1);

        $lessonLabel = $lessonTitle !== ''
            ? $lessonTitle
            : 'درس '.($this->activeUploadLessonIndex + 1);

        return "رفع ملف لـ: {$chapterLabel} / {$lessonLabel}";
    }

    public function addLessonFile(string $path): void
    {
        if (
            $this->activeUploadChapterIndex === null
            || $this->activeUploadLessonIndex === null
            || ! filled($path)
        ) {
            return;
        }

        $chapterIndex = $this->activeUploadChapterIndex;
        $lessonIndex = $this->activeUploadLessonIndex;

        if (! isset($this->chapters[$chapterIndex]['lessons'][$lessonIndex])) {
            return;
        }

        $chapterId = $this->chapters[$chapterIndex]['id'];
        $lessonId = $this->chapters[$chapterIndex]['lessons'][$lessonIndex]['id'];
        $content = $this->content();

        $existingMediaId = isset($this->chapters[$chapterIndex]['lessons'][$lessonIndex]['media_id'])
            ? (int) $this->chapters[$chapterIndex]['lessons'][$lessonIndex]['media_id']
            : null;

        if ($existingMediaId) {
            $this->deleteLessonMedia($existingMediaId);
        }

        $disk = config('media-library.disk_name');

        if (! Storage::disk($disk)->exists($path)) {
            return;
        }

        $media = $content->addMediaFromDisk($path, $disk)
            ->withCustomProperties([
                'chapter_id' => $chapterId,
                'lesson_id' => $lessonId,
            ])
            ->preservingOriginal()
            ->toMediaCollection('course-lesson-files');

        $this->chapters[$chapterIndex]['lessons'][$lessonIndex]['source'] = 'file';
        $this->chapters[$chapterIndex]['lessons'][$lessonIndex]['media_id'] = $media->id;
        $this->chapters[$chapterIndex]['lessons'][$lessonIndex]['file_name'] = $media->file_name;
        $this->chapters[$chapterIndex]['lessons'][$lessonIndex]['file_url'] = $media->getUrl();
        $this->chapters[$chapterIndex]['lessons'][$lessonIndex]['link'] = '';

        $this->clearActiveUpload();
        $this->dispatch('notify', text: __('تم رفع ملف الدرس بنجاح.'));
    }

    public function removeLessonFile(int $chapterIndex, int $lessonIndex): void
    {
        if (! isset($this->chapters[$chapterIndex]['lessons'][$lessonIndex])) {
            return;
        }

        $mediaId = isset($this->chapters[$chapterIndex]['lessons'][$lessonIndex]['media_id'])
            ? (int) $this->chapters[$chapterIndex]['lessons'][$lessonIndex]['media_id']
            : null;

        if ($mediaId) {
            $this->deleteLessonMedia($mediaId);
        }

        $this->chapters[$chapterIndex]['lessons'][$lessonIndex]['media_id'] = null;
        $this->chapters[$chapterIndex]['lessons'][$lessonIndex]['file_name'] = '';
        $this->chapters[$chapterIndex]['lessons'][$lessonIndex]['file_url'] = '';
    }

    public function removeActiveLessonFile(int $mediaId): void
    {
        if ($this->activeUploadChapterIndex === null || $this->activeUploadLessonIndex === null) {
            return;
        }

        $this->removeLessonFile($this->activeUploadChapterIndex, $this->activeUploadLessonIndex);
    }

    /**
     * @param  array<int, int|string>  $orderedIds
     */
    public function noopReorderLessonFiles(array $orderedIds): void
    {
        //
    }

    public function addImage(string $path): void
    {
        if (! filled($path)) {
            return;
        }

        $content = $this->content();

        if ($content->hasMediaAtPath('course-media', $path)) {
            $this->images = $content->fresh()->courseImages();

            return;
        }

        $content->getMedia('course-media')->each(fn (Media $media) => $media->delete());

        $content->attachMediaFromDiskIfNeeded('course-media', $path);
        $this->images = $content->fresh()->courseImages();
    }

    public function removeImage(int $mediaId): void
    {
        $content = $this->content();
        $media = $content->getMedia('course-media')->firstWhere('id', $mediaId);

        if ($media instanceof Media) {
            $media->delete();
        }

        $this->images = $content->fresh()->courseImages();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => 'required|min:1|max:255',
            'subtitle' => 'nullable|string|max:300',
            'body' => 'nullable|string',
            'editorMode' => 'required|in:html,markdown',
            'slug' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'comparePrice' => 'nullable|numeric|min:0',
            'hours' => 'nullable|numeric|min:0',
            'level' => ['required', Rule::in(array_keys(Content::courseLevelOptions()))],
            'courseType' => ['required', Rule::in(array_keys(Content::courseTypeOptions()))],
            'categoryIds' => 'nullable|array',
            'categoryIds.*' => [
                Rule::exists('taxonomies', 'id')->where(function ($query): void {
                    $query->where('type', 'course_category');

                    if ($tenantId = currentTenantId()) {
                        $query->where('tenant_id', $tenantId);
                    }
                }),
            ],
            'published' => 'boolean',
            'chapters' => 'array',
            'chapters.*.title' => 'nullable|string|max:255',
            'chapters.*.description' => 'nullable|string|max:1000',
            'chapters.*.lessons' => 'array',
            'chapters.*.lessons.*.title' => 'nullable|string|max:255',
            'chapters.*.lessons.*.description' => 'nullable|string|max:1000',
            'chapters.*.lessons.*.source' => 'required|in:file,link',
            'chapters.*.lessons.*.link' => 'nullable|string|max:2000',
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

        $data['subtitle'] = $this->subtitle;
        $data['body'] = $this->body;
        $data['editor_mode'] = $this->editorMode;
        $data['price'] = filled($this->price) ? money_minor($this->price) : 0;
        $data['compare_price'] = filled($this->comparePrice) ? money_minor($this->comparePrice) : null;
        $data['hours'] = filled($this->hours) ? (float) $this->hours : 0;
        $data['level'] = $this->level;
        $data['course_type'] = $this->courseType;
        $data['chapters'] = $this->serializeChapters();

        $selectableIds = collect($this->categories())
            ->where('selectable', true)
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->all();

        $categoryIds = collect($this->categoryIds)
            ->map(fn (mixed $id): string => (string) $id)
            ->intersect($selectableIds)
            ->map(fn (string $id): int => (int) $id)
            ->values()
            ->all();

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

        $content->syncTaxonomiesOfType('course_category', $categoryIds);

        $this->slug = $slug;
        $this->images = $content->fresh()->courseImages();
        $this->dispatch('updateCourseList');
        $this->dispatch('notify', text: __('Saved'));

        if ($close) {
            return $this->redirect(route('admin.page.home', [
                'tab' => $this->contentType['tab_id'],
            ]), navigate: true);
        }

        return null;
    }

    /**
     * @param  mixed  $chapters
     * @return array<int, array{id: string, title: string, description: string, lessons: array<int, array<string, mixed>>}>
     */
    private function normalizeChapters(mixed $chapters): array
    {
        if (! is_array($chapters)) {
            return [];
        }

        $lessonFiles = collect($this->content()->courseLessonFiles())->keyBy('lesson_id');

        return collect($chapters)
            ->filter(fn (mixed $chapter): bool => is_array($chapter))
            ->values()
            ->map(function (array $chapter) use ($lessonFiles): array {
                $chapterId = filled($chapter['id'] ?? null)
                    ? (string) $chapter['id']
                    : (string) Str::uuid();

                $lessons = collect($chapter['lessons'] ?? [])
                    ->filter(fn (mixed $lesson): bool => is_array($lesson))
                    ->values()
                    ->map(function (array $lesson) use ($lessonFiles, $chapterId): array {
                        $lessonId = filled($lesson['id'] ?? null)
                            ? (string) $lesson['id']
                            : (string) Str::uuid();

                        $normalized = $this->blankLesson();
                        $normalized['id'] = $lessonId;
                        $normalized['title'] = (string) ($lesson['title'] ?? '');
                        $normalized['description'] = (string) ($lesson['description'] ?? '');
                        $normalized['source'] = in_array($lesson['source'] ?? 'file', ['file', 'link'], true)
                            ? (string) $lesson['source']
                            : 'file';
                        $normalized['link'] = (string) ($lesson['link'] ?? '');

                        $media = $lessonFiles->get($lessonId);

                        if ($media) {
                            $normalized['source'] = 'file';
                            $normalized['media_id'] = $media['id'];
                            $normalized['file_name'] = $media['name'];
                            $normalized['file_url'] = $media['url'];
                        } elseif (isset($lesson['media_id'])) {
                            $normalized['media_id'] = (int) $lesson['media_id'];
                            $normalized['file_name'] = (string) ($lesson['file_name'] ?? '');
                            $normalized['file_url'] = (string) ($lesson['file_url'] ?? '');
                        }

                        return $normalized;
                    })
                    ->all();

                return [
                    'id' => $chapterId,
                    'title' => (string) ($chapter['title'] ?? ''),
                    'description' => (string) ($chapter['description'] ?? ''),
                    'lessons' => $lessons,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function serializeChapters(): array
    {
        return collect($this->chapters)
            ->map(fn (array $chapter): array => [
                'id' => $chapter['id'],
                'title' => trim((string) ($chapter['title'] ?? '')),
                'description' => trim((string) ($chapter['description'] ?? '')),
                'lessons' => collect($chapter['lessons'] ?? [])
                    ->map(fn (array $lesson): array => [
                        'id' => $lesson['id'],
                        'title' => trim((string) ($lesson['title'] ?? '')),
                        'description' => trim((string) ($lesson['description'] ?? '')),
                        'source' => in_array($lesson['source'] ?? 'file', ['file', 'link'], true)
                            ? $lesson['source']
                            : 'file',
                        'link' => ($lesson['source'] ?? 'file') === 'link'
                            ? trim((string) ($lesson['link'] ?? ''))
                            : '',
                        'media_id' => filled($lesson['media_id'] ?? null) ? (int) $lesson['media_id'] : null,
                        'file_name' => (string) ($lesson['file_name'] ?? ''),
                        'file_url' => (string) ($lesson['file_url'] ?? ''),
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function blankLesson(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'title' => '',
            'description' => '',
            'source' => 'file',
            'link' => '',
            'media_id' => null,
            'file_name' => '',
            'file_url' => '',
        ];
    }

    private function deleteLessonMedia(int $mediaId): void
    {
        $content = $this->content();
        $media = $content->getMedia('course-lesson-files')->firstWhere('id', $mediaId);

        if ($media instanceof Media) {
            $media->delete();
        }
    }

    private function deleteLessonMediaForChapter(string $chapterId): void
    {
        $this->content()
            ->getMedia('course-lesson-files')
            ->filter(fn (Media $media): bool => (string) $media->getCustomProperty('chapter_id') === $chapterId)
            ->each(fn (Media $media) => $media->delete());
    }

    public function clearActiveUpload(): void
    {
        $this->activeUploadChapterIndex = null;
        $this->activeUploadLessonIndex = null;
        $this->dispatch('closemodal', modal: 'course-lesson-file-upload');
    }

    private function uniqueSlug(string $baseSlug, int $exceptId): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'course';
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

    private function decimalFromMinor(mixed $minor): string
    {
        if ($minor === null || $minor === '' || (int) $minor === 0) {
            return '';
        }

        return (string) Money::fromMinor((int) $minor);
    }

    public function render()
    {
        return $this->view([
            'content' => $this->content(),
            'categories' => $this->categories(),
            'slugPrefix' => $this->slugPrefix(),
            'levelOptions' => $this->levelOptions(),
            'courseTypeOptions' => $this->courseTypeOptions(),
        ]);
    }
}; ?>
