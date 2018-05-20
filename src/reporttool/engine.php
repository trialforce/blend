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
     * @var mixed
     */
    protected $dataSources;

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

    public function getLayout()
    {
        return $this->layout;
    }

    public function setLayout(\View\Layout $layout)
    {
        $this->layout = $layout;
        return $this;
    }

    function getHeader()
    {
        return $this->header;
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

    public function getLayoutPath()
    {
        return $this->layoutPath;
    }

    public function setLayoutPath($layoutPath)
    {
        $this->layoutPath = $layoutPath;
        return $this;
    }

    public function getDataSources()
    {
        return $this->dataSources;
    }

    public function setDataSources($dataSources)
    {
        $this->dataSources = $dataSources;
        return $this;
    }

    public function addDataSource(\DataSource\DataSource $datasource, $section = 'default')
    {
        $this->dataSources[$section] = $datasource;
        return $this;
    }

    public function getDefaultStyleSheet()
    {
        return $this->defaultStyleSheet;
    }

    public function setDefaultStyleSheet($defaultStyleSheet)
    {
        $this->defaultStyleSheet = $defaultStyleSheet;
    }

    public function generate()
    {
        if ($this->getDefaultStyleSheet())
        {
            $this->layout->addStyleShet('report', BLEND_PATH . '/reporttool/report.css', NULL, NULL);
        }

        $this->content = $this->layout->saveHTML();
        $this->content = $this->replaceContentParams($this->content);

        $dataSources = $this->getDataSources();

        if (count($dataSources) > 0)
        {
            foreach ($dataSources as $section => $dataSource)
            {
                $data = $dataSource->getData();
                //stores for further use
                $this->data[$section] = $data;
                $columns = $dataSource->getColumns();
                $pattern = '/<!--' . $section . '-->.*<!--[!]' . $section . '-->/uis';
                $matches = '';
                $result = '';

                preg_match_all($pattern, $this->content, $matches);

                $originalText = NULL;

                if (isset($matches[0]) && isset($matches[0][0]))
                {
                    $originalText = $matches[0][0];
                }

                if (count($data) > 0)
                {
                    foreach ($data as $item)
                    {
                        if (method_exists($item, 'fillExtraData'))
                        {
                            $item = $item->fillExtraData();
                        }

                        $myResult = $originalText;

                        foreach ($columns as $columnName => $column)
                        {
                            //column is not used in this case
                            $column = null;
                            $value = $this->getValue($item, $columnName);
                            $myResult = str_replace('{$' . $columnName . '}', $value, $myResult);

                            $dbColumn = null;

                            if ($item instanceof \Db\Model)
                            {
                                //support setReferenceDescriptin data
                                $dbColumn = $item->getColumn($columnName);
                            }

                            //add suporte for constante values
                            if ($dbColumn instanceof \Db\Column && $dbColumn->getConstantValues())
                            {
                                $array = $dbColumn->getConstantValues();

                                if ($array instanceof \Db\ConstantValues)
                                {
                                    $array = $array->getArray();
                                }

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

                            $myResult = str_replace('{$' . $columnName . 'Description}', $valueDescription, $myResult);
                        }

                        $result .= $myResult;
                    }
                }

                $this->content = str_replace($originalText, $result, $this->content);
            }
        }

        return $this->content;
    }

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

    /**
     * Retorna o arquivo
     *
     * @param string $type
     * @return \Disk\File
     */
    public function getExportFile($type = 'html')
    {
        $relativePath = strtolower('report/' . $this->layoutPath . '_' . rand()) . '.' . $type;
        return \Disk\File::getFromStorage($relativePath);
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * Generate the file in disk
     *
     * @return \Disk\File
     */
    public function generateFile($type)
    {
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
        return new \mPDF('utf-8', $this->getPageSize(), 0, '', 0, 0, 0, 0, 0, 0);
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
     * Obter relatório
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

}
