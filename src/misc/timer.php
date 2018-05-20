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
        $this->start = microtime( TRUE );
    }

    /**
     * Stops the timer
     */
    public function stop()
    {
        $this->end = microtime( TRUE );
        return $this;
    }

    /**
     * Get the diference between start and stop
     * @return float
     */
    public function diff()
    {
        return $this->end - $this->start;
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
