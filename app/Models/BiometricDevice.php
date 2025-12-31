<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class BiometricDevice extends Model
{
    use HasUuid;

    protected $table = 'biometric_devices';

    protected $fillable = [
        'name',
        'ip',
        'port',
        'is_active',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'biometric_device_id');
    }
}
