<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        'frozen_balance',
        'referrer_id',
        'referral_code',
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
        'frozen_balance' => 'decimal:2',
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

    /**
     * Freeze amount from user balance
     *
     * @param float $amount
     * @return bool
     */
    public function freezeBalance($amount)
    {
        if ($this->balance < $amount) {
            return false;
        }

        $this->balance -= $amount;
        $this->frozen_balance += $amount;
        return $this->save();
    }

    /**
     * Unfreeze amount back to user balance
     *
     * @param float $amount
     * @return bool
     */
    public function unfreezeBalance($amount)
    {
        if ($this->frozen_balance < $amount) {
            return false;
        }

        $this->frozen_balance -= $amount;
        $this->balance += $amount;
        return $this->save();
    }

    /**
     * Get available balance (excluding frozen balance)
     *
     * @return float
     */
    public function getAvailableBalance()
    {
        return $this->balance;
    }

    /**
     * Get total frozen balance
     *
     * @return float
     */
    public function getFrozenBalance()
    {
        return $this->frozen_balance;
    }

    /**
     * Get the referrer (user who referred this user).
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the referrals (users referred by this user).
     */
    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    /**
     * Get the referral earnings for this user.
     */
    public function referralEarnings()
    {
        return $this->hasMany(ReferralEarning::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
    
    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function hasRole($role)
    {
        // For now, we'll consider user with ID 1 as admin
        // In a real application, you would have a proper role system
        if ($role === 'admin') {
            return $this->id === 1;
        }
        
        return false;
    }
}