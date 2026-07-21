<?php

namespace App\API\Tenants;

use App\Actions\CreateTenant;
use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Creates a new tenant page for the authenticated user from a name only,
 * then switches current_tenant_id to the new page.
 */
class CreateUserTenant
{
    use AsAction;

    /**
     * @return list<string>
     */
    public function getControllerMiddleware(): array
    {
        return [
            'auth:sanctum',
            'throttle:10,1',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() instanceof User;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:200'],
        ];
    }

    /**
     * @param  array{name: string}  $data
     */
    public function handle(User $user, array $data): Tenant
    {
        $name = trim($data['name']);
        $handle = $this->uniqueHandle($name, $user);

        $tenant = CreateTenant::run([
            'tenant_name' => $name,
            'tenant_handle' => $handle,
            'email' => (string) ($user->email ?: ($handle.'@'.config('app.domain'))),
            'user_id' => $user->id,
        ]);

        $user->update([
            'current_tenant_id' => $tenant->id,
        ]);

        setCurrentTenant($tenant);

        return $tenant->fresh()->loadMissing('subscription.plan');
    }

    public function asController(ActionRequest $request): Tenant
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array{name: string} $validated */
        $validated = $request->validated();

        return $this->handle($user, $validated);
    }

    public function jsonResponse(Tenant $tenant): TenantResource
    {
        return (new TenantResource($tenant))
            ->additional([
                'message' => __('تم إنشاء الصفحة بنجاح.'),
            ]);
    }

    private function uniqueHandle(string $name, User $user): string
    {
        $base = Str::slug($name);

        if ($base === '' || strlen($base) < 2) {
            $emailPrefix = filled($user->email)
                ? Str::slug((string) Str::before($user->email, '@'))
                : '';

            $base = filled($emailPrefix) ? $emailPrefix : 'page';
        }

        $base = Str::limit($base, 80, '');
        $handle = $base;
        $attempts = 0;

        while (Tenant::query()->where('handle', $handle)->exists()) {
            $attempts++;

            if ($attempts > 20) {
                throw ValidationException::withMessages([
                    'name' => __('تعذر إنشاء رابط فريد للصفحة. جرّب اسماً آخر.'),
                ]);
            }

            $handle = Str::limit($base, 70, '').'-'.Str::lower(Str::random(5));
        }

        return $handle;
    }
}
