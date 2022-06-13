<?php

namespace Validator;

/**
 * Valida a quantidade de palavras
 */
class Words extends \Validator\Validator
{

    protected $wordCount = 2;

    public function getWordCount()
    {
        return $this->wordCount;
    }

    public function setWordCount($wordCount)
    {
        $this->wordCount = $wordCount;

        return $this;
    }

    public function validate($value = null)
    {
        if ($value)
        {
            $this->setValue($value);
        }

        $error = parent::validate($this->value);
        $words = explode(' ', $this->value);

        if (count($words) < $this->wordCount)
        {
            $error[] = 'É necessário preencher ao menos ' . $this->wordCount . ' palavras.';
        }

        return $error;
    }

}
