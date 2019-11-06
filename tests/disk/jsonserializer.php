<?php

class JsonSerializerTest extends PHPunit_Framework_Testcase
{

    public function testEncode()
    {
        $columns['codigo'] = new \Db\Column\Column('Código', 'id', \Db\Column\Column::TYPE_INTEGER, NULL, FALSE, TRUE, NULL, \Db\Column\Column::EXTRA_AUTO_INCREMENT);
        $columns['descricao'] = new \Db\Column\Column('Descrição', 'descricao', \Db\Column\Column::TYPE_VARCHAR, 255, FALSE, FALSE, NULL);

        $json = \Disk\JsonSerializer::encode($columns);

        $this->assertEquals(strlen($json) > 0, TRUE);
    }

    public function testDecode()
    {
        $columnId = \Disk\JsonSerializer::decodeFromFile('columnid.json');

        //$this->assertEquals($columnId->getLabel(), 'Código');
        //$this->assertEquals($columnId->getType(), \Db\Column\Column::TYPE_INTEGER);
    }

}