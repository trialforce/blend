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
     * @var arrray
     */
    protected $params;

    /**
     * Replace simple param
     *
     * @var string
     */
    protected $replaceSimpleParam = FALSE;

    /**
     * DataSources
     * @var Array
     */
    protected $dataSources;

    /**
     * Child Datasources
     *
     * @var array
     */
    protected $childDataSources;

    /**
     * Child conditions
     * @var array
     */
    protected $childCond;

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

    function getReplaceSimpleParam()
    {
        return $this->replaceSimpleParam;
    }

    function setReplaceSimpleParam($replaceSimpleParam)
    {
        $this->replaceSimpleParam = $replaceSimpleParam;

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

    /**
     * Add a child datasource
     *
     * @param \DataSource\DataSource $datasource
     * @param string $section section name
     * @param string $childSection child section name
     *
     * @return $this
     */
    public function addChildDataSource(\DataSource\DataSource $datasource, $section, $childSection, $cond = null)
    {
        //convert to array
        if ($cond)
        {
            $cond = is_array($cond) ? $cond : array($cond);
        }

        $this->childDataSources[$section][$childSection] = $datasource;
        $this->childCond[$section][$childSection] = $cond;

        return $this;
    }

    /**
     * Return all the child datasources
     *
     * @return array
     */
    public function getChildDataSources($sectionName = null)
    {
        if ($sectionName)
        {
            if (isset($this->childDataSources[$sectionName]))
            {
                return $this->childDataSources[$sectionName];
            }

            return null;
        }

        return $this->childDataSources;
    }

    public function execute()
    {
        $this->content = $this->replaceContentParams($this->content);
        $originalContent = $this->content;
        $this->content = $this->replaceGlobalEvals($this->content);

        $dataSources = $this->getDataSources();

        if (isIterable($dataSources) && count($dataSources) > 0)
        {
            foreach ($dataSources as $sectionName => $dataSource)
            {
                $data = $dataSource->getData();
                //stores for further use
                $this->data[$sectionName] = $data;
                $columns = $dataSource->getColumns();
                $sectionContent = $this->getContentForSection($sectionName, $originalContent);
                $sectionContentReplace = $this->getContentForSection($sectionName, $this->content);
                $result = '';

                if (count($data) > 0)
                {
                    foreach ($data as $item)
                    {
                        $result .= $this->replaceOneItem($item, $columns, $sectionContent, $sectionName);
                    }
                }

                $this->content = str_replace($sectionContentReplace, $result, $this->content);
            }
        }

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

        if ($sectionContent)
        {
            $this->getContentForChildren($sectionContent, 'item');
        }

        return $sectionContent;
    }

    /**
     * Return the content of a children
     *
     * @param string $sectionContent section content
     * @param string $childName child name
     * @return string
     */
    private function getContentForChildren($sectionContent, $childName)
    {
        $pattern = '/<!--\*' . $childName . '-->.*<!--\*!' . $childName . '-->/uis';
        $matches = '';

        //locate the part of content of this child
        preg_match_all($pattern, $sectionContent, $matches);

        $childContent = NULL;

        if (isset($matches[0]) && isset($matches[0][0]))
        {
            $childContent = $matches[0][0];
        }

        return $childContent;
    }

    protected function replaceVariable($var, $value, $content)
    {
        if (is_array($value))
        {
            $value = implode(',', $value);
        }

        $result = str_replace('{$' . $var . '}', $value, $content);
        $result = str_replace('%7B%24' . $var . '%7D', $value, $result);

        return $result;
    }

    /**
     * Replace one item (line) from datasource
     *
     * @param mixed $item original item
     * @param array $columns columns
     * @param string $sectionContent section content
     * @return string
     */
    protected function replaceOneItem($item, $columns, $sectionContent, $sectionName)
    {
        $myResult = $sectionContent;
        $myResult = $this->makeExpressions($item, null, $sectionContent);

        //passes trough each column of model
        foreach ($columns as $columnName => $column)
        {
            //column is not used in this case
            $column = null;
            //replace default columns value
            $value = $this->getValue($item, $columnName);
            $myResult = $this->replaceVariable($columnName, $value, $myResult);
            //$myResult = str_replace('{$' . $columnName . '}', $value, $myResult);

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
            }
            else
            {
                $valueDescription = $this->getValue($item, $columnName . 'Description');
            }

            //make replace even it's empty
            $myResult = $this->replaceVariable($columnName . 'Description', $valueDescription, $myResult);
        }

        //after all collumn values, make simple replace by public propertys
        if (is_array($item) || is_object($item))
        {
            foreach ($item as $prop => $value)
            {
                $myResult = $this->replaceVariable($prop, $value, $myResult);
            }
        }

        //make the child replace
        if ($sectionName)
        {
            $childsDs = $this->getChildDataSources($sectionName);

            if (is_array($childsDs))
            {
                $childResult = '';

                foreach ($childsDs as $childName => $childDs)
                {
                    $childContent = $this->getContentForChildren($sectionContent, $childName);
                    //clone for each line has its' conditions
                    $childDs = clone($childDs);

                    //extra filters from child cond
                    if (isset($this->childCond[$sectionName][$childName]))
                    {
                        $conds = $this->childCond[$sectionName][$childName];

                        if (is_array($conds))
                        {
                            foreach ($conds as $cond)
                            {
                                //clone for each line
                                $cond = clone($cond);
                                $cond instanceof \Db\Cond;
                                $propertyToReplace = $cond->getValue();
                                $newValue = null;

                                if (isIterable($propertyToReplace))
                                {
                                    foreach ($propertyToReplace as $myProp)
                                    {
                                        $newValue[] = $this->getValue($item, $myProp);
                                    }
                                }

                                $cond->setValue($newValue);
                                $childDs->addExtraFilter($cond);
                            }
                        }
                    }

                    $columns = $childDs->getColumns();
                    $childData = $childDs->getData();

                    if (count($childData) > 0)
                    {
                        foreach ($childData as $itemChild)
                        {
                            $myChildResult = $this->replaceOneItem($itemChild, $columns, $childContent, NULL);
                            $myChildResult = $this->makeExpressions($item, $itemChild, $myChildResult);
                            $childResult .= $myChildResult;
                        }
                    }
                }

                $myResult = str_replace($childContent, $childResult, $myResult);
            }
        }

        //$myResult = $this->makeExpressionsFinal($item, $myResult);

        return $myResult;
    }

    /* public function makeExpressionsFinal($item, $content)
      {
      global $rGlobal;
      $matches = null;

      $regexp = '/\$\!{(.*)}/uUmi';
      preg_match_all($regexp, $content, $matches);

      if (is_array($matches[0]))
      {
      $expressionsContent = $matches[0];
      $expressions = $matches[1];

      //create variables for use in eval
      $param = $this->params;
      $father = array();

      if ($item instanceof \Db\Model)
      {
      $item = $item->getArray();
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
      } */

    public function makeExpressions($item, $itemChild, $content)
    {
        if (!$content)
        {
            return '';
        }
        global $rGlobal;
        $matches = null;

        $regexp = $itemChild ? '/\$\*{(.*)}/uUmi' : '/\${(.*)}/uUmi';
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
            $param = $this->params;
            $father = array();

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

            if ($itemChild instanceof \Db\Model)
            {
                $itemChild = $itemChild->getArray();
            }

            if (isIterable($itemChild))
            {
                foreach ($itemChild as $prop => $value)
                {
                    $$prop = $value;
                }
            }

            foreach ($expressions as $idx => $expression)
            {
                ob_start();
                $expression = html_entity_decode($expression);
                @eval('echo (' . $expression . ');');
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
     * @param type $columnName
     * @return type
     */
    public function getValue($item, $columnName)
    {
        $value = \DataSource\Grab::getDbValue($columnName, $item);

        return nl2br($value) . '';
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

        //generic params
        if (count($params) > 0)
        {
            foreach ($params as $param => $value)
            {
                $origim = array();

                if ($this->replaceSimpleParam)
                {
                    $origim[] = '%7B%24' . $param . '%7D';
                    $origim[] = '{$' . $param . '}';
                }

                $origim[] = '%7B%24param%5B\'' . $param . '\'%5D%7D';
                $origim[] = '{$param[' . "'" . $param . '\']}';

                $content = str_replace($origim, $value, $content);
            }
        }



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
                $expression = html_entity_decode($matches[1][$idx]);

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
     * @param stirng $modelName model name
     * @return void
     */
    public function setModelParams($model, $modelName)
    {
        if (!$model || !$modelName)
        {
            return;
        }

        $model = $model->getArray();

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

}
