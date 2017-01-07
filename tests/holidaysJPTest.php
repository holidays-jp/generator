<?php
use HolidaysJP\holidaysJP;

class holidaysJPTest extends PHPUnit_Framework_TestCase
{
    /**
     * ical解析関連のテスト
     */
    public function testJson()
    {
        $holidays = new holidaysJP(__DIR__ . '/testdata.ics');
        $data = $holidays->get_ical_data();
        $main_data = $holidays->convert_ical_to_array($data);

        $expected = [
            1420038000 => '元日',
            1458486000 => '春分の日 振替休日',
            1513954800 => '天皇誕生日',
        ];
        $this->assertEquals($expected, $main_data);
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

        $year = date('Y');
        $this->checkApiFile('date.json', $year);
        $this->checkApiFile('datetime.json', $year, true);

        $this->checkApiFile("{$year}/date.json", $year);
        $this->checkApiFile("{$year}/datetime.json", $year, true);

        $nextyear = $year + 1;
        $this->checkApiFile("{$nextyear}/date.json", $nextyear);
        $this->checkApiFile("{$nextyear}/datetime.json", $nextyear, true);
    }

    /**
     * APIファイルが存在し、データが入っているか
     * @param $filename
     * @param $year
     * @param bool $is_date
     */
    private function checkApiFile($filename, $year, $is_datetime = false)
    {
        $filename = dirname(__DIR__) . "/json/{$filename}";
        $this->assertFileExists($filename);

        // 元日のデータが入っているか
        $time = strtotime("{$year}-01-01");
        $key = ($is_datetime) ? $time : date('Y-m-d', $time);
        $data = json_decode(file_get_contents($filename), true);
        $this->assertArrayHasKey($key, $data, $filename);
    }
}
