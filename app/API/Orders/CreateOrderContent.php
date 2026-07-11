<?php

namespace App\API\Orders;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Content;
use App\Models\Order;
use App\Models\Tenant;
use App\Support\Money;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Creates (or reuses) draft content for an order line item type.
 */
class CreateOrderContent
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return list<string>
     */
    public function getControllerMiddleware(): array
    {
        return [
            'auth:sanctum',
            'throttle:30,1',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $types = array_keys(array_diff_key(Order::itemTypeOptions(), ['other' => true]));

        return [
            'type' => ['required', 'string', Rule::in($types)],
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'unit_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @param  array{type: string, title: string, unit_price?: float|string|int|null}  $data
     * @return array{name: string, product_id: int, unit_price: float, status: string, duration_minutes: int}
     */
    public function handle(Tenant $tenant, array $data): array
    {
        setCurrentTenant($tenant);

        $type = $data['type'];
        $title = trim($data['title']);
        $unitPriceDecimal = (string) ($data['unit_price'] ?? 0);

        $content = $this->findOrCreate($tenant, $type, $title, $unitPriceDecimal);

        return [
            'name' => $content->title,
            'product_id' => $content->id,
            'unit_price' => Money::fromMinor((int) ($content->price ?? Order::minorFromDecimal($unitPriceDecimal))),
            'status' => (string) $content->status,
            'duration_minutes' => (int) (data_get($content->data, 'duration_minutes') ?: 60),
        ];
    }

    /**
     * @return array{name: string, product_id: int, unit_price: float, status: string, duration_minutes: int}
     */
    public function asController(ActionRequest $request): array
    {
        /** @var array{type: string, title: string, unit_price?: float|string|int|null} $validated */
        $validated = $request->validated();

        return $this->handle($this->currentDashboardTenant($request), $validated);
    }

    /**
     * @param  array{name: string, product_id: int, unit_price: float, status: string, duration_minutes: int}  $result
     * @return array{data: array{name: string, product_id: int, unit_price: float, status: string, duration_minutes: int}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Draft content saved successfully.'),
        ];
    }

    private function findOrCreate(Tenant $tenant, string $orderItemType, string $title, string $unitPriceDecimal = '0'): Content
    {
        $contentType = $this->orderItemContentType($orderItemType);

        if ($contentType === null) {
            abort(422, __('Unsupported item type.'));
        }

        $priceMinor = Order::minorFromDecimal($unitPriceDecimal);

        $existing = Content::query()
            ->where('type', $contentType)
            ->where('title', $title)
            ->first();

        if ($existing instanceof Content) {
            return $this->syncDraftPrice($existing, $priceMinor);
        }

        $data = [];

        if ($orderItemType === 'course') {
            $data = [
                'level' => 'beginner',
                'course_type' => 'recorded',
                'hours' => 0,
                'chapters' => [],
            ];
        }

        if ($orderItemType === 'digital_service') {
            $data['delivery_days'] = null;
        }

        if (Order::isBookingItemType($orderItemType)) {
            $data['duration_minutes'] = 60;
        }

        return Content::query()->create([
            'tenant_id' => $tenant->id,
            'type' => $contentType,
            'title' => $title,
            'slug' => $this->uniqueContentSlug($title, $orderItemType),
            'status' => 'draft',
            'active' => true,
            'price' => $priceMinor,
            'data' => $data === [] ? null : $data,
        ]);
    }

    private function syncDraftPrice(Content $content, int $priceMinor): Content
    {
        if ($content->status !== 'draft') {
            return $content;
        }

        $currentPrice = (int) ($content->price ?? 0);

        if ($priceMinor <= 0 || $currentPrice === $priceMinor) {
            return $content;
        }

        $content->forceFill(['price' => $priceMinor])->save();

        return $content->refresh();
    }

    private function uniqueContentSlug(string $title, string $orderItemType): string
    {
        $baseSlug = Str::slug($title);
        $fallback = match ($orderItemType) {
            'product' => 'product',
            'digital_product' => 'digital-product',
            'service' => 'service',
            'course' => 'course',
            'digital_service' => 'digital-service',
            'menu' => 'menu-item',
            'unit_rental' => 'unit',
            default => 'item',
        };
        $slug = $baseSlug !== '' ? $baseSlug : $fallback;
        $counter = 1;

        while (Content::query()->where('slug', $slug)->exists()) {
            $slug = ($baseSlug !== '' ? $baseSlug : $fallback).'-'.$counter;
            $counter++;
        }

        return $slug;
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
