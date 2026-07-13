<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\PaymentOptionResource;
use App\Models\Setting;
use App\Models\Tenant;
use App\Support\PaymentMethodRegistry;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Updates payment option settings for a given slug while preserving active.
 */
class UpdatePaymentOptionSettings
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $slug = (string) request()->route('slug');

        return match ($slug) {
            'bank-transfer' => [
                'accounts' => ['array'],
                'accounts.*.id' => ['nullable', 'string', 'max:36'],
                'accounts.*.bank_name' => ['required', 'string', 'max:120'],
                'accounts.*.account_name' => ['required', 'string', 'max:120'],
                'accounts.*.iban' => ['nullable', 'string', 'max:34'],
                'accounts.*.account_number' => ['nullable', 'string', 'max:40'],
            ],
            'credit-card' => [
                'label' => ['nullable', 'string', 'max:120'],
                'description' => ['nullable', 'string', 'max:255'],
            ],
            'cash-on-delivery' => [
                'min_limit' => ['nullable', 'numeric', 'min:0'],
                'label' => ['nullable', 'string', 'max:120'],
                'description' => ['nullable', 'string', 'max:255'],
            ],
            'tabby' => [
                'public_key' => ['nullable', 'string', 'max:255'],
                'secret_key' => ['nullable', 'string', 'max:255'],
                'min_limit' => ['nullable', 'numeric', 'min:0'],
                'max_limit' => ['nullable', 'numeric', 'min:0'],
                'label' => ['nullable', 'string', 'max:120'],
                'description' => ['nullable', 'string', 'max:255'],
            ],
            'tamara' => [
                'api_token' => ['nullable', 'string', 'max:255'],
                'notification_token' => ['nullable', 'string', 'max:255'],
                'min_limit' => ['nullable', 'numeric', 'min:0'],
                'label' => ['nullable', 'string', 'max:120'],
                'description' => ['nullable', 'string', 'max:255'],
            ],
            'custom' => [
                'label' => ['required', 'string', 'max:120'],
                'description' => ['nullable', 'string', 'max:255'],
                'instructions' => ['nullable', 'string', 'max:2000'],
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, string $slug, array $data): array
    {
        setCurrentTenant($tenant);

        $method = app(PaymentMethodRegistry::class)->find($slug);

        if (! $method) {
            throw new NotFoundHttpException;
        }

        if (! $method->available) {
            throw ValidationException::withMessages([
                'slug' => __('This payment method is not available yet.'),
            ]);
        }

        $active = (bool) data_get(Setting::paymentMethod($slug), 'active', false);
        $settings = $this->normalizeSettings($slug, $data);

        Setting::savePaymentMethod($slug, $settings, $active);

        $fresh = Setting::paymentMethod($slug);

        return [
            'slug' => $method->slug,
            'name' => $method->name,
            'description' => $method->description,
            'icon' => $method->icon,
            'icon_url' => asset($method->icon),
            'available' => $method->available,
            'active' => (bool) data_get($fresh, 'active', false),
            'settings' => collect($fresh)->except('active')->all(),
            'order' => $method->order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, string $slug): array
    {
        if (! config("payment-methods.{$slug}")) {
            throw new NotFoundHttpException;
        }

        if ($this->rules() === []) {
            throw ValidationException::withMessages([
                'slug' => __('Unsupported payment method.'),
            ]);
        }

        $tenant = $this->currentDashboardTenant($request);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $slug, $validated);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): PaymentOptionResource
    {
        return (new PaymentOptionResource($payload))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeSettings(string $slug, array $data): array
    {
        return match ($slug) {
            'bank-transfer' => [
                'accounts' => collect($data['accounts'] ?? [])
                    ->map(fn (array $account): array => [
                        'id' => (string) ($account['id'] ?? Str::uuid()),
                        'bank_name' => trim((string) $account['bank_name']),
                        'account_name' => trim((string) $account['account_name']),
                        'iban' => trim((string) ($account['iban'] ?? '')),
                        'account_number' => trim((string) ($account['account_number'] ?? '')),
                    ])
                    ->values()
                    ->all(),
            ],
            'credit-card' => [
                'label' => trim((string) ($data['label'] ?? '')),
                'description' => trim((string) ($data['description'] ?? '')),
            ],
            'cash-on-delivery' => [
                'min_limit' => isset($data['min_limit']) && $data['min_limit'] !== null && $data['min_limit'] !== ''
                    ? (float) $data['min_limit']
                    : null,
                'label' => trim((string) ($data['label'] ?? '')),
                'description' => trim((string) ($data['description'] ?? '')),
            ],
            'tabby' => [
                'public_key' => trim((string) ($data['public_key'] ?? '')),
                'secret_key' => trim((string) ($data['secret_key'] ?? '')),
                'min_limit' => isset($data['min_limit']) && $data['min_limit'] !== null && $data['min_limit'] !== ''
                    ? (float) $data['min_limit']
                    : null,
                'max_limit' => isset($data['max_limit']) && $data['max_limit'] !== null && $data['max_limit'] !== ''
                    ? (float) $data['max_limit']
                    : null,
                'label' => trim((string) ($data['label'] ?? '')),
                'description' => trim((string) ($data['description'] ?? '')),
            ],
            'tamara' => [
                'api_token' => trim((string) ($data['api_token'] ?? '')),
                'notification_token' => trim((string) ($data['notification_token'] ?? '')),
                'min_limit' => isset($data['min_limit']) && $data['min_limit'] !== null && $data['min_limit'] !== ''
                    ? (float) $data['min_limit']
                    : null,
                'label' => trim((string) ($data['label'] ?? '')),
                'description' => trim((string) ($data['description'] ?? '')),
            ],
            'custom' => [
                'label' => trim((string) ($data['label'] ?? '')),
                'description' => trim((string) ($data['description'] ?? '')),
                'instructions' => trim((string) ($data['instructions'] ?? '')),
            ],
            default => $data,
        };
    }
}
