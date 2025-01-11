<?php

namespace Media;

/**
 * Represents a simple color
 */
class Color implements \Type\Generic
{

    /**
     * Red part of the color
     * @var int|null
     */
    protected int|null $red = null;

    /**
     * Green part of the color
     *
     * @var int|null
     */
    protected int|null $green = null;

    /**
     * Blue part of the color
     * @var int|null
     */
    protected int|null $blue = null;

    /**
     * Alpha part of the color
     * @var int|null
     */
    protected int|null $alpha = null;

    /**
     * Create the color by passing a value
     * If the first value is passed, and green, blue a alpha are de default ones it consider as hex value
     *
     * @param $red
     * @param $green
     * @param $blue
     * @param $alpha
     */
    public function __construct($red = NULL, $green = NULL, $blue = NULl, $alpha = 255)
    {
        if ($red && !$green && !$blue && $alpha == 255)
        {
            $this->setHex($red);
        }
        else
        {
            $this->setColor($red, $green, $blue, $alpha);
        }
    }

    /**
     * Define the red portion of the color
     *
     * @param int $red
     * @return \Media\Color
     */
    public function setRed($red)
    {

        if ($red === null || $red === '')
        {
            $this->red = null;
        }
        else
        {
            $this->red = intval(trim($red));
            $this->red = min($this->red, 255);
            $this->red = max($this->red, 0);
        }

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
        if ($green === null || $green === '')
        {
            $this->green = null;
        }
        else
        {
            $this->green = intval(trim($green));
            $this->green = min($this->green, 255);
            $this->green = max($this->green, 0);
        }

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
        if ($blue === null || $blue === '')
        {
            $this->blue = null;
        }
        else
        {
            $this->blue = intval(trim($blue));
            $this->blue = min($this->blue, 255);
            $this->blue = max($this->blue, 0);
        }

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
        if ($alpha === null || $alpha === '')
        {
            $this->alpha = null;
        }
        else
        {
            $this->alpha = intval(trim($alpha));
        }

        return $this;
    }

    /**
     * Return the alpha portion of color
     * @return int
     */
    public function getAlpha()
    {
        return $this->alpha;
    }

    /**
     * Return the alpha converted to 0-127 scale of PHP GD
     * @return int
     */
    public function getAlphaGd()
    {
        if ($this->getAlpha() == 255)
        {
            return 0;
        }

        return 127 - ($this->getAlpha() / 255 * 127);
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

        //for a complete null color, even passing alpha 255 (that is the default value)
        if ($this->getRed() === null && $this->getGreen() === null && $this->getBlue() === null)
        {
            $alpha = null;
        }

        if ($alpha)
        {
            $this->setAlpha($alpha);
        }

        return $this;
    }

    /**
     * Add "light" to the color generating a brighter color
     * @param int $light the light amount to add to the color
     * @return $this
     */
    public function lighter($light = 0)
    {
        $this->setRed($this->getRed() + $light);
        $this->setGreen($this->getGreen() + $light);
        $this->setBlue($this->getBlue() + $light);

        return $this;
    }

    /**
     * Add "shadow" to the color generating a darker color
     * @param int  $shadow the shadow amount to add to the color
     * @return $this
     */
    public function darker($shadow = 0)
    {
        $this->setRed($this->getRed() - $shadow);
        $this->setGreen($this->getGreen() - $shadow);
        $this->setBlue($this->getBlue() - $shadow);

        return $this;
    }

    /**
     * Convert this color to nearest gray
     *
     * @return $this
     */
    public function toGray()
    {
        $gray = ($this->getRed() + $this->getGreen() + $this->getBlue()) / 3;
        $this->setRed($gray);
        $this->setGreen($gray);
        $this->setBlue($gray);

        return $this;
    }

    /**
     * Based on  http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
     * @param string $hex
     * @return $this
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
            else
            {
                $this->setAlpha(255);
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
            else
            {
                $this->setAlpha(255);
            }
        }

        return $this;
    }

    /**
     * Return default CSS color USING RGB or RGBA (when need)
     * @return string
     */
    public function getCssColor()
    {
        if ($this->getAlpha())
        {
            return "RGB({$this->getRed()},{$this->getGreen()},{$this->getBlue()},{$this->getAlpha()});";
        }
        else
        {
            return "RGB({$this->getRed()},{$this->getGreen()},{$this->getBlue()});";
        }
    }

    /**
     * Return the hexadecimal description of color
     *
     * @return string
     */
    public function getHex()
    {
        if ($this->getRed() === null && $this->getBlue() === null && $this->getBlue() === null)
        {
            return null;
        }

        return sprintf("#%02x%02x%02x", $this->getRed(), $this->getGreen(), $this->getBlue());
    }

    /**
     * Convert RGB to HSV
     *
     * RGB values:    0-255, 0-255, 0-255
     * HSV values:    0-360, 0-100, 0-100
     * @return \stdClass
     */
    public function getHSV()
    {
        // Convert the RGB byte-values to percentages
        $R = ($this->getRed() / 255);
        $G = ($this->getGreen() / 255);
        $B = ($this->getBlue() / 255);

        // Calculate a few basic values, the maximum value of R,G,B, the
        // minimum value, and the difference of the two (chroma).
        $maxRGB = max($R, $G, $B);
        $minRGB = min($R, $G, $B);
        $chroma = $maxRGB - $minRGB;

        // Value (also called Brightness) is the easiest component to calculate,
        //   and is simply the highest value among the R,G,B components.
        // We multiply by 100 to turn the decimal into a readable percent value.
        $computedV = 100 * $maxRGB;

        // Special case if hueless (equal parts RGB make black, white, or grays)
        // Note that Hue is technically undefined when chroma is zero, as
        // attempting to calculate it would cause division by zero (see
        // below), so most applications simply substitute a Hue of zero.
        // Saturation will always be zero in this case, see below for details.
        if ($chroma == 0)
        {
            $hsv = new \stdClass();
            $hsv->h = 0;
            $hsv->s = 0;
            $hsv->v = intval($computedV);

            return $hsv;
        }

        // Saturation is also simple to compute, and is simply the chroma
        // over the Value (or Brightness)
        // Again, multiplied by 100 to get a percentage.
        $computedS = 100 * ($chroma / $maxRGB);

        // Calculate Hue component
        // Hue is calculated on the "chromacity plane", which is represented
        // as a 2D hexagon, divided into six 60-degree sectors. We calculate
        // the bisecting angle as a value 0 <= x < 6, that represents which
        // portion of which sector the line falls on.
        if ($R == $minRGB)
        {
            $h = 3 - (($G - $B) / $chroma);
        }
        elseif ($B == $minRGB)
        {
            $h = 1 - (($R - $G) / $chroma);
        }
        else // $G == $minRGB
        {
            $h = 5 - (($B - $R) / $chroma);
        }

        // After we have the sector position, we multiply it by the size of
        // each sector's arc (60 degrees) to obtain the angle in degrees.
        $computedH = 60 * $h;

        $hsv = new \stdClass();
        $hsv->h = intval($computedH);
        $hsv->s = intval($computedS);
        $hsv->v = intval($computedV);

        return $hsv;
    }

    /**
     * Return the string representation of color, that is a CSS string
     * @return string the css version of the color
     */
    public function __toString()
    {
        return $this->getCssColor();
    }

    /**
     * Create a RGB color based on hex value
     * @param string $hex hexadecinal color information
     * @return \Media\Color
     */
    public static function fromHex($hex)
    {
        $color = new \Media\Color();
        return $color->setHex($hex);
    }

    /**
     * Return a color based on a simple array from PHP imagecolorsforindex
     * @param array $array array with, red, green, blue, alpha
     * @return \Media\Color
     */
    public static function fromRGBArray($array)
    {
        $alphaGd = $array['alpha'];
        $alphaPhp = 255;

        if ($alphaGd > 0)
        {
            $alphaPhp = 255 - ( $alphaGd / 127) * 255;
        }

        return new \Media\Color($array['red'], $array['green'], $array['blue'], $alphaPhp);
    }

    public function setValue($value)
    {
        $this->setHex($value);
    }

    public function getValue()
    {
        return $this->getHex();
    }

    public function toHuman()
    {
        return $this->getHex();
    }

    public function toDb()
    {
        return $this->getHex();
    }

    public static function get($value)
    {
        return \Media\Color::fromHex($value);
    }

    public static function value($value)
    {
        return \Media\Color::fromHex($value)->getValue();
    }

    /**
     * Return default transparent color
     *
     * @return \Media\Color
     */
    public static function transparent()
    {
        return new \Media\Color(0, 0, 0, 127);
    }

    /**
     * Return default white color
     *
     * @return \Media\Color
     */
    public static function white()
    {
        return new \Media\Color(255, 255, 255);
    }

    /**
     * Return default red color
     *
     * @return \Media\Color
     */
    public static function red()
    {
        return new \Media\Color(255, 0, 0);
    }

    /**
     * Return default blue
     *
     * @return \Media\Color
     */
    public static function blue()
    {
        return new \Media\Color(0, 0, 255);
    }

    /**
     * Return default green color
     *
     * @return \Media\Color
     */
    public static function green()
    {
        return new \Media\Color(0, 255, 0);
    }

    /**
     * Return a simple random color
     *
     * @return \Media\Color
     */
    public static function rand()
    {
        return new \Media\Color(rand(0, 255), rand(0, 255), rand(0, 255));
    }
}

/*
TO futuro unit test
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
