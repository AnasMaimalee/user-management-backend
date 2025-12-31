<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('attendance.employee.{employeeId}', function ($user, $employeeId) {
    return $user->employee?->id === $employeeId;
});

Broadcast::channel('attendance.hr', function ($user) {
    return $user->hasRole('hr') || $user->hasRole('admin');
});
