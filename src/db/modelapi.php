<?php

namespace Db;

/**
 * Extends a Model adding funcionality to be acessed trough API
 *
 * @deprecated since version 04/10/2021
 */
class ModelApi extends \Db\Model
{

    /**
     * The id uses im mobile/offline device
     * @var string
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
            $tableName = lcfirst(str_replace(['apiRunmore','api'], '', $tableName));
        }

        return $tableName;
    }

    public static function getModelName()
    {
        //necessary because namespace, add support for API classes
        $name = get_called_class();

        if (stripos($name, 'api') === 0)
        {
            $name = 'Model\\' . str_replace(['Api\\Runmore\\','Api\\'], '', $name);
        }

        return $name;
    }

    public static function createUniqueIdMobile()
    {
        $t = intval(microtime(true));
        $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
        $d = new \DateTime(date('Y-m-d H:i:s.' . $micro, $t));

        // note o 'u'
        $time = $d->format("ymdHisu");

        //start with ZERO means created in backend
        return '0' . $time;
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
        $model->setIdMobile($idMobile ? $idMobile : self::createUniqueIdMobile());

        return $model;
    }

    /**
     * Simple, list as array
     *
     * @return array array of models
     */
    public static function parseWhereFromRequest()
    {
        $name = self::getName();
        $name = $name::getModelName();
        $wheres = array();

        if (isset($_REQUEST))
        {
            $params = $_REQUEST;

            if (isset($params['q']))
            {
                unset($params['q']);
            }

            if (isset($params['limit']))
            {
                unset($params['limit']);
            }

            if (isset($params['offset']))
            {
                unset($params['offset']);
            }

            if (isset($params['orderBy']))
            {
                unset($params['orderBy']);
            }

            if (isset($params['orderWay']))
            {
                unset($params['orderWay']);
            }

            //put the params in the request, to filters can use
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

        return $wheres;
    }

    /**
     * Simple, list as array
     *
     * @return array array of models
     */
    public static function list($extraWhere = null)
    {
        $name = self::getName();
        $wheres = self::parseWhereFromRequest();
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
        }

        //no máximo 20 se não passar limite
        $limit = $limit ? $limit : 20;

        //merge where
        if (is_array($extraWhere))
        {
            $wheres = array_merge($wheres, $extraWhere);
        }

        //caso não tenha where retorna nada
        if (!$wheres)
        {
            $wheres = [new \Db\Cond("1=0")];
        }

        $result = $name::smartFind($smartSearch, $wheres, $limit, $offset, $orderBy, $orderWay, 'array');

        return $result;
    }

    public static function countAll()
    {
        $name = self::getName();
        $wheres = self::parseWhereFromRequest();

        return $name::count($wheres, '*');
    }

    public static function put()
    {
        $name = self::getName();
        $dataHandle = new \DataHandle\Request();
        $idMobile = \DataHandle\Request::get('idMobile');
        $id = \DataHandle\Request::get('id');

        //first by id, or by idMobile
        if ($id)
        {
            $model = $name::findOneByPkOrCreate($id);
        }
        else if ($idMobile)
        {
            $model = $name::findOneByIdMobileOrCreate($idMobile);
        }

        foreach ($dataHandle as $key => $value)
        {
            //avoid some crappy names
            if ($key == 'p' || $key == 'e' || $key == 'v')
            {
                continue;
            }

            $model->setValue($key, $value);
        }

        $model->save();

        return $model;
    }

    public static function del()
    {
        $name = self::getName();
        $id = \DataHandle\Request::get('id');

        if (!$id)
        {
            return false;
        }

        $model = $name::findOneByPk($id);

        if (!$model)
        {
            return false;
        }

        return $model->delete();
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
