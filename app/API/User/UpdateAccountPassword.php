<?php

namespace App\API\User;

use App\Models\User;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates the authenticated user's password.
 */
class UpdateAccountPassword
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
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'max:155', 'confirmed'],
        ];
    }

    /**
     * @param  array{current_password: string, password: string}  $data
     */
    public function handle(User $user, array $data): User
    {
        $user->password = $data['password'];
        $user->setRememberToken(Str::random(60));
        $user->save();

        $user->tokens()->delete();

        return $user->refresh();
    }

    /**
     * @return array{message: string}
     */
    public function asController(ActionRequest $request): array
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array{current_password: string, password: string} $validated */
        $validated = $request->validated();

        $this->handle($user, $validated);

        return [
            'message' => __('Password updated successfully.'),
        ];
    }

    /**
     * @param  array{message: string}  $result
     * @return array{message: string}
     */
    public function jsonResponse(array $result): array
    {
        return $result;
    }
}
