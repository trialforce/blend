<?php

namespace Type;

/**
 * Classe para lidar com Datas
 *
 * Criado originalmente em 06/10/2011 por :
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Started atr Solis - Cooperativa de Soluções Livres Ltda. e Univates - Centro Universitário.
 *
 * */
class DateTime extends \Validator\Validator
{

    const MASK_DATE_USER = 'd/m/Y';
    const MASK_DATE_CNAB6 = 'dmy';
    const MASK_DATE_CNAB8 = 'dmY';
    const MASK_DATE_NFE21 = 'Ymd';
    const MASK_DATE_YEAR_MONTH = 'Y-m';
    const MASK_TIME = 'H:i:s';
    const MASK_HOUR = 'H:i';
    const MASK_TIMESTAMP_USER = 'd/m/Y H:i:s';
    const MASK_TIMESTAMP_USER_WITHOUT_SECOND = 'd/m/Y H:i';
    const MASK_DATE_DB = 'Y-m-d';
    const MASK_DATE_FILE = 'Y_m_d';
    const MASK_TIMESTAMP_DB = 'Y-m-d H:i:s';
    const MASK_TIMESTAMP_FILE = 'Y_m_d_H_i_s';
    const MASK_FORMATED = 'd \d\e F \d\e Y';
    const MASK_FORMATED_HOUR = 'd \d\e F \d\e Y H:i:s';
    const ROUND_AUTOMATIC = 'a';
    const ROUND_DOWN = 'd';
    const ROUND_UP = 'u';

    private $day = null;
    private $month = null;
    private $year = null;
    private $hour = null;
    private $minute = null;
    private $second = null;

    /**
     * Contrutor estático usado para que possa se utilizar
     * o construtor e chamar a função necessária na mesma linha.
     *
     * @param string $date
     * @return Date
     *
     * @example \Type\DateTime::get( $date ) = retorna a data em formato de usuário
     */
    public static function get($date)
    {
        return new \Type\DateTime($date);
    }

    /**
     * Seta o dia
     *
     * @param $day
     * @return Date
     */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Obtém o dia
     *
     * @return dia
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Soma dias na data
     *
     * @param $day
     * @return Date para funcionar em uma linha
     */
    public function addDay($day)
    {
        $date = date(self::MASK_TIMESTAMP_USER, mktime($this->hour, $this->minute, $this->second, $this->month, $this->day + $day, $this->year));
        $this->setValue($date);

        return $this;
    }

    /**
     * Seta o mês
     *
     * @param $month
     *
     * @return \Date;
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Obtém o mês
     *
     * @return mês
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Soma meses na data
     *
     * @param $month
     * @return \Type\DateTime
     */
    public function addMonth($month = 1)
    {
        $date = date(self::MASK_TIMESTAMP_USER, mktime($this->hour, $this->minute, $this->second, $this->month + $month, $this->day, $this->year));
        $this->setValue($date);

        return $this;
    }

    /**
     * Define the year
     *
     * @param $year
     * @return \Type\DateTime
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get the year
     *
     * @return int
     */
    public function getYear($digits = 4)
    {
        if ($digits == 2)
        {
            return intval($this->getValue('y'));
        }

        return intval($this->year);
    }

    /**
     * Soma anos na data
     *
     * @param $year
     */
    public function addYear($year)
    {
        $date = date(self::MASK_TIMESTAMP_USER, mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year + $year));
        $this->setValue($date);
    }

    /**
     * Define a hora minuto e segundo
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return \Date
     */
    public function setTime($hour, $minute = NULL, $second = NULL)
    {
        if ($hour && !$minute && !$second)
        {
            $explode = explode(':', $hour);
            $this->setHour($explode[0]);
            $this->setMinute($explode[1]);
            $this->setSecond($explode[2]);
        }
        else
        {
            $this->setHour($hour);
            $this->setMinute($minute);
            $this->setSecond($second);
        }

        return $this;
    }

    /**
     * Seta a hora
     *
     * @param $hour
     * @return Date;
     */
    public function setHour($hour)
    {
        $this->hour = $hour;

        return $this;
    }

    /**
     * Obtém a hora
     *
     * @return hora
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * Soma horas na data
     *
     * @param $hour
     */
    public function addHour($hour)
    {
        $date = date(self::MASK_TIMESTAMP_USER, mktime($this->hour + $hour, $this->minute, $this->second, $this->month, $this->day, $this->year));
        $this->setValue($date);

        return $this;
    }

    /**
     * Seta o minuto
     *
     * @param $minute
     * @return Date;
     */
    public function setMinute($minute)
    {
        $this->minute = intval($minute);

        return $this;
    }

    /**
     * Obtém o minuto
     *
     * @return minuto
     */
    public function getMinute()
    {
        return intval($this->minute);
    }

    /**
     * Soma minutos na data
     *
     * @param $minute
     *
     * @return \Type\DateTime
     */
    public function addMinute($minute)
    {
        $date = date(self::MASK_TIMESTAMP_USER, mktime($this->hour, $this->minute + $minute, $this->second, $this->month, $this->day, $this->year));
        $this->setValue($date);

        return $this;
    }

    /**
     * Seta o segundo
     *
     * @param $second
     * @return Date;
     */
    public function setSecond($second)
    {
        $this->second = $second;

        return $this;
    }

    /**
     * Obtém o segundo
     *
     * @return segundo
     */
    public function getSecond()
    {
        return $this->second;
    }

    /**
     * Soma segundos na data
     *
     * @param $second
     */
    public function addSecond($second)
    {
        $date = date(self::MASK_TIMESTAMP_USER, mktime($this->hour, $this->minute, $this->second + $second, $this->month, $this->day, $this->year));
        $this->setValue($date);

        return $this;
    }

    /**
     * Seta a data que será trabalhada na classe, identificando qual é a máscara passada
     *
     * @param timestamp $date
     */
    public function setValue($date = null)
    {
        if ($date instanceof \Type\DateTime)
        {
            $this->setDay($date->getDay());
            $this->setMonth($date->getMonth());
            $this->setYear($date->getYear());
            $this->setTime($date->getHour(), $date->getMinute(), $date->getSecond());
        }

        $this->clean();

        if (!is_null($date))
        {
            $this->explodeDate($date);
        }

        return $this;
    }

    /**
     * Verifica se é uma data valida.
     *
     * @return boolean
     */
    public function isValid()
    {
        if (!$this->month || !$this->day || !$this->year)
        {
            return false;
        }

        return checkdate($this->month, $this->day, $this->year);
    }

    /**
     * Função chamada automaticamente pelo PHP quando precisa converter dado para String
     *
     * @return a data no formato do usuário
     */
    public function __toString()
    {
        return $this->getValue(self::MASK_TIMESTAMP_USER);
    }

    /**
     * Retorna a diferença entre a data do objeto e a data do objeto do parametro.
     *
     * @param Object Date
     * @return timestamp unix da diferença
     */
    public function subtractDate($date)
    {
        $timesTamp2 = 0;

        if ($date instanceof \Type\DateTime)
        {
            $timesTamp2 = $date->getTimestampUnix();
        }

        return $this->getTimestampUnix() - $timesTamp2;
    }

    /**
     * Calcula a diferença entre datas
     *
     * @param: da a ser comparada
     * @return (object DiffDate)
     */
    public function diffDates($date, $round = null)
    {
        $timesTamp2 = 0;

        if ($date instanceof \Type\DateTime)
        {
            $timesTamp2 = $date->getTimestampUnix();
        }

        $timesTamp1 = $this->getTimestampUnix();
        $diff = $timesTamp1 - $timesTamp2;

        $data = new DiffDate();
        $data->seconds = $diff;
        $data->minutes = $this->roundNumber($diff / 60, $round);
        $data->hours = $this->roundNumber($diff / 3600, $round);
        $data->days = $this->roundNumber($diff / 86400, $round);
        $data->months = $this->roundNumber($diff / 2592000, $round);
        $data->years = $this->roundNumber($diff / 31536000, $round);

        return $data;
    }

    /**
     * This method verify if passed date string has a valid format
     *
     * @param: string date
     * @return (boolean)
     */
    protected function explodeDate($date)
    {
        $numericDate = preg_replace("/[^0-9]/", "", $date);
        $countZero = substr_count($numericDate, '0');
        $length = strlen($numericDate);

        //if is only zeros is am empty date
        if ($countZero == $length)
        {
            $date = '';
            return FALSE;
        }

        //adiciona suporte a data com utc 2017-12-20T16:06:31-02:00
        if (stripos($date, 'T') && strlen($date) > 18)
        {
            //desconsidera gmt
            $date = substr($date, 0, 19);
        }

        //remove some UTC caracters to make regexp work
        $date = str_replace(array('T', 'Z'), ' ', $date);

        // format = dd/mm/yyyy hh:ii:ss
        if (mb_ereg("^([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2})\:([0-9]{2})\:([0-9]{2})\$", $date, $reg))
        {
            $this->hour = $reg[4];
            $this->minute = $reg[5];
            $this->second = $reg[6];
            $this->month = $reg[2];
            $this->day = $reg[1];
            $this->year = $reg[3];

            return true;
        }

        // format = dd/mm/yyyy hh:ii:ss.nnnnnn
        if (mb_ereg("^([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2})\:([0-9]{2})\:([0-9]{2})\.(.{1,})\$", $date, $reg))
        {
            $this->hour = $reg[4];
            $this->minute = $reg[5];
            $this->second = $reg[6];
            $this->month = $reg[2];
            $this->day = $reg[1];
            $this->year = $reg[3];

            return true;
        }

        // format = dd/mm/yyyy hh:ii
        if (mb_ereg("^([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2})\:([0-9]{2})\$", $date, $reg))
        {
            $this->hour = $reg[4];
            $this->minute = $reg[5];
            $this->second = '00';
            $this->month = $reg[2];
            $this->day = $reg[1];
            $this->year = $reg[3];

            return true;
        }

        // format = dd/mm/yyyy
        if (mb_ereg("^([0-9]{2})\/([0-9]{2})\/([0-9]{4})\$", $date, $reg))
        {
            $this->hour = '00';
            $this->minute = '00';
            $this->second = '00';
            $this->month = $reg[2];
            $this->day = $reg[1];
            $this->year = $reg[3];

            return true;
        }

        // format = yyyy-mm-dd hh:ii:ss
        if (mb_ereg("^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2})\:([0-9]{2})\:([0-9]{2})\$", $date, $reg))
        {
            $this->hour = $reg[4];
            $this->minute = $reg[5];
            $this->second = $reg[6];
            $this->month = $reg[2];
            $this->day = $reg[3];
            $this->year = $reg[1];

            return true;
        }

        // format = yyyy-mm-dd hh:ii:ss.nnnnnn
        if (mb_ereg("^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2})\:([0-9]{2})\:([0-9]{2})\.(.{1,})\$", $date, $reg))
        {
            $this->hour = $reg[4];
            $this->minute = $reg[5];
            $this->second = $reg[6];
            $this->month = $reg[2];
            $this->day = $reg[3];
            $this->year = $reg[1];

            return true;
        }

        // format = yyyy-mm-dd hh:ii
        if (mb_ereg("^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2})\:([0-9]{2})\$", $date, $reg))
        {
            $this->hour = $reg[4];
            $this->minute = $reg[5];
            $this->second = '00';
            $this->month = $reg[2];
            $this->day = $reg[3];
            $this->year = $reg[1];

            return true;
        }
        // format = yyyy-mm-dd
        if (mb_ereg("^([0-9]{4})-([0-9]{2})-([0-9]{2})\$", $date, $reg))
        {
            $this->second = '00';
            $this->month = $reg[2];
            $this->day = $reg[3];
            $this->year = $reg[1];

            return true;
        }

        //if is timestamp
        if (is_numeric($date))
        {
            $date = date(self::MASK_TIMESTAMP_USER, $date);
            $this->explodeDate($date);

            return true;
        }

        //if don't reconize data return false
        return false;
    }

    /**
     * Limpa os atributos do objeto
     */
    private function clean()
    {
        $this->hour = $this->minute = $this->second = $this->month = $this->day = $this->year;
    }

    /**
     * Método estático que retorna o tempo e data atual
     *
     * @param máscara a ser aplicada
     * @return (objetct) Date
     */
    public static function now()
    {
        return new \Type\DateTime(date(self::MASK_TIMESTAMP_USER));
    }

    /**
     * Método para obter a data conforme a máscara passada por parâmetro
     *
     * @param $mask
     * @return string com a data
     */
    public function getValue($mask = self::MASK_TIMESTAMP_USER)
    {
        if ($this->getTimestampUnix())
        {
            $date = self::correctMonthNames(date($mask, $this->getTimestampUnix()));

            return $date;
        }
        else
        {
            return '';
        }
    }

    /**
     * Temporary function to adjusts month names do portuguese
     *
     * @param string $dateString
     * @return string
     */
    protected static function correctMonthNames($dateString)
    {
        $english[] = 'May';
        $english[] = 'Apr';
        $english[] = 'April';

        $portuguese[] = 'Maio';
        $portuguese[] = 'Abril';
        $portuguese[] = 'Abril';

        return str_replace($english, $portuguese, $dateString);
    }

    /**
     * Retorna o timestamp unix da data
     *
     * @return long int
     */
    public function getTimestampUnix()
    {
        if ($this->month && $this->day && $this->year)
        {
            return mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
        }
        else
        {
            return null;
        }
    }

    /**
     * Compara dois objetos Date
     *
     * @param (object) Date
     * @param string $operation
     * @return boolean
     */
    public function compare($date, $operation = '=')
    {
        switch ($operation)
        {
            case '>' :
                return ($this->getTimestampUnix() > $date->getTimestampUnix());

            case '<' :
                return ($this->getTimestampUnix() < $date->getTimestampUnix());

            case '>=' :
                return (($this->getTimestampUnix() == $date->getTimestampUnix()) || ($this->getTimestampUnix() > $date->getTimestampUnix()));

            case '<=' :
                return (($this->getTimestampUnix() == $date->getTimestampUnix()) || ($this->getTimestampUnix() < $date->getTimestampUnix()));

            default :
                return ($this->getTimestampUnix() == $date->getTimestampUnix());
        }
    }

    /**
     * Método privado para arredondar valores
     *
     * @param número
     * @param arredondamento
     * @return valor arredondado
     */
    private function roundNumber($number, $round)
    {
        if ($round == self::ROUND_DOWN)
        {
            $number = floor($number);
        }
        elseif ($round == self::ROUND_UP)
        {
            $number = ceil($number);
        }
        elseif ($round == self::ROUND_AUTOMATIC)
        {
            $number = round($number);
        }

        return $number;
    }

    /**
     * Retorna o dia da semana de 1 a 7
     *
     * @return integer
     */
    public function getDayOfWeek()
    {
        return date('N', $this->getTimestampUnix());
    }

    /**
     * Retorna o mês port extenso
     *
     * @param int $mes
     * @return string
     */
    public static function getMonthExt($mes)
    {
        switch ($mes)
        {
            case "01":
                $mes = 'Janeiro';
                break;
            case "02":
                $mes = 'Fevereiro';
                break;
            case "03":
                $mes = 'Março';
                break;
            case "04":
                $mes = 'Abril';
                break;
            case "05":
                $mes = 'Maio';
                break;
            case "06":
                $mes = 'Junho';
                break;
            case "07":
                $mes = 'Julho';
                break;
            case "08":
                $mes = 'Agosto';
                break;
            case "09":
                $mes = 'Setembro';
                break;
            case "10":
                $mes = 'Outubro';
                break;
            case "11":
                $mes = 'Novembro';
                break;
            case "12":
                $mes = 'Dezembro';
                break;
        }

        return $mes;
    }

    /**
     * Return list o months
     *
     * @return array
     *
     */
    public static function listMonth()
    {
        $mes[1] = 'Janeiro';
        $mes[2] = 'Fevereiro';
        $mes[3] = 'Março';
        $mes[4] = 'Abril';
        $mes[5] = 'Maio';
        $mes[6] = 'Junho';
        $mes[7] = 'Julho';
        $mes[8] = 'Agosto';
        $mes[9] = 'Setembro';
        $mes[10] = 'Outubro';
        $mes[11] = 'Novembro';
        $mes[12] = 'Dezembro';

        return $mes;
    }

    /**
     * List years
     *
     * @param int $year
     * @param int $before
     * @param int $after
     *
     * @return array
     */
    public static function listYear($year, $before = 2, $after = 1)
    {
        $yearOrigin = $year;
        $years[$year] = $year;

        for ($i = 0; $i < $before; $i++)
        {
            $year--;
            $years[$year] = $year;
        }

        $year = $yearOrigin;

        for ($i = 0; $i < $after; $i++)
        {
            $year++;
            $years[$year] = $year;
        }

        ksort($years);

        return $years;
    }

    /**
     * Retorna a lista com os dias da semana por extendo
     * @param
     * @return array
     */
    public static function listWeekDayExt($sunday = 0)
    {
        if (!$sunday)
        {
            $sunday = 0;
        }

        $dias[1] = "Segunda-Feira";
        $dias[2] = "Terça-Feira";
        $dias[3] = "Quarta-Feira";
        $dias[4] = "Quinta-Feira";
        $dias[5] = "Sexta-Feira";
        $dias[6] = "Sábado";
        $dias[$sunday] = "Domingo";

        return $dias;
    }

    /**
     * Retorna este objeto escrito no formato que for passado em $format.
     * Para saber como utilizar estes formatos verifique função strftime do php.
     *
     * @param string $format
     * @return string
     */
    public function strftime($format)
    {
        return strftime($format, $this->getTimestampUnix());
    }

    /**
     * Define o dia como último dia do mês
     *
     * @return Date;
     */
    public function setLastDayOfMonth()
    {
        $this->setDay($this->getLastDayOfMonth());

        return $this;
    }

    /**
     * Obtém o último dia do mês
     *
     * @return int
     */
    public function getLastDayOfMonth()
    {
        return date("t", $this->getTimestampUnix());
    }

    public function toDb()
    {
        $value = $this->getValue(self::MASK_TIMESTAMP_DB);

        if (!$value)
        {
            return NULL;
        }

        return $value;
    }

    /**
     * Get value estático.
     * Usado para formatação.
     *
     * @param string $value
     * @return string
     */
    public static function value($value)
    {
        return \Type\DateTime::get($value)->getValue();
    }

    /**
     * Coloca automáticamente a data para o próximo dia de semana.
     * Exemplo :
     * 1 - 01/11/2013 - Sexta-feira
     * 2 - Manda para a próxima segunda
     * 3 - A data fica 04/11/2013
     *
     * @param type $diaSemana (Vide linha dayname na tabela do link http://www.php.net/manual/en/datetime.formats.relative.php)
     */
    public function setNextDayOfWeek($diaSemana)
    {
        $this->setDate(strtotime($this->getTimestampUnix() . ' NEXT ' . $diaSemana));
        return $this;
    }

    public function toHuman()
    {
        return $this->getSmartDate();
    }

    /**
     * Get smart date, like Gmail
     *
     * @return string
     */
    public function getSmartDate()
    {
        $now = Date::now();

        //other year
        if ($this->year != $now->year)
        {
            return $this->getValue(self::MASK_DATE_USER);
        }
        //today
        else if ($this->isToday())
        {
            if ($this->getHour() == 0 && $this->getMinute() == 0 && $this->getSecond())
            {
                return 'Hoje'; //$this->getValue( self::MASK_DATE_USER );
            }

            return $this->getValue(self::MASK_HOUR);
        }
        //other day
        else
        {
            $date = $this->strftime('%d %b');
            $search = array('01', '02', '03', '04', '05', '06', '07', '08', '09');
            $replace = array('1', '2', '3', '4', '5', '6', '7', '8', '9');

            return self::correctMonthNames(str_replace($search, $replace, $date));
        }
    }

    /**
     * Verify is isToday
     *
     * @return string
     */
    public function isToday()
    {
        $now = Date::now();

        return ($this->day == $now->getDay() && $this->month == $now->getMonth() && $this->year == $now->getYear() );
    }

    /**
     * Retorna a data no formato utc.
     * Utilizado pelos xmls de nfs-e nfe
     *
     * @return string
     */
    public function getUtc($withZone = TRUE)
    {
        $withZone = $withZone ? '\Z' : '';
        return date("Y-m-d\TH:i:s" . $withZone, $this->getTimestampUnix());
    }

    /**
     * Bota a data no exato dia útil do mês.
     * Exemplo:
     * O quinto dia útil de 01/07/2013 é 07/07/2013
     *
     * @param type $workingDay
     * @return \Type\DateTime
     */
    public function setWorkingDay($workingDay)
    {
        $day = 1;
        $this->setDay($day);

        if ($this->isWorkingDay())
        {
            $day++;
        }

        while ($day <= $workingDay)
        {
            $this->addDay(1);

            if ($this->isWorkingDay())
            {
                $day++;
            }
        }

        return $this;
    }

    /**
     * Verify is is working day (not weekend)
     *
     * @return boolean
     */
    public function isWorkingDay()
    {
        $dayWeek = $this->getDayOfWeek();

        if ($dayWeek >= 6)
        {
            return FALSE;
        }

        return TRUE;
    }

    public function validate($value = NULL)
    {
        $error = parent::validate($value);

        if (mb_strlen($this->value) > 0)
        {
            list($dia, $mes, $ano) = explode('/', $this->getValue());

            if (!checkdate($mes, $dia, $ano))
            {
                $error[] = 'Data inválida.';
            }
        }

        return $error;
    }

}

class DiffDate
{

    public $days,
            $months,
            $years,
            $hours,
            $minutes,
            $seconds;

}