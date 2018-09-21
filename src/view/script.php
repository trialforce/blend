<?php

namespace View;

/**
 * Script html element
 */
class Script extends \View\View
{

    const TYPE_JAVASCRIPT = 'text/javascript';

    public function __construct($src, $content = \NULL, $type = self::TYPE_JAVASCRIPT, $async = FALSE)
    {
        parent::__construct('script');
        $this->setAttribute('src', $src);
        $this->setAttribute('type', $type);
        $this->append($content);
        $this->setAsync($async);
    }

    /**
     * This class has an special append, to avoid treat
     * @param type $content
     */
    public function append($content = null)
    {
        if ($content)
        {
            if (is_array($content))
            {
                foreach ($content as $item)
                {
                    $this->append($item);
                }
            }
            else
            {
                if (is_string($content))
                {
                    $this->appendChild(new \DOMText($content));
                }
                else
                {
                    $this->appendChild($content);
                }
            }
        }
    }

    /**
     * Set js async or not
     *
     * @param bool $async
     * @return \View\Script
     */
    public function setAsync($async)
    {
        if ($async)
        {
            return $this->setAttribute('async', 'async');
        }
        else
        {
            return $this->removeAttribute('async');
        }
    }

    /**
     * Define o valor de um campo via javascript
     *
     * @param string $id
     * @param string $value
     * @return Script
     */
    public static function setElementValue($dom, $id, $value)
    {
        return new \View\Script($dom, \NULL, "$('#{$id}').val('{$value}');");
    }

    /**
     * Retorna um alert com o conteúdo passado
     *
     * @param string $content
     * @return Script
     */
    public static function alert($dom, $content)
    {
        $content = self::treatStringToJs($content);
        return new \View\Script($dom, \NULL, "alert('$content');");
    }

    /**
     * Trata uma string para o envio Javascript
     * @param string $var
     * @return string
     */
    public static function treatStringToJs($var)
    {
        $valuesPHP = array(PHP_EOL, "\r", "\n", "\t");
        $valuesJS = array('\n', '\n', '\n', '  ');

        return str_replace($valuesPHP, $valuesJS, addslashes($var));
    }

}
