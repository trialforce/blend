<?php

namespace Db;

/**
 * Manager all the connections with databse
 * Utilizes PHP PDO
 */
class Conn
{

    /**
     * Id conection
     * @var string
     */
    protected $id;

    /**
     * @var \Pdo
     */
    protected \Pdo $pdo;

    /**
     * Array de ConnInfo
     *
     * @var array
     */
    protected static $connInfo;

    /**
     * Array de conexões
     *
     * @var array
     */
    protected static $conn;

    /**
     * Último retorno do pdo
     *
     * @var \PDOStatement|False
     */
    protected static $lastRet;

    /**
     * Constroi uma conexão com o banco
     * @param ConnInfo $info
     */
    public function __construct(\Db\ConnInfo $info)
    {
        $this->id = $info->getId();
        $driverOptions = array();
        $this->pdo = new \Pdo($info->getDsn(), $info->getUsername(), $info->getPassword(), $driverOptions);
        //make pdo throws execption
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        //big timeout to avoid problems
        $this->pdo->setAttribute(\PDO::ATTR_TIMEOUT, 600);
        //persistent conection to
        $this->pdo->setAttribute(\PDO::ATTR_PERSISTENT, TRUE);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return \Pdo
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Return some server info
     *
     * @return string
     */
    public function getServerInfo()
    {
        return $this->pdo->getAttribute(\PDO::ATTR_SERVER_INFO);
    }

    /**
     * Return the last statament to be used
     *
     * @return \PDOStatement|false
     */
    public static function getLastRet()
    {
        return self::$lastRet;
    }

    /**
     * Execute one sql
     *
     * @param string $sql the sql string
     * @param array $args arguments
     * @param string $logId any text you want to add to log to identificate the query
     * @return bool
     */
    public function execute($sql, $args = NULL, $logId = null)
    {
        $timer = new \Misc\Timer();
        $lastSql = \Db\SqlLog::interpolateQuery($sql, $args);

        $ret = $this->pdo->prepare($sql);
        self::$lastRet = $ret;
        $this->makeArgs($args, $ret);

        $ok = $ret->execute();
        unset($ret);

        $diffTime = $timer->stop()->diff();
        \Db\SqlLog::add($lastSql, $ok, $diffTime, $this->id, $logId?: ' ');

        return $ok;
    }

    public function exec($sql)
    {
        return $this->pdo->exec($sql);
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    protected static function makeArgs($args, $ret)
    {
        //needed for PHP 8.0
        if (is_null($args))
        {
            return null;
        }

        //compatibility
        if (!is_array($args))
        {
            $args = array($args);
        }

        if (is_array($args))
        {
            foreach ($args as $arg => $value)
            {
                //if is numeric add one, base 1
                if (is_numeric($arg))
                {
                    $arg = $arg + 1;
                }

                if (!is_bool($value ) && (is_null($value) || (is_string($value) && strlen($value) == 0)) ) //empty
                {
                    $ret->bindValue($arg, NULL, \PDO::PARAM_NULL);
                }
                else if (is_int($value))
                {
                    $ret->bindValue($arg, $value, \PDO::PARAM_INT);
                }
                else
                {
                    $ret->bindValue($arg, $value, \PDO::PARAM_STR);
                }
            }
        }

        return $ret;
    }

    /**
     * Make one query on database
     *
     * @param string $sql
     * @param array $args
     * @param string $class
     * @param string $logId any text you want to add to log to identificate the query
     * @return array of object from desired class
     */
    public function query($sql, $args = array(), $class = NULL, $logId = null)
    {
        $timer = new \Misc\Timer();
        $lastSql = \Db\SqlLog::interpolateQuery($sql, $args);
        $ret = $this->pdo->prepare($sql);
        $this->makeArgs($args, $ret);

        $ret->execute();

        if ($class === 'array')
        {
            $ret->setFetchMode(\PDO::FETCH_ASSOC);
        }
        else if ($class)
        {
            $ret->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $class);
        }
        else
        {
            $ret->setFetchMode(\PDO::FETCH_OBJ); //stdclass case
        }

        $result = array();

        //passes trough all data
        while ($obj = $ret->fetch())
        {
            $result[] = $obj;
        }

        self::$lastRet = $ret;
        unset($ret);

        $diffTime = $timer->stop()->diff();
        //$end = microtime(true);
        //$diffTime = ($end - $start);
        \Db\SqlLog::add($lastSql, count($result), $diffTime, $this->id, $logId);

        return $result;
    }

    /**
     * Make an query and return only the first registry
     *
     * Atttention: even if query return more items, this funcon will only return ONE
     * Attention: the method does not add LIMIT to query, you need to do that by hand
     *
     * @param string $sql
     * @param array $args
     * @param string $class
     * @param string $logId any text you want to add to log to identificate the query
     * @return mixed
     */
    public function findOne($sql, $args = array(), $class = NULL, $logId = null)
    {
        $result = $this->query($sql, $args, $class, $logId);

        if (isset($result[0]))
        {
            return $result[0];
        }

        return NULL;
    }

    /**
     * Get one instance of some database connection
     *
     * @param string $id
     *
     * @return \Db\Conn
     * @throws \Exception
     */
    public static function getInstance($id = 'default')
    {
        $connInfo = self::getConnInfo($id);

        if (!isset(self::$conn[$id]))
        {
            try
            {
                self::$conn[$id] = new \Db\Conn($connInfo);
            }
            catch (\Throwable $e)
            {
                if ($connInfo->getOptional())
                {
                    self::$conn[$id] = new \Db\ConnFake($connInfo);
                }
                else
                {
                    \Log::exception($e);
                    throw new \Exception('O sistema não está conseguindo se conectar ao servidor de banco de dados. Por favor tente novamente mais tarde.');
                }
            }
        }

        return self::$conn[$id];
    }

    /**
     * Add one ConnInfo to current available list
     *
     * @param ConnInfo $info
     * @return void
     */
    public static function addConnInfo(\Db\ConnInfo $info)
    {
        self::$connInfo[$info->getId()] = $info;
    }

    /**
     * Return some connection information
     *
     * @param string $id
     * @return ConnInfo
     * @throws \Exception
     */
    public static function getConnInfo($id = 'default', $throw = TRUE)
    {
        $id = is_null($id) ? 'default' : $id;

        if (isset(self::$connInfo[$id]))
        {
            return self::$connInfo[$id];
        }
        else if ($throw)
        {
            throw new \Exception("Informações da conexão '$id' não encontradas.");
        }

        return null;
    }

}
