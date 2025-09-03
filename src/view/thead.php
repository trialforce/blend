<?php

namespace View;

/**
 * Html table head element
 */
class THead extends \View\View
{

    public function __construct( $id = NULL, $innerHtml = NULL, $class = NULL )
    {
        $innerHtml = self::arrayToTh( $innerHtml );
        parent::__construct( 'thead', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

    /**
     * Convert some text in innertHtml to th
     *
     * @param \View\Td $innerHtml
     * @return \View\View
     * @throws \Exception
     */
    public static function arrayToTh( $innerHtml )
    {
        if ( is_array( $innerHtml ) && count( $innerHtml ) > 0 )
        {
            if ( $innerHtml[ 0 ] instanceof \View\Tr )
            {
                return $innerHtml;
            }

            foreach ( $innerHtml as $value => $text )
            {
                if ( is_scalar( $text ) )
                {
                    $innerHtml[ $value ] = new \View\Th( \NULL, $text );
                }
                else if ( $text instanceof \View\Th )
                {
                    $innerHtml[ $value ] = $text;
                }
            }

            return new \View\Tr( NULL, $innerHtml );
        }

        return $innerHtml;
    }

}
