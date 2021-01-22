<?php

ini_set("display_errors", "0"); //desabilita mostrar erros na tela
ini_set("log_errors", "1"); //habilita log de erros
ini_set("error_log", APP_PATH . DS . 'error.log');

use DataHandle\Session;

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

        //retorna erro pelo console
        if (defined('STDIN'))
        {
            echo 'Mensagem do erro: ' . $error['message'] . ' Arquivo: ' . $error['file'] . ' linha: ' . $error['line'] . "\r\n";
        }
        //ou via html no navegador
        else
        {
            echo '<html><head><title>Error</title></head><body>Ops! Algo inesperado aconteceu, mas não se preocupe já avisamos a equipe! Mensagem do erro: ' . $error['message'] . ' Arquivo: ' . $error['file'] . ' linha: ' . $error['line'] . '</body></html>';
        }
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

register_shutdown_function('blend_shutdown');

/**
 * Gerencia logs e debug do framework
 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
 */
class Log
{

    static $debug = true;

    const FOLDER = 'log/';
    const ERROR_FILE = 'error.txt';
    const DEBUG_FILE = 'debug.txt';
    const SQL_FILE = 'sql.txt';

    protected static $logSql = false;
    protected static $logSqlConsole = false;
    protected static $indexData = null;

    /**
     * Determina se deve ou não efetuar o registro dos sqls
     *
     * @param boolean $logSql
     */
    public static function setLogSql($logSql)
    {
        Log::$logSql = $logSql;
    }

    /**
     * Retorna se deve ou não registrar log de sql
     * @return boolean
     */
    public static function getLogSql()
    {
        return Log::$logSql;
    }

    /**
     * Determina se deve ou não retornar ao console o sql executado
     *
     * @param boolean $logSqlConsole
     */
    public static function setLogSqlConsole($logSqlConsole)
    {
        Log::$logSqlConsole = $logSqlConsole;
    }

    /**
     * Retorna se deve ou não retornar ao console o sql executado
     * @return boolean
     */
    public static function getLogSqlConsole()
    {
        return Log::$logSqlConsole;
    }

    public static function setIndexData(\Log\IndexData $data)
    {
        \log::$indexData = $data;
    }

    /**
     * Return the current index data
     * @return \Log\IndexData
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
     * Registra uma exceção no log
     *
     * @param Exception $exception or Exception from php 5 Throwable from php 7
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @return nothing
     */
    public static function exception($exception)
    {
        $devel = \DataHandle\Config::get('devel');

        if ($devel)
        {
            $log = $exception->getCode() . ' - <b>' . $exception->getMessage() . '</b> - ' . $exception->getFile() . ' on line ' . $exception->getLine() . '</br></br>';
            $log .= $exception->getTraceAsString();
            \Log::screen($log);
            return false;
        }

        $mysqlError = \Log::parseMysqlErrors($exception);

        if ($mysqlError)
        {
            return false;
        }

        //don't make any log if it is an UserException
        if ($exception instanceof UserException)
        {
            return false;
        }

        $errorMessage = \Log::generateErrorLog($exception);
        //put log in user file
        Log::put(Log::ERROR_FILE, $errorMessage);

        //put log in default file
        $file = new \Disk\File(APP_PATH . '/error.log');
        $file->append($errorMessage);

        return \Log::sendDevelEmailIfNeeded($errorMessage);
    }

    /**
     * This method verify common mysql erros and improve the message to user
     *
     * @param \Error $exception
     * @return boolean
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
                $exception = \Log::setExceptionMessage($exception, 'Campo \'' . $column . '\' deve ser preenchido!');

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
                    $exception = self::setExceptionMessage($exception, $message);
                    return;
                }
            }
            else
            {
                //$value = $explode[1];
                $exception = self::setExceptionMessage($exception, 'Registro duplicado! ' . $idxName);
            }
        }

        return false;
    }

    /**
     * Send an email to developer
     *
     * @param string $errorMessage the formatted message
     * @return bool
     */
    protected static function sendDevelEmailIfNeeded($errorMessage)
    {
        $develEmail = \DataHandle\Config::get('develEmail');

        if (!$develEmail)
        {
            return null;
        }

        $errorEmail = str_replace("###############################################" . PHP_EOL, '', $errorMessage);
        $serverUrl = \DataHandle\Server::getInstance()->getHost();
        $mail = new Mailer();
        $mail->defineHtmlUft8("Exceção em " . $serverUrl, nl2br($errorEmail), $develEmail);

        return $mail->send();
    }

    /**
     * Generate a full log message based on a exception
     *
     * @param \Exception $exception
     * @return string
     */
    protected static function generateErrorLog($exception)
    {
        $error = '';
        $error .= "###############################################" . PHP_EOL;
        $error .= 'Exception in ' . date('d/m/y G:i:s:u') . ' = ' . $exception->getFile() . ' on line ' . $exception->getLine() . "\n";
        $error .= $exception->getMessage() . PHP_EOL;
        $error .= $exception->getTraceAsString() . PHP_EOL;
        $error .= PHP_EOL . PHP_EOL;
        $error .= '$_REQUEST:' . PHP_EOL;
        $error .= print_r($_REQUEST, TRUE) . PHP_EOL;
        $error .= PHP_EOL;
        $error .= '$_SESSION:' . PHP_EOL;
        $error .= print_r($_SESSION, TRUE) . PHP_EOL;
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
     * @param \Exception $e
     * @param string $message
     * @return \Exception
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
     * Register a error um file
     *
     * @param string $type
     * @param string $message
     * @param int $line
     * @param string $file
     */
    public static function error($type, $message, $line, $file, $errorFile = Log::ERROR_FILE)
    {
        $devel = \DataHandle\Config::get('devel');

        if ($devel)
        {
            \Log::dump(print_r(debug_backtrace(), 1));
            return;
        }

        $error = '';
        $error .= "###############################################" . PHP_EOL;
        $error .= 'Error in ' . date('d/m/y G:i:s:u') . ' = ' . $file . ' on line ' . $line . "\n";
        $error .= $type . ' - ' . $message . PHP_EOL;

        //controls especial js erro type, used in API
        if (strtolower($type) != 'js')
        {
            $error .= print_r(debug_backtrace(), 1) . PHP_EOL;
            $error .= '$_POST' . "\n" . print_r($_POST, 1) . PHP_EOL;
            $error .= '$_GET' . "\n" . print_r($_GET, 1) . PHP_EOL;
        }

        $error .= '$_SERVER' . "\n" . print_r($_SERVER, 1) . PHP_EOL;
        $error .= '$_SESSION' . "\n" . print_r($_SESSION, 1) . PHP_EOL;

        Log::put($errorFile, $error);
        $develEmail = \DataHandle\Config::get('develEmail');

        if ($develEmail)
        {
            $serverUrl = \DataHandle\Server::getInstance()->getHost();
            $mail = new Mailer();
            $mail->defineHtmlUft8($type . " in " . $serverUrl, nl2br($error), $develEmail);
            return $mail->send();
        }
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
    public static function sql($sql)
    {
        if (Log::getLogSql())
        {
            Log::logInFile(LOG::SQL_FILE, $sql);
        }

        if (Log::getLogSqlConsole())
        {
            if (stripos($sql, 'ERROR:') === 0)
            {
                \Console::error($sql);
            }
            else
            {
                \Console::log($sql);
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

        $userFolder = Session::get('user') ? Session::get('user') . DS : '';
        \Disk\File::getFromStorage($userFolder . 'log' . DS . $relativeFilePath)->append($message);
    }

    /**
     * Obtem url para os logs
     *
     * @param string $relativePath
     * @return string
     */
    public static function getLogUrl($relativePath)
    {
        $user = Session::get('user');
        return \Server::getInstance()->getHost() . '/module/' . APP_MODULE . '/' . Log::FOLDER . '/' . $user . '/' . $relativePath;
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

        $content = '<pre class="var-dump"><a href="#" onclick="$(this).parent().remove(); return false;">Fechar </a>' . ob_get_contents() . '</pre>';
        $content = \View\Script::treatStringToJs($content);
        ob_get_clean();

        \App::addJs("$('body').prepend(`{$content}`)");
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

    public static function screen($var = null)
    {
        $vars = func_get_args();

        ob_start();

        foreach ($vars as $var)
        {
            echo($var);
        }

        $content = '<pre class="var-dump">
<a href="#" onclick="$(this).parent().remove(); return false;">Fechar (X)</a>
' . ob_get_contents() . '
</pre>';

        $content = \View\Script::treatStringToJs($content);
        ob_get_clean();

        \App::addJs("$(body).prepend('{$content}')");
    }

    /**
     * Log memory information
     *
     * @param string $message
     */
    public static function memory($message = NULL)
    {
        $memoryUsage = \Disk\File::formatBytes(memory_get_usage(TRUE));
        $memoryPeak = \Disk\File::formatBytes(memory_get_peak_usage(TRUE));
        $message = $message ? $message . ' - ' : '';
        \Log::debug($message . $memoryUsage . '/' . $memoryPeak);
    }

}
