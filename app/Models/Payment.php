<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'invoice_number',
        'amount',
        'plan',
        'status',
        'doku_payment_url',
        'doku_response',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'doku_response' => 'array',
            'paid_at'       => 'datetime',
            'amount'        => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Label nama paket yang ramah pengguna.
     */
    public function getPlanLabelAttribute(): string
    {
        return match ($this->plan) {
            'monthly' => 'Bulanan',
            'yearly'  => 'Tahunan',
            default   => ucfirst($this->plan),
        };
    }

    /**
     * Format harga dalam Rupiah.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}
