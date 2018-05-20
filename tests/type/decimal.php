<?php

class DecimalTest extends PHPunit_Framework_Testcase
{

    public function testTodb()
    {
        $decimal = new \Type\Decimal('1.234,95');
        $this->assertEquals(1234.95, $decimal->toDb());
    }

}