<?php

namespace Validator;

/**
 * Validate word count. Usefull for name validation.
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

        preg_match_all('/\p{L}+/u', $this->value, $matches);

        if (count($matches[0]) < $this->wordCount)
        {
            $error[] = 'É necessário preencher ao menos ' . $this->wordCount . ' palavras.';
        }

        return $error;
    }

}
