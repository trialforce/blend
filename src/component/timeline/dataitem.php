<?php

namespace Component\Timeline;

/**
 * Timeline item interface
 */
interface DataItem
{
    public function getTimelineDateTime();

    public function getTimelineIcon();

    public function getTimelineTitle();

    public function getTimelineContent();

    public function getTimelineLink();

    public function getTimelineColor();

}