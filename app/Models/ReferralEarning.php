<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralEarning extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'referred_user_id',
        'amount',
        'source_transaction_id',
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that received the referral earning.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the referred user who generated the referral earning.
     */
    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    /**
     * Get the source transaction that generated the referral earning.
     */
    public function sourceTransaction()
    {
        return $this->belongsTo(TransactionLog::class, 'source_transaction_id');
    }
}