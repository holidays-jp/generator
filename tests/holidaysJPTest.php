<?php
use HolidaysJP\holidaysJP;
use Carbon\Carbon;


/**
 * Class holidaysJPTest
 */
class holidaysJPTest extends PHPUnit_Framework_TestCase
{
    /**
     * ical解析関連のテスト
     */
    public function testICALAnalyze()
    {
        // サンプル ical データの解析テスト
        $test_file = __DIR__ . '/testdata.ics';
        $holidays = new holidaysJP($test_file);
        $data = $holidays->convert_ical_to_array($holidays->get_ical_data());

        $expected = [
            1420038000 => '元日',
            1458486000 => '春分の日 振替休日',
            1513954800 => '天皇誕生日',
        ];
        $this->assertEquals($expected, $data);
    }

    /**
     * ファイル生成に関するテスト
     */
    public function testGenerator()
    {
        // 実際のデータの生成
        $url = 'https://calendar.google.com/calendar/ical/japanese__ja@holiday.calendar.google.com/public/full.ics';
        $holidays = new holidaysJP($url);
        $holidays->generate();

        // 一覧データのチェック
        $year = Carbon::now()->year;
        $this->checkApiFile('date.json', $year);
        $this->checkApiFile('datetime.json', $year, true);
        $this->checkApiFile('date.csv', $year);
        $this->checkApiFile('datetime.csv', $year, true);

        // 年別データのチェック (今年)
        $this->checkApiFile("{$year}/date.json", $year);
        $this->checkApiFile("{$year}/datetime.json", $year, true);
        $this->checkApiFile("{$year}/date.csv", $year);
        $this->checkApiFile("{$year}/datetime.csv", $year, true);

        // 年別データのチェック (来年)
        $nextyear = $year + 1;
        $this->checkApiFile("{$nextyear}/date.json", $nextyear);
        $this->checkApiFile("{$nextyear}/datetime.json", $nextyear, true);
        $this->checkApiFile("{$nextyear}/date.csv", $nextyear);
        $this->checkApiFile("{$nextyear}/datetime.csv", $nextyear, true);
    }

    /**
     * APIファイルが存在し、データが入っているか
     * @param $filename
     * @param $year
     * @param bool $is_datetime
     */
    private function checkApiFile($filename, $year, $is_datetime = false)
    {
        // ファイルの存在チェック
        $filename = dirname(__DIR__) . "/json/{$filename}";

        $this->assertFileExists($filename);

        $fileChkArr = explode(".", $filename);
        $fileExtension = end($fileChkArr);
        $allowExtensions = array('json', 'csv');

        $this->assertContains($fileExtension, $allowExtensions);

        if ($fileExtension == 'json') {
            $data = json_decode(file_get_contents($filename), true);
        } else {
            // 行で分けたcsvの配列を作る
            // [[日付のcsv文字列],[祝日名のcsv文字列]]
            $csvArrByLine = str_getcsv(file_get_contents($filename), "\n");

            // 行ごとのcsv文字列を配列にする
           // [[0]=>[[0]=>'YYYYMMDD',[1]=>'YYYYMMDD'..],[1]=>[[0]=>'祝日名'..]]
            foreach($csvArrByLine as $csvLine) {
                $dateTextArr[] = str_getcsv($csvLine);
            }

            // $dateTextArr[0]の値 .. 日付一覧
            // $dateTextArr[1]の値 .. 祝日名一覧
            $cnt = count($dateTextArr[0]);
            $this->assertCount($cnt, $dateTextArr[1]);

            $dates = array_values($dateTextArr[0]);
            foreach ($dates as $key => $date) {
                $data[$date] = $dateTextArr[1][$key];
            }
        }

        // 元日のデータが入っているか
        $dt = Carbon::createFromDate($year)->startOfYear();
        $key = ($is_datetime) ? $dt->timestamp : $dt->toDateString();
        $this->assertArrayHasKey($key, $data, $filename);
    }
}
