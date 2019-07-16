<?php

namespace Db;

class ModelApi extends \Db\Model
{

    /**
     * The id uses im mobile/offline device
     * @var type
     */
    protected $idMobile;

    /**
     * Used to test connect
     *
     * @return boolean
     */
    public function ping()
    {
        return true;
    }

    /**
     * Define the mobile identification
     *
     * @param string $idMobile
     * @return $this
     */
    public function setIdMobile($idMobile)
    {
        $this->idMobile = $idMobile;

        return $this;
    }

    /**
     * Return the mobile identification
     * @return string
     */
    public function getIdMobile()
    {
        return $this->idMobile;
    }

    public function insert($columns = NULL)
    {
        if (!$this->idMobile)
        {
            $this->idMobile = self::createUniqueIdMobile();
        }
        return parent::insert($columns);
    }

    public function update($columns = NULL)
    {
        if (!$this->idMobile)
        {
            $this->idMobile = self::createUniqueIdMobile();
        }

        return parent::update($columns);
    }

    public static function getTableName()
    {
        $tableName = parent::getTableName();

        if (stripos($tableName, 'api') === 0)
        {
            $tableName = lcfirst(str_replace('api', '', $tableName));
        }

        return $tableName;
    }

    public static function getModelName()
    {
        //necessary because namespace, add support for API classes
        $name = get_called_class();

        if (stripos($name, 'api') === 0)
        {
            $name = 'Model\\' . str_replace('Api\\', '', $name);
        }

        return $name;
    }

    public static function createUniqueIdMobile()
    {
        //start with ZERO means created in backend
        return '0' . \Type\DateTime::now()->getValue('ymdHis');
    }

    public static function findOneByIdMobile($idMobile)
    {
        $name = self::getName();

        if (!$idMobile)
        {
            return null;
        }

        $filters[] = new \Db\Where('idMobile', '=', $idMobile);

        $model = $name::findOne($filters);

        if ($model)
        {
            $model->setIdMobile($idMobile);
        }

        return $model;
    }

    public static function findOneByIdMobileOrCreate($idMobile)
    {
        $name = self::getName();

        if (!$idMobile)
        {
            return new $name();
        }

        $filters[] = new \Db\Where('idMobile', '=', $idMobile);

        $model = $name::findOneOrCreate($filters);
        $model->setIdMobile($idMobile);

        return $model;
    }

    /**
     * Simple, list as array
     *
     * @return array array of models
     */
    public static function list()
    {
        $name = self::getModelName();
        $wheres = null;
        $smartSearch = null;
        $limit = null;
        $offset = null;
        $orderBy = null;
        $orderWay = null;

        if (isset($_REQUEST))
        {
            $params = $_REQUEST;

            if (isset($params['q']))
            {
                $smartSearch = $params['q'];
                unset($params['q']);
            }

            if (isset($params['limit']))
            {
                $limit = $params['limit'];
                unset($params['limit']);
            }

            if (isset($params['offset']))
            {
                $offset = $params['offset'];
                unset($params['offset']);
            }

            if (isset($params['orderBy']))
            {
                $orderBy = $params['orderBy'];
                unset($params['orderBy']);
            }

            if (isset($params['orderWay']))
            {
                $orderWay = $params['orderWay'];
                unset($params['orderWay']);
            }

            //put the params in the reques, to filters can use
            \DataHandle\Request::getInstance()->setData($params);
            //create a model to use in datasource and in page
            $model = new $name();
            //used only to reference filter to work, need rework
            $page = new \Page\Crud($model);
            $ds = new \DataSource\Model($model);
            $gridColumns = $ds->getColumns();

            $filters = \Component\Grid\MountFilter::getFilters($gridColumns, $model);

            if (is_array($filters))
            {
                foreach ($filters as $filter)
                {
                    $cond = $filter->getDbCond();

                    if ($cond)
                    {
                        $wheres[] = $cond;
                    }
                }
            }
        }

        $result = $name::smartFind($smartSearch, $wheres, $limit, $offset, $orderBy, $orderWay, 'array');

        return $result;
    }

    public static function callMethodFromJson($json, $methodName = NULL, $params = NULL)
    {
        $params = is_array($params) ? $params : [$params];
        $name = self::getName();

        //convert from json string to, stdClass, then datahable
        $vector = \Disk\Json::decode($json);
        $dataHandle = new \DataHandle\DataHandle($vector);

        //locate the model, fill it's data, and save (so the method can be called)
        $model = $name::findOneByIdMobileOrCreate($vector->idMobile);
        $model->setData($dataHandle, false);
        $model->save();

        //if the method exists, call if
        if ($methodName)
        {
            $result = call_user_func_array(array($model, $methodName), $params);
            return $result;
        }

        return $model;
    }

}
