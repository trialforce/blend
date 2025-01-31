<?php

namespace Misc;

/**
 * Simple timer to allow time measurment
 */
class Timer
{

    /**
     * Start time
     *
     * @var float
     */
    private $start;

    /**
     * End time
     * @var float
     */
    private $end;

    /**
     * A global timer acessible in all system
     * @var \Misc\Timer
     */
    private static $global;

    /**
     * Construct and starts the timer
     */
    public function __construct($initialValue = NULL)
    {
        $this->start = $initialValue ?: microtime(TRUE);
    }

    /**
     * Reset timer
     */
    public function reset()
    {
        $this->start = microtime(TRUE);

        return $this;
    }

    /**
     * Stops the timer
     */
    public function stop()
    {
        $this->end = microtime(TRUE);
        return $this;
    }

    /**
     * Get the diference between start and stop in seconds
     *
     * @return float
     */
    public function diff()
    {
        $this->stop();
        return $this->end - $this->start;
    }

    /**
     * Get the diference between start and stop in formated time
     *
     * @return string
     */
    public function diffFormat()
    {
        $diff = $this->diff();
        return gmdate("H:i:s", intval($diff));
    }

    /**
     * Make a stop and diff direct to debug file
     *
     * @param string $message
     */
    public function diffDebug($message)
    {
        \Log::debug($message . ' = ' . $this->stop()->diff());

        return $this;
    }

    /**
     * Show diff
     *
     * @return string
     */
    public function __toString()
    {
        return $this->diff() . '';
    }

    public static function setGlobalTimer(\Misc\Timer $timer)
    {
        self::$global = $timer;
    }

    public static function getGlobalTimer()
    {
        if (!self::$global)
        {
            self::activeGlobalTimerRequest();
        }

        return self::$global;
    }

    /**
     * Active global timer with current timer
     * @return \Misc\Timer
     */
    public static function activeGlobalTimer()
    {
        self::$global = new \Misc\Timer();
        return self::$global;
    }

    /**
     * Active the global timer with the time php script started
     * @return \Misc\Timer
     */
    public static function activeGlobalTimerRequest()
    {
        $request = \DataHandle\Server::getInstance()->getVar('REQUEST_TIME_FLOAT');
        self::$global = new \Misc\Timer($request);
        return self::$global;
    }

    public static function debug($message)
    {
        $global = self::getGlobalTimer()->diffDebug($message);
    }

}
