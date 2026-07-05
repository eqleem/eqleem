<?php

namespace App\Livewire\Tenant\Courses;

use App\Models\Content;
use App\Services\CartService;
use Livewire\Component;

class Detail extends Component
{
    public Content $course;

    public bool $addedToCart = false;

    public function mount(string $slug): void
    {
        $this->course = Content::query()
            ->type(contentTypeModel('courses'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function addToCart(CartService $cart): void
    {
        $cart->addItem($this->course);

        $this->addedToCart = true;
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        $categories = $this->course->taxonomiesOfType('course_category');
        $images = collect($this->course->courseImages())->pluck('url')->values()->all();
        $chapters = $this->chaptersForView();

        return tenantView('courses.detail', [
            'course' => $this->course,
            'categories' => $categories,
            'chapters' => $chapters,
            'subtitle' => (string) data_get($this->course->data, 'subtitle', ''),
            'body' => (string) data_get($this->course->data, 'body', ''),
            'images' => $images,
            'imageUrl' => $images[0] ?? $this->course->avatar,
            'price' => (int) data_get($this->course->data, 'price', 0),
            'comparePrice' => data_get($this->course->data, 'compare_price'),
            'hours' => (int) data_get($this->course->data, 'hours', 0),
            'levelLabel' => $this->course->courseLevelLabel(),
            'lessonCount' => $this->course->courseLessonCount(),
        ])->title($this->course->title);
    }

    /**
     * @return array<int, array{id: string, title: string, description: string, lessons: array<int, array<string, mixed>>}>
     */
    private function chaptersForView(): array
    {
        $lessonFiles = collect($this->course->courseLessonFiles())->keyBy('id');

        return collect(data_get($this->course->data, 'chapters', []))
            ->filter(fn (mixed $chapter): bool => is_array($chapter))
            ->map(function (array $chapter) use ($lessonFiles): array {
                $lessons = collect($chapter['lessons'] ?? [])
                    ->filter(fn (mixed $lesson): bool => is_array($lesson))
                    ->map(function (array $lesson) use ($lessonFiles): array {
                        $mediaId = isset($lesson['media_id']) ? (int) $lesson['media_id'] : null;
                        $media = $mediaId ? $lessonFiles->get($mediaId) : null;
                        $source = (string) ($lesson['source'] ?? 'file');
                        $link = (string) ($lesson['link'] ?? '');
                        $fileUrl = (string) ($lesson['file_url'] ?? ($media['url'] ?? ''));

                        return [
                            'id' => (string) ($lesson['id'] ?? ''),
                            'title' => (string) ($lesson['title'] ?? ''),
                            'description' => (string) ($lesson['description'] ?? ''),
                            'source' => $source,
                            'link' => $link,
                            'file_url' => $fileUrl,
                            'file_name' => (string) ($lesson['file_name'] ?? ($media['name'] ?? '')),
                            'playable' => ($source === 'link' && filled($link)) || filled($fileUrl),
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'id' => (string) ($chapter['id'] ?? ''),
                    'title' => (string) ($chapter['title'] ?? ''),
                    'description' => (string) ($chapter['description'] ?? ''),
                    'lessons' => $lessons,
                ];
            })
            ->values()
            ->all();
    }
}
