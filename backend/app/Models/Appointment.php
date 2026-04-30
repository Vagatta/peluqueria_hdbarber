<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_NO_SHOW = 'no_show';

    public const PAY_UNPAID = 'unpaid';
    public const PAY_PAID = 'paid';
    public const PAY_REFUNDED = 'refunded';
    public const PAY_FAILED = 'failed';

    protected $fillable = [
        'user_id', 'service_id', 'employee_id',
        'start_at', 'end_at', 'status', 'payment_status', 'notes',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function isCancellable(): bool
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return false;
        }
        // Cancellable up to 2 hours before
        return now()->lt($this->start_at->copy()->subHours(2));
    }
}
