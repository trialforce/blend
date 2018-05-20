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

    public function setWordCount( $wordCount )
    {
        $this->wordCount = $wordCount;

        return $this;
    }

    public function validate( $value )
    {
        $error = parent::validate( $value );

        $words = explode( ' ', $this->value );

        if ( count( $words ) < $this->wordCount )
        {
            $error[] = 'É necessário preencher ao menos ' . $this->wordCount . ' palavras.';
        }

        return $error;
    }

}
