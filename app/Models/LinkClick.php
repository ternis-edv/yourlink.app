<?php

namespace App\Models;

use Database\Factories\LinkClickFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'link_id',
    'ip_address',
    'user_agent',
    'browser',
    'os',
    'device_type',
    'referer',
    'country',
    'city',
    'is_robot',
])]
class LinkClick extends Model
{
    /** @use HasFactory<LinkClickFactory> */
    use HasFactory, HasUlids;

    protected static function booted(): void
    {
        static::creating(function (LinkClick $click) {
            if ($click->ip_address) {
                $click->ip_address = static::maskIp($click->ip_address);
            }

            if ($click->user_agent) {
                $parsed = static::parseUserAgent($click->user_agent);
                $click->browser = $parsed['browser'];
                $click->os = $parsed['os'];
                $click->device_type = $parsed['device_type'];
            }
        });
    }

    public static function parseUserAgent(string $ua): array
    {
        $browser = 'Unknown';
        $os = 'Unknown';
        $device = 'Desktop';

        // Basic Browser Detection
        if (preg_match('/MSIE/i', $ua) && ! preg_match('/Opera/i', $ua)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Chrome/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/Opera/i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/Netscape/i', $ua)) {
            $browser = 'Netscape';
        }

        // Basic OS Detection
        if (preg_match('/windows|win32/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/linux/i', $ua)) {
            $os = 'Linux';
        } elseif (preg_match('/android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
            $os = 'iOS';
        }

        // Device Type
        if (preg_match('/mobile|android|iphone|ipod/i', $ua)) {
            $device = 'Mobile';
        } elseif (preg_match('/tablet|ipad/i', $ua)) {
            $device = 'Tablet';
        }

        return [
            'browser' => $browser,
            'os' => $os,
            'device_type' => $device,
        ];
    }

    public static function maskIp(string $ip): string
    {
        if (str_contains($ip, '.')) {
            $parts = explode('.', $ip);
            if (count($parts) === 4) {
                $parts[3] = '0';

                return implode('.', $parts);
            }
        }

        if (str_contains($ip, ':')) {
            // IPv6 masking (zero out the last 64 bits/4 groups)
            $parts = explode(':', $ip);
            for ($i = count($parts) - 4; $i < count($parts); $i++) {
                if (isset($parts[$i])) {
                    $parts[$i] = '0000';
                }
            }

            return implode(':', $parts);
        }

        return $ip;
    }

    protected function casts(): array
    {
        return [
            'is_robot' => 'boolean',
        ];
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }
}
