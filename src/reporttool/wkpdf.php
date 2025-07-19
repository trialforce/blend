<?php

namespace ReportTool;

/**
 * Wrapper for WkHtmltoPpdf to work with MPdf methods
 *
 * It will only be used with you set 'wkpdf-path' file path your configuratio file.
 */
class WkPdf extends \mikehaertl\wkhtmlto\Pdf
{

    public function __construct($mode = '', $format = 'A4', $default_font_size = 0, $default_font = '', $mgl = 15, $mgr = 15, $mgt = 16, $mgb = 16, $mgh = 9, $mgf = 9, $orientation = 'P')
    {
        $format = null;
        $default_font = '';
        $default_font_size = 0;
        $mgh = 0;
        $mgf = 0;
        $orientation = 'P';
        //$this->wk = $this->constructWk('UTF-8', 5, 5, 5, 5);

        $mode = $mode ?: 'UFT-8';

        parent::__construct(array(
            'binary' => \DataHandle\Config::get('wkpdf-path'),
            'encoding' => $mode,
            'no-outline',
            'margin-left' => $mgl,
            'margin-right' => $mgr,
            'margin-top' => $mgt,
            'margin-bottom' => $mgb,
            // Default page options
            'disable-smart-shrinking',
            'enable-local-file-access'
                //'user-style-sheet' => '/path/to/pdf.css',
        ));
    }

    public function addOption($option, $value)
    {
        $this->_options[$option] = $value;

        return $this;
    }

    public function setHtmlHeader($headerHtml, $align = 'right')
    {
        $align = $align ?: 'right';
        $this->addOption('header-' . $align, trim(strip_tags($headerHtml)));
        $this->addOption('header-font-size', '8');
    }

    public function setHtmlFooter($footerHtml, $align = 'right')
    {
        $align = $align ?: 'right';
        $this->addOption('footer-' . $align, trim(strip_tags($footerHtml)));
        $this->addOption('footer-font-size', '8');
    }

    public function writeHtml($html)
    {
        //TODO add suport for header css writeHtml($css,1)
        $this->addPage($html);
    }

    public function output($path)
    {
        if (file_exists($path))
        {
            unlink($path);
        }

        $this->saveAs($path);

        if (!file_exists($path))
        {
            throw new \Exception('Impossible criar WKPDF: ' . $this->getError());
        }

        return true;
    }

}
