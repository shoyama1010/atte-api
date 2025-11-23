<table class="attendance-detail-table">
    <tr>
        <th>作成日</th>
        <td>{{ $attendance->created_at->format('Y年m月d日') }}</td>
    </tr>

    <tr>
        <th>出勤・退勤</th>
        <td>{{ $attendance->clock_in_time }} ～ {{ $attendance->clock_out_time }}</td>
    </tr>
    <tr>
        <th>休憩</th>
        <td>
            @foreach ($attendance->rests as $rest)
                {{ $rest->break_start }} 〜 {{ $rest->break_end }}<br>
            @endforeach
        </td>
    </tr>
    <tr>
        <th>備考</th>
        <td>{{ $attendance->note }}</td>
    </tr>
</table>
