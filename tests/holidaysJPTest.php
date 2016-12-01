<?php
class holidaysJPTest extends PHPUnit_Framework_TestCase
{
    public function testGenerator()
    {
        $holidays = new holidaysJP(__DIR__ . '/testdata.ics');
        $data = $holidays->get_ical();
        $main_data = $holidays->generate_main_json($data);

        $result = [
            1420038000 => [
                'date' => '2015-01-01',
                'title' => '元日',
            ],
            1458486000 => [
                'date' => '2016-03-21',
                'title' => '春分の日 振替休日',
            ],
            1513954800 => [
                'date' => '2017-12-23',
                'title' => '天皇誕生日',
            ],
        ];
        $this->assertEquals($main_data, $result);
    }
}
