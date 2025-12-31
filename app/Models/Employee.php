<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\EmployeeResetPasswordNotification;

class Employee extends Model
{
    use HasFactory, Notifiable, HasRoles;

    // IMPORTANT
    protected $guard_name = 'web';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'department_id',
        'rank_id',
        'branch_id',
        'status',
        'basic_salary',
        'allowances',
        'deductions',
        'monthly_savings',
        'device_user_id',
        'fingerprint_enrolled_at',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'monthly_savings' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($employee) {
            if (! $employee->id) {
                $employee->id = (string) Str::uuid();
            }

            if (! $employee->employee_code) {
                $last = self::where('employee_code', 'like', 'EMP-%')
                    ->orderByRaw("CAST(SUBSTR(employee_code, 5) AS UNSIGNED) DESC")
                    ->first();

                $next = $last ? ((int) substr($last->employee_code, 4) + 1) : 1;
                $employee->employee_code = 'EMP-' . str_pad($next, 2, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }



    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function walletTransactions()
    {
        return $this->hasManyThrough(WalletTransaction::class, Wallet::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new EmployeeResetPasswordNotification($token));
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function dailyAttendances()
    {
        return $this->hasMany(DailyAttendance::class);
    }

}
