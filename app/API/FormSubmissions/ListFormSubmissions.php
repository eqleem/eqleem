<?php

namespace App\API\FormSubmissions;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\FormSubmissionListResource;
use App\Models\FormSubmission;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListFormSubmissions
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
     * @return LengthAwarePaginator<int, FormSubmission>
     */
    public function handle(Tenant $tenant, ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        setCurrentTenant($tenant);

        $query = FormSubmission::query()
            ->select([
                'form_submissions.id',
                'form_submissions.content_id',
                'form_submissions.client_id',
                'form_submissions.status',
                'form_submissions.data',
                'form_submissions.submitted_at',
                'form_submissions.read_at',
                'form_submissions.created_at',
            ])
            ->with([
                'form:id,title',
                'client:id,name,email,phone',
            ])
            ->where('form_submissions.tenant_id', $tenant->id)
            ->orderByRaw('form_submissions.read_at IS NULL DESC')
            ->orderByDesc('form_submissions.submitted_at')
            ->orderByDesc('form_submissions.id');

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

    public function jsonResponse(LengthAwarePaginator $submissions): AnonymousResourceCollection
    {
        return FormSubmissionListResource::collection($submissions);
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $term = '%'.$search.'%';

        $query->where(function (Builder $query) use ($term): void {
            $query->where('form_submissions.id', 'like', $term)
                ->orWhereIn('form_submissions.content_id', function ($sub) use ($term): void {
                    $sub->select('id')->from('contents')->where('title', 'like', $term);
                })
                ->orWhereIn('form_submissions.client_id', function ($sub) use ($term): void {
                    $sub->select('id')->from('clients')
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
        });
    }
}
