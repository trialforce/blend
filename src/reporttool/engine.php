<?php

namespace ReportTool;

/**
 * Report
 */
class Engine
{

    const PAGE_SIZE_A4 = 'A4';
    const PAGE_SIZE_A4_LANDSCAPE = 'A4-L';

    /**
     * Layout
     *
     * @var \View\Layout
     */
    protected $layout;

    /**
     * Layout path
     *
     * @var string
     */
    protected $layoutPath;

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
     * Use default style sheet
     * @var bool
     */
    protected $defaultStyleSheet = TRUE;

    /**
     * Replace simple param
     *
     * @var string
     */
    protected $replaceSimpleParam = FALSE;

    /**
     * Report data
     * @var array
     */
    protected $data;

    /**
     * Page header (html)
     * @var string
     */
    protected $header;

    /**
     * Page footer (html)
     * @var string
     */
    protected $footer;

    /**
     * Margin
     * @var array
     */
    protected $margin = array(0, 0, 0, 0);

    /**
     * Export file path
     * @var string
     */
    protected $exportFile = null;

    public function __construct($layoutPath = NULL)
    {
        $this->layout = new \View\Layout(NULL, TRUE);

        if (!$layoutPath)
        {
            $layoutPath = $this->parseLayout();
        }

        if ($layoutPath)
        {
            $this->layoutPath = $layoutPath;
            $this->layout->loadFromFile($layoutPath);
        }

        $this->setPageSize(self::PAGE_SIZE_A4);
        $this->setSubtitle(''); //default
        $this->setMargin(5, 5, 5, 5);
    }

    /**
     * Margins in milimeter mm
     * @param int $left left margin
     * @param int $right right margin
     * @param int $top top margin
     * @param int $bottom bottom margin
     * @return $this
     */
    public function setMargin($left = 0, $right = 0, $top = 0, $bottom = 0)
    {
        $this->margin['left'] = $left;
        $this->margin['right'] = $right;
        $this->margin['top'] = $top;
        $this->margin['bottom'] = $bottom;

        return $this;
    }

    public function getMargin()
    {
        return $this->margin;
    }

    protected function parseLayout()
    {
        $layoutPath = '';

        $class = get_class($this);

        if ($class != 'ReportTool\Engine')
        {
            $layoutPath = str_replace(array('/', '\\'), DS, $class);
        }

        return $layoutPath;
    }

    /**
     * Define the current layout
     *
     * @param \View\Layout $layout
     * @return $this
     */
    public function setLayout(\View\Layout $layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Define a custom html as the default layout
     *
     * @param string $html
     * @return $this
     */
    public function setHtml($html)
    {
        $this->getLayout()->loadHTML($html);

        return $this;
    }

    /**
     * Define a layout from html body string
     *
     * @return \ReportTool\Engine
     */
    public function loadFromBody($body)
    {
        $head = "<title>{$this->getTitle()}</title>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width'>";

        $html = "<html>
                    <head>
                    $head
                    </head>
                    <body>
                    <!--default-->$body<!--!default-->
                    </body>
                </html>";

        $this->getLayout()->loadHTML($html);

        return $this;
    }

    /**
     * Get the default layout
     * @return \View\Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Define a layout path to be used
     *
     * @param string $layoutPath layout file name
     * @return $this
     */
    public function setLayoutPath($layoutPath)
    {
        $this->layoutPath = $layoutPath;
        return $this;
    }

    public function getLayoutPath()
    {
        return $this->layoutPath;
    }

    function getHeader()
    {
        return $this->header;
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

    function getFooter()
    {
        if (!$this->footer)
        {
            $layout = new \View\Layout(NULL, TRUE);
            $layout->loadFromFile('Report/Footer');

            $content = (string) $layout;
            $content = $this->replaceContentParams($content);

            $this->footer = $content;
        }

        return $this->footer;
    }

    function setHeader($header)
    {
        $this->header = $header;
    }

    function setFooter($footer)
    {
        $this->footer = $footer;
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

    public function setTitle($title)
    {
        $this->setParam('title', $title);
    }

    public function getTitle()
    {
        return $this->getParam('title');
    }

    public function setSubtitle($subtitle)
    {
        $this->setParam('subtitle', $subtitle);
    }

    public function getSubtitle()
    {
        return $this->getParam('subtitle');
    }

    function getReplaceSimpleParam()
    {
        return $this->replaceSimpleParam;
    }

    function setReplaceSimpleParam($replaceSimpleParam)
    {
        $this->replaceSimpleParam = $replaceSimpleParam;
    }

    public function setPageSize($pageSize = self::PAGE_SIZE_A4)
    {
        $this->setParam('pageSize', $pageSize);
    }

    public function getPageSize()
    {
        return $this->getParam('pageSize');
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

    public function getDefaultStyleSheet()
    {
        return $this->defaultStyleSheet;
    }

    public function setDefaultStyleSheet($defaultStyleSheet)
    {
        $this->defaultStyleSheet = $defaultStyleSheet;
    }

    /**
     * Generate the report (make the replaces)
     *
     * @return string
     */
    public function generate()
    {
        global $rGlobal;
        $rGlobal = array();

        if ($this->getDefaultStyleSheet())
        {
            $this->layout->addStyleShet('report', BLEND_PATH . '/reporttool/report.css', NULL, NULL);
        }

        $this->content = $this->layout->saveHTML();
        $this->content = $this->replaceContentParams($this->content);

        $dataSources = $this->getDataSources();

        if (count($dataSources) > 0)
        {
            foreach ($dataSources as $sectionName => $dataSource)
            {
                $data = $dataSource->getData();
                //stores for further use
                $this->data[$sectionName] = $data;
                $columns = $dataSource->getColumns();
                $sectionContent = $this->getContentForSection($sectionName);
                $result = '';

                if (count($data) > 0)
                {
                    foreach ($data as $item)
                    {
                        $result .= $this->replaceOneItem($item, $columns, $sectionContent, $sectionName);
                    }
                }

                $this->content = str_replace($sectionContent, $result, $this->content);
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
    public function getContentForSection($section)
    {
        $pattern = '/<!--' . $section . '-->.*<!--[!]' . $section . '-->/uis';
        $matches = '';

        //locate the part of content of this datasource
        preg_match_all($pattern, $this->content, $matches);

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

    protected function replaceEval()
    {

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
            if ($dbColumn instanceof \Db\Column && $dbColumn->getConstantValues())
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

        $myResult = $this->makeExpressions($item, null, $myResult);

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

        $myResult = $this->makeExpressionsFinal($item, $myResult);

        return $myResult;
    }

    public function makeExpressionsFinal($item, $content)
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
    }

    public function makeExpressions($item, $itemChild, $content)
    {
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
                if ($this->replaceSimpleParam)
                {
                    $origim[] = '%7B%24' . $param . '%7D';
                    $origim[] = '{$' . $param . '}';
                }
                else
                {
                    $origim[] = '%7B%24param%5B\'' . $param . '\'%5D%7D';
                    $origim[] = '{$param[' . "'" . $param . '\']}';
                }

                $content = str_replace($origim, $value, $content);
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
        $value = '';

        if ($item instanceof \Db\Model)
        {
            $value = $item->getValue($columnName);
        }
        else if (is_object($item))
        {
            $methodName = 'get' . $columnName;

            if (method_exists($item, $methodName))
            {
                $value = $item->$methodName();
            }
            else if (isset($item->{$columnName}))
            {
                $value = $item->{$columnName};
            }
        }
        else if (is_array($item))
        {
            if (isset($item[$columnName]))
            {
                $value = $item[$columnName];
            }
        }

        return nl2br($value) . '';
    }

    public function setExportFile($exportFile)
    {
        $this->exportFile = $exportFile;
    }

    /**
     * Retorna o arquivo
     *
     * @param string $type
     * @return \Disk\File
     */
    public function getExportFile($type = 'html')
    {
        if (!$this->exportFile)
        {
            $relativePath = strtolower('report/' . $this->layoutPath . '_' . rand()) . '.' . $type;
            $this->exportFile = \Disk\File::getFromStorage($relativePath);
        }

        return $this->exportFile;
    }

    /**
     * Generate the file in disk
     *
     * @return \Disk\File
     */
    public function generateFile($type)
    {
        //generate report if needed
        if (!$this->content)
        {
            $this->generate();
        }

        $file = $this->getExportFile($type);

        if ($type == 'pdf')
        {
            $file->createFolderIfNeeded();
            $mpdf = $this->getMpdfObj();

            if ($this->getHeader())
            {
                $mpdf->SetHTMLHeader($this->getHeader());
            }

            if ($this->getFooter())
            {
                $mpdf->SetHTMLFooter($this->getFooter());
            }

            $mpdf->WriteHTML($this->content);

            $mpdf->Output($file->getPath());
        }
        else
        {
            $this->content = $this->getHeader() . $this->content . $this->getFooter();
            $file->save($this->content);
        }

        return $file;
    }

    /**
     * Return the mpdf object
     *
     * Commonly used to control page margin, and other mpdf needs to report
     *
     * @return \mPDF
     */
    protected function getMpdfObj()
    {
        if (\DataHandle\Config::get('wkpdf-path'))
        {
            return new \ReportTool\WkPdf('utf-8', $this->getPageSize(), 0, '', $this->margin['left'], $this->margin['right'], $this->margin['top'], $this->margin['bottom'], 0, 0);
        }
        else
        {
            return new \mPDF('utf-8', $this->getPageSize(), 0, '', $this->margin['left'], $this->margin['right'], $this->margin['top'], $this->margin['bottom'], 0, 0);
        }
    }

    /**
     * Make the output of the report
     *
     * @param string $type
     */
    public function output($type = 'html')
    {
        $file = $this->generateFile($type);
        $file->outputToBrowser();
    }

    /**
     * Make the ouput of report (inline)
     */
    public function outputInline($type = NULL)
    {
        $type = $type ? $type : 'pdf';
        $type = \DataHandle\Request::get('type') ? \DataHandle\Request::get('type') : $type;

        $file = $this->generateFile($type);
        $file->outputInline();
    }

    /**
     * Add a custom font to mpdf
     * @param Mpdf $mpdf mpdf object
     * @param array $fonts_list array
     */
    public static function addCustomFontList($mpdf, $fonts_list)
    {
        // Logic from line 1146 mpdf.pdf - $this->available_unifonts = array()...
        foreach ($fonts_list as $f => $fs)
        {
            // add to fontdata array
            $mpdf->fontdata[$f] = $fs;

            // add to available fonts array
            if (isset($fs['R']) && $fs['R'])
            {
                $mpdf->available_unifonts[] = $f;
            }
            if (isset($fs['B']) && $fs['B'])
            {
                $mpdf->available_unifonts[] = $f . 'B';
            }
            if (isset($fs['I']) && $fs['I'])
            {
                $mpdf->available_unifonts[] = $f . 'I';
            }
            if (isset($fs['BI']) && $fs['BI'])
            {
                $mpdf->available_unifonts[] = $f . 'BI';
            }
        }

        $mpdf->default_available_fonts = $mpdf->available_unifonts;
    }

    /**
     * Add a custom font to mpdf
     * @param Mpdf $mpdf mpdf object
     * @param array $fonts_list array
     */
    protected function addCustomFont($mpdf, $fonts_list)
    {
        return self::addCustomFontList($mpdf, $fonts_list);
    }

}
