<?php

namespace App\Livewire\Tenant\Newsletter;

use App\Models\Content;
use Livewire\Component;

class Detail extends Component
{
    public Content $issue;

    public function mount(string $slug): void
    {
        $this->issue = Content::query()
            ->type(contentTypeModel('newsletter'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function render()
    {
        $displayDate = $this->issue->newsletterSentAt() ?? $this->issue->published_at;

        return tenantView('newsletter.detail', [
            'issue' => $this->issue,
            'subject' => (string) data_get($this->issue->data, 'subject', $this->issue->title),
            'subtitle' => (string) data_get($this->issue->data, 'subtitle', ''),
            'body' => (string) data_get($this->issue->data, 'body', ''),
            'imageUrl' => contentImageUrl(data_get($this->issue->data, 'image')) ?? $this->issue->avatar,
            'displayDate' => $displayDate,
        ])->title($this->issue->title);
    }
}
