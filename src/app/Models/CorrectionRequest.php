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

    protected $casts = [
        'before_clock_in' => 'datetime:H:i',
        'before_clock_out' => 'datetime:H:i',
        'before_break_start' => 'datetime:H:i',
        'before_break_end' => 'datetime:H:i',
        'after_clock_in' => 'datetime:H:i',
        'after_clock_out' => 'datetime:H:i',
        'after_break_start' => 'datetime:H:i',
        'after_break_end' => 'datetime:H:i',
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
