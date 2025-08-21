<?php

namespace Component\Grid;

/**
 * Image Column for Grid
 */
class ImageColumn extends \Component\Grid\Column
{

    /**
     * Image source
     *
     * @var string
     */
    protected $source;

    /**
     * Image witdh
     * @var int
     */
    protected $width;

    /**
     * Image height
     * @var int
     */
    protected $height;

    /**
     * Use thumb
     *
     * @var boolean
     */
    protected $useThumb;

    public function __construct($name, $label = \NULL, $align = Column::ALIGN_LEFT, $source = NULL, $width = NULL, $height = '40px')
    {
        parent::__construct($name, $label, $align, \Db\Column\Column::TYPE_VARCHAR);
        $this->setSource($source);
        $this->setWidth($width);
        $this->setHeight($height);
    }

    function getSource()
    {
        return $this->source;
    }

    function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    function getWidth()
    {
        return $this->width;
    }

    function getHeight()
    {
        return $this->height;
    }

    function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    function getUseThumb()
    {
        return $this->useThumb;
    }

    function setUseThumb($useThumb)
    {
        $this->useThumb = $useThumb;
        return $this;
    }

    public function getValue($item, $line = NULL, \View\View $tr = NULL, \View\View $td = NULL)
    {
        $line = \NULL;
        $src = $this->getSource();

        $result = '';

        if ($src instanceof \Disk\File)
        {
            $newSrc = clone($src);
            $path = $this->replaceDataInString($src->getPath(), $item);
            $newSrc->setPath($path);

            if ($newSrc->exists())
            {
                $result = $this->replaceDataInString($newSrc->getUrl(), $item);
            }
        }
        else
        {
            $result = $this->replaceDataInString($this->getSource(), $item);

            //verify if is not a complete link
            if (stripos($result, 'http') !== 0)
            {
                if (!file_exists($result))
                {
                    $result = '';
                }

                //relative link
                if ($result && stripos($result, APP_PATH) >= 0)
                {
                    $host = \DataHandle\Server::getInstance()->getHost();
                    $result = $host . str_replace(APP_PATH, '', $result);
                }
            }
        }

        if ($result)
        {
            $thumb = $result;

            if ($this->getUseThumb())
            {
                $thumb = str_replace('media', 'thumb/s100/', $result);
            }

            $img = new \View\Img(null, $thumb, $this->width);
            $img->css('max-height', $this->height);
            $link = new \View\A(null, $img, $result, null, '_BLANK');

            if ($item instanceof \Db\Model)
            {
                $link->setTitle($item->getOptionLabel());
            }
            else
            {
                $link->setTitle(basename($result));
            }

            return $link;
        }
        else
        {
            return null;
        }
    }

}
