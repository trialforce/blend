<?php

namespace Misc;

/**
 * Simple timer
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
    public function __construct()
    {
        $this->start = microtime(TRUE);
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
        return $this->end - $this->start;
    }

    /**
     * Get the diference between start and stop in formated time
     *
     * @return type
     */
    public function diffFormat()
    {
        $diff = $this->diff();
        return gmdate("H:i:s", $diff);
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
     * @return int
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
        return self::$global;
    }

    public static function activeGlobalTimer()
    {
        self::$global = new \Misc\Timer();
    }

    public static function debug($message)
    {
        $global = self::getGlobalTimer();

        if (!$global)
        {
            return;
        }

        $global->diffDebug($message);
    }

}
