<?php
class holidaysJP
{
    protected $ical_url;
    const DIST = 'json/';

    public function __construct($url)
    {
        $this->ical_url = $url;
    }

    public function generate()
    {
        $data = $this->get_ical();
        $main_data = $this->generate_main_json($data);
        $this->output_json($main_data);
    }

    function generate_main_json($data)
    {
        $dates = [];

        // イベントごとに区切って配列化
        $events = explode('END:VEVENT', $data);

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

    function output_json($data)
    {
        file_put_contents(self::DIST . 'index.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $yearly = [];

        foreach ($data as $key => $a) {
            $y = date('Y', strtotime($a['date']));
            $yearly[$y][$key] = $a;
        }

        foreach ($yearly as $year => $data) {
            file_put_contents(self::DIST . "year/{$year}.json", json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }

    function get_ical()
    {
        // iCal データの取得
        $ics = file_get_contents($this->ical_url);
        return str_replace("\r", '', $ics);
    }
}

$url = 'https://calendar.google.com/calendar/ical/japanese__ja@holiday.calendar.google.com/public/full.ics';
date_default_timezone_set('Asia/Tokyo');
$holidays = new holidaysJP($url);
$holidays->generate();
