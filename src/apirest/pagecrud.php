<?php

namespace ApiRest;

class PageCrud extends \ApiRest\Page
{

    /**
     * Simple, list as array
     *
     * @return array array of models
     */
    public static function parseWhereFromRequest()
    {
        $name = static::$modelClass;
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

        if (isset($_REQUEST['q']))
        {
            $wheres = $name::smartFilters($_REQUEST['q'], $wheres);
        }

        return $wheres;
    }

    protected function getQueryBuilder()
    {
        $wheres = self::parseWhereFromRequest();

        $limit = \DataHandle\Get::getDefault('limit', 10);
        $offset = \DataHandle\Get::getDefault('offset', null);
        $orderBy = \DataHandle\Get::getDefault('orderBy', null);

        $query = static::$modelClass::query()
                ->addWhere($wheres)
                ->limit($limit)
                ->offset($offset)
                ->orderBy($orderBy);

        return $query;
    }

    public function get()
    {
        $query = $this->getQueryBuilder();
        $models = $query->toCollection();

        $result = [];

        foreach ($models as $item)
        {
            $result[] = $this->getBrigdeOut($item);
        }

        return $result;
    }

    /**
     * The default get by id
     *
     * Ex: api/page/1
     *
     * @return \Db\Model
     * @throws \UserException
     */
    public function getPk()
    {
        $id = \DataHandle\Get::get('e');

        if ($id)
        {
            $model = static::$modelClass::findOneByPK($id);

            if ($model)
            {
                return static::getBrigdeOut($model);
            }
            else
            {
                throw new \UserException('Impossível encontrar registro ' . $this->getPageUrl() . ' id ' . $id);
            }
        }

        throw new \UserException('Impossível encontrar registro ' . $this->getPageUrl() . ' id ' . $id);
    }

    /**
     * Default api post (create/update)
     *
     * @return \stdClass return the updated model
     * @throws \UserException
     */
    public function post()
    {
        $json = \ApiRest\App::getPostData();
        $id = \DataHandle\Get::get('e');
        $model = $this->getModelFromUrl('e');

        if ($id && !$model)
        {
            throw new \UserException('Impossível encontrar registro ' . $this->getPageUrl() . ' id ' . $id);
        }

        $model = static::getBrigdeIn($json, $model);

        $errors = $model->validate();

        if (is_array($errors))
        {
            $stdClass = new \stdClass();
            $stdClass->error = 'Erro de validação';
            $stdClass->code = '99';
            $stdClass->info = $errors;

            return $stdClass;
        }

        $model->save();

        return $model;
    }

    /**
     * Default api put (create/update)
     *
     * @return \stdClass return the updated model
     * @throws \UserException
     */
    public function put()
    {
        return $this->post();
    }

    /**
     * Default id model delete
     *
     * @return stdclass a stdclas with, id, deleted and the model
     *
     * @throws \UserException
     */
    public function delete()
    {
        $id = \DataHandle\Get::get('e');
        $model = $this->getModelFromUrl();

        if (!$model)
        {
            throw new \UserException('Impossível encontrar registro ' . $this->getPageUrl() . ' id ' . $id);
        }

        $model->delete();

        return ['id' => $id, 'deleted' => true, 'model' => static::getBrigdeOut($model)];
    }

    /**
     * Return a model based on pk url parameter
     *
     * @param string $variable variable from id
     * @return \Db\Model
     */
    protected function getModelFromUrl($variable = 'e')
    {
        $id = \DataHandle\Get::get($variable);
        $model = static::$modelClass::findOneByPK($id);

        return $model;
    }

    public static function getBrigdeIn($json, $model = null)
    {
        $model = $model ? $model : new self::$className();

        foreach ($json as $name => $variable)
        {
            $model->setValue($name, $variable);
        }

        return $model;
    }

    public static function getBrigdeOut($model)
    {
        return $model;
    }

}
