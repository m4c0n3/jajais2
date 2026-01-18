<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (\DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if ($user instanceof FilamentUser && ! $user->canAccessPanel(Filament::getCurrentPanel())) {
            Filament::auth()->logout();
            $this->logAccessDenied($user);
            $this->throwAccessDeniedValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    private function throwAccessDeniedValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => 'Účet je platný, ale nemáte oprávnenie pre administráciu.',
        ]);
    }

    private function logAccessDenied(object $user): void
    {
        logger()->warning('Admin panel access denied', [
            'user_id' => $user->id ?? null,
            'email' => $user->email ?? null,
        ]);
    }
}
