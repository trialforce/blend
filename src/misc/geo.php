<?php

namespace Misc;

//TODO migrate to type

/**
 * Handle Geo Coords
 */
class Geo extends \Object
{

    /**
     * Converts DMS ( Degrees / minutes / seconds )
     * to decimal format longitude / latitude
     *
     * @param type $deg
     * @param type $min
     * @param type $sec
     * @return type
     */
    public static function DMStoDEC( $deg, $min, $sec )
    {
        return $deg + ((($min * 60) + ($sec)) / 3600);
    }

    /**
     * Converts decimal longitude / latitude to DMS ( Degrees / minutes / seconds )
     * This is the piece of code which may appear to
     * be inefficient, but to avoid issues with floating
     * point math we extract the integer part and the float
     * part by using a string function.
     *
     * @param type $dec
     * @return type
     */
    public static function DECtoDMS( $dec )
    {
        if ( mb_strlen( $dec ) == 0 )
        {
            return '';
        }

        $vars = explode( ".", $dec );

        if ( count( $vars ) <= 1 )
        {
            return '';
        }

        $deg = $vars[ 0 ];
        $tempma = "0." . $vars[ 1 ];

        $tempma = $tempma * 3600;
        $min = floor( $tempma / 60 );
        $sec = $tempma - ($min * 60);

        return $deg . '° ' . $min . '\' ' . $sec . '"';
    }

}
