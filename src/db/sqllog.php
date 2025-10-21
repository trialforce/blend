<?php

namespace Db;

/**
 * Manage sql log
 */
class SqlLog
{
    /**
     * Log all sql, with result and, time
     *
     * @var array
     */
    protected static array $sqlLog = [];

    /**
     * total sql time
     * @var float
     */
    public static float $totalSqlTime = 0.0;

    /**
     *
     * @var string
     */
    protected static $lastSql;

    /**
     * Add to sql log list
     *
     * @param string $sql the sql command
     * @param mixed $result the result
     * @param float $time the time spent in this sql
     * @param string|null $idConn the \Db\ConnInfo id
     * @param string|null $logId the specific id for logging
     */
    public static function add(string $sql, $result, float $time = 0, string|null $idConn = null, string|null $logId = NULL)
    {
        //if not loggind does nothing
        if (\Log::getLogSql() == 0)
        {
            return;
        }

        //format time
        $timeFormated = str_pad($time, 20, '0', STR_PAD_RIGHT);

        $log = new \stdClass();
        $log->create = \Type\DateTime::now()->toDb();
        $log->sql = $sql;
        $log->result = $result;
        $log->time = $timeFormated;
        $log->idConn = $idConn;
        $log->logId = $logId;

        self::$sqlLog[] = $log;
        self::$lastSql = $sql;
        self::$totalSqlTime = self::$totalSqlTime + $time;

        //log to file
        \Log::sql($sql, $time, $idConn, $logId);
    }

    public static function getAll() : array
    {
        return self::$sqlLog;
    }

    /**
     * Return the current sql count
     *
     * @return int
     */
    public static function count() : int
    {
        return count(self::$sqlLog);
    }

    /**
     * Return the total sql time
     * @return float
     */
    public static function getTotalSqlTime(): float
    {
        //return array_sum(array_column(self::$sqlLog, 'time'));
        return self::$totalSqlTime;
    }


    /**
     * Return the last executed sql
     * @return string|null
     */
    public static function getLastSql():string|null
    {
        //return self::$sqlLog[count(self::$sqlLog) - 1]->sql;
        return self::$lastSql;
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
        array_walk($values, function (&$v, $k)
        {
            if (!is_numeric($v) && $v != "NULL")
            {
                $v = "'" . $v . "'";
            }
        });

        return preg_replace($keys, $values, $query, 1);
    }
}