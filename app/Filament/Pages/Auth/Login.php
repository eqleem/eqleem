<?php

namespace App\Filament\Pages\Auth;

use App\Actions\SendSuperpassLoginCode;
use App\Actions\VerifySuperpassLoginCode;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Auth\Events\Login as LoginEvent;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

class Login extends BaseLogin
{
    #[Locked]
    public bool $otpStep = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getCodeFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::auth/pages/login.form.email.label'))
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus(fn (): bool => ! $this->otpStep)
            ->disabled(fn (): bool => $this->otpStep)
            ->dehydrated()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCodeFormComponent(): Component
    {
        return TextInput::make('code')
            ->label('كود التحقق')
            ->numeric()
            ->length(6)
            ->required(fn (): bool => $this->otpStep)
            ->visible(fn (): bool => $this->otpStep)
            ->autocomplete('one-time-code')
            ->autofocus(fn (): bool => $this->otpStep)
            ->extraInputAttributes([
                'tabindex' => 2,
                'inputmode' => 'numeric',
                'pattern' => '[0-9]*',
            ])
            ->helperText(new HtmlString(
                'أدخل الكود المرسل إلى بريدك الإلكتروني. '
                .'<button type="button" wire:click="resendCode" class="text-primary-600 underline font-medium">إعادة الإرسال</button>'
                .' · '
                .'<button type="button" wire:click="resetToEmailStep" class="text-gray-500 underline">تغيير البريد</button>'
            ));
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->visible(fn (): bool => $this->otpStep);
    }

    public function getHeading(): string|Htmlable|null
    {
        return $this->otpStep
            ? 'أدخل كود التحقق'
            : parent::getHeading();
    }

    public function getSubheading(): string|Htmlable|null
    {
        if ($this->otpStep) {
            return 'أرسلنا كود الدخول إلى بريدك الإلكتروني.';
        }

        return 'أدخل بريدك الإلكتروني وسنرسل لك كود الدخول.';
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('authenticate')
                ->label(fn (): string => $this->otpStep ? 'تأكيد الدخول' : 'إرسال كود الدخول')
                ->submit('authenticate'),
        ];
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        if (! $this->otpStep) {
            $this->sendLoginCode();

            return null;
        }

        return $this->verifyLoginCode();
    }

    public function resendCode(): void
    {
        $this->sendLoginCode(showNotification: true);
    }

    public function resetToEmailStep(): void
    {
        $this->otpStep = false;
        $this->data['code'] = null;

        $this->resetErrorBag();
    }

    protected function sendLoginCode(bool $showNotification = true): void
    {
        $data = $this->form->getState();
        $email = (string) ($data['email'] ?? '');

        SendSuperpassLoginCode::run($email);

        $this->otpStep = true;
        $this->data['code'] = null;
        $this->resetErrorBag('data.code');

        if ($showNotification) {
            Notification::make()
                ->title('تم إرسال كود الدخول')
                ->body('تحقق من بريدك الإلكتروني وأدخل الكود هنا.')
                ->success()
                ->send();
        }
    }

    protected function verifyLoginCode(): LoginResponse
    {
        $data = $this->form->getState();
        $email = (string) ($data['email'] ?? '');
        $code = (string) ($data['code'] ?? '');
        $remember = (bool) ($data['remember'] ?? false);

        try {
            $user = VerifySuperpassLoginCode::run($email, $code);
        } catch (ValidationException $exception) {
            throw $exception;
        }

        $authGuard = Filament::auth();
        $authGuard->login($user, $remember);

        event(new LoginEvent($authGuard->name, $user, $remember));

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
