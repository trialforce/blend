<?php

namespace Db\Migration;

/**
 * Database Migration Manager
 */
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

    protected $currentVersion;

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
     * Get the connection where migration table is created
     * @return \Db\Conn
     * @throws \Exception
     */
    public function getConn()
    {
        return \Db\Conn::getInstance($this->getConnInfoId());
    }

    /**
     *
     * @return string
     * @throws \Exception
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
        $this->createMigrationTableIfNeeeded();

        $dbVersion = $this->getDbVersion();
        $list = $this->getVersionList();
        $updates = 0;

        foreach ($list as $version)
        {
            if ($dbVersion < $version)
            {
                $this->executeVersion($version);
                $updates++;
            }
        }

        return $updates;
    }

    /**
     * Verify if database need update
     * @return boolean
     */
    public function needUpdate()
    {
        $this->createMigrationTableIfNeeeded();
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
        $className = str_replace('/', '\\', str_replace('.php', '', $file->getPath()));

        if (!class_exists($className))
        {
            throw new \Exception('Atualizacao de banco de dados: classe/versão com nome ' . $className . ' não existe.');
        }

        $versionObj = new $className();
        $versionObj->setMigration($this);
        return $versionObj->execute();
    }

    /**
     * @param \Disk\File $file
     * @return string
     * @throws \Exception
     */
    public function getCurrentConnInfoId(\Disk\File $file)
    {
        $explode = explode('_',$file->getPath());
        $connInfoId = $explode[1];
        $connInfo = \Db\Conn::getConnInfo($connInfoId,false);
        return $connInfo ? $connInfo->getId() : $this->getConnInfoId();
    }

    public function executeVersionSql(\Disk\File $file)
    {
        \Log::debug($file);
        $file->load();
        $content = $file->getContent();
        $queries = explode(';', $content);
        $connInfo = $this->getCurrentConnInfoId($file);
        $conn =  \Db\Conn::getInstance($connInfo);

        $result = true;

        foreach ($queries as $query)
        {
            $query = trim($query);

            if (!$query)
            {
                continue;
            }

            $start = substr($query, 0, 2);

            //dont run commented query
            if ($start == '/*' || $start == '*/' || $start == "//")
            {
                continue;
            }

            try
            {
                $result = $conn->execute($query . ';');
            }
            catch (\Exception $exception)
            {
                $message = $exception->getMessage() . ' WRONG SQL:' . $query . ';';
                \Log::setExceptionMessage($exception, $message);
                throw $exception;
            }
        }

        return $result;
    }

    public function getDbVersion()
    {
        $query = new \Db\QueryBuilder('migration', $this->getConnInfoId());
        return $query->setLogId('getLastMigrationVersion')->columns('max(version) as dbVersion')->where('folder', '=', $this->getFolder())->first()->dbVersion;
    }

    private function insertVersion($version)
    {
        $sql = "INSERT INTO migration (version,folder) VALUES ('$version','$this->folder');";
        $this->getConn()->execute($sql, null, 'insertVersion');
    }

    private function createMigrationTableIfNeeeded()
    {
        $cataalogClass = $this->getCatalogClass();
        $catalog = new $cataalogClass();

        if ($catalog->tableExists('migration', false))
        {
            return;
        }

        $conn = $this->getConn();

        $sqlCreateTable = "
CREATE TABLE `migration` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`version` VARCHAR(150) NOT NULL,
	`folder` VARCHAR(50) NOT NULL,
	`executedOn` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)
COMMENT='Migration'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;";

        $conn->execute($sqlCreateTable, null, 'Create table migration');
    }

    public function diffModel($modelName, $params = null)
    {
        $tableName = $modelName::getTableName();
        $label = ucfirst($modelName::getLabel());
        $columns = $modelName::getColumns();

        if (!is_array($params))
        {
            $params = array();
        }

        if ($this->getCatalogClass() == '\Db\Catalog\Mysql')
        {
            $params = array_merge(['collate' => 'utf8_general_ci', 'engine' => 'InnoDB'], $params);
        }

        return $this->diffTable($tableName, $label, $columns, $params);
    }

    public function diffTable($tableName, $comment, $columns, $params)
    {
        $catalog = $this->getCatalogClass();
        $conn = $this->getConn();

        if (!$catalog::tableExists($tableName, false))
        {
            $sql = $catalog::mountCreateTable($tableName, $comment, $columns, $params);
            return $conn->execute($sql);
        }
        else
        {
            $dbColumns = $catalog::listColums($tableName, false);

            foreach ($columns as $column)
            {
                //locate current column in databse columns
                $dbColumn = $dbColumns[$column->getName()];

                //if finded remove to avoid remove
                if ($dbColumn)
                {
                    unset($dbColumns[$column->getName()]);

                    //collumn has diffs
                    if ($column->getName() != $dbColumn->getName() ||
                            $column->getType() != $dbColumn->getType() ||
                            $column->getSize() != $dbColumn->getSize() ||
                            $column->getDefaultValue() != $dbColumn->getDefaultValue() ||
                            $column->getNullable() != $dbColumn->getNullable() ||
                            $column->getLabel() != $dbColumn->getLabel()
                    )
                    {
                        $sql = $catalog::mountCreateColumn($tableName, $column, 'alter');
                        return $conn->execute($sql);
                    }
                }
                //if don't exists in database
                else
                {
                    $sql = $catalog::mountCreateColumn($tableName, $column, 'add');

                    return $conn->execute($sql);
                }
            }
        }

        return null;
    }

}
