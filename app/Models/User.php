<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'avatar',
        'password',
        'is_premium',
        'premium_plan',
        'premium_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'premium_expires_at'  => 'datetime',
            'password'            => 'hashed',
            'is_premium'          => 'boolean',
        ];
    }

    /**
     * Cek apakah user masih aktif berlangganan Premium.
     */
    public function isPremium(): bool
    {
        if (!$this->is_premium) {
            return false;
        }

        // Jika expires_at null, berarti permanent (tidak expire)
        if ($this->premium_expires_at === null) {
            return true;
        }

        return $this->premium_expires_at->isFuture();
    }

    /**
     * Relasi ke riwayat pembayaran user.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }
}
