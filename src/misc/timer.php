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
     * Show diff
     *
     * @return int
     */
    public function __toString()
    {
        return $this->diff() . '';
    }

}
