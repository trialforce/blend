<?php

namespace Type;

/**
 * Classe to deal with String
 *
 * */
class Text implements \Type\Generic, \JsonSerializable
{

    /**
     * String of the text
     *
     * @var string
     */
    private $string;

    public function __construct($string = null)
    {
        //little control to avoid error in stdClass
        if ($string instanceof \stdClass)
        {
            $string = null;
        }

        //convert to string
        $this->setValue($string . '');
    }

    /**
     * Define a string
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->string = $value;
    }

    /**
     * Adiciona algum texto a string.
     *
     * Passa pela função de conversão para garantir a string esteja na codificação utilizada.
     *
     * @param string $string texto a ser adicionado
     */
    public function append($string)
    {
        $this->string .= $string;

        return $this;
    }

    /**
     * Troca um contéudo por outro, na string atual.
     * Além disso retorna a nova string
     *
     * @param string|string[] $search conteúdo original, a buscar
     * @param string|string[] $replace novo conteúdo a subistituir
     * @return $this
     */
    public function replace($search, $replace)
    {
        $this->string = str_replace($search, $replace, $this->string);

        return $this;
    }

    /**
     * Converte o texto para minusculas
     *
     * @return $this
     */
    public function toLower()
    {
        $this->string = mb_strtolower($this->string);

        return $this;
    }

    /**
     * Converte o texto para maisculas
     *
     * @return \Type\Text
     */
    public function toUpper()
    {
        $this->string = mb_strtoupper($this->string);

        return $this;
    }

    /**
     * Retorna o caracter solicitado pelo parametro index
     *
     * @param integer $index indice do caracter a obter
     * @return string retorna o caracter solicitado
     */
    public function charAt($index)
    {
        return $this->string[$index];
    }

    /**
     * Obtém a string
     *
     * @return string
     */
    public function getValue()
    {
        return $this->string;
    }

    /**
     * Retorna o tamnho da string
     *
     * @return int string length
     */
    public function length()
    {
        return mb_strlen($this->getValue());
    }

    /**
     * Remove os espaços no inicio e fim do texto
     *
     * @return $this
     */
    public function trim()
    {
        $this->string = trim($this->string);

        return $this;
    }

    /**
     * Converte a string para caracteres ASCII.
     * Retira acentos e outros caracteres especificos.
     *
     * @return $this
     */
    public function toASCII()
    {
        $this->trim(); //remove espaços
        $content = $this->string;
        $content = mb_ereg_replace("[ÁÀÂÃÄ]", "A", $content);
        $content = mb_ereg_replace("[áàâãä]", "a", $content);
        $content = mb_ereg_replace("[ÉÈÊË]", "E", $content);
        $content = mb_ereg_replace("[éèêë]", "e", $content);
        $content = mb_ereg_replace("[ÍÌÎÏ]", "I", $content);
        $content = mb_ereg_replace("[íìîï]", "i", $content);
        $content = mb_ereg_replace("[ÓÒÔÕÖ]", "O", $content);
        $content = mb_ereg_replace("[óòôõö]", "o", $content);
        $content = mb_ereg_replace("[ÚÙÛÜ]", "U", $content);
        $content = mb_ereg_replace("[úùûü]", "u", $content);
        $content = mb_ereg_replace("[Ñ]", "N", $content);
        $content = mb_ereg_replace("[ñ]", "n", $content);
        $content = mb_ereg_replace("[Ç]", "C", $content);
        $content = mb_ereg_replace("[ç]", "c", $content);
        $content = mb_ereg_replace("[´‘`^~¨]", "", $content);
        $content = str_replace("²", "2", $content);
        $content = str_replace("³", "3", $content);
        $content = str_replace('º', 'o', $content);
        $content = str_replace('•', 'o', $content);
        $content = str_replace('°', 'o', $content);
        $content = str_replace('ª', 'a', $content);
        $content = str_replace('&', 'e', $content);
        $content = str_replace('\'', '', $content);
        $content = str_replace('®', '', $content);
        $content = str_replace('<', '', $content);
        $content = str_replace('>', '', $content);
        $content = str_replace('™', '', $content);

        $this->string = $content;

        return $this;
    }

    /**
     * To file name
     *
     * @return \Type\Text
     */
    public function toFile($space = '_')
    {
        $space = $space ?: '_';
        // replace invisible space characters
        $this->string = str_replace("\xc2\xa0", $space, $this->string);
        $this->toASCII();
        $this->toLower();
        $this->replace(' ', $space);
        $this->replace(array(' ', '.', ':', '/', '\\', '\'', '"', '[', ']', '=', '–'), $space);
        $this->replace(array('?', '!', '(', ')', ',', '+', '%', '|', '*', '#', '°', '’', '”', '“'), '');
        //avoid ugly file name with -_-
        $this->replace(array('-', '_'), $space);
        //remove double space
        $this->string = preg_replace('/' . $space . '+/', $space, $this->string);

        $this->string = rtrim($this->string, $space);

        return $this;
    }

    /**
     * Corta a string de um ponto inicial, considerando ou não um tamanho
     *
     * @param integer $start posição inicial
     * @param integer $length quantidade de caracteres até o corte / tamanho
     * @return \Type\Text
     */
    public function sub($start, $length)
    {
        $this->string = mb_substr($this->string, $start, $length);

        return $this;
    }

    /**
     * Explode a string retornando um array
     *
     * @param string $delimiter delimitador
     * @return array array com a string explodida
     */
    public function explode($delimiter)
    {
        return explode($delimiter, $this->string);
    }

    /**
     * Se a string for maior que o $size (tamanho passado)
     * corta-a para o tamanho do $size e coloca $ellipsisText ao fim.
     *
     * @param int $size o tamanho que a string ficará.
     * @param string $ellipsisText o texto que será colocado ao final da string para dar o efeito de ellipsis (...)
     * @return \Type\Text Retorna a string com o tamanho do $size se ela for maior que o parametro passado.
     */
    public function ellipsis($size, $ellipsisText = '...')
    {
        if ($this->length() > $size)
        {
            //Calcula (tamanho da string) - (tamanho texto do ellipsis) + $ellipsisText
            $this->string = $this->sub(0, $size - self::get($ellipsisText)->length()) . $ellipsisText;
        }

        return $this;
    }

    /**
     * Limit text to given size, trying to limit on a phrase end point.
     *
     * @param int $size the maximum number of characters
     * @return \Type\Text
     */
    public function phraseWrap($size)
    {
        if ($this->length() > $size)
        {
            $aux = $this->sub(0, $size)->getValue();

            $endPoint = strrpos($aux, '.');

            if ($endPoint === false)
            {
                // search for the last space
                $endPoint = strrpos($aux, ' ');
            }
            else
            {
                // the endpoint '.' stays
                $endPoint++;
            }

            if ($endPoint !== false)
            {
                // if there is no break in string, don't wrap further
                $this->string = $this->sub(0, $endPoint)->getValue();
            }
        }

        return $this;
    }

    /**
     * Verify if string starts with some text
     *
     * @param string $beginString
     * @return boolean
     */
    public function beginsWith($beginString)
    {
        if (!$beginString)
        {
            return false;
        }

        return (strpos($this->string . '', $beginString) === 0);
    }

    public function startWith($beginString)
    {
        return $this->beginsWith($beginString);
    }

    public function endsWith($endString)
    {
        if (!$endString)
        {
            return false;
        }

        return substr($this->string, -strlen($endString)) == $endString;
    }

    /**
     * Valida se determinado texto existe dentro da string.
     *
     * @param string $needle texto a ser encontrado dentro da string.
     * @return boolean
     */
    public function contains($needle)
    {
        return ( stripos($this->string, $needle) !== FALSE );
    }

    /**
     * Like method similar to Sql but, in php
     *
     * FROM https://stackoverflow.com/questions/11434305/simulating-like-in-php
     *
     * @param string $needle needle
     * @return bool if matches
     */
    public function like($needle)
    {
        $delimiter = '~';
        // Escape meta-characters from the string so that they don't gain special significance in the regex
        $needle = preg_quote($needle, $delimiter);

        // Replace SQL wildcards with regex wildcards
        $needle = str_replace('%', '.*?', $needle);
        $needle = str_replace('_', '.', $needle);

        // Add delimiters, beginning + end of line and modifiers
        $needle = $delimiter . '^' . $needle . '$' . $delimiter . 'isu';

        // Matches are not useful in this case; we just need to know whether or not the needle was found.
        return (bool) preg_match($needle, $this->string);
    }

    /**
     * Remove new lines
     *
     * @return \Type\Text
     */
    public function removeNL()
    {
        $this->string = trim(preg_replace('@[\s]{2,}@', ' ', $this->string));

        return $this;
    }

    /**
     * Padronize line ending
     *
     * @return \Type\Text
     */
    public function padronizeLineEnding()
    {
        $this->string = str_replace("\r\n", 'NEW_LINE', $this->string);
        $this->string = str_replace("\r\n", 'NEW_LINE', $this->string);
        $this->string = str_replace(array("\r", "\n"), 'NEW_LINE', $this->string);
        $this->string = str_replace(array("\r", "\n"), 'NEW_LINE', $this->string);
        $this->string = str_replace('NEW_LINE', "\r\n", $this->string);

        return $this;
    }

    /**
     * Remove double spaces (white spaces)
     *
     * @return \Type\Text
     */
    public function removeDoubleSpaces()
    {
        $this->string = trim(preg_replace('/[[:blank:]]+/', ' ', $this->string));

        return $this;
    }

    /**
     * Verify if some text has html
     *
     * @return boolean
     */
    public function isHtml()
    {
        if (strlen($this->string) <= 3)
        {
            return false;
        }

        if ($this->string != strip_tags($this->string))
        {
            return true;
        }

        return false;
    }

    /**
     * Get a simple text and convert it to html
     *
     * @return \Type\Text
     */
    public function toHtml($verify = true)
    {
        if ($verify && $this->isHtml())
        {
            return $this;
        }

        $this->padronizeLineEnding();

        //make links clicable
        $this->string = preg_replace(
                "/(?<!a href=\")(?<!src=\")((http|ftp)+(s)?:\/\/[^<>\s]+)/i", "<a href=\"\\0\" target=\"blank\">\\0</a>", $this->string
        );

        //new line to br
        $this->string = nl2br($this->string);

        return $this;
    }

    public function __toString()
    {
        return $this->string . '';
    }

    public function toHuman()
    {
        return $this->__toString();
    }

    public function toDb()
    {
        return $this->__toString();
    }

    public function jsonSerialize():mixed
    {
        return $this->toDb();
    }

    /**
     * Contrutor estático usado para que possa se utilizar
     * o construtor e chamar a função necessária na mesma linha.
     *
     * @param string $value
     * @return \Type\Text
     *
     * @example Text::get( $string )->toLower() = retorna a string em formato de usuário
     */
    public static function get($value = NULL)
    {
        return new Text($value);
    }

    /**
     * Return the value
     *
     * @param string $value
     * @return string
     */
    public static function value($value)
    {
        return \Type\Text::get($value)->getValue();
    }

    /**
     * Random string with defined lenght
     *
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public static function rand($length = 10)
    {
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;

        for ($i = 0; $i < $length; ++$i)
        {
            $str .= $keyspace[random_int(0, $max)];
        }

        return $str;
    }

    /**
     * Verify if the string is UTF8
     *
     * @param string $string
     * @return boolean
     */
    public static function isUTF8($string)
    {
        return preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )+%xs', $string);
    }

    /**
     * Verifica se a string é da codificação passada
     *
     * @param string $string
     * @param string $enc
     * @return boolean
     */
    public static function checkEncoding($string, $enc)
    {
        return \Type\Text::detectEncoding($string) == $enc;
    }

    /**
     * Retorna a codifificação da string
     *
     * @param string $string
     * @return string retorna a codifificação da string
     */
    public static function detectEncoding($string)
    {
        $encList = array('UTF-8', 'ISO-8859-1');

        foreach ($encList as $enc)
        {
            if ($enc == 'UTF-8')
            {
                if (iconv('ISO-8859-1//IGNORE', 'UTF-8//IGNORE', iconv('UTF-8//IGNORE', 'ISO-8859-1//IGNORE', $string)) === $string)
                {
                    return 'UTF-8';
                }
            }
            else
            {
                if (iconv('UTF-8', $enc, iconv($enc, 'UTF-8', $string)) === $string)
                {
                    return $enc;
                }
            }
        }

        return '';
    }

    /**
     * Tryng to convert a word do singular
     *
     * Only works with portuguese
     *
     * @param string $str the plural word
     * @return string the singular version of this word
     */
    public static function toSingular($str)
    {
        if (substr($str, (strlen($str) - 3)) == 'oes')
        {
            $str = substr($str, 0, (strlen($str) - 3)) . 'ao';
        }

        if (substr($str, (strlen($str) - 3)) == 'aes')
        {
            $str = substr($str, 0, (strlen($str) - 3)) . 'ao';
        }

        if (substr($str, (strlen($str) - 3)) == 'eis')
        {
            $str = substr($str, 0, (strlen($str) - 3)) . 'el';
        }

        if (substr($str, (strlen($str) - 1)) == 's')
        {
            $str = substr($str, 0, (strlen($str) - 1));
        }

        return $str;
    }

    /**
     * Return a list of stop/exception words, that can be ignorated in searchs
     * and replace
     *
     * Only works with portuguese
     *
     * @return array of string
     */
    public static function listExceptionWords()
    {
        $exception = ["\r\n", "\n", "\r", ' - ', ' " ', " ' ", " / ", ' • ', ' : ', '::', ' % ', ' ª ', ' º ', ' de ', ' da ', ' do ', ' ne ', ' na ', ' no ', ' des ', ' das ', ' dos ', ' nes ', ' nas ',
            ' nos ', ' es ', ' mais ', ' menos ', ' com ', ' como ', ' Os ', ' online ', ' uma ', ' seus ', ' que ', ' não ', ' tem ', ' tm ', ' ser ', ' estão ', ' to ', ' já ', ' a ', ' e ', ' i ',
            ' x ', ' o ', ' u ', ' ou ', ' as ', ' para ', ' como ', ' À ', ' Á ', ' Ã ', ' Â ', ' à ', ' á ', ' ã ', ' â ', ' Ê ', ' É ', ' Í ', ' í ', ' Ó ', ' Õ ', ' Ô ', ' ó ', ' õ ', ' ô ', ' Ú ', ' Ü ', ' Ç ',
            ' ao ', ' aos ', ' ç ', ' é ', ' ê ', ' ú ', ' ü ', ' os ', ' sem ', ' em ', ' sua ', ' deve ', ' as ', ' um ', ' n&atilde;o '];

        return $exception;
    }

    /**
     * This small helper function generates RFC 4122 compliant Version 4 UUIDs.
     * https://www.uuidgenerator.net/dev-corner/php
     *
     * @param string $data
     * @return string uuid with 36 characters
     * @throws \Exception
     */
    public static function generateUUID($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

}
