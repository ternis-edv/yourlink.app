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
        });
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
