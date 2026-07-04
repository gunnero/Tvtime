<?php

namespace App\Services\Concerns;

trait SanitizesMetadata
{
    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    protected function sanitizeMetadata(array $metadata): array
    {
        $blockedFragments = [
            'access_token',
            'auth',
            'device',
            'ip',
            'password',
            'refresh_token',
            'secret',
            'token',
            'user_agent',
        ];

        return collect($metadata)
            ->reject(function (mixed $value, string $key) use ($blockedFragments): bool {
                $normalized = strtolower($key);

                foreach ($blockedFragments as $fragment) {
                    if (str_contains($normalized, $fragment)) {
                        return true;
                    }
                }

                return false;
            })
            ->all();
    }
}
