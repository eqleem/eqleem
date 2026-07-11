<?php

namespace App\API\User;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Uploads the authenticated user's profile avatar.
 */
class UploadAccountAvatar
{
    use AsAction;

    /**
     * @return list<string>
     */
    public function getControllerMiddleware(): array
    {
        return [
            'auth:sanctum',
            'throttle:20,1',
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
        $maxFileSizeKb = (int) (config('media-library.max_file_size') / 1024);

        return [
            'file' => ['required', 'image', 'max:'.$maxFileSizeKb],
        ];
    }

    public function handle(User $user, UploadedFile $file): User
    {
        $mediaKey = (string) ($user->uuid ?? $user->id);
        $path = $file->storePublicly('user-media/'.$mediaKey.'/avatar', 'spaces');

        $user->image = $path;
        $user->save();

        return $user->refresh();
    }

    public function asController(ActionRequest $request): User
    {
        /** @var User $user */
        $user = $request->user();

        /** @var UploadedFile $file */
        $file = $request->file('file');

        return $this->handle($user, $file);
    }

    public function jsonResponse(User $user): UserResource
    {
        return (new UserResource($user))
            ->additional([
                'message' => __('Profile photo updated successfully.'),
            ]);
    }
}
