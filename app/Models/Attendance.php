<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'class_session_id',
        'student_id',
        'window_index',
        'scan_ip',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
