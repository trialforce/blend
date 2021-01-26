<?php

namespace Type;

/**
 * Class the emulates the behave of a date using \Type\DateTime
 *
 * It not the ideal, but works.
 *
 */
class Date extends \Type\DateTime
{

    public function __construct($value = NULL, $column = NULL)
    {
        parent::__construct($value, $column);
        $this->setTime(0, 0, 0);
    }

    public function __toString()
    {
        return $this->getValue(self::MASK_DATE_USER);
    }

    public function toHuman()
    {
        return $this->getValue(self::MASK_DATE_USER);
    }

    public function getValue($mask = self::MASK_DATE_USER)
    {
        return parent::getValue($mask);
    }

    public static function get($date = NULL, $column = NULL)
    {
        $obj = new \Type\Date($date);
        return $obj->setTime(0, 0, 0);
    }

    public function getHour()
    {
        $this->hour = 0;
        return parent::getHour();
    }

    public function getMinute()
    {
        $this->minute = 0;
        return parent::getMinute();
    }

    public function getSecond()
    {
        $this->second = 0;
        return parent::getSecond();
    }

    public function toDb()
    {
        $this->setTime(0, 0, 0);
        $value = $this->getValue(self::MASK_DATE_DB);

        //to correct go to database
        if (!$value)
        {
            return NULL;
        }

        return $value;
    }

    /**
     * Return the current date
     *
     * @return \Type\Date
     */
    public static function now()
    {
        $now = new \Type\Date(date(self::MASK_TIMESTAMP_USER));
        return $now->setTime(0, 0, 0);
    }

    /**
     * Create a rando date from 1980
     *
     * @return \Type\DateTime
     */
    public static function createRandom()
    {
        $day = rand(0, 28);
        $month = rand(1, 12);
        $year = 1980 + rand(0, 20);

        $date = new \Type\Date();
        $date->setDay($day)->setMonth($month)->setYear($year);

        return $date;
    }

}
