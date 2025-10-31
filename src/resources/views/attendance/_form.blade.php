{{-- 勤務登録・編集 共通フォーム --}}
{{-- resources/views/attendance/_form.blade.php --}}
<div class="form-section">

    {{-- 出勤・退勤 --}}
    <div class="form-row">
        <label>出勤・退勤</label>
        <div class="time-range">
            <input type="time" name="clock_in_time"
                   value="{{ $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '' }}">
            ～
            <input type="time" name="clock_out_time"
                   value="{{ $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '' }}">
        </div>
    </div>

    {{-- 休憩（複数対応） --}}
    @if ($attendance->rests && $attendance->rests->count() > 0)
        @foreach ($attendance->rests as $index => $rest)
            <div class="form-row">
                <label>休憩{{ $index + 1 }}</label>
                <div class="time-range">
                    <input type="time" name="rests[{{ $index }}][break_start]"
                           value="{{ $rest->break_start ? \Carbon\Carbon::parse($rest->break_start)->format('H:i') : '' }}">
                    ～
                    <input type="time" name="rests[{{ $index }}][break_end]"
                           value="{{ $rest->break_end ? \Carbon\Carbon::parse($rest->break_end)->format('H:i') : '' }}">
                </div>
            </div>
        @endforeach
    @else
        <div class="form-row">
            <label>休憩1</label>
            <div class="time-range">
                <input type="time" name="rests[0][break_start]" value="">
                ～
                <input type="time" name="rests[0][break_end]" value="">
            </div>
        </div>
    @endif

    {{-- 備考欄 --}}
    <div class="form-row">
        <label>備考（修正理由など）</label>
        <textarea name="note" id="note" placeholder="例）業務対応のため延長">{{ $attendance->note ?? '' }}</textarea>
    </div>

</div>

