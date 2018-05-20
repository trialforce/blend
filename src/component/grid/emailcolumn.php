<?php

namespace Component\Grid;

/**
 * Email column
 */
class EmailColumn extends Column
{

    public function getValue( $item, $line = NULL, \View\View $tr = NULL, \View\View $td = NULL )
    {
        $value = parent::getValue( $item, $line );
        $url = $value;

        if ( stripos( $value, '@' ) > 0 )
        {
            $url = 'mailto:' . $value;
        }
        else if ( is_numeric( $value ) )
        {
            $url = 'tel:' . $value;
        }
        else if ( $value )
        {
            $url = self::fixUrl( $value );
        }

        return new \View\A( 'edit', $value, $url, NULL, \View\A::TARGET_BLANK );
    }

    /**
     * Fix url
     *
     * @param string $url
     * @return string
     */
    public static function fixUrl( $url )
    {
        if ( !preg_match( "@^https?://@i", $url ) && !preg_match( "@^ftps?://@i", $url ) )
        {
            $url = 'http://' . $url;
        }

        return $url;
    }

}
