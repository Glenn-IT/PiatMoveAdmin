<?php

function status_badge(string $status): string {
    $map = [
        'pending'   => 'neutral',
        'accepted'  => 'primary',
        'started'   => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        'rejected'  => 'warning',
    ];
    $cls = $map[$status] ?? 'neutral';
    return '<span class="badge badge-' . htmlspecialchars($cls, ENT_QUOTES) . '">'
         . htmlspecialchars(ucfirst($status), ENT_QUOTES)
         . '</span>';
}

function render_booking_history_chart(array $history, string $label = 'Booking history'): string {
    $n = count($history);
    if ($n === 0) return '<div class="chart-empty">No booking data yet.</div>';

    $values  = array_values($history);
    $dates   = array_keys($history);
    $maxVal  = max($values);
    $axisMax = $maxVal > 0 ? $maxVal : 4;

    $w = 720; $h = 200;
    $padL = 10; $padR = 10; $padT = 12; $padB = 10;
    $plotW = $w - $padL - $padR;
    $plotH = $h - $padT - $padB;

    $points = [];
    for ($i = 0; $i < $n; $i++) {
        $x = $padL + ($n > 1 ? $i * ($plotW / ($n - 1)) : $plotW / 2);
        $y = $padT + $plotH - ($values[$i] / $axisMax) * $plotH;
        $points[] = [$x, $y, $values[$i], $dates[$i]];
    }

    $gridLines = '';
    for ($g = 0; $g <= 3; $g++) {
        $gy = $padT + ($plotH / 3) * $g;
        $gridLines .= '<line x1="' . $padL . '" y1="' . $gy . '" x2="' . ($w - $padR) . '" y2="' . $gy . '" stroke="var(--gray-200)" stroke-width="1"/>';
    }

    $linePath = 'M ' . implode(' L ', array_map(fn($p) => round($p[0], 1) . ' ' . round($p[1], 1), $points));
    $areaPath = $linePath
        . ' L ' . round($points[$n - 1][0], 1) . ' ' . ($padT + $plotH)
        . ' L ' . round($points[0][0], 1) . ' ' . ($padT + $plotH) . ' Z';

    $markers = '';
    foreach ($points as [$x, $y, $val, $date]) {
        $tip = date('M j', strtotime($date)) . ': ' . $val . ' booking' . ($val === 1 ? '' : 's');
        if ($val > 0) {
            $markers .= '<circle cx="' . round($x, 1) . '" cy="' . round($y, 1) . '" r="3.5" fill="var(--blue-600)"><title>' . htmlspecialchars($tip) . '</title></circle>';
        }
        $markers .= '<circle cx="' . round($x, 1) . '" cy="' . round($y, 1) . '" r="8" fill="transparent"><title>' . htmlspecialchars($tip) . '</title></circle>';
    }

    $svg = '<svg viewBox="0 0 ' . $w . ' ' . $h . '" width="100%" height="' . $h . '" preserveAspectRatio="none" role="img" aria-label="' . htmlspecialchars($label) . '">'
         . $gridLines
         . '<path d="' . $areaPath . '" fill="var(--blue-50)" stroke="none"/>'
         . '<path d="' . $linePath . '" fill="none" stroke="var(--blue-600)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>'
         . $markers
         . '</svg>';

    $firstLabel = htmlspecialchars(date('M j', strtotime($dates[0])));
    $lastLabel  = htmlspecialchars(date('M j', strtotime($dates[$n - 1])));

    return $svg . '<div class="chart-svg-labels"><span>' . $firstLabel . '</span><span>' . $lastLabel . '</span></div>';
}

function render_bar_rows(array $items, string $color, string $emptyText): string {
    if (empty($items)) return '<div class="chart-empty">' . htmlspecialchars($emptyText) . '</div>';

    $max = max($items);
    $html = '';
    foreach ($items as $label => $count) {
        $pct = $max > 0 ? round(($count / $max) * 100, 1) : 0;
        $html .= '<div class="chart-bar-row">'
               . '<div class="chart-bar-label" title="' . htmlspecialchars($label) . '">' . htmlspecialchars($label) . '</div>'
               . '<div class="chart-bar-track"><div class="chart-bar-fill" style="width:' . $pct . '%;background:' . $color . '"></div></div>'
               . '<div class="chart-bar-value">' . number_format($count) . '</div>'
               . '</div>';
    }
    return $html;
}
