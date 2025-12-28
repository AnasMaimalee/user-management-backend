<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Wallet extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'balance',
        'monthly_savings',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'monthly_savings' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // Helper method to add transaction and update balance
    public function addTransaction($amount, $type, $description = null, $status = 'approved', $processedBy = null)
    {
        $transaction = $this->transactions()->create([
            'id' => (string) Str::uuid(),
            'amount' => $amount,
            'type' => $type, // ⚠️ Must match enum: deposit, withdrawal, adjustment
            'description' => $description,
            'status' => $status,
            'processed_by' => $processedBy,
            'processed_at' => $status === 'approved' ? now() : null,
        ]);

        if ($status === 'approved') {
            if ($type === 'deposit' || $type === 'adjustment') {
                $this->balance += $amount;
            } elseif ($type === 'withdrawal') {
                $this->balance -= $amount;
            }
            $this->save();
        }

        return $transaction;
    }

}
