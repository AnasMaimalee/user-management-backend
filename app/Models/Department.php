<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Department extends Model
{
    use HasFactory, HasUuids ;
    public $incrementing = false;
    protected $keyType = 'string';


    protected $fillable = ['name', 'description', 'status'];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
