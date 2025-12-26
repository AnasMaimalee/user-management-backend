<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\EmployeeResetPasswordNotification;
use Illuminate\Support\Str;
class Employee extends Model

{



    use HasApiTokens, HasFactory, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });

        static::creating(function ($employee) {
            if (!$employee->employee_code) {
                // Lock for concurrency safety (optional but recommended)
                $last = Employee::where('employee_code', 'like', 'EMP-%')
                    ->lockForUpdate()
                    ->orderByRaw("CAST(SUBSTR(employee_code, 5) AS UNSIGNED) DESC")
                    ->first();

                $nextNumber = 1;
                if ($last) {
                    $lastNumber = (int) substr($last->employee_code, 4);  // after "EMP-"
                    $nextNumber = $lastNumber + 1;
                }

                $employee->employee_code = 'EMP-' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
            }
        });
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'department_id',
        'rank_id',
        'branch_id',
        'status',
    ];

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

    public function user()
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new EmployeeResetPasswordNotification($token));
    }

}
