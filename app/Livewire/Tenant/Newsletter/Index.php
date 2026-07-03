<?php

namespace App\Livewire\Tenant\Newsletter;

use App\Models\Content;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Index extends Component
{
    public string $search = '';

    public function render()
    {
        $issues = Content::query()
            ->type(contentTypeModel('newsletter'))
            ->published()
            ->where('active', true)
            ->when(
                $this->search !== '',
                fn (Builder $query) => $query->where(function (Builder $builder): void {
                    $term = '%'.$this->search.'%';

                    $builder
                        ->where('title', 'like', $term)
                        ->orWhere('data->subject', 'like', $term)
                        ->orWhere('data->subtitle', 'like', $term)
                        ->orWhere('data->body', 'like', $term);
                }),
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return tenantView('newsletter.index', [
            'issues' => $issues,
        ])->title(Setting::newsletterSettings()['section_title']);
    }
}
