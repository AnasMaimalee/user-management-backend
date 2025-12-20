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
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'employee_code',
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
