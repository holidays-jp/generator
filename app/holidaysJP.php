<?php
namespace HolidaysJP;
use Carbon\Carbon;
use Illuminate\Support\Collection;

require_once dirname(__DIR__) . '/vendor/autoload.php';


/**
 * Class holidaysJP
 * @package HolidaysJP
 */
class holidaysJP
{
    protected $ical_url;
    const DIST = __DIR__ . '/../json';


    /**
     * holidaysJP constructor.
     * @param $url
     */
    public function __construct($url = null)
    {
        $this->ical_url = $url;
    }


    /**
     * APIファイル生成 メイン処理
     */
    public function generate()
    {
        // icalデータを取得して配列化
        $data = $this->get_ical_data();
        $holidays = $this->convert_ical_to_array($data);

        // 一覧データを出力
        $this->generate_api_file($holidays);

        // データを年別に分解
        $yearly = Collection::make($holidays)
                    ->groupBy(function ($item, $key) {
                        return Carbon::createFromTimestamp($key)->year;
                    }, true)
                    ->toArray();

        // 年別データを出力
        foreach ($yearly as $year => $ary) {
            $this->generate_api_file($ary, $year);
        }
    }


    /**
     * iCalデータの取得 (+ 不要文字などの削除)
     * @return mixed
     */
    function get_ical_data()
    {
        $ics = file_get_contents($this->ical_url);
        return str_replace("\r", '', $ics);
    }


    /**
     * iCal形式のデータを配列に変換
     * @param $data
     * @return array
     */
    function convert_ical_to_array($data)
    {
        $results = [];

        // イベントごとに区切って配列化
        $events = explode('END:VEVENT', $data);

        foreach ($events as $event) {
            // 日付を求める
            if (preg_match('/DTSTART;\D*(\d+)/m', $event, $m) != 1) {
                continue;
            }
            $date = Carbon::createFromTimestamp(strtotime($m[1]));

            // サマリ(祝日名)を求める
            if (preg_match('/SUMMARY:(.+?)\n/m', $event, $summary) != 1) {
                continue;
            }

            $results[$date->timestamp] = $this->convert_holiday_name($date, $summary[1]);
        }

        // 日付順にソートして返却
        ksort($results);
        return $results;
    }


    /**
     * @param Carbon $date
     * @param $name
     * @return string
     */
    public function convert_holiday_name(Carbon $date, $name)
    {
        if ($name == '体育の日' && $date->year >= 2020) {
            return 'スポーツの日';
        }

        return $name;
    }

    /**
     * APIデータをファイルに出力
     * @param $data
     * @param string $year
     */
    function generate_api_file($data, $year = '')
    {
        // 出力先フォルダがなければ作成
        $dist_dir = (! empty($year)) ? self::DIST.'/'.$year : self::DIST;
        if (! is_dir($dist_dir)) {
            mkdir($dist_dir);
        }

        // ファイル出力 (datetime型)
        $this->output_json_file("{$dist_dir}/datetime.json", $data);
        $this->output_csv_file("{$dist_dir}/datetime.csv", $data);

        // キーをYMD形式に変換して出力
        $date_data = Collection::make($data)
            ->keyBy(function ($item, $key) {
                return Carbon::createFromTimestamp($key)->toDateString();
            })
            ->toArray();

        // ファイル出力 (date)
        $this->output_json_file("{$dist_dir}/date.json", $date_data);
        $this->output_csv_file("{$dist_dir}/date.csv", $date_data);
    }


    /**
     * JSONファイルを出力
     * @param $filename
     * @param $data
     */
    protected function output_json_file($filename, $data)
    {
        file_put_contents($filename, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }


    /**
     * CSVファイルを出力
     * @param $filename
     * @param $data
     */
    protected function output_csv_file($filename, $data)
    {
        $recordArr = array();

        foreach($data as $date => $text) {
            $recordArr[] = [$date, $text];
        }
        $fp = fopen($filename, 'w');
        foreach ($recordArr as $record) {
            fputcsv($fp, $record);
        }
        fclose($fp);
    }
}
