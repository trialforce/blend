<?php

use DataHandle\Session;

ini_set("display_errors", "0");
ini_set("log_errors", "1");
ini_set("error_log", APP_PATH .'/storage/error.log');

register_shutdown_function('blend_shutdown');

/**
 * Blend shutdown function
 */
function blend_shutdown()
{
    $error = error_get_last();
    $friendlyType = isset($error['type']) ? FriendlyErrorType($error['type']) : 'E_UNKNOWN';

    if (isset($error) && is_array($error))
    {
        //avoid send buggy error on some instalations
        if (stripos($error['message'], 'ps_files_cleanup_dir') > 0)
        {
            return;
        }
        //avoid freegeoip warnigns, it has a lot
        else if (stripos($error['message'], 'freegeoip') > 0)
        {
            return;
        }
        //avoid mpdf warnigns, it has a lot
        else if (stripos($error['file'], 'mpdf') > 0)
        {
            return;
        }
        //avoid outdated phpmailer in php 7.2
        else if (stripos($error['file'], 'phpmailer') > 0)
        {
            return;
        }
        //avoid weird xmlwriter error
        else if (stripos($error['message'], 'XMLWriter') === 0)
        {
            return;
        }
        //avoid DOMDocument wargings of malformed html tag
        else if (stripos($error['message'], 'DOMDocument') === 0)
        {
            return;
        }

        Log::error($friendlyType, $error['message'], $error['line'], $error['file']);

        //don't inform user of notices e warnings
        if ($error['type'] == E_NOTICE || $error['type'] == E_USER_NOTICE || $error['type'] == E_USER_WARNING || $error['type'] == E_WARNING || $error['type'] == E_CORE_WARNING || $error['type'] == E_DEPRECATED)
        {
            return;
        }

        //return error throgh shell
        if (defined('STDIN'))
        {
            echo 'Mensagem do erro: ' . $error['message'] . ' Arquivo: ' . $error['file'] . ' linha: ' . $error['line'] . "\r\n";
        }
        //ou trough browser
        else
        {
            $msg = '';

            if (\DataHandle\Config::get('emailTest'))
            {
                $msg = '<br/>Erro:<br/>' . nl2br($error['message']) . ' Arquivo: ' . $error['file'] . ' linha: ' . $error['line'];
            }

            echo '<html><head><title>Error</title></head><body>Ops! Algo inesperado aconteceu, mas não se preocupe já avisamos a equipe!' . $msg . '</body></html>';
        }
    }

    if (\Log::getLogSql() > 0)
    {
        \Log::sql('TOTAL SQL TIME ' . \Db\Conn::$totalSqlTime . ' seg');
    }
}

function FriendlyErrorType($type)
{
    switch ($type)
    {
        case E_ERROR: // 1 //
            return 'E_ERROR';
        case E_WARNING: // 2 //
            return 'E_WARNING';
        case E_PARSE: // 4 //
            return 'E_PARSE';
        case E_NOTICE: // 8 //
            return 'E_NOTICE';
        case E_CORE_ERROR: // 16 //
            return 'E_CORE_ERROR';
        case E_CORE_WARNING: // 32 //
            return 'E_CORE_WARNING';
        case E_COMPILE_ERROR: // 64 //
            return 'E_COMPILE_ERROR';
        case E_COMPILE_WARNING: // 128 //
            return 'E_COMPILE_WARNING';
        case E_USER_ERROR: // 256 //
            return 'E_USER_ERROR';
        case E_USER_WARNING: // 512 //
            return 'E_USER_WARNING';
        case E_USER_NOTICE: // 1024 //
            return 'E_USER_NOTICE';
        case E_STRICT: // 2048 //
            return 'E_STRICT';
        case E_RECOVERABLE_ERROR: // 4096 //
            return 'E_RECOVERABLE_ERROR';
        case E_DEPRECATED: // 8192 //
            return 'E_DEPRECATED';
        case E_USER_DEPRECATED: // 16384 //
            return 'E_USER_DEPRECATED';
    }
    return "";
}

/**
 * Logs and debugs of blend
 */
class Log
{

    static $debug = true;

    const FOLDER = 'log/';
    const ERROR_FILE = 'error_php.log';
    const DEBUG_FILE = 'debug.log';
    const SQL_FILE = 'sql.log';

    const LOG_SQL_OFF = 0;
    const LOG_SQL_FILE = 1;
    const LOG_SQL_CONSOLE = 2;
    const LOG_SQL_OBJ = 3;

    protected static $logSql = false;
    protected static $indexData = null;

    /**
     * Active SQL LOG
     *
     * @param boolean $logSql
     */
    public static function setLogSql($logSql)
    {
        Log::$logSql = $logSql;

        if ($logSql)
        {
            $requestUri = \DataHandle\Server::getInstance()->getRequestUri(true);
            \Log::sql('---------------------------------- ' . $requestUri);
        }
    }

    /**
     * Retorna se deve ou não registrar log de sql
     * @return boolean
     */
    public static function getLogSql()
    {
        return Log::$logSql;
    }

    public static function setIndexData(\Log\IndexData $data)
    {
        \log::$indexData = $data;
    }

    /**
     * Return the current index data
     *
     * @return  \Log\IndexData|null
     */
    public static function getIndexData()
    {
        if (!\Log::$indexData instanceof \Log\IndexData)
        {
            \Log::$indexData = new \Log\IndexData();
        }

        return \Log::$indexData;
    }

    /**
     * Register a log of an \Exception
     *
     * @param $exception
     * @return bool|null
     * @throws \PHPMailer\PHPMailer\Exception|ReflectionException
     */
    public static function exception($exception)
    {
        $devel = \DataHandle\Config::get('devel');

        $mysqlError = \Log::parseMysqlErrors($exception);

        if ($mysqlError)
        {
            return false;
        }
        //don't make any log if it is an UserException
        else if ($exception instanceof \UserException)
        {
            return false;
        }
        else if ($devel)
        {
            $log = $exception->getCode() . ' - <b>' . $exception->getMessage() . '</b> - ' . $exception->getFile() . ' on line ' . $exception->getLine() . '</br></br>';
            $log .= $exception->getTraceAsString();
            echo $log;
        }

        $errorMessage = \Log::generateErrorLog($exception);
        //put log in user file
        Log::put(Log::ERROR_FILE, $errorMessage);

        //put log in default file
        $file = new \Disk\File(init_get('error_log'));
        $file->append($errorMessage);

        return \Log::sendDevelEmailIfNeeded('Exceção', $exception->getMessage(), $errorMessage);
    }

    /**
     * This method verify common mysql erros and improve the message to user
     *
     * @param \Error $exception
     * @return boolean
     * @throws ReflectionException
     */
    protected static function parseMysqlErrors($exception)
    {
        $message = $exception->getMessage();
        $explode = explode('\'', $message);

        //SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'criacao' cannot be null
        //required column
        if (stripos($message, ': 1048'))
        {
            if (isset($explode[1]))
            {
                $column = $explode[1];
                \Log::setExceptionMessage($exception, 'Campo \'' . $column . '\' deve ser preenchido!');

                return true;
            }
        }

        //http://dev.mysql.com/doc/refman/5.5/en/error-messages-server.html
        //SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '6-91155908015' for key 'index_cliente_cpf_duplicado'
        //duplication
        else if (stripos($message, ': 1062'))
        {
            //var_dump($message, $explode);
            $idxName = $explode[3];

            if (isset($explode[3]) && $explode[3])
            {
                $message = \Log::getIndexData()->getIndex($idxName);

                if ($message)
                {
                    self::setExceptionMessage($exception, $message);
                    return false;
                }
            }
            else
            {
                self::setExceptionMessage($exception, 'Registro duplicado! ' . $idxName);
            }
        }

        return false;
    }

    /**
     * Send an email to developer
     *
     * @param $type
     * @param $message
     * @param $backTrace
     * @return bool
     * @throws \PHPMailer\PHPMailer\Exception
     */
    protected static function sendDevelEmailIfNeeded($type, $message, $backTrace)
    {
        //only send mail if server has phpmailer
        if (!class_exists('\PHPMailer\PHPMailer\PHPMailer'))
        {
            return false;
        }

        $develEmail = \DataHandle\Config::get('develEmail');

        if (!$develEmail)
        {
            return false;
        }

        $file = \Disk\File::getFromStorage('error-email.log');

        try
        {
            $vector = \Disk\Json::decodeFromFile($file);
        }
        catch (\Exception)
        {
            //create a simple vector if file not exists or is empty
            $vector = [];
        }

        //if email was in one of 5 last emails, don't send the message again
        if (in_array($message, $vector))
        {
            return false;
        }

        $serverUrl = \DataHandle\Server::getInstance()->getHost();
        $errorEmail = str_replace("###############################################" . PHP_EOL, '', $backTrace);

        $mail = new Mailer();
        $mail->defineHtmlUft8($type . ' ' . $message . ' em ' . $serverUrl, nl2br($errorEmail), $develEmail);
        $okay = $mail->send();

        //add message to list and limit to 5
        array_unshift($vector, $message);
        $vector = array_slice($vector, 0, 5);

        $file->save(\Disk\Json::encode($vector));

        return $okay;
    }

    /**
     * Generate a full log message based on a exception
     *
     * @param \Exception $exception
     * @return string
     */
    protected static function generateErrorLog($exception)
    {
        $error = "###############################################" . PHP_EOL;
        $error .= 'Exception in ' . date('d/m/y G:i:s:u') . ' = ' . $exception->getFile() . ' on line ' . $exception->getLine() . "\n";
        $error .= $exception->getMessage() . PHP_EOL;
        $error .= $exception->getTraceAsString() . PHP_EOL;
        $error .= PHP_EOL . PHP_EOL;
        $error .= '$_REQUEST:' . PHP_EOL;
        $error .= print_r($_REQUEST, TRUE) . PHP_EOL;
        $error .= PHP_EOL;

        if (isset($_SESSION))
        {
            $error .= '$_SESSION:' . PHP_EOL;
            $error .= print_r($_SESSION, TRUE) . PHP_EOL;
        }

        $error .= '$_SERVER:' . PHP_EOL;
        $error .= print_r($_SERVER, TRUE) . PHP_EOL . "\r\n";

        if ($exception instanceof \PDOException)
        {
            Log::sql('ERROR:' . \Db\Conn::getLastSql());
            $error .= 'SQL ERROR:' . \Db\Conn::getLastSql();
        }

        return $error;
    }

    /**
     * Define the message of an exception
     *
     * @param $exception
     * @param string $message
     * @return Exception
     * @throws ReflectionException
     */
    public static function setExceptionMessage($exception, $message)
    {
        if (class_exists('ReflectionClass'))
        {
            $myClassReflection = new ReflectionClass(get_class($exception));
            $secret = $myClassReflection->getProperty('message');
            $secret->setAccessible(true);

            $secret->setValue($exception, $message);
        }

        return $exception;
    }

    /**
     * Register an error in file (an email is sended if needed)
     *
     * @param string $type tipo (warning, notice, error, etc)
     * @param string $message error message
     * @param int $line line of the error
     * @param string $file file of the error
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function error($type, $message, $line, $file, $errorFile = Log::ERROR_FILE)
    {
        $devel = \DataHandle\Config::get('devel');

        if ($devel)
        {
            \Log::dump(print_r(debug_backtrace(), 1));
            return;
        }

        $error = "###############################################" . PHP_EOL;
        $error .= 'Error ' . $type . ' - ' . $message . ' in ' . $file . ' on line ' . $line . PHP_EOL;

        //controls especial js erro type, used in API
        if (strtolower($type) != 'js')
        {
            $error .= print_r(debug_backtrace(), 1) . PHP_EOL;
            $error .= '$_POST' . "\n" . print_r($_POST, 1) . PHP_EOL;
            $error .= '$_GET' . "\n" . print_r($_GET, 1) . PHP_EOL;
        }

        $error .= '$_SERVER' . "\n" . print_r($_SERVER, 1) . PHP_EOL;

        //avoid error when session is not started
        if (isset($_SESSION))
        {
            $error .= '$_SESSION' . "\n" . print_r($_SESSION, 1) . PHP_EOL;
        }

        \Log::put($errorFile, $error);
        \Log::sendDevelEmailIfNeeded($type, $message, $error);
    }

    /**
     * Faz debug de um arquivo em disco
     *
     * @param string $msg
     */
    public static function debug($msg = NULL)
    {
        $result = "";
        $vars = func_get_args();

        if (is_array($vars))
        {
            foreach ($vars as $var)
            {
                if (is_object($var) || is_array($var))
                {
                    $var = print_r($var, 1);
                }

                $result = $result . " " . $var;
            }
        }
        else
        {
            if (is_object($msg) || is_array($msg))
            {
                $result = print_r($msg, 1);
            }
        }

        if (self::$debug)
        {
            Log::put(LOG::DEBUG_FILE, $result);
        }
    }

    /**
     * Register backtrace on log::debug
     */
    public static function debugBackTrace()
    {
        \Log::debug(debug_backtrace());
    }

    /**
     * Efetua log de um sql
     *
     * @param string $sql
     */
    public static function sql($sql, $time = null, $idConn = null, $logId = null)
    {
        $sql = str_replace(array("\r\n", "\r", "\n"), ' ', $sql);

        if ($time && $idConn && $logId)
        {
            $string = $idConn . ' - ' . $time . ' - ' . $logId . ' - ' . $sql;
        }
        else
        {
            $string = $sql;
        }

        $logSql = Log::getLogSql() ;

        if ( $logSql> 0 )
        {
            if ($logSql == self::LOG_SQL_FILE)
            {
                Log::logInFile(LOG::SQL_FILE, $string);
            }
            else if ($logSql == self::LOG_SQL_CONSOLE)
            {
                if (stripos($sql, 'ERROR:') === 0)
                {
                    \Console::error($sql);
                }
                else
                {
                    \Console::log($string);
                }
            }
        }
    }

    /**
     * Coloca uma mensagem no log de acordo com a configuração
     *
     * @param string $relativeFilePath
     * @param string $msg
     * @param boolean $addHeader
     */
    public static function put($relativeFilePath, $msg, $addHeader = true)
    {
        Log::logInFile($relativeFilePath, $msg, $addHeader);
    }

    /**
     * Converte uma mensagem para ser usada no log
     *
     * @param string $msg
     * @return string
     */
    public static function convertMessage($msg)
    {
        //caso seja um objeto converte para string para debug
        if (is_object($msg) || is_array($msg))
        {
            $msg = print_r($msg, 1);
        }

        return $msg;
    }

    /**
     * Loga em um arquivo
     *
     * @param string $relativeFilePath
     * @param string $msg
     * @param boolean $addHeader
     */
    public static function logInFile($relativeFilePath, $msg, $addHeader = true)
    {
        $msg = Log::convertMessage($msg);

        if ($addHeader)
        {

            $message = Log::now() . ' - ' . $msg . PHP_EOL;
        }
        else
        {
            $message = $msg;
        }

        $userFolder = Session::get('user') ? Session::get('user') .'/'  : '';
        \Disk\File::getFromStorage($userFolder . 'log/' . $relativeFilePath)->append($message);
    }

    /**
     * Retorna o timestamp atual com milesegundos
     *
     * @return string
     */
    public static function now($mili = TRUE)
    {
        $date = date("d/m/Y H:i:s");

        if ($mili)
        {
            $microtime = explode(' ', microtime());
            $date .= ':' . (int) round($microtime[0] * 1000, 3);
            $date = str_pad($date, 23);
        }

        return $date;
    }

    /**
     * Faz um var-dump pré-formatado
     * @param mixed $var
     */
    public static function dump($var = null)
    {
        $vars = func_get_args();

        ob_start();

        foreach ($vars as $var)
        {
            var_dump($var);
        }

        self::dumpToScreen(ob_get_contents());

        ob_get_clean();
    }

    /**
     * Debug backtrace in this exact moment
     */
    public static function dumpBackTrace($var = null)
    {
        ob_start();
        debug_print_backtrace();
        $ob = ob_get_clean();
        \Log::dump($var . "\r\n" . $ob);
    }

    private static function dumpToScreen($content)
    {
        $content = \View\Script::treatStringToJs("<p>".$content."</p>");

        $divVarDump = \View\Script::treatStringToJs('<pre class="var-dump"><a href="#" onclick="$(this).parent().remove(); return false;">Fechar </a></pre>');

        \App::addJs(
            'if ($("body > pre.var-dump").length == 0) 
            { 
                $("body").prepend(`'.$divVarDump.'`); 
            } 
            $("body > pre.var-dump").append(`'.$content.'`);'
        );
    }
}
