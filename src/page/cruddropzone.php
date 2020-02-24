<?php

namespace Page;

use DataHandle\Request;

class CrudDropZone extends \Page\Crud
{

    protected $activeDropzone;

    /**
     * Active dropzone
     *
     * @return int
     */
    public function getActiveDropzone()
    {
        return $this->activeDropzone;
    }

    /**
     * Define if dropzone is actived
     *
     * @param int $activeDropzone
     */
    public function setActiveDropzone($activeDropzone)
    {
        $this->activeDropzone = $activeDropzone;

        return $this;
    }

    /**
     * Construct dropzone pag
     *
     * @param \Db\Model $model
     * @param int $activeDropzone
     */
    public function __construct($model = NULL, $activeDropzone = FALSE)
    {
        $this->setActiveDropzone($activeDropzone);
        parent::__construct($model);
    }

    public function editar()
    {
        $fields = parent::editar();

        if ($this->activeDropzone)
        {
            $this->createDropZone();
            $this->updateImages();
        }

        return $fields;
    }

    public function updateImages()
    {
        if (!$this->isUpdate())
        {
            \App::dontChangeUrl();
        }

        \App::addJs(\View\Blend\Popup::getJs('close'));
        $id = strlen(Request::get('v')) > 0 ? Request::get('v') : $this->getFormValue('id');
        $images = $this->listImages($id);
        $content = NULL;

        if (is_array($images))
        {
            foreach ($images as $item)
            {
                $file = new \Disk\File($item);
                $file->isImage();

                $media = new \Disk\Media($item);
                $thumbFile = $this->getThumbFile($media);

                $img = new \View\Div(NULL, NULL, 'img-back');

                if ($file->isImage())
                {
                    $img->addClass('swipebox');
                    $img->css('background-image', "url('{$thumbFile->getUrl() }')");
                    $img->attr('data-href', $thumbFile->getUrl());
                    $img->attr('title', $thumbFile->getBasename(FALSE));
                }
                else
                {
                    $img->append(new \View\Ext\Icon('file-o'));
                }

                $span = new \View\Span(NULL, $media->getBasename(TRUE));

                $delete = new \View\Ext\Icon('times');
                $delete->click("return p('{$this->getPageUrl()}/deleteImage/?file=" . $media->getPath() . "')");

                $array = array($img, $span, $delete);

                $array[] = $this->getImageExtraButton($item);

                $link = new \View\A('', $array, $media->getUrl(), 'image-container');
                $link->setTarget(\View\A::TARGET_BLANK);
                $link->setTitle($media->getUrl());

                $content[] = $link;
            }
        }

        $this->byId('img-all')->html($content);
        \App::addJs("$('.dz-preview').remove();");
    }

    public function getImageExtraButton($image)
    {
        //not used in this case
        $image = NULL;
    }

    public function deleteImage()
    {
        \App::dontChangeUrl();
        $file = Request::get('file');
        $label = basename($file);

        $confirmationLink = "return p('{$this->getPageUrl()}/deleteImageConfirm/?file=" . $file . "')";
        \View\Blend\Popup::prompt('Confirma remoção de imagem', 'Arquivo: ' . $label, $confirmationLink, \View\Blend\Popup::getJs('destroy'))->setId('deleteConfirm')->show();
    }

    public function deleteImageConfirm()
    {
        \App::dontChangeUrl();
        \View\Blend\Popup::delete('deleteConfirm');

        $file = new \Disk\File(Request::get('file'));
        $thumbFile = $this->getThumbFile($file);

        if ($file->exists() && $file->remove())
        {
            $thumbFile->remove(); //remove thumb
            toast('Arquivo ' . $file->getBasename() . ' excluído com sucesso!');
            $this->updateImages();
        }
        else
        {
            toast('Impossível remover arquivo! Procure administrador!', 'danger');
        }
    }

    public function createDropZone()
    {
        $id = Request::get('v');
        $imgAll = new \View\Div('img-all', NULL, 'img-all');
        new \View\Div('myAwesomeDropzone', $imgAll, 'dropzone clearfix');

        $uploadUrl = $this->getPageUrl() . '/dropUpload/' . $id . '/';
        $pageName = $this->getPageUrl();
        $acceptedFiles = $this->getAcceptFiles() ? $this->getAcceptFiles() : 'image/*';

        $js = "createDropZone( '{$uploadUrl}', '{$acceptedFiles}', '{$pageName}')";
        \App::addJs($js);
    }

    /**
     * Drop zone upload
     */
    public function dropUpload()
    {
        $id = Request::get('v');
        \Disk\Media::createMediaFolderIfNeeded();

        if (!empty($_FILES))
        {
            $tempFile = $_FILES['file']['tmp_name'];

            $targetPath = self::getCompleteFolderName($id);

            if (!file_exists($targetPath))
            {
                mkdir($targetPath, 0777, TRUE);
            }

            $file = new \Disk\File($_FILES['file']['name']);
            $fileName = \Type\Text::get($file->getBasename(FALSE))->toFile('-') . '.' . $file->getExtension();
            $targetFile = new \Disk\File($targetPath . $fileName);

            $ok = move_uploaded_file($tempFile, $targetFile);

            if ($ok && $targetFile->exists())
            {
                if ($targetFile->isImage() && \DataHandle\Config::get('makeThumb'))
                {
                    $thumbFile = $this->getThumbFile($targetFile);
                    $thumbFile->createFolderIfNeeded();

                    $imageObj = new \Media\Image($targetFile->getPath());

                    $width = \DataHandle\Config::getDefault('thumbWidth', 300);
                    $height = \DataHandle\Config::get('thumbHeight');

                    $imageObj->resize($width, $height);
                    $imageObj->export($thumbFile);
                }
            }
            else
            {
                throw new \UserException('Ops! Problema em enviar arquivo!');
            }
        }
    }

    /**
     * Return folder name
     *
     * @return string
     */
    public function getFolderName()
    {
        return $this->getPageUrl();
    }

    public function getAcceptFiles()
    {
        return 'image/*';
    }

    /**
     * List images
     *
     * @param int $id
     * @return array
     */
    public function listImages($id)
    {
        $path = $this->getCompleteFolderName($id);
        return \Disk\File::find($path . DS . '*');
    }

    /**
     *
     * @param int $id
     * @return \Disk\Media
     */
    public function getCompleteFolderName($id)
    {
        return new \Disk\Media($this->getFolderName() . '/' . $id . '/');
    }

    /**
     * Get thumb file
     *
     * @param \Disk\File $file
     * @return \Disk\File
     */
    public function getThumbFile(\Disk\File $file)
    {
        if (\DataHandle\Config::get('makeThumb'))
        {
            return new \Disk\Media($file->getDirname() . '/thumb/' . $file->getBasename(TRUE));
        }
        else
        {
            return $file;
        }
    }

}
