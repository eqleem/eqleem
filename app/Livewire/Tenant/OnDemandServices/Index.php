<?php

namespace App\Livewire\Tenant\OnDemandServices;

use App\Models\Content;
use App\Models\Setting;
use App\Support\OnDemandUnit;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Index extends Component
{
    public string $search = '';

    public function render()
    {
        $services = Content::query()
            ->type(contentTypeModel('on-demand-services'))
            ->published()
            ->where('active', true)
            ->with('media')
            ->when(
                $this->search !== '',
                fn (Builder $query) => $query->where(function (Builder $builder): void {
                    $term = '%'.$this->search.'%';

                    $builder
                        ->where('title', 'like', $term)
                        ->orWhere('data->subtitle', 'like', $term)
                        ->orWhere('data->body', 'like', $term);
                }),
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get()
            ->map(function (Content $service): array {
                $price = (int) data_get($service->data, 'price', 0);
                $unitType = (string) data_get($service->data, 'unit_type', '');
                $unitLabel = (string) data_get($service->data, 'unit_label', '');

                return [
                    'model' => $service,
                    'subtitle' => (string) data_get($service->data, 'subtitle', ''),
                    'imageUrl' => $service->getFirstMediaUrl('on-demand-service-media') ?: $service->avatar,
                    'price' => $price,
                    'comparePrice' => data_get($service->data, 'compare_price'),
                    'unitDisplay' => OnDemandUnit::label($unitType, $unitLabel),
                    'priceHtml' => OnDemandUnit::priceHtml($price, $unitType, $unitLabel),
                ];
            });

        return tenantView('on-demand-services.index', [
            'services' => $services,
        ])->title(Setting::onDemandServiceSettings()['section_title']);
    }
}
