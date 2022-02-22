<?php

namespace DataHandle;

//verify if session allready start
if (session_status() == PHP_SESSION_NONE && !headers_sent())
{
    $sessionId = session_id();

    try
    {
        session_start();
    }
    catch (\ErrorExpression $e)
    {
        session_regenerate_id();
        session_start();
    }
}

class Session extends DataHandle
{

    /**
     * Session activated
     */
    const STATUS_ACTIVE = 2;

    /**
     * Session disabled
     */
    const STATUS_DISABLED = 0;

    /**
     * Session not started
     */
    const STATUS_NONE = 1;

    /**
     * Construct session super global
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct()
    {
        parent::__construct(isset($_SESSION) ? $_SESSION : null);
    }

    /**
     * Obtém a instancia de um \Session
     *
     * @return \DataHandle\Session
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * Set session var
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @param string $var
     * @param mixed $value
     */
    public function setVar($var, $value)
    {
        parent::setVar($var, $value);
        //it is necessary to do this on session
        $_SESSION[$var] = $value;
    }

    /**
     * Get session var
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @param string $var
     * @return mixed
     */
    public function getVar($var)
    {
        if (isset($_SESSION[$var]))
        {
            return $_SESSION[$var];
        }
    }

    /**
     * Static get an session var
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @param string $var
     * @return mixed
     */
    public static function get($var)
    {
        if (isset($_SESSION[$var]))
        {
            return $_SESSION[$var];
        }
    }

    /**
     * Return session status
     *
     * http://br2.php.net/session_status
     *
     * php > 5.4
     *
     * @return int
     */
    public static function getStatus()
    {
        return session_status();
    }

    /**
     * Return the user session id
     *
     * @return string
     */
    public static function getId()
    {
        return session_id();
    }

    /**
     * Set session name
     *
     * @param string $name
     * @return string last session name
     */
    public static function setName($name)
    {
        return session_name($name);
    }

    /**
     * Return session name
     *
     * @return string
     */
    public static function getName()
    {
        return session_name();
    }

    /**
     * Define the session id
     *
     * http://php.net/manual/pt_BR/function.session-id.php
     *
     * @param string $sessionId
     * @return \DataHandle\Session
     */
    public static function setId($sessionId)
    {
        session_id($sessionId);
        return;
    }

    /**
     * Destroy session
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function destroy()
    {
        $vars = array_keys(get_object_vars($this));

        //session destroy parece não ser o suficiente
        $_SESSION = array();

        //limpa as propriedades
        foreach ($vars as $var)
        {
            if ($var)
            {
                unset($this->$var);
            }
        }

        if (\DataHandle\Session::getStatus() == \DataHandle\Session::STATUS_ACTIVE)
        {
            //session_regenerate_id(true);
            session_destroy();
        }
    }

}
