<?php

class TimeTest extends PHPunit_Framework_Testcase
{

    public function testParse()
    {
        $time = new \Type\Time('06:08:56');

        $this->assertEquals($time->getHour(), '6');
        $this->assertEquals($time->getMinute(), '8');
        $this->assertEquals($time->getSecond(), '56');
    }

    public function testParseSimple()
    {
        $time = new \Type\Time('6:8:56');

        $this->assertEquals($time->getHour(), '6');
        $this->assertEquals($time->getMinute(), '8');
        $this->assertEquals($time->getSecond(), '56');
    }

    public function testParseWithMileseconds()
    {
        $time = new \Type\Time('15:22:56.123456');

        $this->assertEquals($time->getHour(), '15');
        $this->assertEquals($time->getMinute(), '22');
        $this->assertEquals($time->getSecond(), '56');
        $this->assertEquals($time->getMilesecond(), '123456');
    }

    public function testHuman()
    {
        $time = new \Type\Time('15:22:56.123456');

        $this->assertEquals($time->toHuman(), '15h 22m');
    }

}