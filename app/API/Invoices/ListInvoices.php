<?php

namespace App\API\Invoices;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\InvoiceListResource;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListInvoices
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
     * @return LengthAwarePaginator<int, Invoice>
     */
    public function handle(Tenant $tenant, ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        setCurrentTenant($tenant);

        $query = Invoice::query()
            ->select([
                'invoices.id',
                'invoices.uuid',
                'invoices.number',
                'invoices.initial_status',
                'invoices.type',
                'invoices.currency',
                'invoices.total_after_vat',
                'invoices.amount_paid',
                'invoices.issued_on',
                'invoices.created_at',
                'invoices.invoicable_type',
                'invoices.invoicable_id',
                'invoices.user_id',
            ])
            ->with([
                'user:id,name',
                'invoicable' => fn ($relation) => $relation->select(['id', 'uuid', 'number']),
            ])
            ->where('invoices.tenant_id', $tenant->id)
            ->orderByDesc('invoices.id');

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

    public function jsonResponse(LengthAwarePaginator $invoices): AnonymousResourceCollection
    {
        return InvoiceListResource::collection($invoices);
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $term = '%'.$search.'%';

        $query->where(function (Builder $query) use ($term): void {
            $query->where('invoices.number', 'like', $term)
                ->orWhere('invoices.note', 'like', $term)
                ->orWhereIn('invoices.user_id', function ($sub) use ($term): void {
                    $sub->select('id')->from('users')->where('name', 'like', $term);
                })
                ->orWhereIn('invoices.invoicable_id', function ($sub) use ($term): void {
                    $sub->select('id')
                        ->from('orders')
                        ->where('number', 'like', $term)
                        ->orWhere('id', 'like', $term);
                });
        });
    }
}
