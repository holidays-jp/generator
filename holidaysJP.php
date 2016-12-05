<?php
date_default_timezone_set('Asia/Tokyo');

class holidaysJP
{
    protected $ical_url;
    const DIST = __DIR__ . '/json/';


    /**
     * holidaysJP constructor.
     * @param $url
     */
    public function __construct($url)
    {
        $this->ical_url = $url;
    }


    /**
     * generate
     */
    public function generate()
    {
        $data = $this->get_ical();
        $main_data = $this->generate_main_json($data);
        $this->output_json($main_data);
    }


    /**
     * @param $data
     * @return array
     */
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

            $dates[$datetime] = $summary[1];
        }

        // 日付順にソートして返却
        ksort($dates);
        return $dates;
    }


    /**
     * @param $data
     */
    function output_json($data)
    {
        $data_date = [];
        $yearly = [];
        $yearly_date = [];

        foreach ($data as $key => $value) {
            $y = date('Y', $key);
            $ymd = date('Y-m-d', $key);

            $data_date[$ymd] = $value;
            $yearly[$y][$key] = $value;
            $yearly_date[$y][$ymd] = $value;
        }

        file_put_contents(self::DIST . 'datetime.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        file_put_contents(self::DIST . 'date.json', json_encode($data_date, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        foreach ($yearly as $year => $data) {
            $dir = self::DIST . $year;
            if (! is_dir($dir)) {
                mkdir($dir);
            }

            file_put_contents(self::DIST . $year . '/datetime.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            file_put_contents(self::DIST . $year . '/date.json', json_encode($yearly_date[$year], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }


    /**
     * @return mixed
     */
    function get_ical()
    {
        // iCal データの取得
        $ics = file_get_contents($this->ical_url);
        return str_replace("\r", '', $ics);
    }
}
