<?php

namespace Type;

/**
 * Classe para lidar com strings
 *
 * Criado originalmente em 01/07/2011 por :
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Na Solis - Cooperativa de Soluções Livres Ltda. e Univates - Centro Universitário.
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
        //convert to string
        $this->setValue($string . '');
    }

    /**
     * Define a string
     *
     * @param $string
     */
    public function setValue($string)
    {
        $this->string = $string;
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
     * @param string $search conteúdo original, a buscar
     * @param string $replace novo conteúdo a subistituir
     * @param string retorna a nova string
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
     * @return String
     */
    public function toLower()
    {
        $this->string = mb_strtolower($this->string);

        return $this;
    }

    /**
     * Converte o texto para maisculas
     *
     * @return String
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
     * @return char retorna o caracter solicitado
     */
    public function charAt($index)
    {
        return $this->string[$index];
    }

    /**
     * Obtém a string
     *
     * @return dia
     */
    public function getValue()
    {
        return $this->string;
    }

    /**
     * Verifica se a string é UTF8
     *
     * @param o $string
     *
     * @internal param \o $string texto a verificar
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

        if (is_array($encList))
        {
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
        }
    }

    /**
     * Retorna o tamnho da string
     *
     * @return tamanho da string
     */
    public function length()
    {
        return mb_strlen($this->getValue());
    }

    /**
     * Remove os espaços no inicio e fim do texto
     *
     * @return String
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
     * @return String
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
        $content = mb_ereg_replace("[´`^~¨]", "", $content);
        $content = str_replace('º', 'o', $content);
        $content = str_replace('ª', 'a', $content);
        $content = str_replace('&', 'e', $content);
        $content = str_replace('\'', '', $content);
        $content = str_replace('®', '', $content);

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
        $space = $space ? $space : '_';
        $this->toASCII();
        $this->toLower();
        $this->replace(' ', $space);
        $this->replace(array(' ', '.', ':', '/', '\\', '\'', '"', '[', ']', '='), $space);
        $this->replace(array('?', '!', '(', ')', ',', '+', '%', '|'), '');
        //avoid ugly file name with -_-
        $this->replace(array('-', '_'), $space);
        //remove double space
        $this->replace($space . $space, $space);
        $this->replace($space . $space, $space);

        return $this;
    }

    /**
     * Corta a string de um ponto inicial, considerando ou não um tamanho
     *
     * @param integer $start posição inicial
     * @param integer $length quantidade de caracteres até o corte / tamanho
     * @return String
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
     * Função chamada automaticamente pelo PHP quando precisa converter objeto para String
     *
     * @return a data no formato do usuário
     */
    public function __toString()
    {
        return $this->string . '';
    }

    public function toHuman()
    {
        return $this->__toString();
    }

    /**
     * Se a string for maior que o $size (tamanho passado)
     * corta-a para o tamanho do $size e coloca $ellipsisText ao fim.
     *
     * @param type $size o tamanho que a string ficará.
     * @param type $ellipsisText o texto que será colocado ao final da string para dar o efeito de ellipsis (...)
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
        return ( strpos($this->string, $needle) !== FALSE );
    }

    /**
     * Contrutor estático usado para que possa se utilizar
     * o construtor e chamar a função necessária na mesma linha.
     *
     * @param string $string
     * @return \Type\Text
     *
     * @example Text::get( $string )->toLower() = retorna a string em formato de usuário
     */
    public static function get($string = NULL)
    {
        return new Text($string);
    }

    /**
     * Return the value
     *
     * @param string $value
     * @return string
     */
    public static function value($value)
    {
        return String::get($value)->getValue();
    }

    public function toDb()
    {
        return $this->__toString();
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
     * @param string $content
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
     * @param string $string
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
     * @param string $string
     * @return \Type\Text
     */
    public function toHtml()
    {
        if (self::isHtml($this->string))
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

    /**
     * Random string with defined lenght
     *
     * @param int $length
     * @return string
     */
    public static function rand($length = 10)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    public function jsonSerialize()
    {
        return $this->toDb();
    }

}

//Create mb_str_pad function if not exists, for old php compatibility
if (!function_exists('mb_str_pad'))
{

    function mb_str_pad($input, $padLength, $padString, $padStyle, $encoding = "UTF-8")
    {
        return str_pad($input, strlen($input) - mb_strlen($input, $encoding) + $padLength, $padString, $padStyle);
    }

}
