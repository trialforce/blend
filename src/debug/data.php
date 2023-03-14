<?php

namespace Debug;

/*
 * Create the debug data
 * Example: https://www.treblle.com/
 *
 */
class Data
{
    /**
     * @var \Type\DateTime
     */
    public $dateTime;
    public $statusCode;
    public $requestMethod;
    public $requestUri;
    public $host;
    public $referer;
    public $ip;
    public $userAgent;
    public $browser;
    public $version;
    public $platform;
    public $mobile;
    public $botService;
    public $authUser;
    public $authPassword;
    public $memoryLimit;
    public $memoryAllocated;
    public $memoryUsed;
    public $timeServer;
    public $timeSql;
    public $timePhp;
    public $sqlCount;
    public $sqlCountRepeated=0;
    public $sqlCountSlow=0;
    public $slowQueryTime = 0.1;
    public $input = '';
    public $inputSize = 0;
    public $output = 0;
    public $outputLength = 0;
    public $get = [];
    public $post = [];
    public $session = [];
    public $cookie = [];
    public $headers = [];
    public $sqlLog = [];
    public $error;
    public $extra = [];

    /**
     * Extra informartion
     * @var array
     */
    private static $extras = [];

    public static function start($obStart = false)
    {
        \Log::setLogSql(\Log::LOG_SQL_OBJ);
        \Misc\Timer::activeGlobalTimer();

        if ($obStart)
        {
            ob_start();
        }

        register_shutdown_function('\Debug\Data::shutdown');
    }

    public static function shutdown()
    {
        $debugData = new \Debug\Data(true);

        \Disk\File::getFromStorage('debugapi.json')->save($debugData->toJson());
    }

    /**
     * Add an extra property to debug data
     *
     * @param $property property
     * @param $value value
     * @return void
     */
    public static function addExtra($property, $value)
    {
        self::$extras[$property] = $value;
    }

    /**
     * Return the extra content
     *
     * @return array
     */
    public static function getExtras()
    {
        return self::$extras;
    }

    public function __construct($complete = false)
    {
        $serverTime = \Misc\Timer::getGlobalTimer()->stop()->diff();

        $sqlTime = \Db\Conn::$totalSqlTime;

        $server = \DataHandle\Server::getInstance();
        $userAgent = $server->getUserAgent();

        $this->requestMethod = $server->getRequestMethod();
        $this->host = $server->getHost();
        $this->referer = $server->getRefererUrl();
        $this->requestUri = \DataHandle\Server::getInstance()->getRequestUri(true);

        if ($server->get('REDIRECT_URL'))
        {
            $this->requestUri =$server->get('REDIRECT_URL');
        }

        $this->dateTime = \Type\DateTime::now();

        $this->memoryLimit = ini_get('memory_limit').'';
        $this->memoryAllocated = \Type\Bytes::get(memory_get_usage(true)).'';
        $this->memoryUsed = \Type\Bytes::get(memory_get_peak_usage(true)).'';

        $this->timeServer = \Type\Decimal::get($serverTime)->setDecimals(4);
        $this->timeSql = \Type\Decimal::get($sqlTime)->setDecimals(4);
        $this->timePhp = \Type\Decimal::get($serverTime - $sqlTime)->setDecimals(4);
        $this->sqlCount = count(\Db\Conn::getSqlLog());

        $this->userAgent = $userAgent->getUserAgent();
        $this->browser = $userAgent->getCompleteName();
        $this->platform = $userAgent->getPlatform();
        $this->version = $userAgent->getSimpleVersion();
        $this->mobile = $userAgent->isMobile();
        $this->botService = $userAgent->isBotOrService();

        //todo get location
        $this->ip = $server->getUserIp();

        if ($complete)
        {
            $this->complete();
        }
    }

    public function getSqlLog()
    {
        if (!$this->sqlLog)
        {
            $this->sqlLog = \Db\Conn::getSqlLog();
            $logIds = [];

            foreach ($this->sqlLog as $idx => $log)
            {
                $log->repeated = false;
                $log->slowQuery = false;

                if (isset($logIds[$log->logId]))
                {
                    $log->repeated = true;
                    $this->sqlCountRepeated++;
                }

                if ($log->time > $this->slowQueryTime)
                {
                    $log->slowQuery = true;
                    $this->sqlCountSlow++;
                }

                $logIds[$log->logId] = $log->logId;
            }
        }

        return $this->sqlLog;
    }

    public function complete()
    {
        $server = \DataHandle\Server::getInstance();

        $this->statusCode = http_response_code();
        $this->headers = getallheaders();

        if (isset($this->headers['Cookie']))
        {
            unset($this->headers['Cookie']);
        }

        $this->authPassword = $server->getAuthPassword();
        $this->authUser = $server->getAuthUser();

        $this->input = file_get_contents('php://input');
        $this->inputSize = mb_strlen($this->input);
        $this->get = $_GET;
        $this->post = $_POST;
        $this->session = [];

        if (session_status() == PHP_SESSION_ACTIVE )
        {
            $this->session = $_SESSION;
        }

        $this->cookie = $_COOKIE;

        $this->getSqlLog();
        $this->error();
        $this->obContent();

        $this->extra = self::getExtras();
    }

    public function obContent()
    {
        $this->outputLength = ob_get_length();

        if ($this->outputLength>0)
        {
            $this->output = ob_get_contents();
            ob_end_flush();
        }
    }

    public function error()
    {
        $error = error_get_last();

        if (isset($error['type']))
        {
            $error['type'] = FriendlyErrorType($error['type']) ?: 'E_UNKNOWN';
            $this->error = $error;
        }
    }


    public function toJson()
    {
        return \Disk\Json::encode($this);
    }
}