<?php

namespace Misc;

/**
 * Handle Geo Coords
 */
class Geo
{

    /**
     * Converts DMS ( Degrees / minutes / seconds )
     * to decimal format longitude / latitude
     *
     * @param float $deg
     * @param float $min
     * @param float $sec
     * @return float
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
     * @param float $dec
     * @return string
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

    /**
     * Return the distance between 2 geo coordinates.
     * Result in kilometers
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return int
     */
    public static function distanceLatLong($lat1, $lon1, $lat2, $lon2)
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2))
        {
            return 0;
        }
        else
        {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            return ($miles * 1.609344);
        }
    }

}
