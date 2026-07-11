<?php

namespace App\API\Orders;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Content;
use App\Models\Order;
use App\Models\Tenant;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Typeahead search for order line items by content type.
 */
class SearchOrderContent
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(Order::itemTypeOptions()))],
            'search' => ['required', 'string', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return list<array{name: string, product_id: int|null, unit_price: float, duration_minutes: int}>
     */
    public function handle(Tenant $tenant, string $type, string $search): array
    {
        setCurrentTenant($tenant);

        $term = trim($search);

        if ($term === '' || $type === 'other') {
            return [];
        }

        $contentType = $this->orderItemContentType($type);

        if ($contentType === null) {
            return [];
        }

        $like = '%'.$term.'%';

        $results = Content::query()
            ->where('type', $contentType)
            ->where('title', 'like', $like)
            ->orderBy('title')
            ->limit(8)
            ->get(['id', 'title', 'data', 'price', 'status'])
            ->map(fn (Content $content): array => [
                'name' => $content->title,
                'product_id' => $content->id,
                'unit_price' => Money::fromMinor((int) ($content->price ?? 0)),
                'duration_minutes' => (int) (data_get($content->data, 'duration_minutes') ?: 60),
                'status' => (string) $content->status,
            ])
            ->all();

        if ($results !== []) {
            return $results;
        }

        return DB::table('order_items')
            ->select('name', DB::raw('MAX(product_id) as product_id'), DB::raw('MAX(unit_price) as unit_price'))
            ->where('name', 'like', $like)
            ->where(function ($query) use ($type): void {
                $query->where('meta->type', $type);

                if ($type === 'product') {
                    $query->orWhereNull('meta');
                }
            })
            ->groupBy('name')
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'name' => $row->name,
                'product_id' => $row->product_id ? (int) $row->product_id : null,
                'unit_price' => Money::fromMinor((int) $row->unit_price),
                'duration_minutes' => 60,
            ])
            ->all();
    }

    /**
     * @return list<array{name: string, product_id: int|null, unit_price: float, duration_minutes: int}>
     */
    public function asController(ActionRequest $request): array
    {
        /** @var array{type: string, search: string} $validated */
        $validated = $request->validated();

        return $this->handle(
            $this->currentDashboardTenant($request),
            $validated['type'],
            $validated['search'],
        );
    }

    /**
     * @param  list<array{name: string, product_id: int|null, unit_price: float, duration_minutes: int}>  $results
     * @return array{data: list<array{name: string, product_id: int|null, unit_price: float, duration_minutes: int}>}
     */
    public function jsonResponse(array $results): array
    {
        return ['data' => $results];
    }

    private function orderItemContentType(string $type): ?string
    {
        return match ($type) {
            'product' => contentTypeModel('store'),
            'digital_product' => contentTypeModel('digital-products'),
            'service' => contentTypeModel('services'),
            'course' => contentTypeModel('courses'),
            'digital_service' => contentTypeModel('digital-services'),
            'menu' => contentTypeModel('menu'),
            'unit_rental' => contentTypeModel('unit-rental'),
            default => null,
        };
    }
}
