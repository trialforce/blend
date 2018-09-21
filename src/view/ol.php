<?php

namespace View;

/**
 * An ordered HTML list.
 * Html ol element
 *
 */
class Ol extends \View\View
{

    public function __construct($id = NULL, $innerHtml = NULL, $class = NULL)
    {
        $innerHtml = self::arrayToLi($innerHtml);
        parent::__construct('ol', \NULL, $innerHtml, $class);
        $this->setId($id);
    }

    /**
     * Convert some text in innertHtml to td
     *
     * @param \View\Td $innerHtml
     * @return \View\Td
     */
    public static function arrayToLi($innerHtml)
    {
        if (is_array($innerHtml))
        {
            foreach ($innerHtml as $value => $text)
            {
                if (is_scalar($text))
                {
                    $innerHtml[$value] = new \View\Li(\NULL, $text);
                }
            }
        }

        return $innerHtml;
    }

}
