<?php

namespace View\Ext;

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
     * Day indexed data, used to create calender with content
     * @var array
     */
    protected $dataDay;

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

    public function getMonth()
    {
        return $this->month;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setMonth($month)
    {
        $this->month = $month;
        return $this;
    }

    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * Define week days
     *
     * @param array $weekDays
     */
    public function setWeekDays($weekDays)
    {
        $this->weekDays = $weekDays;
    }

    /**
     * Set complete week day
     */
    public function setCompleteWeekDays()
    {
        $this->weekDays = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado');
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

    public function getDataDay()
    {
        return $this->dataDay;
    }

    public function setDataDay($dataDay)
    {
        $this->dataDay = $dataDay;
    }

    public function getLastDayofMonth()
    {
        $date = new \Type\DateTime();
        $date->setDay(1)->setMonth($this->getMonth())->setYear($this->getYear());
        $lastDayOfMonth = $date->getLastDayOfMonth();

        return $lastDayOfMonth;
    }

    public function onCreate($createSpan = FALSE)
    {
        $today = \Type\Date::now();
        $todayDayTrim = ltrim($today->getDay(), 0);
        $this->append(new \View\Caption('caption_' . $this->getId(), $this->getMonth() . '/' . $this->getYear()));
        $dias = $this->getLastDayofMonth();

        $weekDays = $this->getWeekDays();

        foreach ($weekDays as $weekDay)
        {
            $ths[] = new \View\Th(NULL, $weekDay);
        }

        $trTh = new \View\Tr(NULL, $ths);
        $this->append(new \View\THead(NULL, $trTh));

        $myTr = new \View\Tr(NULL);

        for ($i = 1; $i <= $dias; $i++)
        {
            $diaSemana = date("w", mktime(0, 0, 0, $this->getMonth(), $i, $this->getYear()));

            $cont = 0;

            //pula dias iniciais
            if ($i == 1)
            {
                while ($cont < $diaSemana)
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

            if ($diaSemana == 6)
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
        
        if ( $today->getMonth() == $this->getMonth() )
        {
            $todayDayTrim = ltrim($today->getDay(), 0);

            $idToday = $this->getId() . '_day_' . $todayDayTrim;
            \App::addJs("$('body').scrollTop( $('#{$idToday}').offset().top-100)");
        }
    }

}