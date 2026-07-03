<?php

namespace App\Livewire\Tenant\Portfolio;

use App\Models\Content;
use Livewire\Component;

class Detail extends Component
{
    public Content $project;

    public function mount(string $slug): void
    {
        $this->project = Content::query()
            ->type('portfolio')
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function render()
    {
        $this->project->migrateLegacyPortfolioImagesIfNeeded();

        $categories = $this->project->taxonomiesOfType('portfolio_category');
        $images = $this->project->portfolioImageUrls();

        return tenantView('portfolio.detail', [
            'project' => $this->project,
            'categories' => $categories,
            'subtitle' => (string) data_get($this->project->data, 'subtitle', ''),
            'body' => (string) data_get($this->project->data, 'body', ''),
            'images' => $images,
            'imageUrl' => $images[0] ?? null,
        ])->title($this->project->title);
    }
}
