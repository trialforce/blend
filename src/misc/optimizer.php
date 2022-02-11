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

    /**
     * Return the output file
     *
     * @return \Disk\File
     */
    public function getOutputFile()
    {
        return new \Disk\File($this->getOutFile());
    }

    /**
     * Verify if optimized file need redoing
     *
     * @return boolean
     * @throws \Exception
     */
    public function verifyNeedRedo()
    {
        $needRedo = false;
        $objOut = $this->getOutputFile();
        $mTime = 0;

        if ($objOut->exists())
        {
            $mTime = intval($objOut->getMTime());
        }

        $files = $this->getFiles();

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

        return $needRedo;
    }

    public function execute()
    {
        $outputFile = $this->getTimestampedFile();

        if ($this->verifyNeedRedo())
        {
            $outputFile = $this->reallyExecute();
        }

        return $outputFile;
    }

    /**
     * Really execute the file optmization
     *
     * @param \Disk\File $objOut file
     * @return \Disk\File the output file
     */
    public function reallyExecute()
    {
        $objOut = $this->getOutputFile();
        $this->deleteOld();

        $objOut->save(\Type\DateTime::now()->getTimestampUnix());
        $outputFile = $this->getTimestampedFile();
        $outputFile->save($this->optimize());

        return $outputFile;
    }

    /**
     * Return the timestamped file
     * @return \Disk\File
     */
    public function getTimestampedFile()
    {
        $objOut = $this->getOutputFile();
        $mTime = 0;

        if ($objOut->exists())
        {
            $mTime = intval($objOut->getMTime());
        }

        return \Disk\File::getFromStorage($objOut->getBasename(FALSE) . '_' . $mTime . '.' . $objOut->getExtension());
    }

    /**
     * Delete old generated files when needed
     *
     * @return $this
     */
    public function deleteOld()
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
