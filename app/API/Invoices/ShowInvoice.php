<?php

namespace App\API\Invoices;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowInvoice
{
    use AsAction;
    use AuthorizesDashboardTenant;

    public function handle(Tenant $tenant, string $uuid): Invoice
    {
        setCurrentTenant($tenant);

        $invoice = Invoice::query()
            ->with([
                'user:id,name',
                'items:id,invoice_id,name,type,quantity,amount_after_vat,total_after_vat,note',
                'payments' => fn ($query) => $query
                    ->select(['id', 'uuid', 'invoice_id', 'amount', 'currency', 'initial_status', 'meta', 'created_at'])
                    ->latest('id'),
                'invoicable' => fn ($relation) => $relation->select(['id', 'uuid', 'number']),
            ])
            ->where('tenant_id', $tenant->id)
            ->where('uuid', $uuid)
            ->first();

        if (! $invoice instanceof Invoice) {
            throw (new ModelNotFoundException)->setModel(Invoice::class, [$uuid]);
        }

        return $invoice;
    }

    public function asController(ActionRequest $request, string $uuid): Invoice
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($invoice);
    }
}
