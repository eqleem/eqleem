<?php

namespace App\API\Reviews;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\ReviewListResource;
use App\Models\Review;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListReviews
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->listQueryRules();
    }

    /**
     * @return LengthAwarePaginator<int, Review>
     */
    public function handle(Tenant $tenant, ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        setCurrentTenant($tenant);

        $query = Review::query()
            ->with([
                'content:id,title,type',
                'client:id,name,email,phone',
                'order:id,uuid,number',
                'branch:id,name',
            ])
            ->orderByDesc('reviews.created_at')
            ->orderByDesc('reviews.id');

        $this->applySearch($query, $search);

        return $query->paginate($perPage);
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        /** @var array{search?: string|null, per_page?: int} $validated */
        $validated = $request->validated();

        return $this->handle(
            $this->currentDashboardTenant($request),
            isset($validated['search']) ? trim((string) $validated['search']) : null,
            (int) ($validated['per_page'] ?? 20),
        );
    }

    public function jsonResponse(LengthAwarePaginator $reviews): AnonymousResourceCollection
    {
        return ReviewListResource::collection($reviews);
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $term = '%'.$search.'%';

        $query->where(function (Builder $query) use ($term): void {
            $query->where('reviews.title', 'like', $term)
                ->orWhere('reviews.score', 'like', $term)
                ->orWhere('reviews.name', 'like', $term)
                ->orWhere('reviews.email', 'like', $term)
                ->orWhere('reviews.phone', 'like', $term)
                ->orWhereHas('client', function (Builder $clients) use ($term): void {
                    $clients->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                })
                ->orWhereHas('content', fn (Builder $contents): Builder => $contents->where('title', 'like', $term))
                ->orWhereHas('order', fn (Builder $orders): Builder => $orders->where('number', 'like', $term));
        });
    }
}
