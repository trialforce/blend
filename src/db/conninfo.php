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
     * Port of the server
     * @var integer
     */
    protected $port;

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
     * Connection charset
     * @var string
     */
    protected $charset = 'utf8';

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
     * @param int $port port
     * @param string $charset charset
     */
    public function __construct($id, $type, $host, $name, $username, $password = NULL, $port = NULL, $charset = 'utf8mb4')
    {
        $this->id = $id;
        $this->type = $type;
        $this->host = $host;
        $this->name = trim($name);
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->charset = $charset;
        $this->makeDsn();

        //auto add to connection info list
        \Db\Conn::addConnInfo($this);
    }

    /**
     * Mount dsn
     */
    protected function makeDsn()
    {
        $charset = strlen($this->charset) > 0 ? 'charset=' . $this->charset . ';' : '';
        $port = $this->port ? ';port=' . $this->port : '';
        $this->dsn = $this->type . ':' . $charset . 'host=' . $this->host . $port . ';dbname=' . $this->name;
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
     * @param string $name
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
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Define the host
     *
     * @param string $host
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
     * @param string $password
     * @return \Db\ConnInfo
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port)
    {
        $this->port = $port;
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
     * @param string $dsn
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
