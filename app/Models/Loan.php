<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Loan extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id', 'amount', 'months', 'monthly_deduction', 'remaining_amount',
        'reason', 'status', 'approved_by', 'admin_note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
            $model->remaining_amount = $model->amount;
            $model->monthly_deduction = $model->amount / $model->months;
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Deduct monthly payment during payroll
    public function deductPayment($amount)
    {
        $deduct = min($amount, $this->remaining_amount);
        $this->paid_amount += $deduct;
        $this->remaining_amount -= $deduct;
        if ($this->remaining_amount <= 0) {
            $this->status = 'completed';
            $this->remaining_amount = 0;
        }
        $this->save();
    }

    public function getPaidAmountAttribute(): float
    {
        return max(0, $this->amount - $this->remaining_amount);
    }
}
