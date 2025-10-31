<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'admin_id',
        'request_type',
        'reason',
        'before_clock_in',
        'before_clock_out',
        'before_break_start',
        'before_break_end',
        'after_clock_in',
        'after_clock_out',
        'after_break_start',
        'after_break_end',
        'status',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
