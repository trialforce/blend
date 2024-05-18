<?php

namespace Component;

/**
 * File Visualizer/Uploader/Explorer
 * Is a simplified version of Window Explorer
 * You can upload/download files and navigate trough folders
 *
 */
class FileVisualizer extends \Component\Component
{

    /**
     * File
     * @var \Disk\File
     */
    private $file;

    public function __construct($file = null)
    {
        parent::__construct();
        $this->setFile($file);
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

    /**
     * Create one file
     * @return array|\View\Div|\View\View|null
     * @throws \Exception
     */
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

        $link = new \View\A('', array($holder, $span), $this->getFile()->getUrl(), 'file-visualizer-link', \View\A::TARGET_BLANK);
        $link->click("return p('$previewUrl');");
        $link->setTitle($this->getFile()->getUrl());

        $url = $this->getLink('delete', null, ['file' => $this->getFile()->getPath()]);
        $delete = new \View\Ext\Icon('times', null, "if ( confirm('Confirma remoção de arquivo?')) { return p('$url') } ;", 'file-visualizer-remove-icon');
        $content = array($link, $delete);

        $externo = new \View\Div('', $content, 'file-visualizer');

        return $externo;
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
            return false;
        }
        else if ($file->isText())
        {
            $file->load();
            $body[] = new \View\Pre('preview-text', $file->getContent());
        }
        else if ($file->isImage())
        {
            $body[] = new \View\Img('preview-image', $file->getUrl(), null, null, $file->getBasename());
        }
        else
        {
            \App::windowOpen($file->getUrl());
            return false;
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
        return true;
    }

    public function upload()
    {
        \Log::dump('aqui');
        \Log::dump($_FILES);
        \App::dontChangeUrl();

        if (empty($_FILES))
        {
            return;
        }

        $files = $_FILES;

        foreach ($files as $uploadedFile)
        {
            $targetPath = \DataHandle\Request::get('file-visualizer-folder');
            $tempFile = $uploadedFile['tmp_name'];
            $file = new \Disk\File($uploadedFile['name']);
            $fileName = \Type\Text::get($file->getBasename(false))->toFile('-') . '.' . $file->getExtension();

            $targetFile = new \Disk\File($targetPath . '/' . $fileName);
            $targetFile->createFolderIfNeeded();

            $ok = move_uploaded_file($tempFile, $targetFile);

            if ($ok && $targetFile->exists())
            {
                $this->byId('file-visualizer-upload')->val('');
                $this->mountFolder($targetFile);
            }
            else
            {
                throw new \UserException('Ops! Problema em enviar arquivo!');
            }
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
     * Create a FileVisualizar holder
     *
     * @param \Disk\Folder $folder the root folder
     * @param string $search the files to search, use glob sintax
     * @return \View\Div the holder element
     * @throws \Exception
     */
    public static function createHolder(\Disk\Folder $folder, $search = '*', $title = null)
    {
        $isCkEditor = \DataHandle\Request::get('CKEditor');
        $accept = '*';

        if ($isCkEditor || $search == 'image')
        {
            $search = '*.{jpg,jpeg,png,gif,wbep,svg}';
            $accept = 'image/*';
        }

        $folder->createFolderIfNeeded();

        $uploadUrl = \Component\FileVisualizer::getLinkForComponent(null, 'upload');

        $content = [];
        $content[] = new \View\Input('file-visualizer-root', \View\Input::TYPE_HIDDEN, $folder->getPath());
        $content[] = new \View\Input('file-visualizer-folder', \View\Input::TYPE_HIDDEN, $folder->getPath());
        $content[] = new \View\Input('file-visualizer-search', \View\Input::TYPE_HIDDEN, $search);
        $content[] = new \View\Input('file-visualizer-ckeditor', \View\Input::TYPE_HIDDEN, $isCkEditor);

        $content[] = new \View\H1('file-visualizer-title', $title ? $title : $folder->getBasename());
        $content[] = $upload = new \View\Input('file-visualizer-upload', \View\Input::TYPE_FILE);
        $upload->attr('multiple', true)
            ->attr('accept', $accept)
            ->css('margin-bottom', '30px')
            ->change('p("' . $uploadUrl . '");');

        $components = [];
        $files = $folder->listFiles($search, null);

        foreach ($files as $file)
        {
            $visualizer = new \Component\FileVisualizer($file);
            $components[] = $visualizer->onCreate();
        }

        $content[] = new \View\Div('file-visualizer-files', $components, 'file-visualizer-files clearfix');

        return new \View\Div('file-visualizer-holder', $content, 'file-visualizer-holder clearfix');
    }

}
