<?php

//FIXME isso não irá funcionar corretamente para base multiplas
$conn = \Db\Conn::getConnInfo();

if ( $conn->getType() == \Db\ConnInfo::TYPE_MYSQL )
{
    require_once 'mysqlcatalog.php';
}
else if ( $conn->getType() == \Db\ConnInfo::TYPE_POSTGRES )
{
    require_once 'pgsqlcatalog.php';
}
else if ( $conn->getType() == \Db\ConnInfo::TYPE_MSSQL )
{
    require_once 'mssqlcatalog.php';
}