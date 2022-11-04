<?php

namespace Validator;

/**
 * Universal GTIN/EAN validator.
 * Checks validity of: GTIN-8, GTIN-12, GTIN-13, GTIN-14, GSIN, SSCC.
 * See: http://www.gs1.org
 * Source: https://stackoverflow.com/questions/29076255/how-do-i-validate-a-barcode-number-using-php
 */
class Gtin extends \Validator\Validator
{

    /**
     * @param string $value
     * @return string
     */
    public function validate($value = NULL)
    {
        $barcode = (string) self::unmask($value);

        $error = [];

        if (!$barcode || mb_strlen($barcode) == 0)
        {
            return;
        }

        //check valid lengths:
        $l = strlen($barcode);

        if (!in_array($l, [8, 12, 13, 14, 17, 18]))
        {
            $error[] = 'Tamanho inválido';
            return $error;
        }

        //get check digit
        $check = substr($barcode, -1);

        $barcode = substr($barcode, 0, -1);

        $sumEven = $sumOdd = 0;

        $even = true;

        while (strlen($barcode) > 0)
        {
            $digit = substr($barcode, -1);

            if ($even)
            {
                $sumEven += 3 * $digit;
            }
            else
            {
                $sumOdd += $digit;
            }

            $even = !$even;
            $barcode = substr($barcode, 0, -1);
        }

        $sum = $sumEven + $sumOdd;
        $sumRoundedUp = ceil($sum / 10) * 10;

        $isValid = ($check == ($sumRoundedUp - $sum));

        if (!$isValid)
        {
            $error[] = 'Código GTIN inválido';
        }

        return $error;
    }

}
