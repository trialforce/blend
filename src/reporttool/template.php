<?php

namespace ReportTool;

class Template
{

    /**
     * Content
     * @var string
     */
    protected $content;

    /**
     * Params
     * @var array
     */
    protected $params;

    /**
     * DataSources
     * @var array
     */
    protected $dataSources;

    /**
     * Report data
     * @var array
     */
    protected $data;

    public function __construct()
    {

    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    public function setParam($param, $value)
    {
        $this->params[$param] = $value;

        return $this;
    }

    public function getParam($param)
    {
        if (isset($this->params[$param]))
        {
            return $this->params[$param];
        }

        return NULL;
    }

    /**
     * Add params from a iterable object or array
     *
     * @param iterable $iterable
     */
    public function addParams($iterable)
    {
        if (isIterable($iterable))
        {
            foreach ($iterable as $property => $value)
            {
                $this->setParam($property, $value);
            }
        }
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Return the list of datasources
     *
     * @return array
     */
    public function getDataSources()
    {
        return $this->dataSources;
    }

    /**
     * Add a list of datasources
     *
     * @param array $dataSources
     * @return $this
     */
    public function setDataSources($dataSources)
    {
        $this->dataSources = $dataSources;
        return $this;
    }

    /**
     * Add one datasource to the report
     *
     * @param \DataSource\DataSource $datasource
     * @param string $section section name
     * @return $this
     */
    public function addDataSource(\DataSource\DataSource $datasource, $section = 'default')
    {
        $this->dataSources[$section] = $datasource;
        return $this;
    }

    public function execute()
    {
        //\Misc\Timer::getGlobalTimer()->reset();
        //pass trough datasource to put the default _count parameters
        $dataSources = $this->getDataSources();
        $this->content = $this->replaceContentParams($this->content);
        $originalContent = $this->content;

        if (isIterable($dataSources) && count($dataSources) > 0)
        {
            foreach ($dataSources as $sectionName => $dataSource)
            {
                $data = $dataSource->getData();
                //create the default _count parameter
                $countData = isCountable($data) ? count($data) : 0;
                $this->setParam($sectionName . '_count', $countData);

                //stores for further use
                $this->data[$sectionName] = $data;
                $columns = $dataSource->getColumns();
                $sectionContent = $this->getContentForSection($sectionName, $originalContent);
                $sectionContentReplace = $this->getContentForSection($sectionName, $this->content);
                $result = '';

                if (isIterable($data) && count($data) > 0)
                {
                    foreach ($data as $position => $item)
                    {
                        $result .= $this->replaceOneItem($item, $columns, $sectionContent, $sectionName, $position);
                    }
                }

                if ($sectionContentReplace)
                {
                    $this->content = str_replace($sectionContentReplace.'', $result, $this->content);
                }
            }
        }

        $this->content = $this->replaceGlobalEvals($this->content);
        $this->removePropertysMissing();

        return $this->content;
    }

    /**
     * Remove missing propertys
     */
    public function removePropertysMissing()
    {
        $matches = '';
        preg_match_all('/{\$(.*)}/uUmi', $this->content, $matches);

        if (is_array($matches[0]))
        {
            foreach ($matches[0] as $match)
            {
                $this->content = str_replace($match, '', $this->content);
            }
        }

        // necessário para remover as tags de looping, ex: <!--produto-images-->, <!--!produto-images-->
        $this->content = preg_replace('/<!--(.|\s)*?-->/', '', $this->content);
    }

    /**
     * Return the part of content for one section
     *
     * @param string $section section name
     *
     * @return string
     */
    public function getContentForSection($section, $originalContent)
    {
        $pattern = '/<!--' . $section . '-->.*<!--[!]' . $section . '-->/uis';
        $matches = '';

        //locate the part of content of this datasource
        preg_match_all($pattern, $originalContent, $matches);

        $sectionContent = NULL;

        if (isset($matches[0]) && isset($matches[0][0]))
        {
            $sectionContent = $matches[0][0];
        }

        return $sectionContent;
    }

    protected function replaceVariable($var, $value, $content)
    {
        if (is_array($value))
        {
            $value = implode(',', $value);
        }

        $result = str_replace('{$' . $var . '}', $value.'', $content . '');
        $result = str_replace('%7B%24' . $var . '%7D', $value.'', $result);

        return $result;
    }

    /**
     * Replace one item (line) from datasource
     *
     * @param mixed $item original item
     * @param array $columns columns
     * @param string $sectionContent section content
     * @param int $position position in array
     * @return string
     * @throws \Exception
     */
    protected function replaceOneItem($item, $columns, $sectionContent, $sectionName, $position = null)
    {
        $myResult = $sectionContent;
        //add support for position in array variable
        $myResult = $this->replaceVariable('position', $position, $myResult);
        $item->position = $position;
        $myResult = $this->makeExpressions($item, $sectionContent);

        //passes trough each column of model
        foreach ($columns as $columnName => $column)
        {
            //column is not used in this case
            $column = null;
            //replace default columns value
            $value = $this->getValue($item, $columnName);
            $myResult = $this->replaceVariable($columnName, $value, $myResult);
            $dbColumn = null;

            //support setReferenceDescriptin data
            if ($item instanceof \Db\Model)
            {
                $dbColumn = $item->getColumn($columnName);
            }

            //add suport for constant values
            if ($dbColumn instanceof \Db\Column\Column && $dbColumn->getConstantValues())
            {
                $array = $dbColumn->getConstantValues();
                $valueDescription = '';

                if (isset($array[$value]) && $array[$value])
                {
                    $valueDescription = $array[$value];
                }
                //make replace even it's empty
                $myResult = $this->replaceVariable($columnName . 'Description', $valueDescription, $myResult);
            }
        }

        //after all collumn values, make simple replace by public propertys
        if (is_array($item) || is_object($item))
        {
            foreach ($item as $prop => $value)
            {
                $myResult = $this->replaceVariable($prop, $value, $myResult);
            }
        }

        return $myResult;
    }

    public function makeExpressions($item, $content)
    {
        if (!$content)
        {
            return '';
        }
        global $rGlobal;
        $matches = null;

        $regexp = '/\${(.*)}/uUmi';
        preg_match_all($regexp, $content, $matches);

        if (is_array($matches[0]))
        {
            $expressionsContent = $matches[0];
            $expressions = $matches[1];
            $model = $item;

            if ($item instanceof \Db\Model)
            {
                $item = $item->getArray();
            }

            //create variables for use in eval
            $params = $this->params;
            $father = array();

            foreach ($params as $param => $value)
            {
                $$param = $value;
            }

            //create variables for use in eval
            if (isIterable($item))
            {
                foreach ($item as $prop => $value)
                {
                    $$prop = $value;
                    //fill father props, in case chield has same names
                    $father[$prop] = $value;
                }
            }

            foreach ($expressions as $idx => $expression)
            {
                ob_start();
                $expression = html_entity_decode($expression);
                eval('echo (' . $expression . ');');
                $result = ob_get_contents();
                ob_end_clean();
                $find = $expressionsContent[$idx];
                $content = str_replace($find, $result, $content);
            }
        }

        return $content;
    }

    /**
     * Return the value of one property
     *
     * @param \Db\Model $item
     * @param string $columnName
     * @return string
     */
    public function getValue($item, $columnName)
    {
        $value = \DataSource\Grab::getDbValue($columnName, $item);

        return nl2br($value.'');
    }

    /**
     * Replace global parametros
     *
     * @param string $content
     * @return string
     */
    public function replaceContentParamsNew($content)
    {
        $matches = null;
        //{$site_url}
        $regexp = '/(?<!\$){\$(.*)}/uUmi';
        preg_match_all($regexp, $content, $matches);

        if (is_array($matches[0]))
        {
            $params = $this->params;
            //create param as public variables
            foreach ($params as $prop => $value)
            {
                $$prop = $value;
            }

            foreach ($matches[0] as $idx => $item)
            {
                $original = $item;
                $param = htmlspecialchars_decode(html_entity_decode($matches[1][$idx]), ENT_QUOTES);
                $value = $this->getParam($param);

                $origim[] = '%7B%24' . $param . '%7D';
                $origim[] = '{$' . $param . '}';

                $content = str_replace($origim, $value.'', $content);
            }
        }

        return $content;
    }

    /**
     * Replace global parametros
     *
     * @param string $content
     * @return string
     */
    public function replaceContentParams($content)
    {
        $params = $this->getParams();
        $replaces = [];

        //generic params
        if (count($params) > 0)
        {
            foreach ($params as $param => $value)
            {
                $replaces['%7B%24' . $param . '%7D'] = $value;
                $replaces['{$' . $param . '}'] = $value ;
            }
        }

        //$content = str_replace(array_keys($replaces), $replaces, $content);
        $content = strtr($content, $replaces);

        return $content;
    }

    /**
     * Replace global evals
     *
     * @global array $rGlobal
     * @param string $content
     * @return string
     */
    public function replaceGlobalEvals($content)
    {
        global $rGlobal;
        $matches = null;
        $regexp = '/\${(.*)}/uUmi';
        preg_match_all($regexp, $content, $matches);

        $params = $this->params;

        if (is_array($matches[0]))
        {
            //create param as public variables
            foreach ($params as $prop => $value)
            {
                $$prop = $value;
            }

            foreach ($matches[0] as $idx => $item)
            {
                $original = $item;
                $expression = htmlspecialchars_decode(html_entity_decode($matches[1][$idx]), ENT_QUOTES);
                ob_start();
                eval('echo (' . $expression . ');');
                $result = ob_get_contents();
                ob_end_clean();
                $content = str_replace($original, $result, $content);
            }
        }

        return $content;
    }

    /**
     * Add a all propertys from a model to a global param
     *
     * @param \Db\Model $model the current model
     * @param string $modelName model name
     * @return \ReportTool\Template
     */
    public function setModelParams($model, $modelName, $setModel = false)
    {
        if (!$model || !$modelName)
        {
            return $this;
        }

        if ($setModel)
        {
            //put the entire model like a para
            $this->setParam($modelName, $model);
        }

        $model = $model->getArray(false);

        foreach ($model as $property => $value)
        {
            if (is_array($value))
            {
                $value = print_r($value, 1);
            }

            $paramName = $modelName . '_' . $property;

            $this->setParam($paramName, $value);
        }

        return $this;
    }

    /**
     * Execute the template and return it's string representation
     *
     * @return string return the result of the execute
     */
    public function __toString()
    {
        return $this->execute();
    }

}
