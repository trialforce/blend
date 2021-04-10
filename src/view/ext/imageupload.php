<?php

namespace View\Ext;

class ImageUpload extends \View\Div
{

    public $imgResult;

    /**
     * Image href
     *
     * @var string
     */
    protected $href;

    /**
     *
     * @var bool
     */
    protected static $cropCreated = false;

    public function __construct($id, $href, $phpFunction = 'fileUpload', $accept = 'image/*', $class = 'fileUpload', $removeImageFunction = 'removeImage')
    {
        parent::__construct($id, NULL, $class);
        $removeImageFunction = $removeImageFunction ? $removeImageFunction : 'removeImage';

        $this->href = $href;
        $pageUrl = \View\View::getDom()->getPageUrl();

        $upload = new \View\Input('label_' . $id, \View\Input::TYPE_FILE);
        $urlChangeUpload = "fileUpload('{$pageUrl}/{$phpFunction}/?idUpload={$id}');";
        $upload->change($urlChangeUpload);

        $accept = $accept ? $accept : 'image/*';
        $upload->setAttribute('accept', $accept);

        $upload->hide();
        $this->appendChild($upload);

        if ($href)
        {
            $dom = \View\View::getDom();
            $url = '';

            if ($dom instanceof \Page\Page)
            {
                $url = $dom->getPageUrl();
            }

            $idUrl = !is_null($id) ? '?idUpload=' . $id : '';
            $icon = new Icon('cancel remove');
            $icon->click("return p('{$url}/{$removeImageFunction}{$idUrl}')");
            $this->append($icon);
        }

        $img = self::getImg($href, $id);

        $this->imgResult = new \View\Div('imgResult_' . $id, $img);
        $labelImg = new \View\Label('label_' . $id, null, $this->imgResult);
        //support various navigators
        $labelImg->click("$(this).parent().find('input').click()");

        $this->append($labelImg);

        $this->addJs($urlChangeUpload);
    }

    private function addJs($urlChangeUpload)
    {
        \App::addJs("
    setTimeout( function()
    {
        var dropContainer = document.getElementById('{$this->getId()}');
        var fileInput = document.getElementById('label_{$this->getId()}');

        if (dropContainer && fileInput)
        {
            dropContainer.ondragover = function(evt)
            {
                $('#{$this->getId()}').addClass('drag-over');
                evt.preventDefault();
            }

            dropContainer.ondragenter = function(evt)
            {
                evt.preventDefault();
            };

            dropContainer.ondrop = function(evt)
            {
                $('#{$this->getId()}').removeClass('drag-over');
                // pretty simple -- but not for IE :(
                fileInput.files = evt.dataTransfer.files;

                // If you want to use some of the dropped files
                //const dT = new DataTransfer();
                //dT.items.add(evt.dataTransfer.files[0]);
                //dT.items.add(evt.dataTransfer.files[3]);
                //fileInput.files = dT.files;

                evt.preventDefault();
                {$urlChangeUpload}
            };
        }
    },500);
");
    }

    /**
     * Create crop holder
     * @param int $aspectRatio
     * @return boolean
     */
    public function createCropHolder($aspectRatio = NULL)
    {
        $href = $this->href;
        $cropResponseId = 'holder-' . $this->getId();
        $aspectRatioTxt = $aspectRatio ? ',' . $aspectRatio : '';

        $iconCrop = new Icon('scissors');
        $iconCrop->click("$('#imageHandlerId').val( $(this).parent().find('label').attr('id').replace('label_','') );  return cropCanvas( $(this).parent().find('img').attr('src' ){$aspectRatioTxt} );");
        $this->append($iconCrop);

        if (self::$cropCreated == true)
        {
            return false;
        }

        self::$cropCreated = true;

        self::createCropCanvas();
    }

    public static function createCropCanvas()
    {
        $contentCrop[] = $closeLink = new \View\A('hide-crop-canvas', 'X');
        $closeLink->click('return destroyCropCanvas();');

        $fields[] = new \View\Input('x', \View\Input::TYPE_HIDDEN);
        $fields[] = new \View\Input('y', \View\Input::TYPE_HIDDEN);
        $fields[] = new \View\Input('w', \View\Input::TYPE_HIDDEN);
        $fields[] = new \View\Input('h', \View\Input::TYPE_HIDDEN);

        $fields[] = new \View\Input('imageHandlerId', \View\Input::TYPE_HIDDEN, '');
        $fields[] = new \View\Input('imageHandlerHref', \View\Input::TYPE_HIDDEN, '');

        $fields[] = new \View\Img('crop-image-handler', NULL);

        $fields[] = new \View\Button('btn-crop', 'Cortar', 'cropImage');
        $fields[] = new \View\Button('btn-crop-cancel', 'Cancelar', 'return destroyCropCanvas();');

        $contentCrop[] = $formCrop = new \View\Div('crop-image-holder', $fields);

        $cropCanvas = new \View\Div('crop-canvas', $contentCrop);

        \View\View::getDom()->byId('divLegal')->append($cropCanvas);
    }

    /**
     * Get img view
     *
     * @param string $href
     * @return \View\Img
     */
    public static function getImg($href, $id = NULL)
    {
        $result[] = new \View\Img('image', $href);

        return $result;
    }

}
