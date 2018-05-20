<?php

namespace View;

/**
 * Convert a XPath instruction to a CSS
 */
class XPathToCss
{

    public static function convert( $cssSelectors )
    {
        $cssSelectors = explode( ',', $cssSelectors );

        foreach ( $cssSelectors as $cssSelector )
        {
            $cssSelector = trim( $cssSelector );

            $result = preg_replace( '/([a-zA-Z-_]*)#([a-zA-Z-_]*)/i', '//$1[@id = \'$2\']', $cssSelector );
            //support simple selector
            $result = str_replace( '//[@id =', '//*[@id =', $result );

            if ( $result == $cssSelector )
            {
                $result = preg_replace( '/([a-zA-Z-_]*)\.([a-zA-Z-_]*)/i', '//$1[@class and contains(concat(\' \', normalize-space(@class), \' \'), \' $2 \')]', $cssSelector );
                //support simple selector
                $result = str_replace( '//[@class and', '//*[@class and', $result );
                //*[@class and contains(concat(' ', normalize-space(@class), ' '), ' formTitle ')]
            }

            if ( $result == $cssSelector )
            {
                $result = '//' . $result;
            }

            // div#example a => //div[@id='example']//a
            $newSelector[] = $result;
        }

        $selector = implode( ' | ', $newSelector );

        return $selector;
    }

}
