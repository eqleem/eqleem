<?php

namespace App\Livewire\Tenant\Blog;

use App\Models\Content;
use Livewire\Component;

class Detail extends Component
{
    public Content $post;

    public function mount(string $slug): void
    {
        if ($this->isPreviewRequest()) {
            $post = Content::query()
                ->type(contentTypeModel('blog'))
                ->where('slug', $slug)
                ->firstOrFail();

            abort_unless($this->canPreviewPost($post), 404);

            $this->post = $post;

            return;
        }

        $this->post = Content::query()
            ->type(contentTypeModel('blog'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function isPreviewRequest(): bool
    {
        return request()->query('mod') === 'preview';
    }

    private function canPreviewPost(Content $post): bool
    {
        $userId = auth()->id();

        return $userId !== null && (int) $post->user_id === (int) $userId;
    }

    public function render()
    {
        $this->post->migrateLegacyBlogCategoriesIfNeeded();

        $categories = $this->post->taxonomiesOfType('blog_category');

        return tenantView('blog.detail', [
            'post' => $this->post,
            'categories' => $categories,
            'subtitle' => (string) data_get($this->post->data, 'subtitle', ''),
            'body' => (string) data_get($this->post->data, 'body', ''),
            'imageUrl' => contentImageUrl(data_get($this->post->data, 'image')),
        ])->title($this->post->title);
    }
}
