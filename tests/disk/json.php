<?php

class JsonTest extends PHPunit_Framework_Testcase
{

    public function testEncodeDecode()
    {
        $jsonOriginal = '{"author":"Eduardo Bonfandini","project":"Blend"}';

        $array['author'] = 'Eduardo Bonfandini';
        $array['project'] = 'Blend';

        $json = \Disk\Json::encode($array);

        $this->assertEquals($json, $jsonOriginal);

        $newArray = \Disk\Json::decode($json, true);

        $this->assertEquals($array, $newArray);
    }

}