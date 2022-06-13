<?php

namespace Db;

/**
 * One conection with one database
 */
class Conn extends \PDO
{

    /**
     * Id conection
     * @var string
     */
    protected $id;

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
     * @var \PDOStatement
     */
    protected static $lastRet;

    /**
     *
     * @var string
     */
    protected static $lastSql;

    /**
     * Log all sql, with result and, time
     *
     * @var array
     */
    protected static $sqlLog;

    /**
     * total sql time
     * @var float
     */
    public static $totalSqlTime;

    /**
     * Constroi uma conexão com o banco
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $driverOptions
     */
    public function __construct(\Db\ConnInfo $info)
    {
        $this->id = $info->getId();

        if ($info->getType() == \Db\ConnInfo::TYPE_MYSQL)
        {
            $driverOptions[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8';
        }
        else
        {
            $driverOptions = array();
        }

        parent::__construct($info->getDsn(), $info->getUsername(), $info->getPassword(), $driverOptions);
        //make pdo throws execption
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        //big timeout to avoid problems
        $this->setAttribute(\PDO::ATTR_TIMEOUT, 600);
        //persistent conection to
        $this->setAttribute(\PDO::ATTR_PERSISTENT, TRUE);
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
     * Return some server info
     *
     * @return string
     */
    public function getServerInfo()
    {
        return $this->getAttribute(\PDO::ATTR_SERVER_INFO);
    }

    /**
     * Add to sql log list
     *
     * @param string $sql
     * @param mixed $result
     * @param int $time
     * @param string $idConn
     */
    protected static function addSqlLog($sql, $result, $time, $idConn = NULL, $logId = NULL)
    {
        //if not loggind does nothing
        if (!(\Log::getLogSql() || \Log::getLogSqlConsole()))
        {
            return;
        }

        //format time
        $time = str_pad($time, 20, '0', STR_PAD_RIGHT);

        $log = new \stdClass();
        $log->create = \Type\DateTime::now()->toDb();
        $log->sql = $sql;
        $log->result = $result;
        $log->time = $time;
        $log->idConn = $idConn;
        $log->logId = $logId;

        self::$sqlLog[] = $log;

        \Log::sql($sql, $time, $idConn, $logId);
    }

    public static function getSqlLog()
    {
        return self::$sqlLog;
    }

    /**
     * Retorna o última prepareStatament a ser utilizado
     *
     * @return type
     */
    public static function getLastRet()
    {
        return self::$lastRet;
    }

    /**
     * Retorna a última sql executada
     * @return string
     */
    public static function getLastSql()
    {
        return self::$lastSql;
    }

    /**
     * Execute one sql
     *
     * @param string $sql the sql string
     * @param array $args arguments
     * @param string $logId any text you want to add to log to identificate the query
     * @return int
     */
    public function execute($sql, $args = NULL, $logId = null)
    {
        $timer = new \Misc\Timer();
        self::$lastSql = \Db\Conn::interpolateQuery($sql, $args);

        $ret = $this->prepare($sql);
        self::$lastRet = $ret;
        $this->makeArgs($args, $ret);

        $ok = $ret->execute();
        unset($ret);

        $diffTime = $timer->stop()->diff();
        self::addSqlLog(self::$lastSql, $ok, $diffTime, $this->id, $logId);
        self::$totalSqlTime += $diffTime;

        return $ok;
    }

    protected static function makeArgs($args, $ret)
    {
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

                if (is_null($value) || strlen($value) == 0) //empty
                {
                    if (\DataHandle\Config::get('forceEmptyString'))
                    {
                        $ret->bindValue($arg, $value . '', \PDO::PARAM_STR);
                    }
                    else
                    {
                        $ret->bindValue($arg, NULL, \PDO::PARAM_NULL);
                    }
                }
                else if (is_int($value) || is_float($value))
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
        self::$lastSql = \Db\Conn::interpolateQuery($sql, $args);

        //adiciona suporte "manual" a campos de final de select
        if (isset($args['orderBy']))
        {
            $sql = str_replace(':orderBy', $args['orderBy'], $sql);
            unset($args['orderBy']);
        }

        if (isset($args['orderWay']))
        {
            $sql = str_replace(':orderWay', $args['orderWay'], $sql);
            unset($args['orderWay']);
        }

        if (isset($args['limit']))
        {
            $sql = str_replace(':limit', $args['limit'], $sql);
            unset($args['limit']);
        }

        if (isset($args['offset']))
        {
            $sql = str_replace(':offset', $args['offset'], $sql);
            unset($args['offset']);
        }

        $ret = $this->prepare($sql);
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
        self::addSqlLog(self::$lastSql, count($result), $diffTime, $this->id, $logId);
        self::$totalSqlTime += $diffTime;

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
     * Replaces any parameter placeholders in a query with the value of that
     * parameter. Useful for debugging. Assumes anonymous parameters from
     * $params are are in the same order as specified in $query
     *
     * @param string $query The sql query with parameter placeholders
     * @param array $params The array of substitution parameters
     * @return string The interpolated query
     */
    public static function interpolateQuery($query, $params)
    {
        if (!is_array($params))
        {
            return $query;
        }

        $keys = array();
        $values = $params;

        # build a regular expression for each parameter
        foreach ($params as $key => $value)
        {
            if (is_string($key))
            {
                $keys[] = '/:' . $key . '/';
            }
            else
            {
                $keys[] = '/[?]/';
            }

            if (is_array($value))
            {
                $values[$key] = implode(',', $value);
            }

            if (is_null($value))
            {
                $values[$key] = 'NULL';
            }
        }

        // Walk the array to see if we can add single-quotes to strings
        //array_walk($values, create_function('&$v, $k', 'if (!is_numeric($v) && $v!="NULL") $v = "\'".$v."\'";'));
        array_walk($values, function(&$v, $k)
        {
            if (!is_numeric($v) && $v != "NULL")
            {
                $v = "'" . $v . "'";
            }
        });

        return preg_replace($keys, $values, $query, 1);
    }

    public static function addConnInfo(\Db\ConnInfo $info)
    {
        self::$connInfo[$info->getId()] = $info;
    }

    /**
     * Obtém uma instancia da conexão
     *
     * @param string $id
     *
     * @return \Db\Conn
     */
    public static function getInstance($id = 'default')
    {
        $connInfo = self::getConnInfo($id);

        if (!isset(self::$conn[$id]))
        {
            try
            {
                self::$conn[$id] = new \Db\Conn($connInfo, $id);
            }
            catch (\Exception $e)
            {
                \Log::exception($e);
                throw new \Exception('O sistema não está conseguindo se conectar ao servidor de banco de dados. Por favor tente novamente mais tarde.');
            }
        }

        return self::$conn[$id];
    }

    /**
     * Return some connection information
     *
     * @param string $id
     * @return ConnInfo
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
    }

}
