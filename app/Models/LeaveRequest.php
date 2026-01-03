<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id',
        'reason',
        'start_date',
        'end_date',
        'resume_date',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_note',
    ];

    public $incrementing = false;

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // CORRECT: Link to User, then access employee via user->employee
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Helper to get employee details easily
    public function employee()
    {
        return $this->user?->employee();
    }
}
