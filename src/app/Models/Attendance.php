<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * 複数代入を許可するカラム
     */
    protected $fillable = [
        'user_id',
        'clock_in_time',
        'clock_out_time',
        'break_start',
        'break_end',
        'status',
    ];

    /**
     * 日付型のカラムをCarbonインスタンスとして扱う
     */
    protected $dates = [
        'clock_in_time',
        'clock_out_time',
        'break_start',
        'break_end',
        'created_at',
        'updated_at',
    ];

    /**
     * リレーション：Attendance は 1人のUserに属する
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ステータスをわかりやすく返すアクセサ（任意）
     * 例）'working' → '勤務中' に変換
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'working'   => '勤務中',
            'on_break'  => '休憩中',
            'left'      => '退勤済み',
            'none'      => '未出勤',
            default     => '不明',
        };
    }
}
