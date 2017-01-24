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

        // 年別データのチェック (今年)
        $this->checkApiFile("{$year}/date.json", $year);
        $this->checkApiFile("{$year}/datetime.json", $year, true);

        // 年別データのチェック (来年)
        $nextyear = $year + 1;
        $this->checkApiFile("{$nextyear}/date.json", $nextyear);
        $this->checkApiFile("{$nextyear}/datetime.json", $nextyear, true);
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

        $data = json_decode(file_get_contents($filename), true);

        // 元日のデータが入っているか
        $dt = Carbon::createFromDate($year)->startOfYear();
        $key = ($is_datetime) ? $dt->timestamp : $dt->toDateString();
        $this->assertArrayHasKey($key, $data, $filename);
    }
}
