<?php

namespace App\API\FormSubmissions;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\FormSubmissionResource;
use App\Models\FormSubmission;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowFormSubmission
{
    use AsAction;
    use AuthorizesDashboardTenant;

    public function handle(Tenant $tenant, int $id): FormSubmission
    {
        setCurrentTenant($tenant);

        $submission = FormSubmission::query()
            ->with([
                'form:id,uuid,title',
                'client:id,uuid,name,email,phone,meta',
                'replies' => fn ($query) => $query
                    ->with('user:id,name')
                    ->latest('id'),
            ])
            ->where('tenant_id', $tenant->id)
            ->whereKey($id)
            ->first();

        if (! $submission instanceof FormSubmission) {
            throw (new ModelNotFoundException)->setModel(FormSubmission::class, [$id]);
        }

        $submission->markAsRead();

        return $submission->fresh([
            'form:id,uuid,title',
            'client:id,uuid,name,email,phone,meta',
            'replies' => fn ($query) => $query->with('user:id,name')->latest('id'),
        ]);
    }

    public function asController(ActionRequest $request, int $id): FormSubmission
    {
        return $this->handle($this->currentDashboardTenant($request), $id);
    }

    public function jsonResponse(FormSubmission $submission): FormSubmissionResource
    {
        return new FormSubmissionResource($submission);
    }
}
