<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Ulid;

class SocialLink extends Model
{
    public const PLATFORMS = [
        'instagram' => ['label' => 'Instagram', 'icon' => 'ti ti-brand-instagram'],
        'facebook' => ['label' => 'Facebook', 'icon' => 'ti ti-brand-facebook'],
        'tiktok' => ['label' => 'TikTok', 'icon' => 'ti ti-brand-tiktok'],
        'youtube' => ['label' => 'YouTube', 'icon' => 'ti ti-brand-youtube'],
        'x' => ['label' => 'X', 'icon' => 'ti ti-brand-x'],
        'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'ti ti-brand-whatsapp'],
        'telegram' => ['label' => 'Telegram', 'icon' => 'ti ti-brand-telegram'],
        'linkedin' => ['label' => 'LinkedIn', 'icon' => 'ti ti-brand-linkedin'],
        'threads' => ['label' => 'Threads', 'icon' => 'ti ti-brand-threads'],
        'snapchat' => ['label' => 'Snapchat', 'icon' => 'ti ti-brand-snapchat'],
        'pinterest' => ['label' => 'Pinterest', 'icon' => 'ti ti-brand-pinterest'],
        'discord' => ['label' => 'Discord', 'icon' => 'ti ti-brand-discord'],
        'twitch' => ['label' => 'Twitch', 'icon' => 'ti ti-brand-twitch'],
        'spotify' => ['label' => 'Spotify', 'icon' => 'ti ti-brand-spotify'],
        'reddit' => ['label' => 'Reddit', 'icon' => 'ti ti-brand-reddit'],
        'tumblr' => ['label' => 'Tumblr', 'icon' => 'ti ti-brand-tumblr'],
        'github' => ['label' => 'GitHub', 'icon' => 'ti ti-brand-github'],
        'medium' => ['label' => 'Medium', 'icon' => 'ti ti-brand-medium'],
        'behance' => ['label' => 'Behance', 'icon' => 'ti ti-brand-behance'],
        'dribbble' => ['label' => 'Dribbble', 'icon' => 'ti ti-brand-dribbble'],
        'vimeo' => ['label' => 'Vimeo', 'icon' => 'ti ti-brand-vimeo'],
        'soundcloud' => ['label' => 'SoundCloud', 'icon' => 'ti ti-brand-soundcloud'],
        'line' => ['label' => 'LINE', 'icon' => 'ti ti-brand-line'],
        'wechat' => ['label' => 'WeChat', 'icon' => 'ti ti-brand-wechat'],
        'mastodon' => ['label' => 'Mastodon', 'icon' => 'ti ti-brand-mastodon'],
        'bluesky' => ['label' => 'Bluesky', 'icon' => 'ti ti-brand-bluesky'],
        'website' => ['label' => 'Website', 'icon' => 'ti ti-world'],
    ];

    protected $fillable = [
        'name',
        'platform',
        'url',
        'is_active',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::creating(function (SocialLink $socialLink): void {
            $socialLink->ulid ??= (string) new Ulid;
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public static function platformIcon(?string $platform): string
    {
        return self::PLATFORMS[$platform]['icon'] ?? 'ti ti-world';
    }
}
