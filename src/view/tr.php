<?php

namespace View;

/**
 * Tr html element
 */
class Tr extends \View\View
{

    public function __construct( $id = NULL, $innerHtml = NULL, $class = NULL )
    {
        $innerHtml = self::arrayToTd( $innerHtml );
        parent::__construct( 'tr', $id, $innerHtml, $class );
    }

    /**
     * Convert some text in innertHtml to td
     *
     * @param \View\Td $innerHtml
     * @return \View\Td
     */
    public static function arrayToTd( $innerHtml )
    {
        if ( is_array( $innerHtml ) && count( $innerHtml ) > 0 )
        {
            foreach ( $innerHtml as $value => $text )
            {
                $isThTd = $text instanceof \View\Th || $text instanceof \View\Td;
                $isView = $text instanceof \View\View;

                if ( (isset( $text ) && is_scalar( $text ) ) || ($isView && !$isThTd) )
                {
                    $innerHtml[ $value ] = new \View\Td( \NULL, $text );
                }
                else if ( $isThTd )
                {
                    $innerHtml[ $value ] = $text;
                }
            }
        }

        return $innerHtml;
    }

}
