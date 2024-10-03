<?php

namespace DataHandle;

use DataHandle\Request;

/**
 * Class to handle with Server Super global
 */
class Server extends DataHandle
{

    const AJAX_REQUEST = 'XMLHttpRequest';
    const REQUEST_METHOD_POST = 'POST';
    const REQUEST = 'GET';

    /**
     * Store "calculate" host
     * @var string
     */
    protected static $host = '';

    /**
     * Construct server super global
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct()
    {
        parent::__construct($_SERVER);
    }

    /**
     * Singleton instance
     * Used for IDE autocomplete
     *
     * @return Server
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * Retorna o browser do cliente
     *
     * @return string
     */
    public function getUserAgent()
    {
        $agent = $this->getVar('HTTP_USER_AGENT');

        // avoid hacking
        if (stripos($agent, '${') !== false)
        {
            $agent = '';
        }

        return new \DataHandle\UserAgent($agent);
    }

    /**
     * Retorna url da requisão
     *
     * @return string
     */
    public function getRequestUri($considerAjax = FALSE)
    {
        $uri = $this->getVar('REQUEST_URI');

        if ($this->isPost() && $this->isAjax() && $considerAjax)
        {
            $uri = Request::getInstance()->mountUri();
        }

        return $uri;
    }

    /**
     * Retorna o método de request
     *
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->getVar('REQUEST_METHOD');
    }

    /**
     * Verifica se o modo de sincronia é post
     * @return boolean
     */
    public function isPost()
    {
        return $this->getRequestMethod() == Server::REQUEST_METHOD_POST;
    }

    /**
     * Return the user ip
     *
     * @return string
     */
    public function getUserIp()
    {
        if (strlen($this->getVar('HTTP_CLIENT_IP').'') > 0)
        {
            $ip = $this->getVar('HTTP_CLIENT_IP');
        }
        elseif (strlen($this->getVar('HTTP_X_FORWARDED_FOR').'') > 0)
        {
            $ip = $this->getVar('HTTP_X_FORWARDED_FOR');
        }
        else
        {
            $ip = $this->getVar('REMOTE_ADDR');
        }

        return $ip;
    }

    /**
     * Get the referer url HTTP_REFERER
     *
     * @return string
     */
    public function getRefererUrl()
    {
        $referer = $this->getVar('HTTP_REFERER');

        // avoid hacking
        if (stripos($referer, '${') !== false)
        {
            return '';
        }

        return $referer;
    }

    /**
     * Verify is an ajax request
     *
     * @return boolean
     */
    public function isAjax()
    {
        return $this->getVar('HTTP_X_REQUESTED_WITH') == Server::AJAX_REQUEST;
    }

    /**
     * Get php auth user
     *
     * @return string
     */
    public function getAuthUser()
    {
        return $this->getVar('PHP_AUTH_USER');
    }

    /**
     * Get php auth password
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->getVar('PHP_AUTH_PW');
    }

    /**
     * Define if php is running from SHELL
     *
     * @return bool
     */
    public function isShell()
    {
        return defined("STDIN");
    }

    /**
     * Returns the server url
     *
     * @return string
     */
    public function getHost()
    {
        //evita de fazer toda a programação novamente
        if (strlen(Server::$host) > 0)
        {
            return Server::$host;
        }

        $host = '';

        if (strlen(Config::get('serverUrl')) > 0)
        {
            $host = Config::get('serverUrl');
        }
        else
        {
            $host = $this->mountHostUrl();
        }

        //stores to avoid recalc
        Server::$host = $host;

        return $host;
    }

    /**
     * Verify if server is running in a https secure connection
     * @return bool
     */
    public function isHttps()
    {
        return mb_strtolower($this->getVar('HTTPS')) == 'on' || $this->getVar('HTTP_X_HTTPS');
    }

    /**
     * Mount host url
     * Only suports one folder
     *
     * @return string
     */
    protected function mountHostUrl()
    {
        $folder = '';
        $requestURI = $this->getVar('REQUEST_URI'); //folder
        $parts = array_values(array_filter(explode('/', $requestURI)));

        if (count($parts) > 0 && Request::get('e') != $parts[0])
        {
            $folder = '/' . $parts[0] . '/';
        }
        else
        {
            //adds slash to avoid final url without
            $folder = '/';
        }

        $prefix = $this->isHttps() ? 'https://' : 'http://';
        $host = $prefix . $this->getVar('HTTP_HOST') . $folder;

        return $host;
    }

    /**
     * Get the domain that of the application
     *
     * @return string
     */
    public function getDomain()
    {
        $prefix = $this->isHttps() ? 'https://' : 'http://';
        $defaultPort = $this->isHttps() ? ':443' : ':80';
        $host = $prefix . $this->getVar('HTTP_HOST') . '/';

        //remove unnecessary port
        $host = str_replace($defaultPort, '', $host);

        return $host;
    }

}
