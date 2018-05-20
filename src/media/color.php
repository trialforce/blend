<?php

namespace Media;

/**
 * Represents a color
 */
class Color
{

    /**
     * Red part of the color
     *
     * @var int
     *
     */
    protected $red;

    /**
     * Green part of the color
     *
     * @var int
     */
    protected $green;

    /**
     * Blue part of the color
     * @var int
     */
    protected $blue;

    /**
     * Alpha part of the color
     *
     * @var int
     */
    protected $alpha;

    public function __construct($red = NULL, $green = NULL, $blue = NULl, $alpha = 255)
    {
        $this->setColor($red, $green, $blue, $alpha);
    }

    /**
     * Create a color from hex
     *
     * @param string $hex
     *
     * @return \Media\Color
     */
    public static function fromHex($hex)
    {
        $color = new Color();
        return $color->setHex($hex);
    }

    /**
     * Define the red portion of the color
     *
     * @param int $red
     * @return \Media\Color
     */
    public function setRed($red)
    {
        $this->red = intval(trim($red));
        return $this;
    }

    /**
     * Return the red portion of color
     *
     * @return int
     */
    public function getRed()
    {
        return $this->red;
    }

    /**
     * Define the green portion of color
     *
     * @param int $green
     * @return \Media\Color
     */
    public function setGreen($green)
    {
        $this->green = intval(trim($green));
        return $this;
    }

    /**
     * Return the green portion of color
     *
     * @return int
     */
    public function getGreen()
    {
        return $this->green;
    }

    /**
     * Define the green portion of the color
     *
     * @param string $blue
     * @return \Media\Color
     */
    public function setBlue($blue)
    {
        $this->blue = intval(trim($blue));
        return $this;
    }

    /**
     * Reutrn the blue portion of color
     *
     * @return int
     */
    public function getBlue()
    {
        return $this->blue;
    }

    /**
     * Define the alpha portion of color
     *
     * @param int $alpha
     * @return \Media\Color
     */
    public function setAlpha($alpha)
    {
        $this->alpha = intval(trim($alpha));
        return $this;
    }

    /**
     * Return the alpha portion of color
     * @return type
     */
    public function getAlpha()
    {
        return $this->alpha;
    }

    /**
     * Define all portions of color once
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $alpha
     * @return \Media\Color
     */
    public function setColor($red, $green, $blue, $alpha = NULL)
    {
        $this->setRed($red);
        $this->setGreen($green);
        $this->setBlue($blue);

        if ($alpha)
        {
            $this->setAlpha($alpha);
        }

        return $this;
    }

    /**
     * Based on  http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
     * @param type $hex
     * @return type
     */
    public function setHex($hex)
    {
        //remove prefix
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) == 3 || strlen($hex) == 4)
        {
            $this->setRed(hexdec(substr($hex, 0, 1) . substr($hex, 0, 1)));
            $this->setGreen(hexdec(substr($hex, 1, 1) . substr($hex, 1, 1)));
            $this->setBlue(hexdec(substr($hex, 2, 1) . substr($hex, 2, 1)));

            if (strlen($hex) == 4)
            {
                $this->setAlpha(hexdec(substr($hex, 3, 1) . substr($hex, 3, 1)));
            }
        }
        else
        {
            $this->setRed(hexdec(substr($hex, 0, 2)));
            $this->setGreen(hexdec(substr($hex, 2, 2)));
            $this->setBlue(hexdec(substr($hex, 4, 2)));

            if (strlen($hex) == 8)
            {
                $this->setAlpha(hexdec(substr($hex, 6, 2)));
            }
        }

        return $this;
    }

    /**
     * Return default transparent color
     *
     * @return \Color
     */
    public static function transparent()
    {
        return new Color(0, 0, 0, 127);
    }

    /**
     * Return default white color
     *
     * @return \Color
     */
    public static function white()
    {
        return new Color(255, 255, 255);
    }

    /**
     * Return default red color
     *
     * @return \Color
     */
    public static function red()
    {
        return new Color(255, 0, 0);
    }

    /**
     * Return default blue
     *
     * @return \Color
     */
    public static function blue()
    {
        return new Color(0, 0, 255);
    }

    /**
     * Return default green color
     *
     * @return \Color
     */
    public static function green()
    {
        return new Color(0, 255, 0);
    }

}
/*
new Color( 255, 0, 0, 255 ) ;
Color::fromHex( 'f00' ) ;
Color::fromHex( 'f00f' ) ;
Color::fromHex( 'ff0000' ) ;
Color::fromHex( 'ff0000ff' ) ;
Color::red();

//we are all the same
$ok1 = new Color(255,255,255) == new Color(255,255,255,255);
$ok2 = new Color(255,255,255,255) == Color::fromHex('fff');
$ok3 = Color::fromHex('fff') == Color::white();

*/
