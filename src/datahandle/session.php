<?php

namespace DataHandle;

//verify if session allready start
if (session_status() == PHP_SESSION_NONE && !headers_sent())
{
    session_start();
}

/**
 * Blend class to deal with Server Session
 */
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
        parent::__construct($_SESSION ?? null);
    }

    /**
     * Get one current instance of \DataHandle\Session
     *
     * @return \DataHandle\Session
     */
    public static function getInstance()
    {
        $class = get_called_class();

        if (!isset(self::$dataHandle[$class]))
        {
            self::$dataHandle[$class] = new \DataHandle\Session();
        }

        return self::$dataHandle[$class];
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

        return null;
    }

    /**
     * Destroy session
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function destroy()
    {
        $vars = array_keys(get_object_vars($this));

        //session destroy seens not to be enough
        $_SESSION = array();

        //clear properties
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

        return null;
    }

    /**
     * Return session status
     *
     * http://br2.php.net/session_status
     *
     *
     * @return int
     */
    public static function getStatus()
    {
        return session_status();
    }

    /**
     * Define the session id
     *
     * http://php.net/manual/pt_BR/function.session-id.php
     *
     * @param string $sessionId
     *
     */
    public static function setId($sessionId)
    {
        return session_id($sessionId);
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
     * Close the possibility of writing in session
     * This allow more speed avoiding unnecessary session locking
     *
     * https://www.php.net/manual/en/function.session-write-close.php
     *
     * @return bool
     */
    public static function writeClose()
    {
        return session_write_close();
    }


}
