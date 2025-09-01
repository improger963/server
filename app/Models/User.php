<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'password',
        'balance',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance' => 'decimal:2',
    ];

    /**
     * Get the sites for the user.
     */
    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    /**
     * Get the campaigns for the user.
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Get the creatives for the user.
     */
    public function creatives()
    {
        return $this->hasMany(Creative::class);
    }

    /**
     * Deduct amount from user balance
     *
     * @param float $amount
     * @return bool
     */
    public function deductBalance($amount)
    {
        if ($this->balance < $amount) {
            return false;
        }

        $this->balance -= $amount;
        return $this->save();
    }

    /**
     * Add amount to user balance
     *
     * @param float $amount
     * @return bool
     */
    public function addBalance($amount)
    {
        $this->balance += $amount;
        return $this->save();
    }

    /**
     * Check if user has enough balance
     *
     * @param float $amount
     * @return bool
     */
    public function hasBalance($amount)
    {
        return $this->balance >= $amount;
    }
}