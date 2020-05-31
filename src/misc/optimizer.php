<?php

namespace Misc;

/**
 * Text File group/optimize.
 * Created to be used with CssMin and JsMin
 */
class Optimizer
{

    /**
     * Output file
     * @var string
     */
    protected $outFile = '';

    /**
     * List of files to optmize
     * @var array
     */
    protected $files = array();

    /**
     * Class used to optimize
     * @var string
     */
    protected $optimizeClass = '';

    public function __construct($outFile, $class = null)
    {
        $this->setOutFile($outFile);
        $this->setOptimizeClass($class);
    }

    public function getOutFile()
    {
        return $this->outFile;
    }

    public function setOutFile($outFile)
    {
        $this->outFile = $outFile;
        return $this;
    }

    public function getOptimizeClass()
    {
        return $this->optimizeClass;
    }

    public function setOptimizeClass($optimizeClass)
    {
        $this->optimizeClass = $optimizeClass;
        return $this;
    }

    public function addFile($file)
    {
        $this->files[] = $file;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function setFiles($files)
    {
        $this->files = $files;
        return $this;
    }

    public function execute()
    {
        $objOut = new \Disk\File($this->getOutFile());

        $mTime = intval($objOut->getMTime());

        $outputFile = \Disk\File::getFromStorage($objOut->getBasename(FALSE) . '_' . $mTime . '.' . $objOut->getExtension());

        $files = $this->getFiles();
        $needRedo = false;

        //pass trough files detecting if need redo
        foreach ($files as $file)
        {
            $obj = new \Disk\File($file);

            if (!$obj->exists())
            {
                throw new \Exception('Arquivo ' . $file . ' não existe na otimização de arquivos');
            }

            $myMTime = $obj->getMTime();

            if ($myMTime > $mTime)
            {
                $needRedo = true;
                continue;
            }
        }

        if ($needRedo)
        {
            $outputFile = $this->reallyExecute($objOut);
        }

        return $outputFile;
    }

    /**
     * Really execute the file optmization
     *
     * @param \Disk\File $objOut file
     * @return \Disk\File the output file
     */
    protected function reallyExecute(\Disk\File $objOut)
    {
        $this->deleteOld();

        $objOut->save(\Type\DateTime::now()->getTimestampUnix());
        $outputFile = \Disk\File::getFromStorage($objOut->getBasename(FALSE) . '_' . $objOut->getMTime() . '.' . $objOut->getExtension());
        $outputFile->save($this->optimize());

        return $outputFile;
    }

    /**
     * Delete old generated files when needed
     *
     * @return $this
     */
    protected function deleteOld()
    {
        $file = new \Disk\File($this->getOutFile());

        $folder = $file->getFolder();
        $name = $file->getBasename(false);
        $ext = $file->getExtension();

        $filesToDelete = $folder->listFiles($name . '_*.' . $ext);

        foreach ($filesToDelete as $file)
        {
            $file->remove();
        }

        return $this;
    }

    /**
     * Generate the new optimized content
     *
     * @return string
     */
    protected function optimize()
    {
        $optimizeClass = $this->getOptimizeClass();
        $files = $this->getFiles();
        $result = '';

        foreach ($files as $file)
        {
            $obj = new \Disk\File($file);

            if (!$obj->exists())
            {
                throw new \Exception('File Optimizer Error: File ' . $obj->getPath() . ' does not exists.');
            }

            $obj->load();

            $content = $obj->getContent();

            if ($optimizeClass && class_exists($optimizeClass))
            {
                $content = $optimizeClass::optimize($content);
            }

            $result .= $content . "\r\n";
        }

        return $result;
    }

}
