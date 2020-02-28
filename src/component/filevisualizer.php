<?php

namespace Component;

class FileVisualizer extends \Component\Component
{

    /**
     * File
     * @var \Disk\File
     */
    private $file;

    public function __construct($file = null)
    {
        $this->setFile($file);
    }

    public function onCreate()
    {
        if ($this->isCreated())
        {
            return $this->content;
        }

        $holder = new \View\Div(NULL, NULL, 'img-back');
        $thumbFile = $this->getFile();
        $isDir = $this->getFile()->isDir();

        if ($this->getFile()->isImage())
        {
            $holder->css('background-image', "url('{$thumbFile->getUrl() }')");
            $holder->attr('title', $thumbFile->getBasename(FALSE));
        }
        else if ($isDir)
        {
            $myContent = new \View\Ext\Icon('folder', null, null, 'file-visualizer-main-icon');
            $holder->append($myContent);
        }

        if (!$isDir)
        {
            $holder->append(new \View\P(null, $this->getFile()->getExtension(), 'file-visualizer-extension'));
        }

        $span = new \View\Span(NULL, $this->getFile()->getBasename(FALSE));
        $previewUrl = $this->getLink('preview', null, ['file' => $this->getFile()->getPath()]);

        //$array[] = $this->getImageExtraButton($item);

        $link = new \View\A('', array($holder, $span), $this->getFile()->getUrl(), 'file-visualizer-link', \View\A::TARGET_BLANK);
        $link->click("return p('{$previewUrl}');");
        $link->setTitle($this->getFile()->getUrl());

        $url = $this->getLink('delete', null, ['file' => $this->getFile()->getPath()]);
        $delete = new \View\Ext\Icon('times', null, "if ( confirm('Confirma remoção de arquivo?')) { return p('{$url}') } ;", 'file-visualizer-remove-icon');
        $content = array($link, $delete);

        $externo = new \View\Div('', $content, 'file-visualizer');

        return $externo;
    }

    /**
     * Get the relative file
     * @return \Disk\File
     */
    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function delete()
    {
        \App::dontChangeUrl();
        $path = \DataHandle\Request::get('file');
        $file = new \Disk\File($path);
        $ok = $file->remove();

        if (!$ok)
        {
            throw new \UserException('Impossível remover arquivo ' . $file->getBasename());
        }
        else
        {
            toast('Arquivo removido com sucesso!');
            $this->mountFolder($file);
        }
    }

    public function preview()
    {
        if (!\DataHandle\Server::getInstance()->isAjax())
        {
            $folder = \DataHandle\Request::get('file');
            return \Component\FileVisualizer::createHolder(new \Disk\Folder($folder), '*');
        }

        $path = \DataHandle\Request::get('file');
        $file = new \Disk\File($path);
        $isDir = $file->isDir();

        if (!($file->exists() || $isDir))
        {
            throw new \UserException('Arquivo ' . $file->getBasename() . ' não encontrado!');
        }

        $body = [];

        if ($isDir)
        {
            $this->mountFolder($file);
            return;
        }
        else if ($file->isText())
        {
            $file->load();
            $body[] = new \View\Pre('preview-text', $file->getContent());
        }
        else if ($file->isImage())
        {
            $body[] = new \View\Img('preview-image', $file->getUrl(), '100%', null, $file->getBasename());
        }
        else
        {
            throw new \UserException('Impossível fazer preview do arquivo!');
        }

        $isCkEditor = \DataHandle\Request::get('file-visualizer-ckeditor');

        if ($isCkEditor)
        {
            $buttons[] = new \View\Ext\Button('adInCkEditor', 'file-code', 'Adicionar ao editor', "return useImageCkEditor('{$file->getUrl()}')", 'primary');
        }
        else
        {
            $buttons[] = new \View\Ext\LinkButton('download', 'download', 'Download', $file->getUrl(), 'primary');
        }

        $buttons[] = new \View\Ext\Button('close', 'cancel', 'Fechar', \View\Blend\Popup::getJs('destroy'));

        $popup = new \View\Blend\Popup('preview', 'Pre-visualização ' . $file->getBasename(), $body, $buttons);
        $popup->addClass('big')->show();

        \App::dontChangeUrl();
    }

    public function upload()
    {
        \App::dontChangeUrl();
        if (empty($_FILES))
        {
            return;
        }

        $targetPath = \DataHandle\Request::get('file-visualizer-folder');
        $tempFile = $_FILES['file-0']['tmp_name'];
        $file = new \Disk\File($_FILES['file-0']['name']);
        $fileName = \Type\Text::get($file->getBasename(false))->toFile('-') . '.' . $file->getExtension();
        $targetFile = new \Disk\File($targetPath . '/' . $fileName);

        $ok = move_uploaded_file($tempFile, $targetFile);

        if ($ok && $targetFile->exists())
        {
            if ($targetFile->isImage())
            {
                //TODO MAKE THUMB
            }

            $this->byId('file-visualizer-upload')->val('');
            $this->mountFolder($targetFile);
        }
        else
        {
            throw new \UserException('Ops! Problema em enviar arquivo!');
        }
    }

    public function mountFolder(\Disk\File $file)
    {
        $folder = $file->getFolder();
        $root = \DataHandle\Request::get('file-visualizer-root');
        $search = \DataHandle\Request::get('file-visualizer-search');
        $title = '';

        if ($root)
        {
            $title = str_replace($root, '', $folder->getPath());
        }

        if (!$title)
        {
            $title = $file->getBasename();
        }

        $content = [];
        $files = $folder->listFiles($search);

        foreach ($files as $file)
        {
            $visualizer = new \Component\FileVisualizer($file);
            $content[] = $visualizer->onCreate();
        }

        $this->byId('file-visualizer-folder')->val($folder->getPath());
        $this->byId('file-visualizer-title')->html($title);
        $this->byId('file-visualizer-files')->html($content);
    }

    /**
     * Create a FileVisualizae holder
     *
     * @param \Disk\Folder $folder the root folder
     * @param type $search the files to search, use glob sintax
     * @return \View\Div the holder element
     */
    public static function createHolder(\Disk\Folder $folder, $search = '*')
    {
        $uploadUrl = \Component\FileVisualizer::getLinkForComponent(null, 'upload');

        $content = [];
        $content[] = new \View\Input('file-visualizer-root', \View\Input::TYPE_HIDDEN, $folder->getPath());
        $content[] = new \View\Input('file-visualizer-folder', \View\Input::TYPE_HIDDEN, $folder->getPath());
        $content[] = new \View\Input('file-visualizer-search', \View\Input::TYPE_HIDDEN, $search);
        $content[] = new \View\Input('file-visualizer-ckeditor', \View\Input::TYPE_HIDDEN, \DataHandle\Request::get('CKEditor'));

        $content[] = new \View\H1('file-visualizer-title', $folder->getBasename());
        $content[] = $upload = new \View\Input('file-visualizer-upload', \View\Input::TYPE_FILE);
        $upload->css('margin-bottom', '30px')->change('p("' . $uploadUrl . '");');

        $components = [];
        $files = $folder->listFiles($search);

        foreach ($files as $file)
        {
            $visualizer = new \Component\FileVisualizer($file);
            $components[] = $visualizer->onCreate();
        }

        $content[] = new \View\Div('file-visualizer-files', $components, 'file-visualizer-files clearfix');

        return new \View\Div('file-visualizer-holder', $content, 'file-visualizer-holder clearfix');
    }

}
