<?php

namespace Migration;

class Manager
{

    /**
     *
     * @var string
     */
    protected $connInfoId = 'default';

    /**
     *
     * @var string
     */
    protected $folder = '';

    public function __construct($connInfoId = NULL, $folder = '')
    {
        $this->setConnInfoId($connInfoId);
        $this->setFolder($folder);
    }

    public function getConnInfoId()
    {
        return $this->connInfoId;
    }

    public function setConnInfoId($connInfoId)
    {
        $this->connInfoId = $connInfoId;
        return $this;
    }

    public function getFolder()
    {
        return $this->folder;
    }

    public function setFolder($folder)
    {
        $this->folder = $folder;
        return $this;
    }

    /**
     *
     * @return \Db\Conn
     */
    public function getConn()
    {
        return \Db\Conn::getInstance($this->getConnInfoId());
    }

    /**
     *
     * @return string
     */
    public function getCatalogClass()
    {
        return \Db\Conn::getConnInfo($this->getConnInfoId())->getCatalogClass();
    }

    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    public function setCurrentVersion($currentVersion)
    {
        $this->currentVersion = $currentVersion;
        return $this;
    }

    public function getVersionList()
    {
        $list = array();

        if ($this->getFolder())
        {
            $dir = new \Disk\Folder($this->getFolder());
            $files = $dir->listFiles('*');

            foreach ($files as $file)
            {
                $list[] = str_replace($this->getFolder() . '/', '', $file->getPath());
            }
        }

        sort($list);

        return $list;
    }

    public function execute()
    {
        $conn = $this->getConn();
        $catalogClass = $this->getCatalogClass();
        $this->createMigrationTable();

        $dbVersion = $this->getDbVersion();
        $list = $this->getVersionList();

        foreach ($list as $version)
        {
            if ($dbVersion < $version)
            {
                $this->executeVersion($version);
            }
        }
    }

    public function needUpdate()
    {
        $dbVersion = $this->getDbVersion();
        $list = $this->getVersionList();

        foreach ($list as $version)
        {
            if ($dbVersion < $version)
            {
                return true;
            }
        }

        return false;
    }

    public function executeVersion($version)
    {
        $filePath = $this->getFolder() . '/' . $version;
        $file = new \Disk\File($filePath);

        if ($file->getExtension() == 'sql')
        {
            $this->executeVersionSql($file);
        }
        else if ($file->getExtension() == 'php')
        {
            $this->executeVersionPhp($file);
        }

        $this->insertVersion($version);
    }

    public function executeVersionPhp(\Disk\File $file)
    {
        $className = 'Version' . str_replace(array($this->getFolder() . '/', '.php'), '', $file->getPath());
        require $file->getPath();
        $versionObj = new $className();
        $versionObj->setMigration($this);
        return $versionObj->execute();
    }

    public function executeVersionSql(\Disk\File $file)
    {
        $file->load();
        $content = $file->getContent();
        $conn = $this->getConn();
        return $conn->execute($content);
    }

    public function getDbVersion()
    {
        $query = new \Db\QueryBuilder('migration', $this->getConnInfoId());
        return $query->columns('max(version) as dbVersion')->where('folder', '=', $this->getFolder())->first()->dbVersion;
    }

    private function insertVersion($version)
    {
        $sql = "INSERT INTO migration (version,folder) VALUES ('$version','$this->folder');";
        $this->getConn()->execute($sql);
    }

    private function createMigrationTable()
    {
        $catalog = new \Db\Catalog\Mysql();

        if ($catalog->tableExists('migration', false))
        {
            return;
        }

        $conn = $this->getConn();

        $sqlCreateTable = "
CREATE TABLE `migration` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`version` VARCHAR(50) NOT NULL,
	`folder` VARCHAR(50) NOT NULL,
	`executedOn` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)
COMMENT='Migration'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;";

        $conn->execute($sqlCreateTable);
    }

}
