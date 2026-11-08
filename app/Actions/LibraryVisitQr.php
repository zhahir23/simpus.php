<?php

namespace App\Actions;

class LibraryVisitQr
{
    public const TTL_SECONDS = 30;

    /**
     * @return array{issued_at: int, nonce: string}|null
     */
    public function verify(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 2) {
            return null;
        }

        [$encodedPayload, $signature] = $parts;
        $expectedSignature = hash_hmac('sha256', $encodedPayload, $this->signingKey());

        if (! hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $json = base64_decode(strtr($encodedPayload, '-_', '+/').str_repeat('=', (4 - strlen($encodedPayload) % 4) % 4), true);

        if ($json === false) {
            return null;
        }

        $payload = json_decode($json, true);

        if (
            ! is_array($payload)
            || ! is_int($payload['issued_at'] ?? null)
            || ! is_string($payload['nonce'] ?? null)
            || ! preg_match('/^[a-f0-9]{32}$/', $payload['nonce'])
            || $payload['issued_at'] > now()->timestamp
            || now()->timestamp - $payload['issued_at'] > self::TTL_SECONDS
        ) {
            return null;
        }

        return [
            'issued_at' => $payload['issued_at'],
            'nonce' => $payload['nonce'],
        ];
    }

    public function issue(): string
    {
        $payload = json_encode([
            'issued_at' => now()->timestamp,
            'nonce' => bin2hex(random_bytes(16)),
        ], JSON_THROW_ON_ERROR);
        $encodedPayload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

        return $encodedPayload.'.'.hash_hmac('sha256', $encodedPayload, $this->signingKey());
    }

    private function signingKey(): string
    {
        $key = config('app.key');

        if (! is_string($key) || $key === '') {
            throw new \RuntimeException('The application key is required to sign library visit QR codes.');
        }

        return $key;
    }
}
