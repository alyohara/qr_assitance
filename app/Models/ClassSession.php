<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ClassSession extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'subject_id',
        'topic',
        'professor_pin',
        'qr_rotation_seconds',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ClassSession $session): void {
            if (! $session->uuid) {
                $session->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function isActive(?Carbon $at = null): bool
    {
        $at = $at ?? now();

        return $at->betweenIncluded($this->starts_at, $this->ends_at);
    }

    public function windowForTime(?Carbon $at = null): int
    {
        $at = $at ?? now();

        return (int) floor($at->timestamp / max(1, $this->qr_rotation_seconds));
    }

    public function signWindow(int $window): string
    {
        return hash_hmac('sha256', "{$this->uuid}|{$window}", (string) config('app.key'));
    }

    public function verifyWindowSignature(int $window, string $signature): bool
    {
        return hash_equals($this->signWindow($window), $signature);
    }
}
