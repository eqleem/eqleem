<?php

namespace App\API\User;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates the authenticated user's personal account profile.
 *
 * @see https://www.laravelactions.com/2.x/basic-usage.html
 */
class UpdateAccountProfile
{
    use AsAction;

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
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->getAuthUserId()),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:14',
                Rule::unique('users', 'phone')->ignore($this->getAuthUserId()),
            ],
        ];
    }

    /**
     * @param  array{name: string, email: string, phone?: string|null}  $data
     */
    public function handle(User $user, array $data): User
    {
        $emailChanged = $user->email !== $data['email'];

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => filled($data['phone'] ?? null) ? $data['phone'] : null,
        ]);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user->refresh();
    }

    public function asController(ActionRequest $request): User
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array{name: string, email: string, phone?: string|null} $validated */
        $validated = $request->validated();

        return $this->handle($user, $validated);
    }

    public function jsonResponse(User $user): UserResource
    {
        return (new UserResource($user))
            ->additional([
                'message' => __('Account info updated successfully.'),
            ]);
    }

    private function getAuthUserId(): ?int
    {
        $user = auth()->user();

        return $user instanceof User ? (int) $user->id : null;
    }
}
