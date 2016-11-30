<?php
/**
 * @param $ical_url
 * @return array|false
 */
function get_data_from_ics($ical_url)
{
    $dates = [];

    // iCal データの取得
    $ics = file_get_contents($ical_url);
    if (empty($ics)) {
        return false;
    }

    // イベントごとに区切って配列化
    $events = explode('END:VEVENT', str_replace("\r", '', $ics));

    foreach ($events as $event) {
        // 日付を求める
        if (preg_match('/DTSTART;\D*(\d+)/m', $event, $date) != 1) {
            continue;
        }
        $datetime = strtotime($date[1]);

        // サマリ(祝日名)を求める
        if (preg_match('/SUMMARY:(.+?)\n/m', $event, $summary) != 1) {
            continue;
        }

        $dates[$datetime] = [
            'date' => date('Y-m-d', $datetime),
            'title' => $summary[1],
        ];
    }

    // 日付順にソートして返却
    ksort($dates);
    return $dates;
}

/**
 * @param $holidays
 * @return array
 */
function convert_yearly_data($holidays)
{
    $yearly = [];

    foreach ($holidays as $key => $a) {
        $y = date('Y', strtotime($a['date']));
        $yearly[$y][$key] = $a;
    }

    return $yearly;
}


$dist = 'json/';
$ical_url = 'https://calendar.google.com/calendar/ical/japanese__ja@holiday.calendar.google.com/public/full.ics';

$holidays = get_data_from_ics($ical_url);
$yearly = convert_yearly_data($holidays);

file_put_contents("{$dist}index.json", json_encode($holidays, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

foreach ($yearly as $year => $data) {
    file_put_contents("{$dist}year/{$year}.json", json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}
