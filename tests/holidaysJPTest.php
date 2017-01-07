<?php
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

    public function testYearly()
    {
        $input = [
            strtotime('2016-01-01') => 'a',
            strtotime('2017-02-01') => 'b',
            strtotime('2018-03-01') => 'c',
            strtotime('2017-04-01') => 'd',
        ];
        $expected = [
            '2016' => [
                strtotime('2016-01-01') => 'a',
            ],
            '2017' => [
                strtotime('2017-02-01') => 'b',
                strtotime('2017-04-01') => 'd',
            ],
            '2018' => [
                strtotime('2018-03-01') => 'c',
            ],
        ];

        $holidays = new holidaysJP();
        $result = $holidays->convert_yearly_data($input);

        $this->assertEquals($expected, $result);
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

        $this->checkJsonFile('date.json');
        $this->checkJsonFile('datetime.json');
        $this->checkJsonFile(date('Y') . '/date.json');
        $this->checkJsonFile(date('Y') . '/datetime.json');
    }

    /**
     * ファイルが存在し、データが1件よりも多く入っているか
     * @param $filename
     */
    private function checkJsonFile($filename)
    {
        $filename = dirname(__DIR__) . "/json/{$filename}";
        $this->assertFileExists($filename);
        $data = json_decode(file_get_contents($filename), true);
        $this->assertGreaterThan(1, count($data));
    }
}
