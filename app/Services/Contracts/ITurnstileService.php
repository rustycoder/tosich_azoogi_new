<?php

namespace App\Services\Contracts;

interface ITurnstileService
{
    /**
     * Validate a Turnstile response token against Cloudflare's siteverify API.
     */
    public function verify(?string $response, ?string $remoteIp = null): bool;
}
