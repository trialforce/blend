<?php

namespace ReportTool;

/**
 * Report
 */
class Engine extends Template
{

    const PAGE_SIZE_A4 = 'A4';
    const PAGE_SIZE_A4_LANDSCAPE = 'A4-L';
    const PAGE_SIZE_A3 = 'A3';
    const PAGE_SIZE_A3_LANDSCAPE = 'A3-L';

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
     * Use default style sheet
     * @var bool
     */
    protected $defaultStyleSheet = TRUE;

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
            $layoutPath = str_replace(array('/', '\\'), '/', $class);
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

    public function setPageSize($pageSize = self::PAGE_SIZE_A4)
    {
        $this->setParam('pageSize', $pageSize);
    }

    public function getPageSize()
    {
        return $this->getParam('pageSize');
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

        return $this->execute();
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
    public function getMpdfObj()
    {
        if (\DataHandle\Config::get('wkpdf-path'))
        {
            return new \ReportTool\WkPdf('utf-8', $this->getPageSize(), 0, '', $this->margin['left'], $this->margin['right'], $this->margin['top'], $this->margin['bottom'], 0, 0);
        }
        //mpdf 6
        else if (class_exists('mPDF'))
        {
            return new \mPDF('utf-8', $this->getPageSize(), 0, '', $this->margin['left'], $this->margin['right'], $this->margin['top'], $this->margin['bottom'], 0, 0);
        }
        //mpdf 8
        else if (class_exists('Mpdf\Mpdf'))
        {
            $mpdfConfig = [];
            $mpdfConfig['mode'] = 'utf-8';
            $mpdfConfig['format'] = $this->getPageSize();
            $mpdfConfig['orientation'] = 'P';
            $mpdfConfig['margin_left'] = $this->margin['left'];
            $mpdfConfig['margin_right'] = $this->margin['right'];
            $mpdfConfig['margin_top'] = $this->margin['top'];
            $mpdfConfig['margin_bottom'] = $this->margin['bottom'];
            $mpdfConfig['default_font_size'] = 9;
            $mpdfConfig['default_font'] = '';

            return new \Mpdf\Mpdf($mpdfConfig);
        }

        throw new \UserException('Impossível encontrar biblioteca de geração de PDF');
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
