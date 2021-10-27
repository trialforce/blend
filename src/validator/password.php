<?php

namespace Validator;

/**
 * Valida a quantidade de palavras
 * https://www.ti-enxame.com/pt/php/como-verificar-se-string-tem-pelo-menos-uma-letra-numero-e-caractere-especial-em-php/942194795/
 */
class Password extends \Validator\Validator
{

    protected $capitalLetter = true;
    protected $lowerCase = true;
    protected $alphanumeric = true;
    protected $specialCharacter = false;
    protected $characters = 8;

    public function getCapitalLetter()
    {
        return $this->capitalLetter;
    }

    public function setCapitalLetter($capitalLetter)
    {
        $this->capitalLetter = $capitalLetter;
        return $this;
    }

    public function getLowerCase()
    {
        return $this->lowerCase;
    }

    public function setLowerCase($lowerCase)
    {
        $this->lowerCase = $lowerCase;
        return $this;
    }

    public function getAlphanumeric()
    {
        return $this->alphanumeric;
    }

    public function setAlphanumeric($alphanumeric)
    {
        $this->alphanumeric = $alphanumeric;
        return $this;
    }

    public function getSpecialCharacter()
    {
        return $this->specialCharacter;
    }

    public function setSpecialCharacter($specialCharacter)
    {
        $this->specialCharacter = $specialCharacter;
        return $this;
    }

    public function getCharacters()
    {
        return $this->characters;
    }

    public function setCharacters($characters)
    {
        $this->characters = $characters;
        return $this;
    }

    public function returnValidate()
    {
        $return = ' É necessário ter ' . $this->characters . ' caracteres<br/>';

        if ($this->capitalLetter)
        {
            $return .= ' é necessário ter uma letra maiúscula<br/>';
        }

        if ($this->lowerCase)
        {
            $return .= ' é necessário ter uma letra minúscula<br/>';
        }

        if ($this->alphanumeric)
        {
            $return .= ' é necessário ter um número<br/>';
        }

        if ($this->specialCharacter)
        {
            $return .= ' é necessário ter caracteres especiais<br/>';
        }

        return $return;
    }

    public function validate($value = null)
    {
        $error = parent::validate($value);

        if (strlen($value) < $this->characters)
        {
            $error[] = $this->returnValidate();
            return $error;
        }

        if ($this->capitalLetter)
        {
            if (!preg_match('/[A-Z]/', $value))
            {
                $error[] = $this->returnValidate();
                return $error;
            }
        }

        if ($this->lowerCase)
        {
            if (!preg_match('/[a-z]/', $value))
            {
                $error[] = $this->returnValidate();
                return $error;
            }
        }

        if ($this->alphanumeric)
        {
            if (!preg_match('/\d/', $value))
            {
                $error[] = $this->returnValidate();
                return $error;
            }
        }

        if ($this->specialCharacter)
        {
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $value))
            {
                $error[] = $this->returnValidate();
                return $error;
            }
        }

        return $error;
    }

}
