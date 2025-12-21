<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
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
            // Find the highest existing number
            $last = Employee::where('employee_code', 'like', 'EMP-%')
                ->orderByRaw('CAST(SUBSTRING(employee_code, 5) AS UNSIGNED) DESC')
                ->first();

            $nextNumber = 1;
            if ($last) {
                $lastNumber = (int) substr($last->employee_code, 4); // after "EMP-"
                $nextNumber = $lastNumber + 1;
            }

            $employee->employee_code = 'EMP-' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
            // → EMP-01, EMP-02, ..., EMP-10, EMP-11, etc.
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

}
