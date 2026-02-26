<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'student_code',
        'full_name',
        'email',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
