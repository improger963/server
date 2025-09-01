<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'reference',
        'status',
        'description',
        'ip_address',
        'user_agent',
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
     * Get the user that owns the transaction log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include deposit transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeposits($query)
    {
        return $query->where('type', 'deposit');
    }

    /**
     * Scope a query to only include withdrawal transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithdrawals($query)
    {
        return $query->where('type', 'withdrawal');
    }

    /**
     * Scope a query to only include budget allocation transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBudgetAllocations($query)
    {
        return $query->where('type', 'budget_allocation');
    }

    /**
     * Scope a query to only include budget return transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBudgetReturns($query)
    {
        return $query->where('type', 'budget_return');
    }

    /**
     * Scope a query to only include impression charge transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeImpressionCharges($query)
    {
        return $query->where('type', 'impression_charge');
    }

    /**
     * Check if transaction is a deposit
     *
     * @return bool
     */
    public function isDeposit()
    {
        return $this->type === 'deposit';
    }

    /**
     * Check if transaction is a withdrawal
     *
     * @return bool
     */
    public function isWithdrawal()
    {
        return $this->type === 'withdrawal';
    }

    /**
     * Check if transaction is a budget allocation
     *
     * @return bool
     */
    public function isBudgetAllocation()
    {
        return $this->type === 'budget_allocation';
    }

    /**
     * Check if transaction is a budget return
     *
     * @return bool
     */
    public function isBudgetReturn()
    {
        return $this->type === 'budget_return';
    }

    /**
     * Check if transaction is an impression charge
     *
     * @return bool
     */
    public function isImpressionCharge()
    {
        return $this->type === 'impression_charge';
    }
}