<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidSocialMedia implements Rule
{
    private string $errorMessage = '';

    private array $domainMap = [
        'Instagram'   => 'instagram.com',
        'X (Twitter)' => 'twitter.com',
        'YouTube'     => 'youtube.com',
        'Facebook'    => 'facebook.com',
        'TikTok'      => 'tiktok.com',
        'Spotify'     => 'spotify.com',
        'Apple Music' => 'music.apple.com',
        'SoundCloud'  => 'soundcloud.com',
        'Bandcamp'    => 'bandcamp.com',
        'LinkedIn'    => 'linkedin.com',
    ];

    public function passes($attribute, $value): bool
    {
        $networks = is_string($value) ? json_decode($value, true) : $value;

        foreach ($networks as $network) {
            $nombre = $network['nombre'] ?? '';
            $url    = $network['url'] ?? '';

            if (empty($nombre) || empty($url)) {
                $this->errorMessage = 'Cada red social debe tener nombre y URL.';
                return false;
            }

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $this->errorMessage = "La URL de $nombre no es válida.";
                return false;
            }

            $expectedDomain = $this->domainMap[$nombre] ?? null;
            $actualHost     = parse_url($url, PHP_URL_HOST) ?? '';
            $actualHost     = strtolower(str_replace('www.', '', $actualHost));

            if ($expectedDomain && !str_contains($actualHost, $expectedDomain)) {
                $this->errorMessage = "La URL de $nombre debe pertenecer a $expectedDomain.";
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return $this->errorMessage;
    }
}
