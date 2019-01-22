<?php

namespace Db;

/**
 * Manage databse connection information
 */
class ConnInfo
{

    /**
     * Mysql/Maria db server
     */
    const TYPE_MYSQL = 'mysql';

    /**
     * MSSql server
     */
    const TYPE_MSSQL = 'dblib';

    /**
     * Postgress server
     */
    const TYPE_POSTGRES = 'pgsql';

    /**
     * Firebird server
     */
    const TYPE_FIREBIRD = 'firebird';

    /**
     * Unique identificator
     *
     * @var string
     */
    protected $id;

    /**
     * Connection type
     *
     * @var string
     */
    protected $type;

    /**
     * Host, address of the server
     * @var string
     */
    protected $host;

    /**
     * Database name
     * @var string
     */
    protected $name;

    /**
     * Connection username
     * @var string
     */
    protected $username;

    /**
     * Conncetion password
     * @var string
     */
    protected $password;

    /**
     * DNS String for PDFO
     *
     * @var string
     */
    protected $dsn;

    /**
     * Construct a connection info
     *
     * @param string $id unique identificator
     * @param string $type connection type, use constants
     * @param string $host server address
     * @param string $name database name
     * @param string $username username
     * @param string $password password
     * @param string $dsn dsn for pdo
     */
    public function __construct($id, $type, $host, $name, $username, $password = NULL, $dsn = NULL)
    {
        $this->id = $id;
        $this->type = $type;
        $this->host = $host;
        $this->name = trim($name);
        $this->username = $username;
        $this->password = $password;
        $this->dsn = $dsn;

        if (is_null($this->dsn))
        {
            $this->makeDsn();
        }

        //auto add to connection info list
        \Db\Conn::addConnInfo($this);
    }

    /**
     * Mount dsn
     */
    protected function makeDsn()
    {
        $this->dsn = $this->type . ':host=' . $this->host . ';dbname=' . $this->name;
    }

    /**
     * Return the o unique identificator
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Define the id
     *
     * @param string $id
     * @return \Db\ConnInfo
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Return the name of database
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Define the name of databse
     *
     * @param stirng $name
     * @return \Db\ConnInfo
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Return the connection type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Define the connection type
     *
     * @param string $type
     * @return \Db\ConnInfo
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Return server address/host
     * @return type
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Define the host
     *
     * @param type $host
     * @return \Db\ConnInfo
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Return the connection user name
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Define the connection user name
     *
     * @param string $username
     * @return \Db\ConnInfo
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Return the password
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Define the password
     *
     * @param type $password
     * @return \Db\ConnInfo
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Return the DSN connection string
     *
     * @return string
     */
    public function getDsn()
    {
        return $this->dsn;
    }

    /**
     * Define the DSN connection string
     *
     * @param type $dsn
     * @return \Db\ConnInfo
     */
    public function setDsn($dsn)
    {
        $this->dsn = $dsn;
        return $this;
    }

    /**
     * Return the catalog class for this connection info
     *
     * @return string
     */
    public function getCatalogClass()
    {
        if ($this->getType() == \Db\ConnInfo::TYPE_MYSQL)
        {
            return '\Db\Catalog\Mysql';
        }
        else if ($this->getType() == \Db\ConnInfo::TYPE_POSTGRES)
        {
            return '\Db\Catalog\Pgsql';
        }
        else if ($this->getType() == \Db\ConnInfo::TYPE_MSSQL)
        {
            return '\Db\Catalog\Mssql';
        }

        return '\Db\Catalog\Mysql';
    }

}
