<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Uid\Ulid;

class VenueBankAccount extends Model
{
    protected $fillable = [
        'bank_name',
        'account_name',
        'account_number',
    ];

    protected static function booted(): void
    {
        static::creating(function (VenueBankAccount $account): void {
            $account->ulid ??= (string) new Ulid;
        });
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
