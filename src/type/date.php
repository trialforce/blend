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

    public function __toString()
    {
        return $this->getValue(self::MASK_DATE_USER);
    }

    public function getValue($mask = self::MASK_DATE_USER)
    {
        return parent::getValue($mask);
    }

    public static function get($date)
    {
        return new \Type\Date($date);
    }

    public function toDb()
    {
        $value = $this->getValue(self::MASK_DATE_DB);

        //to correct go to database
        if (!$value)
        {
            return NULL;
        }

        return $value;
    }

    public static function now()
    {
        $now = new \Type\Date(date(self::MASK_TIMESTAMP_USER));
        $now->setHour(0);
        $now->setMinute(0);
        $now->setSecond(0);
        return $now;
    }

}