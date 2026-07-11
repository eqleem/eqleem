<?php

namespace App\API\Payments;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\PaymentListResource;
use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListPayments
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
     * @return LengthAwarePaginator<int, Payment>
     */
    public function handle(Tenant $tenant, ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        setCurrentTenant($tenant);

        $query = Payment::query()
            ->forTenant()
            ->select([
                'payments.id',
                'payments.uuid',
                'payments.amount',
                'payments.currency',
                'payments.reason',
                'payments.gateway',
                'payments.source_type',
                'payments.source_name',
                'payments.source_company',
                'payments.source_last_four',
                'payments.source_number',
                'payments.initial_status',
                'payments.meta',
                'payments.user_id',
                'payments.client_id',
                'payments.order_id',
                'payments.created_at',
            ])
            ->with([
                'user:id,name,email',
                'client:id,name,email',
                'order:id,uuid,number',
            ])
            ->where('payments.tenant_id', $tenant->id)
            ->orderByDesc('payments.id');

        $this->applySearch($query, $search);

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

    public function jsonResponse(LengthAwarePaginator $payments): AnonymousResourceCollection
    {
        return PaymentListResource::collection($payments);
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $term = '%'.$search.'%';

        $query->where(function (Builder $query) use ($term): void {
            $query->where('payments.id', 'like', $term)
                ->orWhere('payments.payment_id', 'like', $term)
                ->orWhere('payments.description', 'like', $term)
                ->orWhere('payments.reason', 'like', $term)
                ->orWhere('payments.source_name', 'like', $term)
                ->orWhereIn('payments.user_id', function ($sub) use ($term): void {
                    $sub->select('id')->from('users')
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                })
                ->orWhereIn('payments.client_id', function ($sub) use ($term): void {
                    $sub->select('id')->from('clients')
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
        });
    }
}
