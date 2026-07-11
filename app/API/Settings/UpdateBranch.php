<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Updates a branch for the current dashboard tenant.
 */
class UpdateBranch
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'country' => ['required', 'string', 'max:3'],
            'city' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'phonecode' => ['nullable', 'string', 'max:6'],
            'phone' => ['nullable', 'string', 'max:14'],
            'active' => ['boolean'],
            'is_warehouse' => ['boolean'],
            'is_pickup' => ['boolean'],
            'working_hours' => ['sometimes', 'array'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Tenant $tenant, int $id, array $data): Branch
    {
        setCurrentTenant($tenant);

        $branch = Branch::query()->find($id);

        if (! $branch) {
            throw new NotFoundHttpException;
        }

        $branch->update([
            'name' => Branch::localizedName((string) $data['name']),
            'country' => $data['country'],
            'city' => $data['city'],
            'address' => $data['address'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'email' => $data['email'] ?? null,
            'phonecode' => $data['phonecode'] ?? $branch->phonecode,
            'phone' => $data['phone'] ?? null,
            'active' => (bool) ($data['active'] ?? $branch->active),
            'is_warehouse' => (bool) ($data['is_warehouse'] ?? $branch->is_warehouse),
            'is_pickup' => (bool) ($data['is_pickup'] ?? $branch->is_pickup),
        ]);

        if (isset($data['working_hours']) && is_array($data['working_hours'])) {
            $branch->setWorkingHours($data['working_hours']);
            $branch->save();
        }

        return $branch->fresh();
    }

    public function asController(ActionRequest $request, int $id): Branch
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $id, $validated);
    }

    public function jsonResponse(Branch $branch): BranchResource
    {
        return (new BranchResource($branch))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }
}
