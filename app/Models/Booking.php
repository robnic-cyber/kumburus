<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'confirmation_code',
        'check_in',
        'check_out',
        'guests',
        'total_amount',
        'payment_method',
        'status',
        'special_requests',
        'email_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'total_amount' => 'decimal:2',
            'email_sent_at' => 'datetime',
        ];
    }

    public static function paymentMethods(): array
    {
        return [
            'ewallet' => 'E-wallet',
            'cash' => 'Cash payment',
            'bank_transfer' => 'Bank transfer',
        ];
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::paymentMethods()[$this->payment_method] ?? ucfirst(str_replace('_', ' ', (string) $this->payment_method));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function getNightsAttribute(): int
    {
        return $this->check_in->diffInDays($this->check_out);
    }
}
