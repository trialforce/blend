<?php

namespace Disk;

/**
 * File upload
 */
class FileUpload extends \Disk\File
{

    protected $type;
    protected $tmpName;
    protected $error;
    protected $size;

    public function __construct($path, $load = FALSE)
    {
        parent::__construct(NULL, FALSE);

        if (is_array($path))
        {
            $this->setName($path['name']);
            $this->setTmpName($path['tmp_name']);
            $this->setType($path['type']);
            $this->setError($path['error']);
            $this->setSize($path['size']);
        }
    }

    /**
     * Verify the extension
     *
     * @param array $extList
     * @throws \Exception
     */
    public function verifyExtension($extList, $deny = FALSE)
    {
        $inArray = in_array(strtolower($this->getExt()), $extList);
        $canUpload = !$inArray;

        if (!$deny)
        {
            $canUpload = $inArray;
        }

        if (!$canUpload)
        {
            throw new \Exception('Formato de arquivo não permitido! Formato: ' . $this->getExt());
        }
        else if ($this->error > 0)
        {
            throw new \Exception($this->codeToMessage($this->error));
        }
    }

    private function codeToMessage($code)
    {
        $maxUploadFileSize = new \Type\Bytes(self::getMaxUploadFileSize());

        switch ($code)
        {
            case UPLOAD_ERR_INI_SIZE:
                $message = "Arquivo maior que o LIMITE de {$maxUploadFileSize}";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "Arquivo maior que o LIMITE do formulário!";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "O arquivo foi enviado parcialmente!";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "Nenhum arquivo enviado";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Diretório temporário inexistente!";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Falha ao salvar arquivo no disco!";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "Upload bloqueado por extensão!";
                break;

            default:
                $message = "Erro desconhecido!";
                break;
        }
        return $message;
    }

    /**
     * Returns a file size limit in bytes based on the PHP upload_max_filesize and post_max_size
     *
     * @staticvar type $maxSize
     * @return type
     */
    static function getMaxUploadFileSize()
    {
        static $maxSize = -1;

        if ($maxSize < 0)
        {
            // Start with post_max_size.
            $postMaxSize = self::parseSize(ini_get('post_max_size'));
            $uploadMax = self::parseSize(ini_get('upload_max_filesize'));

            if ($postMaxSize > 0)
            {
                $maxSize = $postMaxSize;
            }

            // If upload_max_size is less, then reduce. Except if upload_max_size is zero, which indicates no limit.
            if ($uploadMax > 0 && $uploadMax < $maxSize)
            {
                $maxSize = $uploadMax;
            }
        }

        return $maxSize;
    }

    protected static function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.

        if ($unit)
        {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        else
        {
            return round($size);
        }
    }

    /**
     * Return the basename for file
     *
     * @return string
     */
    public function getBasenameForFile()
    {
        $explode = explode('.', \Type\Text::get($this->name)->toFile());

        return $explode[0];
    }

    /**
     * Get file name for upload
     *
     * @return string
     */
    public function getUploadFileName($folder = NULL)
    {
        $folder = $folder ? $folder . DS : '';
        return $folder . $this->getBasenameForFile() . '_' . rand() . '.' . $this->getExt();
    }

    /**
     * Upload the file
     *
     * @param string $dest
     * @return boolean
     * @throws \Exception
     */
    public function upload($dest)
    {
        $dir = dirname($dest) . '/';

        if (!is_dir($dir))
        {
            mkdir($dir, 0777, TRUE);
        }

        $ok = move_uploaded_file($this->tmpName, $dest . ''); //faz upload
        $this->setPath($dest . '');

        if ($ok && $this->exists())
        {
            return TRUE;
        }
        else
        {
            throw new \Exception('Erro ao mover arquivo no servidor. Verifique permissões!');
        }
    }

    /**
     * Return file extension
     *
     * @return string
     */
    public function getExt()
    {
        $explode = explode('.', $this->name);
        return $explode[count($explode) - 1];
    }

    /**
     * Return the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return the temporary name
     *
     * @return string
     */
    public function getTmpName()
    {
        return $this->tmpName;
    }

    /**
     * Return the upload error
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set the upload size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set upload name
     *
     * @param string $name
     * @return \Disk\FileUpload
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set upload mime type
     *
     * @param string $type
     * @return \Disk\FileUpload
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Set temporary upload nam
     *
     * @param string $tmpName
     * @return \Disk\FileUpload
     */
    public function setTmpName($tmpName)
    {
        $this->tmpName = $tmpName;
        return $this;
    }

    /**
     * Set upload error
     *
     * @param string $error
     * @return \Disk\FileUpload
     */
    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * Set upload size
     *
     * @param int $size
     * @return \Disk\FileUpload
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

}
