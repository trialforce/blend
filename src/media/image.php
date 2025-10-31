<?php

namespace Media;

/**
 * Image
 * It's a wrapper for image magic and gd.
 */
class Image extends \Disk\File
{

    /**
     * Png
     */
    const EXT_PNG = 'png';

    /**
     * Jpeg
     */
    const EXT_JPEG = 'jpeg';

    /**
     * Jpg
     */
    const EXT_JPG = 'jpg';

    /**
     * Gif
     */
    const EXT_GIF = 'gif';

    /**
     * ICO image extension
     */
    const EXT_ICO = 'ico';

    /**
     * Google WebP
     */
    const EXT_WEBP = 'webp';

    /**
     * Vectorial image SVG
     */
    const EXT_SVG = 'svg';

    /**
     * Cache for size of the image (getimagesizes)
     *
     * @var array
     */
    protected $sizes;

    /**
     * Construct the images
     * @param string $path
     * @param bool $load
     */
    public function __construct($path = NULL, $load = FALSE)
    {
        parent::__construct($path, $load);
    }

    /**
     * @return \GdImage|Resource
     */
    public function getContent()
    {
        return parent::getContent();
    }

    /**
     * Construct a new blank image
     *
     * @param boolean $trueColor
     * @param int $width
     * @param int $height
     * @return \Media\Image
     */
    public static function create($trueColor, $width, $height)
    {
        $image = new Image();

        if ($trueColor)
        {
            $content = imagecreatetruecolor($width, $height);
        }
        else
        {
            $content = imagecreate($width, $height);
        }

        $image->setContent($content);

        return $image;
    }

    /**
     * Load the image according his extension
     *
     * @return \Media\Image
     * @throws \Exception
     */
    public function load()
    {
        //if loaded or file don't exist exit
        if ($this->content || !$this->exists())
        {
            return $this;
        }

        $extension = $this->getExtension();

        //SVG extension we do manually
        if ($extension == Image::EXT_SVG)
        {
            $this->content = parent::load();
        }
        else if ($extension == Image::EXT_PNG)
        {
            $this->content = imagecreatefrompng($this->path);

            //avoid some warning
            if ($this->content instanceof \GdImage)
            {
                imagesavealpha($this->content, true);
            }
        }
        else if ($extension == Image::EXT_JPEG || $extension == Image::EXT_JPG)
        {
            $this->content = @imagecreatefromjpeg($this->path);

            @$this->fixOrientation();
        }
        else if ($extension == Image::EXT_GIF)
        {
            $this->content = imagecreatefromgif($this->path);
        }
        else if ($extension == Image::EXT_WEBP)
        {
            $this->content = imagecreatefromwebp($this->path);
        }

        return $this;
    }

    /**
     * Fixes image 'fake' orientation in jpg files produced by mobile phone cameras.
     * This solution uses GD.
     */
    public function fixOrientation()
    {
        if (!$this->path)
        {
            return;
        }

        $info = [];

        // check if APP1 exists
        $this->sizes = getimagesize($this->path, $info);

        $exif = [];

        if (function_exists('exif_read_data') && isset($info['APP1']))
        {
            $exif = @exif_read_data($this->path, 'EXIF');
        }

        if (!empty($exif['Orientation']))
        {
            switch ($exif['Orientation'])
            {
                case 3:
                    $this->content = imagerotate($this->content, 180, 0);
                    break;

                case 6:
                    $this->content = imagerotate($this->content, -90, 0);
                    break;

                case 8:
                    $this->content = imagerotate($this->content, 90, 0);
                    break;
            }
        }
    }

    /**
     * Define the path of the image
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->sizes = NULL;
        $this->content = NULL;
        parent::setPath($path);
    }

    /**
     * Get the width
     *
     * @return int
     */
    public function getWidth()
    {
        if ($this->content)
        {
            return imagesx($this->content);
        }

        $imageSize = $this->getSizes();
        return $imageSize['width'];
    }

    /**
     * Get the height
     *
     * @return int
     */
    public function getHeight()
    {
        if ($this->content)
        {
            return imagesy($this->content);
        }

        $imageSize = $this->getSizes();
        return $imageSize['height'];
    }

    public function getType()
    {
        $imageSize = $this->getSizes();
        return $imageSize[2];
    }

    public function getBits()
    {
        $imageSize = $this->getSizes();
        return $imageSize['bits'];
    }

    public function getChannels()
    {
        $imageSize = $this->getSizes();
        return $imageSize['channels'];
    }

    public function getMime()
    {
        $imageSize = $this->getSizes();
        return $imageSize['mime'];
    }

    /**
     * imageistruecolor — Finds whether an image is a truecolor image
     *
     * @return bool if the image is truecolor, FALSE otherwise.
     * @throws \Exception
     */
    public function isTrueColor()
    {
        $this->load();

        if (!$this->content)
        {
            return false;
        }

        return imageistruecolor($this->content);
    }

    /**
     * Output a image to the specified filename
     *
     * @param \Media\Image|string $filename The path to save the file to. If not set or NULL, the raw image stream will be outputted directly.
     * @param int $quality 0 to 100
     * @throws \Exception
     */
    public function export($filename, $quality = 100)
    {
        if (is_string($filename))
        {
            $filename = new \Media\Image($filename);
        }

        if (!$filename)
        {
            throw new \UserException('You need to inform a filename!');
        }

        if (!$this->content)
        {
            $this->load();

            if (!$this->content)
            {
                throw new \UserException('Image ' . $this->getPath() . ' without content!');
            }
        }

        $extension = $filename->getExtension();

        if ($extension == Image::EXT_ICO)
        {
            $pngQuality = round(abs(($quality - 100) / 11.111111));

            imageico($this->content, $filename . '', $pngQuality);
        }
        else if ($extension == Image::EXT_PNG)
        {
            $width = $this->getWidth();
            $height = $this->getHeight();
            //create image
            $dimg = imagecreatetruecolor($width, $height);

            // integer representation of the color black (rgb: 0,0,0)
            $background = imagecolorallocate($dimg, 0, 0, 0);
            // removing the black from the placeholder
            imagecolortransparent($dimg, $background);

            // turning off alpha blending (to ensure alpha channel information
            // is preserved, rather than removed (blending with the rest of the
            // image in the form of black))
            imagealphablending($dimg, false);

            // turning on alpha channel information saving (to ensure the full range
            // of transparency is preserved)
            imagesavealpha($dimg, true);

            imagecopy($dimg, $this->content, 0, 0, 0, 0, $width, $height);

            $pngQuality = round(abs(($quality - 100) / 11.111111));

            imagepng($dimg, $filename . '', $pngQuality);
        }
        else if ($extension == Image::EXT_JPEG || $extension == Image::EXT_JPG)
        {
            //put white background
            $width = $this->getWidth();
            $height = $this->getHeight();
            //create image
            $output = imagecreatetruecolor($width, $height);
            //put white color
            $white = imagecolorallocate($output, 255, 255, 255);
            //fill background rectangle
            imagefilledrectangle($output, 0, 0, $width, $height, $white);
            //copy the image to new
            imagecopy($output, $this->content, 0, 0, 0, 0, $width, $height);

            //if (!is_writable(dirname($filename . '')))
            //{
            //throw new \UserException('Sem permissões para escrever em ' . $filename . '');
            //}
            //export the new generate image
            imagejpeg($output, $filename . '', $quality);

            if (\DataHandle\Config::get('optimizeJpeg'))
            {
                self::optimizeJpg($filename);
            }
        }
        else if ($extension == Image::EXT_WEBP)
        {
            // Before creating an image in .webp format, needs to convert file to RGB
            // webp does not support palletes
            imagepalettetotruecolor($this->content);

            imagewebp($this->content, $filename . '', $quality);
        }

        return $this;
    }

    /**
     * Optimize jpg using shell exec
     *
     * @param string $filename
     * @return int bits optmized
     */
    public static function optimizeJpg($filename)
    {
        $optmizeJpg = \DataHandle\Config::get('optimizeJpeg');

        if ($optmizeJpg == 'jpegtran')
        {
            //$cmd = 'jpegtran -copy none -optimize -outfile ' . $filename . ' ' . $filename;
            $cmd = 'jpegoptim -f -s -m56 --all-progressive ' . $filename;
            shell_exec($cmd);

            return 1;
        }

        return 0;
    }

    /**
     * imagealphablending() allows for two different modes of drawing on truecolor images.
     * In blending mode, the alpha channel component of the color supplied
     * to all drawing function, such as imagesetpixel()
     * determines how much of the underlying color should be allowed
     * to shine through.
     * As a result, gd automatically blends the existing color at that point with the drawing color,
     * and stores the result in the image.
     * The resulting pixel is opaque.
     * In non-blending mode, the drawing color is copied literally with its
     * alpha channel information, replacing the destination pixel.
     * Blending mode is not available when drawing on palette images.
     *
     * @param boolean $blendmode Whether to enable the blending mode or not. On true color images the default value is TRUE otherwise the default value is FALSE
     */
    public function alphaBlending($blendmode)
    {
        imagealphablending($this->content, $blendmode);

        return $this;
    }

    /**
     * imagesavealpha() sets the flag to attempt to save full alpha channel
     * information (as opposed to single-color transparency) when saving PNG images.
     * You have to unset alphablending (imagealphablending($im, false)), to use it.
     *
     * Alpha channel is not supported by all browsers, if you have problem with
     * your browser, try to load your script with an alpha channel compliant
     * browser, e.g. latest Mozilla.
     *
     * @param boolean $saveflag Whether to save the alpha channel or not. Default to FALSE.
     * @return bool
     */
    public function saveAlpha($saveflag)
    {
        return imagesavealpha($this->content, $saveflag);
    }

    /**
     * Allocate a color for an image
     *
     * imagecolorallocatealpha() behaves identically to imagecolorallocate()
     * with the addition of the transparency parameter alpha.
     *
     * http://php.net/manual/en/function.imagecolorallocate.php
     *
     * @param Color $color
     * @return false|int
     */
    public function allocateColor(Color $color)
    {
        return imagecolorallocatealpha($this->content, $color->getRed(), $color->getGreen(), $color->getBlue(), $color->getAlphaGd());
    }

    /**
     * Color transparent
     *
     * @param int $colorIndex
     */
    public function colorTransparent($colorIndex)
    {
        return imagecolortransparent($this->content, $colorIndex);
    }

    /**
     * Performs a flood fill starting at the given coordinate
     * (top left is 0, 0) with the given color in the image.
     *
     * @param int $x x-coordinate of start point.
     * @param int $y y-coordinate of start point.
     * @param int $colorIdentifier
     *
     */
    public function fill($x, $y, $colorIdentifier)
    {
        imagefill($this->content, $x, $y, $colorIdentifier);
    }

    /**
     *
     * imagetruecolortopalette() converts a truecolor image to a palette image.
     * The code for this function was originally drawn from the Independent
     * JPEG Group library code, which is excellent.
     * The code has been modified to preserve as much alpha channel
     * information as possible in the resulting palette,
     * in addition to preserving colors as well as possible.
     * This does not work as well as might be hoped.
     * It is usually best to simply produce a truecolor output image
     * instead, which guarantees the highest output quality
     * @param bool $dither Indicates if the image should be dithered - if it is TRUE then dithering will be used which will result in a more speckled image but with better color approximation.
     * @param bool $ncolors Sets the maximum number of colors that should be retained in the palette.
     *
     * http://php.net/manual/en/function.imagetruecolortopalette.php
     *
     */
    public function toPalette($dither = false, $ncolors = 255)
    {
        imagetruecolortopalette($this->content, $dither, $ncolors);

        return $this;
    }

    /**
     *
     * Gets the width and height as an associative array.
     * Works great with or without load the image.
     *
     * http://php.net/manual/en/function.getimagesize.php
     * http://www.php.net/manual/en/imagick.getimagegeometry.php
     *
     * @return array
     */
    public function getSizes()
    {
        //make cache
        if (!$this->sizes)
        {
            $this->sizes['width'] = null;
            $this->sizes['height'] = null;

            if (file_exists($this->path))
            {
                //works without load the image (read from path)
                $this->sizes = getimagesize($this->path);

                //padroniz format with image magick
                if (isset($this->sizes[0]) && isset($this->sizes[1]))
                {
                    $this->sizes['width'] = $this->sizes[0];
                    $this->sizes['height'] = $this->sizes[1];
                }
            }
        }

        return $this->sizes;
    }

    /**
     * Resize the image
     * TODO support percent %
     *
     * From: http://www.white-hat-web-design.co.uk/blog/resizing-images-with-php/
     *
     * @param int $width can be null
     * @param int $height can be null
     * @throws \Exception
     */
    function resize($width = NULL, $height = NULL)
    {
        //load if not make yet
        $this->load();

        if ($this->getWidth() == 0 || $this->getHeight() == 0)
        {
            return $this;
        }

        if (!$width && !$height)
        {
            $width = $this->getWidth();
            $height = $this->getHeight();
        }
        if (!$width && $height)
        {
            $ratio = $height / $this->getHeight();
            $width = $this->getWidth() * $ratio;
        }
        else if (!$height && is_numeric($width))
        {
            $ratio = $width / $this->getWidth();
            $height = $this->getHeight() * $ratio;
        }

        $this->copyresampled(0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());

        return $this;
    }

    /**
     * Resize the image to fit a square, adding white letterboxes where needed
     *
     * @param int $maxWidth
     * @throws \Exception
     */
    function toSquare($maxWidth = null)
    {
        $this->load();

        if ($this->getWidth() == 0 || $this->getHeight() == 0)
        {
            return $this;
        }

        $width = $this->getWidth();
        $height = $this->getHeight();

        if ($height > $width)
        {
            $ratio = $maxWidth / $height;
            $x = $width * $ratio;
            $y = $maxWidth;
            $pos_x = round(($maxWidth - $x) / 2);
            $pos_y = 0;
        }
        else
        {
            $ratio = $maxWidth / $width;
            $x = $maxWidth;
            $y = $height * $ratio;
            $pos_x = 0;
            $pos_y = round(($maxWidth - $y) / 2);
        }

        $thumb = imagecreate($maxWidth, $maxWidth);
        imagepalettetotruecolor($thumb);

        $color = $this->allocateColor($this->getColorAt(0, 0));
        imagefill($thumb, 0, 0, $color);
        imagecopyresampled($thumb, $this->content, intval($pos_x), intval($pos_y), 0, 0, intval($x), intval($y), $width, $height);

        $this->content = $thumb;

        return $this;
    }

    public function copyresampled($dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH)
    {
        $dstW = intval($dstW);
        $dstH = intval($dstH);

        if ($dstW == 0 || $dstH == 0)
        {
            return $this;
        }

        //support transparent png
        if ($this->isTrueColor())
        {
            $thumb = imagecreatetruecolor($dstW, $dstH);
        }
        else
        {
            $thumb = imagecreate($dstW, $dstH);
        }

        //get out when don't find things
        if (!$thumb || !$this->content)
        {
            return null;
        }

        imagealphablending($thumb, false);
        $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
        imagefill($thumb, 0, 0, $transparent);
        imagesavealpha($thumb, true);
        imagealphablending($thumb, true);

        imagecopyresampled($thumb, $this->content, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
        $this->content = $thumb;

        return $this;
    }

    /**
     * Crop an image automatically using one of the available modes
     *
     * @param int $mode
     * @param $threshold
     * @param $color
     * @return \Media\Image;
     * @throws \Exception
     */
    public function cropAuto($mode = IMG_CROP_SIDES, $threshold = 0.5, $color = -1)
    {
        $this->load();
        $image = imagecropauto($this->getContent(), $mode, $threshold, $color);

        if ($image)
        {
            $this->setContent($image);
        }
        else
        {
            throw new \Exception('Impossível cortar automaticamente a imagem!');
        }

        return $this;
    }

    /**
     * Faz o crop da imagem
     * @param $x
     * @param $y
     * @param $width
     * @param $height
     * @return $this
     * @throws \Exception
     */
    public function crop($x, $y, $width, $height)
    {
        $array = ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height];
        $image = imagecrop($this->getContent(), $array);

        if ($image)
        {
            $this->setContent($image);
        }
        else
        {
            throw new \Exception('Impossível fazer crop da imagem.');
        }

        return $this;
    }

    /**
     * List supported image types
     *
     * @return array
     */
    public static function listImagesTypes()
    {
        $exts[] = self::EXT_JPEG;
        $exts[] = self::EXT_JPG;
        $exts[] = self::EXT_GIF;
        $exts[] = self::EXT_PNG;
        $exts[] = self::EXT_WEBP;

        return $exts;
    }

    /**
     * Return the filename extension which represents the byte data.
     *
     * @param string $data bytecode
     * @return string
     */
    public static function getImageExtension($data)
    {
        $types = array('jpg' => "\xFF\xD8\xFF", 'gif' => 'GIF', 'png' => "\x89\x50\x4e\x47\x0d\x0a", 'bmp' => 'BM', 'psd' => '8BPS', 'swf' => 'FWS');
        $bytes = \Type\Text::get($data)->sub(0, 8)->getValue();
        $found = 'other';

        foreach ($types as $type => $header)
        {
            if (strpos($bytes, $header) === 0)
            {
                $found = $type;
                break;
            }
        }

        return $found;
    }

    public static function createFromBytes($bytes)
    {
        $gd = imagecreatefromstring($bytes);

        $image = new \Media\Image(null);
        $image->setContent($gd);

        return $image;
    }

    public static function createFromBase64($base64)
    {
        $bytes = base64_decode($base64);

        return \Media\Image::createFromBytes($bytes);
    }

    /**
     * Verify if some extension is supported
     *
     * @param string $format
     * @return boolean
     */
    public function isExtensionSupported($format)
    {
        return in_array($format, self::listImagesTypes());
    }

    /**
     * Make an output inline
     * @param $disposition
     * @param $request
     * @return $this
     * @throws \Exception
     */
    public function outputInline($disposition = 'inline', $request = NULL)
    {
        if ($request instanceof \DataHandle\Request)
        {
            $width = intval($request->get('w'));
            $height = intval($request->get('h'));

            if ($width || $height)
            {
                $this->resize($width, $height);
            }
        }

        parent::outputInline($disposition, $request);
        return $this;
    }

    protected function outputBrowser()
    {
        $this->load();
        $extension = $this->getExtension();

        if (!$this->content)
        {
            return null;
        }

        if ($extension == Image::EXT_ICO)
        {
            imageico($this->content);
        }
        else if ($extension == Image::EXT_PNG)
        {
            imagepng($this->content);
        }
        else if ($extension == Image::EXT_JPEG || $extension == Image::EXT_JPG)
        {
            imagejpeg($this->content);
        }
        else if ($extension == Image::EXT_WEBP)
        {
            imagewebp($this->content);
        }
        else
        {
            readfile($this->path);
        }
    }

    /**
     * Return the image as base64 string
     * @return string
     * @throws \Exception
     */
    public function toBase64()
    {
        $this->load();
        ob_start();
        $this->outputBrowser();
        $imageData = ob_get_contents();
        ob_end_clean();

        if ($imageData)
        {
            return 'data:' . $this->getMimeType() . ';base64,' . base64_encode($imageData);
        }

        return '';
    }

    /**
     * Destroy the image resource
     */
    public function __destruct()
    {
        if ($this->content && is_resource($this->content))
        {
            imagedestroy($this->content);
        }
    }

    /**
     * Get the RGBA color at informed pixel
     * @param int $x x
     * @param int $y y
     * @return \Media\Color
     */
    public function getColorAt($x, $y)
    {
        $rgba = imagecolorsforindex($this->getContent(), imagecolorat($this->getContent(), $x, $y));
        return \Media\Color::fromRGBArray($rgba);
    }

    /**
     * Set the RGBA color at informed pixel
     *
     * @param int $x x
     * @param int $y y
     * @param \Media\Color $color color
     * @return $this \Media\Image
     */
    public function setColorAt($x, $y, \Media\Color $color)
    {
        $this->setPixel($x, $y, $this->allocateColor($color));

        return $this;
    }

    public function setPixel($x, $y, $allocate)
    {
        return imagesetpixel($this->getContent(), $x, $y, $allocate);
    }

    private function resizeWatermark($watermark, $type = "width")
    {
        list($wWidth, $wHeight) = $watermark->getSizes();

        list($pWidth, $pHeight) = $this->getSizes();
        $pWidth = ($pWidth * 0.9);
        $pHeight = ($pHeight * 0.9);

        $media = ($pWidth / $wWidth);

        if ($type != 'width')
        {
            $media = ($pHeight / $wHeight);
        }

        $width = $wWidth * $media;
        $height = $wHeight * $media;

        if ($pHeight < $height)
        {
            return $this->resizeWatermark($watermark, 'height');
        }
        else
        {
            $thumb = imagecreatetruecolor($width, $height);
            imagecolortransparent($thumb, imagecolorallocate($thumb, 255, 255, 255));
            imagecolortransparent($thumb, imagecolorallocate($thumb, 0, 0, 0));

            imagecopyresized($thumb, $watermark->getContent(), 0, 0, 0, 0, $width, $height, $watermark->getWidth(), $watermark->getHeight());

            $watermark->setContent($thumb);

            return $watermark;
        }
    }

    public function addWatermark($urlWatermark, $opacity = 30)
    {
        if (!$this->content)
        {
            $this->load();
        }

        $watermark = new \Media\Image($urlWatermark, true);

        if (!$watermark->getContent())
        {
            return;
        }

        imagefilter($watermark->getContent(), IMG_FILTER_BRIGHTNESS, 30);
        imagefilter($watermark->getContent(), IMG_FILTER_GRAYSCALE);

        $watermark = $this->resizeWatermark($watermark);

        $top = ($this->getHeight() - $watermark->getHeight()) / 2;
        $left = ($this->getWidth() - $watermark->getWidth()) / 2;

        ImageCopyMerge($this->getContent(), $watermark->getContent(), $left, $top, 0, 0, $watermark->getWidth(), $watermark->getHeight(), $opacity);

        $this->export($this->getPath());
    }

    /**
     * Remove the background color
     * It's a very simple aprouch, very dummy
     *
     * @return $this
     */
    public function removeBackground()
    {
        $image = $this->getContent();
        imagealphablending($image, false);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);
        imagesavealpha($image, true);
        imagealphablending($image, true);

        return $this;
    }

    /**
     * Return the base64 string representating the currente image/file
     * @return string
     * @throws \Exception
     */
    public function getBase64()
    {
        //caso tenha sido carrega, pega a versão na memória
        if ($this->isLoaded())
        {
            ob_start();

            if ($this->getExtension() == self::EXT_PNG )
            {
                imagepng($this->getContent());
            }
            else if ($this->getExtension() == self::EXT_JPG || $this->getExtension() == self::EXT_JPEG)
            {
                imagejpeg($this->getContent());
            }
            else if ($this->getExtension() == self::EXT_WEBP)
            {
                imagewebp($this->getContent());
            }
            else if ($this->getExtension() == self::EXT_ICO)
            {
                imageico($this->getContent());
            }

            return base64_encode(ob_get_clean());
        }

        $byteArray = file_get_contents($this->getPath());
        $encode = base64_encode($byteArray);
        return $encode;
    }

    /**
     * This static function converts a image from a file to an SVG
     * With a desired with.
     * To be true, it only generate a SVG/XML with the embebed image inside
     * IT does not do trace or anything
     *
     * @param string $filePath
     * @param int $width
     * @return string
     * @throws \Exception
     */
    public static function imageToSVG(string $filePath, int $width)
    {
        $image = new \Media\Image($filePath);

        if ($width == 0) //keep
        {
            $width = $image->getWidth();
        }

        $extension = $image->getExtension();
        $ratio = $width / $image->getWidth();
        $height = intval($image->getHeight() * $ratio);
        $image->load();
        $base64 = $image->getBase64();

        $svgString = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg
   width="'.$width.'"
   height="'.$height.'"
   viewBox="0 0 '.$width.' '.$height.'"
   version="1.1"
   xmlns:xlink="http://www.w3.org/1999/xlink"
   xmlns="http://www.w3.org/2000/svg"
   >
	<image
       width="'.$width.'"
       height="'.$height.'"
       preserveAspectRatio="none"
       xlink:href="data:image/'.$extension.';base64,'.$base64.'"
       x="0"
       y="0"
	   />
</svg>';

        return $svgString;
    }

}

/**
 * Output an ICO image to either the standard output or a file.
 *
 * It takes the same arguments as 'imagepng' from the GD library. Works by
 * creating a ICO container with a single PNG image.
 * This type of ICO image is supported since Windows Vista and by all major
 * browsers.
 *
 * https://en.wikipedia.org/wiki/ICO_(file_format)#PNG_format
 *
 * Take from
 */
function imageico($image, $filename = null, $quality = 9, $filters = PNG_NO_FILTER)
{
    $x = imagesx($image);
    $y = imagesy($image);

    if ($x > 256 || $y > 256)
    {
        trigger_error('ICO images cannot be larger than 256 pixels wide/tall', E_USER_WARNING);
        return;
    }

    // Collect PNG data.
    ob_start();
    imagesavealpha($image, true);
    imagepng($image, null, $quality, $filters);
    $png_data = ob_get_clean();

    // Write ICO header, image entry and PNG data.
    $content = pack('v3', 0, 1, 1);
    $content .= pack('C4v2V2', $x, $y, 0, 0, 1, 32, strlen($png_data), 22);
    $content .= $png_data;

    // Output to file.
    if ($filename)
    {
        file_put_contents($filename, $content);
    }
    else
    {
        echo $content;
    }
}
