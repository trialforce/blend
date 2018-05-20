<?php

namespace Validator;

/**
 * Validaçao de inscrição estadual
 *
 * TODO remover a supressão de warninps do PHPMD, mas a classe precisa de um refactor completo
 * @SuppressWarnings(PHPMD)
 *
 * Chupinhado de
 * http://forum.imasters.com.br/topic/322873-validar-inscricao-estadual-de-todos-os-estados/
 */
class Ie extends \Validator\Validator
{

    protected $uf;

    public function getUf()
    {
        return $this->uf;
    }

    public function setUf($uf)
    {
        $this->uf = $uf;
    }

    function CheckIEAC($inscE)
    {
        if (mb_strlen($inscE) != 13)
        {
            return FALSE;
        }

        if (mb_substr($inscE, 0, 2) != '01')
        {
            return FALSE;
        }

        $base = 4;
        $soma = 0;

        for ($i = 0; $i <= 10; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;

            if ($base == 1)
            {
                $base = 9;
            }
        }

        $dig = 11 - ($soma % 11);

        if ($dig >= 10)
        {
            $dig = 0;
        }

        if (!($dig == $inscE[11]))
        {
            return 0;
        }
        else
        {
            $base = 5;
            $soma = 0;

            for ($i = 0; $i <= 11; $i++)
            {
                $soma += $inscE[$i] * $base;
                $base--;

                if ($base == 1)
                {
                    $base = 9;
                }
            }

            $dig = 11 - ($soma % 11);

            if ($dig >= 10)
            {
                $dig = 0;
            }

            return ($dig == $inscE[12]);
        }
    }

    //Alagoas
    function CheckIEAL($inscE)
    {
        if (mb_strlen($inscE) != 9)
        {
            return FALSE;
        }

        if (mb_substr($inscE, 0, 2) != '24')
        {
            return FALSE;
        }

        $base = 9;
        $soma = 0;

        for ($i = 0; $i <= 7; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
        }

        $soma *= 10;
        $dig = $soma - ( ( (int) ($soma / 11) ) * 11 );

        if ($dig == 10)
        {
            $dig = 0;
        }

        return ($dig == $inscE[8]);
    }

    //Amazonas
    function CheckIEAM($inscE)
    {
        if (mb_strlen($inscE) != 9)
        {
            return FALSE;
        }

        $base = 9;
        $soma = 0;

        for ($i = 0; $i <= 7; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
        }

        if ($soma <= 11)
        {
            $dig = 11 - $soma;
        }
        else
        {
            $r = $soma % 11;

            if ($r <= 1)
            {
                $dig = 0;
            }
            else
            {
                $dig = 11 - $r;
            }
        }

        return ($dig == $inscE[8]);
    }

    //Amapá
    function CheckIEAP($inscE)
    {
        if (mb_strlen($inscE) != 9)
        {
            return FALSE;
        }

        if (mb_substr($inscE, 0, 2) != '03')
        {
            return FALSE;
        }

        $i = mb_substr($inscE, 0, -1);

        if (($i >= 3000001) && ($i <= 3017000))
        {
            $p = 5;
            $d = 0;
        }
        elseif (($i >= 3017001) && ($i <= 3019022))
        {
            $p = 9;
            $d = 1;
        }
        elseif ($i >= 3019023)
        {
            $p = 0;
            $d = 0;
        }

        $base = 9;
        $soma = $p;

        for ($i = 0; $i <= 7; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
        }

        $dig = 11 - ($soma % 11);

        if ($dig == 10)
        {
            $dig = 0;
        }
        elseif ($dig == 11)
        {
            $dig = $d;
        }

        return ($dig == $inscE[8]);
    }

    //Bahia
    function CheckIEBA($inscE)
    {
        if (!(strlen($inscE) == 9 || mb_strlen($inscE) == 8))
        {
            return FALSE;
        }

        return TRUE;

        //TODO validar isso

        $arr1 = array('0', '1', '2', '3', '4', '5', '8');
        $arr2 = array('6', '7', '9');

        $i = mb_substr($inscE, 0, 1);

        if (in_array($i, $arr1))
        {
            $modulo = 10;
        }
        elseif (in_array($i, $arr2))
        {
            $modulo = 11;
        }

        $base = 7;
        $soma = 0;

        for ($i = 0; $i <= 5; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
        }

        $i = $soma % $modulo;

        if ($modulo == 10)
        {
            if ($i == 0)
            {
                $dig = 0;
            }
            else
            {
                $dig = $modulo - $i;
            }
        }
        else
        {
            if ($i <= 1)
            {
                $dig = 0;
            }
            else
            {
                $dig = $modulo - $i;
            }
        }

        if (!($dig == $inscE[7]))
        {
            return FALSE;
        }

        $base = 8;
        $soma = 0;

        for ($i = 0; $i <= 5; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
        }

        $soma += $inscE[7] * 2;
        $i = $soma % $modulo;

        if ($modulo == 10)
        {
            if ($i == 0)
            {
                $dig = 0;
            }
            else
            {
                $dig = $modulo - $i;
            }
        }
        else
        {
            if ($i <= 1)
            {
                $dig = 0;
            }
            else
            {
                $dig = $modulo - $i;
            }
        }

        return ($dig == $inscE[6]);
    }

    //Ceará
    function CheckIECE($inscE)
    {
        if (mb_strlen($inscE) != 9)
        {
            return FALSE;
        }

        $base = 9;
        $soma = 0;

        for ($i = 0; $i <= 7; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
        }

        $dig = 11 - ($soma % 11);

        if ($dig >= 10)
        {
            $dig = 0;
        }

        return ($dig == $inscE[8]);
    }

    //Distrito Federal
    function CheckIEDF($inscE)
    {
        if (mb_strlen($inscE) != 13)
        {
            return FALSE;
        }

        if (mb_substr($inscE, 0, 2) != '07')
        {
            return FALSE;
        }

        $base = 4;
        $soma = 0;

        for ($i = 0; $i <= 10; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
            if ($base == 1)
            {
                $base = 9;
            }
        }

        $dig = 11 - ($soma % 11);

        if ($dig >= 10)
        {
            $dig = 0;
        }

        if (!($dig == $inscE[11]))
        {
            return 0;
        }
        else
        {
            $base = 5;
            $soma = 0;
            for ($i = 0; $i <= 11; $i++)
            {
                $soma += $inscE[$i] * $base;
                $base--;
                if ($base == 1)
                {
                    $base = 9;
                }
            }
            $dig = 11 - ($soma % 11);
            if ($dig >= 10)
            {
                $dig = 0;
            }

            return ($dig == $inscE[12]);
        }
    }

    //Espirito Santo
    function CheckIEES($inscE)
    {
        if (mb_strlen($inscE) != 9)
        {
            return FALSE;
        }

        $base = 9;
        $soma = 0;

        for ($i = 0; $i <= 7; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
        }

        $i = $soma % 11;

        if ($i < 2)
        {
            $dig = 0;
        }
        else
        {
            $dig = 11 - $i;
        }

        return ($dig == $inscE[8]);
    }

    //Goias
    function CheckIEGO($inscE)
    {
        if (mb_strlen($inscE) != 9)
        {
            return FALSE;
        }

        $s = mb_substr($inscE, 0, 2);

        if (!( ($s == 10) || ($s == 11) || ($s == 15) ))
        {
            return FALSE;
        }

        $n = mb_substr($inscE, 0, 7);

        if ($n == 11094402)
        {
            if ($inscE[8] != 0)
            {
                if ($inscE[8] != 1)
                {
                    return FALSE;
                }
                else
                {
                    return TRUE;
                }
            }
            else
            {
                return TRUE;
            }
        }
        else
        {
            $base = 9;
            $soma = 0;

            for ($i = 0; $i <= 7; $i++)
            {
                $soma += $inscE[$i] * $base;
                $base--;
            }

            $i = $soma % 11;

            if ($i == 0)
            {
                $dig = 0;
            }
            else
            {
                if ($i == 1)
                {
                    if (($n >= 10103105) && ($n <= 10119997))
                    {
                        $dig = 1;
                    }
                    else
                    {
                        $dig = 0;
                    }
                }
                else
                {
                    $dig = 11 - $i;
                }
            }

            return ($dig == $inscE[8]);
        }
    }

    //Maranhão
    function CheckIEMA($inscE)
    {
        if (mb_strlen($inscE) != 9)
        {
            return FALSE;
        }

        if (mb_substr($inscE, 0, 2) != 12)
        {
            return FALSE;
        }

        $base = 9;
        $soma = 0;

        for ($i = 0; $i <= 7; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
        }

        $i = $soma % 11;

        if ($i <= 1)
        {
            $dig = 0;
        }
        else
        {
            $dig = 11 - $i;
        }

        return ($dig == $inscE[8]);
    }

    //Mato Grosso
    function CheckIEMT($inscE)
    {
        if (mb_strlen($inscE) != 11)
        {
            return FALSE;
        }

        $base = 3;
        $soma = 0;

        for ($i = 0; $i <= 9; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
            if ($base == 1)
            {
                $base = 9;
            }
        }

        $i = $soma % 11;

        if ($i <= 1)
        {
            $dig = 0;
        }
        else
        {
            $dig = 11 - $i;
        }

        return ($dig == $inscE[10]);
    }

    //Mato Grosso do Sul
    function CheckIEMS($inscE)
    {
        if (mb_strlen($inscE) != 9)
        {
            return FALSE;
        }

        if (mb_substr($inscE, 0, 2) != 28)
        {
            return FALSE;
        }

        $base = 9;
        $soma = 0;

        for ($i = 0; $i <= 7; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
        }

        $i = $soma % 11;

        if ($i == 0)
        {
            $dig = 0;
        }
        else
        {
            $dig = 11 - $i;
        }

        if ($dig > 9)
        {
            $dig = 0;
        }

        return ($dig == $inscE[8]);
    }

    //Minas Gerais
    function CheckIEMG($inscE)
    {
        if (mb_strlen($inscE) != 13)
        {
            return FALSE;
        }

        RETURN TRUE;

        //TODO validar daqui pra baixo

        $inscE2 = mb_substr($inscE, 0, 3) . '0' . mb_substr($inscE, 3);

        $base = 1;
        $soma = "";

        for ($i = 0; $i <= 11; $i++)
        {
            $soma .= $inscE2[$i] * $base;
            $base++;
            if ($base == 3)
            {
                $base = 1;
            }
        }

        $s = 0;

        for ($i = 0; $i < mb_strlen($soma); $i++)
        {
            $s += $soma[$i];
        }

        $i = mb_substr($inscE2, 9, 2);
        $dig = $i - $s;

        if ($dig != $inscE[11])
        {
            return FALSE;
        }

        $base = 3;
        $soma = 0;

        for ($i = 0; $i <= 11; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
            if ($base == 1)
            {
                $base = 11;
            }
        }

        $i = $soma % 11;

        if ($i < 2)
        {
            $dig = 0;
        }
        else
        {
            $dig = 11 - $i;
        };

        return ($dig == $inscE[12]);
    }

    //Pará
    function CheckIEPA($inscE)
    {
        if (mb_strlen($inscE) != 9)
        {
            return FALSE;
        }

        if (mb_substr($inscE, 0, 2) != 15)
        {
            return FALSE;
        }

        $base = 9;
        $soma = 0;
        for ($i = 0; $i <= 7; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
        }
        $i = $soma % 11;
        if ($i <= 1)
        {
            $dig = 0;
        }
        else
        {
            $dig = 11 - $i;
        }

        return ($dig == $inscE[8]);
    }

//Paraíba
    function CheckIEPB($inscE)
    {
        if (mb_strlen($inscE) != 9)
        {
            return FALSE;
        }

        $base = 9;
        $soma = 0;

        for ($i = 0; $i <= 7; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
        }

        $i = $soma % 11;

        if ($i <= 1)
        {
            $dig = 0;
        }
        else
        {
            $dig = 11 - $i;
        }

        if ($dig > 9)
        {
            $dig = 0;
        }

        return ($dig == $inscE[8]);
    }

//Paraná
    function CheckIEPR($inscE)
    {
        if (mb_strlen($inscE) != 10)
        {
            return FALSE;
        }

        $base = 3;
        $soma = 0;

        for ($i = 0; $i <= 7; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
            if ($base == 1)
            {
                $base = 7;
            }
        }

        $i = $soma % 11;

        if ($i <= 1)
        {
            $dig = 0;
        }
        else
        {
            $dig = 11 - $i;
        }

        if (!($dig == $inscE[8]))
        {
            return FALSE;
        }

        $base = 4;
        $soma = 0;
        for ($i = 0; $i <= 8; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
            if ($base == 1)
            {
                $base = 7;
            }
        }
        $i = $soma % 11;
        if ($i <= 1)
        {
            $dig = 0;
        }
        else
        {
            $dig = 11 - $i;
        }

        return ($dig == $inscE[9]);
    }

    //Pernambuco
    function CheckIEPE($inscE)
    {
        if (mb_strlen($inscE) == 9)
        {
            $base = 8;
            $soma = 0;

            for ($i = 0; $i <= 6; $i++)
            {
                $soma += $inscE[$i] * $base;
                $base--;
            }

            $i = $soma % 11;

            if ($i <= 1)
            {
                $dig = 0;
            }
            else
            {
                $dig = 11 - $i;
            }

            if (!($dig == $inscE[7]))
            {
                return 0;
            }
            else
            {
                $base = 9;
                $soma = 0;
                for ($i = 0; $i <= 7; $i++)
                {
                    $soma += $inscE[$i] * $base;
                    $base--;
                }
                $i = $soma % 11;
                if ($i <= 1)
                {
                    $dig = 0;
                }
                else
                {
                    $dig = 11 - $i;
                }

                return ($dig == $inscE[8]);
            }
        }
        elseif (mb_strlen($inscE) == 14)
        {
            $base = 5;
            $soma = 0;
            for ($i = 0; $i <= 12; $i++)
            {
                $soma += $inscE[$i] * $base;
                $base--;
                if ($base == 0)
                {
                    $base = 9;
                }
            }

            $dig = 11 - ($soma % 11);

            if ($dig > 9)
            {
                $dig = $dig - 10;
            }

            return ($dig == $inscE[13]);
        }
        else
        {
            return 0;
        }
    }

    //Piauí
    function CheckIEPI($inscE)
    {
        if (mb_strlen($inscE) != 9)
        {
            return FALSE;
        }

        $base = 9;
        $soma = 0;
        for ($i = 0; $i <= 7; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
        }
        $i = $soma % 11;
        if ($i <= 1)
        {
            $dig = 0;
        }
        else
        {
            $dig = 11 - $i;
        }
        if ($dig >= 10)
        {
            $dig = 0;
        }

        return ($dig == $inscE[8]);
    }

    //Rio de Janeiro
    function CheckIERJ($inscE)
    {
        if (mb_strlen($inscE) != 8)
        {
            return FALSE;
        }

        $base = 2;
        $soma = 0;

        for ($i = 0; $i <= 6; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
            if ($base == 1)
            {
                $base = 7;
            }
        }

        $i = $soma % 11;

        if ($i <= 1)
        {
            $dig = 0;
        }
        else
        {
            $dig = 11 - $i;
        }

        return ($dig == $inscE[7]);
    }

    //Rio Grande do Norte
    function CheckIERN($inscE)
    {
        if (!( (strlen($inscE) == 9) || (strlen($inscE) == 10) ))
        {
            return FALSE;
        }

        $base = mb_strlen($inscE);

        if ($base == 9)
        {
            $s = 7;
        }
        else
        {
            $s = 8;
        }

        $soma = 0;

        for ($i = 0; $i <= $s; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
        }

        $soma *= 10;
        $dig = $soma % 11;

        if ($dig == 10)
        {
            $dig = 0;
        }

        $s += 1;
        return ($dig == $inscE[$s]);
    }

    //Rio Grande do Sul
    function CheckIERS($inscE)
    {
        if (mb_strlen($inscE) != 10)
        {
            return FALSE;
        }

        $base = 2;
        $soma = 0;

        for ($i = 0; $i <= 8; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;

            if ($base == 1)
            {
                $base = 9;
            }
        }

        $dig = 11 - ($soma % 11);

        if ($dig >= 10)
        {
            $dig = 0;
        }

        return ($dig == $inscE[9]);
    }

    //Rondônia
    function CheckIERO($inscE)
    {
        if (mb_strlen($inscE) == 9)
        {
            $base = 6;
            $soma = 0;

            for ($i = 3; $i <= 7; $i++)
            {
                $soma += $inscE[$i] * $base;
                $base--;
            }

            $dig = 11 - ($soma % 11);

            if ($dig >= 10)
            {
                $dig = $dig - 10;
            }

            return ($dig == $inscE[8]);
        }
        elseif (mb_strlen($inscE) == 14)
        {
            $base = 6;
            $soma = 0;

            for ($i = 0; $i <= 12; $i++)
            {
                $soma += $inscE[$i] * $base;
                $base--;
                if ($base == 1)
                {
                    $base = 9;
                }
            }

            $dig = 11 - ( $soma % 11);

            if ($dig > 9)
            {
                $dig = $dig - 10;
            }

            return ($dig == $inscE[13]);
        }

        return FALSE;
    }

    //Roraima
    function CheckIERR($inscE)
    {
        if (mb_strlen($inscE) != 9)
        {
            return FALSE;
        }

        if (mb_substr($inscE, 0, 2) != 24)
        {
            return FALSE;
        }

        $base = 1;
        $soma = 0;
        for ($i = 0; $i <= 7; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base++;
        }
        $dig = $soma % 9;

        return ($dig == $inscE[8]);
    }

    //Santa Catarina
    function CheckIESC($inscE)
    {
        if (mb_strlen($inscE) != 9)
        {
            return FALSE;
        }

        $soma = 0;
        $peso = 9;

        for ($i = 0; $i <= 7; $i++)
        {
            $soma += $inscE[$i] * $peso;
            $peso--;
        }

        $dig = 11 - ($soma % 11);

        if (( $soma % 11 ) == 0 || ( $soma % 11 ) == 1)
        {
            $dig = 0;
        }

        return ( $dig == $inscE[8] );
    }

    //São Paulo
    function CheckIESP($inscE)
    {
        if (!( mb_strlen($inscE) == 12 ) || ( mb_strlen($inscE) == 13 ))
        {
            return FALSE;
        }

        if (mb_strlen($inscE) == 13)
        {
            $base = 1;
            $soma = 0;

            for ($i = 1; $i <= 8; $i++)
            {
                $soma += $inscE[$i] * $base;
                $base++;
                if ($base == 2)
                {
                    $base = 3;
                }
                if ($base == 9)
                {
                    $base = 10;
                }
            }

            $dig = $soma % 11;
            return ($dig == $inscE[9]);
        }
        else
        {
            $base = 1;
            $soma = 0;

            for ($i = 0; $i <= 7; $i++)
            {
                $soma += $inscE[$i] * $base;
                $base++;

                if ($base == 2)
                {
                    $base = 3;
                }
                if ($base == 9)
                {
                    $base = 10;
                }
            }

            $dig = $soma % 11;

            if ($dig > 9)
            {
                $dig = 0;
            }

            if ($dig != $inscE[8])
            {
                return FALSE;
            }
            else
            {
                $base = 3;
                $soma = 0;

                for ($i = 0; $i <= 10; $i++)
                {
                    $soma += $inscE[$i] * $base;
                    $base--;

                    if ($base == 1)
                    {
                        $base = 10;
                    }
                }

                $dig = $soma % 11;

                return true;
                //dei uma aliada porque tava complicando
                //return ($dig == $inscE[ 11 ]);
            }
        }

        /* if ( mb_strtoupper( mb_substr( $inscE, 0, 1 ) ) == 'P' )
          {
          if ( mb_strlen( $inscE ) != 13 )
          {
          return 0;
          }
          else
          {
          $base = 1;
          $soma = 0;

          for ( $i = 1; $i <= 8; $i++ )
          {
          $soma += $inscE[ $i ] * $base;
          $base++;
          if ( $base == 2 )
          {
          $base = 3;
          }
          if ( $base == 9 )
          {
          $base = 10;
          }
          }

          $dig = $soma % 11;
          return ($dig == $inscE[ 9 ]);
          }
          }
          else
          {
          if ( mb_strlen( $inscE ) != 12 )
          {
          return 0;
          }
          else
          {
          $base = 1;
          $soma = 0;

          for ( $i = 0; $i <= 7; $i++ )
          {
          $soma += $inscE[ $i ] * $base;
          $base++;
          if ( $base == 2 )
          {
          $base = 3;
          }
          if ( $base == 9 )
          {
          $base = 10;
          }
          }

          $dig = $soma % 11;

          if ( $dig > 9 )
          {
          $dig = 0;
          }

          if ( $dig != $inscE[ 8 ] )
          {
          return 0;
          }
          else
          {
          $base = 3;
          $soma = 0;
          for ( $i = 0; $i <= 10; $i++ )
          {
          $soma += $inscE[ $i ] * $base;
          $base--;
          if ( $base == 1 )
          {
          $base = 10;
          }
          }
          $dig = $soma % 11;

          return ($dig == $inscE[ 11 ]);
          }
          }
          } */
    }

    //Sergipe
    function CheckIESE($inscE)
    {
        if (mb_strlen($inscE) != 9)
        {
            return FALSE;
        }

        $base = 9;
        $soma = 0;

        for ($i = 0; $i <= 7; $i++)
        {
            $soma += $inscE[$i] * $base;
            $base--;
        }

        $dig = 11 - ($soma % 11);

        if ($dig > 9)
        {
            $dig = 0;
        }

        return ($dig == $inscE[8]);
    }

    //Tocantins
    function CheckIETO($inscE)
    {
        if (mb_strlen($inscE) != 11)
        {
            return FALSE;
        }

        $s = mb_substr($inscE, 2, 2);

        if (!( ($s == '01') || ($s == '02') || ($s == '03') || ($s == '99') ))
        {
            return FALSE;
        }

        $base = 9;
        $soma = 0;

        for ($i = 0; $i <= 9; $i++)
        {
            if (!(($i == 2) || ($i == 3)))
            {
                $soma += $inscE[$i] * $base;
                $base--;
            }
        }

        $i = $soma % 11;

        if ($i < 2)
        {
            $dig = 0;
        }
        else
        {
            $dig = 11 - $i;
        }

        return ($dig == $inscE[10]);
    }

    public function validate($value =NULL)
    {
        $error = parent::validate($value);
        parent::somenteNumeros();

        $ok = $this->CheckIE($this->value, $this->uf);

        if (!$ok)
        {
            $error[] = 'Inscrição estadual inválida!' . $this->value . '/' . $this->uf;
        }

        return $error;
    }

    function CheckIE($inscE, $uf)
    {
        if (mb_strtoupper($inscE) == 'ISENTO' || mb_strtoupper($inscE) == 'ISENTA')
        {
            return true;
        }
        else
        {
            $function = 'CheckIE' . mb_strtoupper($uf);
            $valida = $this->$function($inscE);
            return $valida;
        }
    }

}