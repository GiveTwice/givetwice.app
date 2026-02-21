<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GdprAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_email',
        'action',
        'details',
        'performed_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $action, User $user, ?string $details = null, ?string $performedBy = null): static
    {
        return static::create([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'action' => $action,
            'details' => $details,
            'performed_by' => $performedBy,
            'created_at' => now(),
        ]);
    }
}
