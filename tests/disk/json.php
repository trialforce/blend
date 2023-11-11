<?php

class JsonTest //extends PHPunit_Framework_Testcase
{
    protected $json = '{"author":"Eduardo Bonfandini","nickName":"Bonfa", "project":"Blend"}';

    public function testEncodeDecode()
    {
        $array['author'] = 'Eduardo Bonfandini';
        $array['project'] = 'Blend';

        $json = \Disk\Json::encode($array);

        //$this->assertEquals($json, $this->json);

        $newArray = \Disk\Json::decode($json, true);

        //$this->assertEquals($array, $newArray);
    }

    public function testDecodeToClass()
    {
        $json = \Disk\Json::decodeToClass($this->json, 'MyUser');

        \Log::dump($json);
    }

}

class MyUser
{
    public $author;
    public $nickName;
    private $project;
}

$jsonTest = new JsonTest();
$jsonTest->testDecodeToClass();