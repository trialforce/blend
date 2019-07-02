<?php

namespace View\Ext;

/**
 * A calendar of a month
 */
class Calendar extends \View\Table
{

    /**
     * Current month of calendar
     *
     * @var int
     */
    protected $month;

    /**
     * Current year of the calendar
     *
     * @var int
     */
    protected $year;

    /**
     * Default week day labels
     *
     * @var array
     */
    protected $weekDays = array('D', 'S', 'T', 'Q', 'Q', 'S', 'S');

    /**
     * Day indexed data, used to create calendar with content
     * @var array
     */
    protected $dataDay;

    /**
     * Construct the calendar
     *
     * @param string $id calendar id
     * @param int $month calendar month
     * @param int $year calendar year
     * @param string $class css class
     * @param \View\View $father
     */
    public function __construct($id = NULL, $month = NULL, $year = NULL, $class = NULL, $father = NULL)
    {
        parent::__construct($id, NULL, $class, $father);
        $this->addClass('calendar');

        $this->setMonth($month);
        $this->setYear($year);

        if ($this->year && $this->month)
        {
            $this->onCreate();
        }
    }

    /**
     * Set month
     * @param int $month
     * @return $this
     */
    public function setMonth($month)
    {
        $this->month = $month;
        return $this;
    }

    /**
     * Return the current month
     * @return int
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set year
     * @param int $year
     * @return $this
     */
    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * Return the year
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Return an array with week days
     *
     * @return array
     */
    public function getWeekDays()
    {
        return $this->weekDays;
    }

    /**
     * Define week days labels
     *
     * @param array $weekDays
     */
    public function setWeekDays($weekDays)
    {
        $this->weekDays = $weekDays;
    }

    /**
     * Set complete week day
     * A shortcut method to full day name in labels
     *
     * @todo need to use system language
     */
    public function setCompleteWeekDays()
    {
        $this->weekDays = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado');
    }

    /**
     * Return the data indexed by day
     *
     * @return array
     */
    public function getDataDay()
    {
        return $this->dataDay;
    }

    /**
     * Define the data indexed by dia
     * @param array $dataDay
     */
    public function setDataDay($dataDay)
    {
        $this->dataDay = $dataDay;
    }

    /**
     * Return the last day of calendar month
     * @return int
     */
    protected function getLastDayofMonth()
    {
        $date = new \Type\DateTime();
        $date->setDay(1)->setMonth($this->getMonth())->setYear($this->getYear());
        $lastDayOfMonth = $date->getLastDayOfMonth();

        return $lastDayOfMonth;
    }

    protected function createThs()
    {
        $ths = array();
        $weekDays = $this->getWeekDays();

        foreach ($weekDays as $weekDay)
        {
            $ths[] = new \View\Th(NULL, $weekDay);
        }

        return $ths;
    }

    protected function createCaption()
    {
        $caption = new \View\Caption('caption_' . $this->getId(), \Type\DateTime::getMonthExt($this->getMonth()) . '/' . $this->getYear());
        return $caption;
    }

    /**
     * Create the calendar
     *
     * @param bool $createSpan create a span inside TD
     */
    public function onCreate($createSpan = FALSE)
    {
        $today = \Type\Date::now();
        $todayDayTrim = ltrim($today->getDay(), 0);
        $totalDaysOfMonth = $this->getLastDayofMonth();
        $this->append($this->createCaption());

        $trTh = new \View\Tr(NULL, $this->createThs());
        $this->append(new \View\THead(NULL, $trTh));

        $myTr = new \View\Tr(NULL);

        for ($i = 1; $i <= $totalDaysOfMonth; $i++)
        {
            $dayOfWeek = date("w", mktime(0, 0, 0, $this->getMonth(), $i, $this->getYear()));

            $cont = 0;

            //jump initial days
            if ($i == 1)
            {
                while ($cont < $dayOfWeek)
                {
                    $tds[] = new \View\Td(NULL, NULL);

                    $cont++;
                }
            }

            $myValue = null;

            if ($createSpan)
            {
                $myValue[] = new \View\Span('calendarDay_' . $i, $i, 'calendarDay');
            }
            else
            {
                $myValue[] = $i;
            }

            //get value from data
            if (isset($this->dataDay[$i]))
            {
                $myValue[] = $this->dataDay[$i];
            }

            //add support for days with zero ex.: 03
            if ($i < 10)
            {
                if (isset($this->dataDay['0' . $i]))
                {
                    $myValue[] = $this->dataDay['0' . $i];
                }
            }

            $class = '';

            //put today class
            if ($today->getMonth() == $this->getMonth() && $todayDayTrim == $i)
            {
                $class = 'today';
            }

            $tds[] = new \View\Td($this->getId() . '_day_' . $i, $myValue, $class);

            if ($dayOfWeek == 6)
            {
                $myTr->append($tds);
                $this->append($myTr);

                $myTr = new \View\Tr(NULL);
                $tds = NULL;
            }
        }

        $myTr->append($tds);
        $this->append($myTr);
    }

    /**
     * Scroll to today, for big calendar
     */
    public function scrollToToday()
    {
        $today = \Type\Date::now();

        if ($today->getMonth() == $this->getMonth())
        {
            $todayDayTrim = ltrim($today->getDay(), 0);

            $idToday = $this->getId() . '_day_' . $todayDayTrim;
            \App::addJs("$('body').scrollTop( $('#{$idToday}').offset().top-100)");
        }
    }

}
