<?php

namespace Db;

/**
 * Gerencia informações de conexão com o banco
 */
class ConnInfo
{

    /**
     * Mysql/Mari db server
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
     * Identificador
     *
     * @var string
     */
    protected $id;

    /**
     * Tipo de conexão
     *
     * @var string
     */
    protected $type;

    /**
     * Host, endereço do servidor
     * @var string
     */
    protected $host;

    /**
     * Nome da base de dados
     * @var string
     */
    protected $name;

    /**
     * Nome do usuario de conexão
     * @var string
     */
    protected $username;

    /**
     * Senha
     * @var string
     */
    protected $password;

    /**
     * String de conexão com pdo
     *
     * @var string
     */
    protected $dsn;

    /**
     * Constroi informações de conexão
     *
     * @param string $id
     * @param string $type
     * @param string $host
     * @param string $name
     * @param string $username
     * @param string $password
     * @param string $dsn
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

        //auto adiciona a lista de conexões
        \Db\Conn::addConnInfo($this);
    }

    /**
     * Monta o dsn automaticamente
     */
    protected function makeDsn()
    {
        $this->dsn = $this->type . ':host=' . $this->host . ';dbname=' . $this->name;
    }

    /**
     * Retorna o id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Define o id
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
     * Retorna o nome
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Define o nome
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
     * Retorna o tipo
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Define o tipo
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
     * Retorna o host
     * @return type
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Define o host
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
     * Retorna usuário
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Define usuário
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
     * Retorna senha
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Define senha
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
     * Obtém string de conexão
     *
     * @return string
     */
    public function getDsn()
    {
        return $this->dsn;
    }

    /**
     * Define o dsn (string de conexão)
     * @param type $dsn
     * @return \Db\ConnInfo
     */
    public function setDsn($dsn)
    {
        $this->dsn = $dsn;
        return $this;
    }

}
