<?php

namespace Validator;

/**
 * Validate strong password
 * 
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
        $result = ' É necessário ter  ao menos ' . $this->characters . ' caracteres';

        if ($this->capitalLetter)
        {
            $result .= ', letra maiúscula';
        }

        if ($this->lowerCase)
        {
            $result .= ', letra minúscula';
        }

        if ($this->alphanumeric)
        {
            $result .= ', um número';
        }

        if ($this->specialCharacter)
        {
            $result .= ', caracteres especiais';
        }

        $result .= '.';

        return $result;
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
