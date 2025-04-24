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

    /**
     * Construct the manager
     * @param $connInfoId string the conninfo id where the migration table are
     * @param $folder string the folder with migration files
     */
    public function __construct($connInfoId = NULL, $folder = '')
    {
        $this->setConnInfoId($connInfoId);
        $this->setFolder($folder);
    }

    /**
     * Add some string to the result / out
     * @param string $result
     * @return $this
     */
    public function addResult(string $result)
    {
        echo $result."\r\n";
        return $this;
    }

    /**
     * Return the conninfoid
     * @return string
     */
    public function getConnInfoId()
    {
        return $this->connInfoId;
    }

    /**
     * Set the conn info id
     * @param $connInfoId
     * @return $this
     */
    public function setConnInfoId($connInfoId)
    {
        $this->connInfoId = $connInfoId;
        return $this;
    }

    /**
     * Get the folder with the migration files
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set the folder with the migration files
     * @param $folder
     * @return $this
     */
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
     * Get the conn info if of the current file
     *
     * @param \Disk\File $file
     * @return string
     * @throws \Exception
     */
    public function getCurrentConnInfoId(\Disk\File $file)
    {
        $explode = explode('_',$file->getPath());
        $connInfoId = $explode[1];
        $connInfo = \Db\Conn::getConnInfo($connInfoId,false);

        if ($connInfo)
        {
            return $connInfoId;
        }

        throw new \Exception('Impossível encontrar connInfo para "'.$connInfoId.'"');
    }

    /**
     * Get The catalog class of the current conn info
     * @return string
     * @throws \Exception
     */
    public function getCatalogClass()
    {
        return \Db\Conn::getConnInfo($this->getConnInfoId())->getCatalogClass();
    }

    /**
     * List all version files
     * @return array
     */
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

    /**
     * Execute the migration files needed
     * @return int
     * @throws \Exception
     */
    public function execute()
    {
        $this->createMigrationTableIfNeeeded();

        $dbVersion = $this->getDbVersion();
        $list = $this->getVersionList();
        $updates = 0;
        $currentVersion = $list[count($list)-1];

        $this->addResult('Current db version: ' . $dbVersion);
        $this->addResult('Current file version: ' . $currentVersion);

        foreach ($list as $version)
        {
            if ($dbVersion < $version)
            {
                $this->addResult('Executing version: '. $version);
                $this->executeVersion($version);
                $updates++;
            }
        }

        if ($updates ==0)
        {
            $this->addResult('Nothing to update.');
        }
        else
        {
            $this->addResult('Update succefull!');
        }

        return $updates;
    }

    /**
     * Execute ONE version
     * Detects if is and PHP or SQL
     * @param $version
     * @return void
     * @throws \Exception
     */
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

    /**
     * Execute an PHP version file
     *
     * @param \Disk\File $file
     * @return mixed
     * @throws \Exception
     */
    protected function executeVersionPhp(\Disk\File $file)
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
     * Execute an SQL file version
     *
     * @param \Disk\File $file
     * @return bool|null
     * @throws \ReflectionException
     */
    public function executeVersionSql(\Disk\File $file)
    {
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
            if ($start == '/*' || $start == '*/' || $start == "//" || $start == '--')
            {
                continue;
            }

            $trimmed = str_replace(["\r","\r","\r\n"], '' ,$query);

            try
            {

                $this->addResult("Query: " . $trimmed);
                $result = $conn->execute($query . ';');
            }
            catch (\Exception $exception)
            {
                $this->addResult("ERROR IN QUERY: " . $trimmed);
                $message = $exception->getMessage() . ' WRONG SQL:' . $trimmed. ';';
                \Log::setExceptionMessage($exception, $message);
                throw $exception;
            }
        }

        return $result;
    }

    /**
     * Get currente db version
     * @return string
     * @throws \Exception
     */
    public function getDbVersion()
    {
        $query = new \Db\QueryBuilder('migration', $this->getConnInfoId());

        return $query->setLogId('getLastMigrationVersion')
            ->columns('max(version) as dbVersion')
            ->where('folder', '=', $this->getFolder())
            ->first()
            ->dbVersion;
    }

    /**
     * Insert current version on database
     * After version is executed
     * @param $version
     * @return void
     * @throws \Exception
     */
    private function insertVersion($version)
    {
        $sql = "INSERT INTO migration (version,folder) VALUES ('$version','$this->folder');";
        $this->getConn()->execute($sql, null, 'insertVersion');
    }

    /**
     * Create migration table if needed
     * @return void
     * @throws \Exception
     */
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

    /**
     * Create the sqls needed to sync an model
     * @todo this method is not homologed
     *
     * @param $modelName
     * @param $params
     * @return bool|null
     * @throws \Exception
     */
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

    /**
     * Create the sqls needed to sync an table
     * @todo this method is not homologed
     * @param $tableName
     * @param $comment
     * @param $columns
     * @param $params
     * @return bool|null
     * @throws \Exception
     */
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
