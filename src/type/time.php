<?php

namespace Type;

/**
 * Time type
 */
class Time implements \Type\Generic, \JsonSerializable
{

    /**
     * Hour
     * @var float
     */
    protected $hour = '0';

    /**
     * Minute
     * @var int
     */
    protected $minute = '0';

    /**
     * Minute
     *
     * @var int
     */
    protected $second = '0';

    /**
     * Milesecond
     * @var int
     */
    protected $milesecond = '0';

    /**
     * Use milesecond
     * @var bool
     */
    protected $useMilesecond = false;

    /**
     * Avoid zero in human time
     *
     * @var bool
     */
    protected $avoidZero = false;

    public function __construct($value = null)
    {
        $this->setValue($value);
    }

    /**
     * To human
     * @return string
     */
    public function toHuman()
    {
        //avoid 0h
        if ($this->avoidZero && $this->isEmpty())
        {
            return '';
        }

        $human = '';

        if ($this->hour)
        {
            $human .= $this->hour . 'h';
        }

        if ($this->minute)
        {
            $human .= ' ' . $this->minute . 'm';
        }

        if (!$human)
        {
            $human = '0h';
        }

        return $human;
    }

    public function __toString()
    {
        return $this->toDb();
    }

    public function isEmpty()
    {
        return $this->hour == 0 && $this->minute == 0 && $this->second == 0 && $this->milesecond == 0;
    }

    public function getAvoidZero()
    {
        return $this->avoidZero;
    }

    public function setAvoidZero($avoidZero)
    {
        $this->avoidZero = $avoidZero;
        return $this;
    }

    public function getValue()
    {
        return $this->toDb();
    }

    public function setValue($value)
    {
        if ($value instanceof \Type\Generic)
        {
            $value = $value->getValue();
        }

        $this->treatValue($value);

        return $this;
    }

    /**
     * Soma minutos no horário
     *
     * @param $minute
     *
     * @return \Type\Time
     */
    public function addMinute($minute)
    {
        $minutes = $this->minute + $minute;

        $hours = intval($minutes / 60);

        $minutes %= 60;

        $hours += $this->getHour();

        $this->setHour($hours);
        $this->setMinute($minutes);

        return $this;
    }

    /**
     * Treat value to support brazilian format
     *
     * @param string $value
     * @return string
     */
    public function treatValue($value)
    {
        //parse milesecond
        if (stripos($value, '.'))
        {
            $explode = explode('.', $value);
            $this->milesecond = intval($explode[1]);
            $value = $explode[0];
        }

        //parse hour, minute, second
        if (stripos($value, ':'))
        {
            $explode = explode(':', $value);
            $this->hour = intval(ltrim($explode[0], '0'));

            if (isset($explode[1]))
            {
                $this->minute = intval(ltrim($explode[1], '0'));
            }

            if (isset($explode[2]))
            {
                $this->second = intval($explode[2]);
            }
        }
        else if (is_numeric($value))
        {
            $hours = intval($value / 60);
            $minutes = $value % 60;
            $this->hour = $hours;
            $this->minute = $minutes;
        }

        return $this;
    }

    public function toDb()
    {
        $string = str_pad($this->hour, 2, '0', STR_PAD_LEFT) .
                ':' . str_pad($this->minute, 2, '0', STR_PAD_LEFT) .
                ':' . str_pad($this->second, 2, '0', STR_PAD_LEFT);

        if ($this->useMilesecond)
        {
            $string .= '.' . str_pad($this->milesecond, 6, '0', STR_PAD_LEFT);
        }

        return $string;
    }

    public function format()
    {
        $string = str_pad($this->hour, 2, '0', STR_PAD_LEFT) .
                ':' . str_pad($this->minute, 2, '0', STR_PAD_LEFT);

        return $string;
    }

    public function getHour()
    {
        return $this->hour;
    }

    public function getMinute()
    {
        return $this->minute;
    }

    public function getSecond()
    {
        return $this->second;
    }

    public function getMilesecond()
    {
        return $this->milesecond;
    }

    public function getUseMilesecond()
    {
        return $this->useMilesecond;
    }

    public function setHour($hour)
    {
        $this->hour = intval($hour);
        return $this;
    }

    public function setMinute($minute)
    {
        $this->minute = intval($minute);
        return $this;
    }

    public function setSecond($second)
    {
        $this->second = intval($second);
        return $this;
    }

    public function setMilesecond($milesecond)
    {
        $this->milesecond = intval($milesecond);
        return $this;
    }

    public function setUseMilesecond($useMilesecond)
    {
        $this->useMilesecond = $useMilesecond;
        return $this;
    }

    public function jsonSerialize()
    {
        return $this->toDb();
    }

    /**
     * To hour (int)
     *
     * @param  $time
     * @return \Type\Decimal
     */
    public function toDecimal()
    {
        return new \Type\Decimal($this->hour + ($this->minute / 60));
    }

    public static function now()
    {
        return \Type\Date::now()->getValue(\Type\Date::MASK_TIME);
    }

    /**
     * Static get a time type
     *
     * @param type $value
     * @return \Type\Time
     */
    public static function get($value)
    {
        return new \Type\Time($value . '');
    }

    /**
     * Format a value
     *
     * @param string $value
     * @return string
     */
    public static function value($value)
    {
        return \Type\Time::get($value)->getValue();
    }

    /**
     * Pass a integer amount of second and this method create
     * a hourly time
     *
     * @param int $value
     * @return \Type\Time
     */
    public static function createBySeconds($value)
    {
        $newTime = new \Type\Time(0);

        $horas = 0;

        if ($value >= 3600)
        {
            while ($value >= 3600)
            {
                $value = $value - 3600;
                $horas++;
            }
        }

        $minutos = 0;

        if ($value >= 60)
        {
            while ($value >= 60)
            {
                $value = $value - 60;
                $minutos++;
            }
        }

        $newTime->setHour($horas);
        $newTime->setMinute($minutos);
        $newTime->setSecond($value);
        return $newTime;
    }

}
