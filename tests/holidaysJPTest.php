<?php
class holidaysJPTest extends PHPUnit_Framework_TestCase
{
    public function testJson()
    {
        $holidays = new holidaysJP(__DIR__ . '/testdata.ics');
        $data = $holidays->get_ical();
        $main_data = $holidays->generate_main_json($data);

        $expected = [
            1420038000 => '元日',
            1458486000 => '春分の日 振替休日',
            1513954800 => '天皇誕生日',
        ];
        $this->assertEquals($expected, $main_data);
    }

    public function testGenerator()
    {
        $url = 'https://calendar.google.com/calendar/ical/japanese__ja@holiday.calendar.google.com/public/full.ics';
        $holidays = new holidaysJP($url);
        $holidays->generate();

        $dist = dirname(__DIR__) . '/json';

        // date.json
        $file_name = "{$dist}/date.json";
        $this->assertFileExists($file_name);
        $data = json_decode(file_get_contents($file_name));
        $this->assertGreaterThanOrEqual(1, count($data));

        // datetime.json
        $file_name = "{$dist}/datetime.json";
        $this->assertFileExists($file_name);
        $data = json_decode(file_get_contents($file_name));
        $this->assertGreaterThanOrEqual(1, count($data));

        // this year date.json
        $file_name = "{$dist}/" . date('Y') . "/date.json";
        $this->assertFileExists($file_name);
        $data = json_decode(file_get_contents($file_name));
        $this->assertGreaterThanOrEqual(1, count($data));
    }
}
