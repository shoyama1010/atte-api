<?php

namespace App\Models;

use Carbon\Carbon;

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

    public function rests()
    {
        return $this->hasMany(Rest::class);
    }


    public function getTotalRestTimeAttribute()
    {
        $totalMinutes = 0;

        foreach ($this->rests as $rest) {
            if ($rest->break_start && $rest->break_end) {
                $start = \Carbon\Carbon::parse($rest->break_start);
                $end   = \Carbon\Carbon::parse($rest->break_end);
                $totalMinutes += $end->diffInMinutes($start);
            }
        }

        // 分→時:分 形式で返す（例：01:30）
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }


    /**
     * 🔹 単一勤務における休憩時間を返すアクセサ
     * 例： "01:15" または "-" を返す
     */
    public function getBreakTimeAttribute()
    {
        if (!$this->break_start || !$this->break_end) {
            return '-';
        }

        $start = \Carbon\Carbon::parse($this->break_start);
        $end = \Carbon\Carbon::parse($this->break_end);

        return $end->diff($start)->format('%H:%I');
    }

    /**
     * 🔹勤務時間の合計（出勤〜退勤 − 休憩）
     */
    public function getWorkingDurationAttribute()
    {
        if (!$this->clock_in_time || !$this->clock_out_time) {
            return null;
        }

        $clockIn  = Carbon::parse($this->clock_in_time);
        $clockOut = Carbon::parse($this->clock_out_time);

        $totalWorkMinutes = $clockOut->diffInMinutes($clockIn);
        $restMinutes = 0;

        foreach ($this->rests as $rest) {
            if ($rest->break_start && $rest->break_end) {
                $start = Carbon::parse($rest->break_start);
                $end   = Carbon::parse($rest->break_end);
                $restMinutes += $end->diffInMinutes($start);
            }
        }

        $netMinutes = $totalWorkMinutes - $restMinutes;
        $hours = floor($netMinutes / 60);
        $minutes = $netMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function correctionRequest()
    {
        // 1つの勤怠レコードに対して、1件の修正申請が紐づく（1対1）
        // return $this->hasOne(CorrectionRequest::class);
        // return $this->hasMany(CorrectionRequest::class);
        return $this->hasOne(CorrectionRequest::class)->latestOfMany();
    }
}
