<?php

namespace App\API\Newsletter;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Newsletter\Concerns\ResolvesNewsletter;
use App\Http\Resources\NewsletterListResource;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists newsletter issues for the current dashboard tenant.
 */
class ListNewsletters
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesNewsletter;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->listQueryRules();
    }

    /**
     * @return LengthAwarePaginator<int, Content>
     */
    public function handle(Tenant $tenant, ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        setCurrentTenant($tenant);

        $query = Content::query()
            ->type($this->newsletterType())
            ->orderByDesc('id');

        if ($search !== null && $search !== '') {
            $term = '%'.$search.'%';

            $query->where(function ($builder) use ($term): void {
                $builder
                    ->where('title', 'like', $term)
                    ->orWhere('data->subject', 'like', $term)
                    ->orWhere('data->subtitle', 'like', $term);
            });
        }

        return $query->paginate($perPage);
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{search?: string|null, per_page?: int} $validated */
        $validated = $request->validated();

        return $this->handle(
            $tenant,
            isset($validated['search']) ? trim((string) $validated['search']) : null,
            (int) ($validated['per_page'] ?? 20),
        );
    }

    public function jsonResponse(LengthAwarePaginator $issues): AnonymousResourceCollection
    {
        return NewsletterListResource::collection($issues);
    }
}
