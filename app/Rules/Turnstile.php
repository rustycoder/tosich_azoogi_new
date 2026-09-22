<?php

namespace App\Rules;

use App\Services\Contracts\ITurnstileService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Turnstile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! (bool) config('services.turnstile.enabled', true)) {
            return;
        }

        $service = app(ITurnstileService::class);
        $token = is_string($value) ? $value : null;

        if (! $service->verify($token, request()->ip())) {
            $fail('The security challenge verification failed. Please try again.');
        }
    }
}
