<?php

namespace Component\Grid;

use DataHandle\Request;
use DataHandle\Cookie;

/**
 * Paginador
 */
class Paginator extends \View\Div
{

    /**
     * Link to grid
     *
     * @var Simple
     */
    protected $grid;

    /**
     * Count messafe (footer)
     * @var string
     */
    protected $countMessage;

    /**
     * Construct the paginator
     *
     * @param \View\Layout $dom
     * @param string $id
     * @param \Component\Grid\Grid $grid
     */
    public function __construct($id, $grid)
    {
        parent::__construct($id ? $id : 'paginator');
        $this->setGrid($grid);
    }

    /**
     * Return a grid
     *
     * @return \Component\Grid\Grid
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * Define a grid
     * @param \Component\Grid\Grid $grid
     */
    public function setGrid($grid)
    {
        $this->grid = $grid;
    }

    /**
     * Retorna o datasource
     * @return \DataSource\DataSource
     */
    public function getDataSource()
    {
        return $this->getGrid()->getDataSource();
    }

    public function getCountMessage()
    {
        return $this->countMessage;
    }

    public function setCountMessage($countMessage)
    {
        $this->countMessage = $countMessage;

        return $this;
    }

    /**
     * Retorna a página atual
     *
     * @return int
     */
    protected function getCurrentPage()
    {
        $currentPage = 0;

        if (Request::get('page'))
        {
            $currentPage = Request::get('page');
        }

        return $currentPage;
    }

    protected function getPrevPage()
    {
        $currentPage = $this->getCurrentPage();
        return $currentPage - 1 < 0 ? 0 : $currentPage - 1;
    }

    protected function getNextPage()
    {
        $currentPage = $this->getCurrentPage();
        $lastPage = $this->getLastPage();
        return $currentPage + 1 > $lastPage ? $lastPage : $currentPage + 1;
    }

    protected function getLastPage()
    {
        $count = $this->getCount();
        $dataSource = $this->getDataSource();
        $paginationLimit = $dataSource->getPaginationLimit();

        return (int) ( ($count - 1) / $paginationLimit );
    }

    /**
     * Retorna a contagem total de registros
     *
     * @return int
     */
    protected function getCount()
    {
        $countElementId = $this->getCountElementId();
        $dataSource = $this->getDataSource();

        if (is_numeric(Request::get($countElementId)) && $this->getCurrentPage() != 0)
        {
            $count = Request::get($countElementId);
        }
        else if (!$dataSource->getLimit() || $dataSource->getLimit() == 0)
        {
            $count = count($dataSource->getData());
        }
        else
        {
            $count = $dataSource->getCount();
        }

        return $count;
    }

    /**
     * Retorna o id do elemento de contagem, usado para cache
     *
     * @return string
     */
    public function getCountElementId()
    {
        return $this->getGrid()->getId() . 'Count';
    }

    /**
     * Retorna se é ou não para fazer paginador
     *
     * @return boolean
     */
    protected function getMakePaginator()
    {
        $makePaginator = TRUE;
        $dataSource = $this->getDataSource();

        if (!$dataSource->getLimit() || $dataSource->getLimit() == 0)
        {
            $makePaginator = FALSE;
        }

        return $makePaginator;
    }

    /**
     * Return the name of current model
     *
     * @return string
     */
    public static function getModelName()
    {
        $pageName = 'registro';

        $dom = \View\View::getDom();

        if (method_exists($dom, 'getModel'))
        {
            $model = $dom->getModel();
            $pageName = $model? $model->getLabel() : '';
        }

        return $pageName;
    }

    /**
     * Return the label of current model in plural
     *
     * @return string
     */
    public static function getModelNamePlural()
    {
        $pageName = 'registros';

        $dom = \View\View::getDom();

        if (method_exists($dom, 'getModel'))
        {
            $model = $dom->getModel();
            $pageName = $model ? $model->getLabelPlural() : '';
        }

        return $pageName;
    }

    /**
     * Return the current pagination limit value
     *
     * @return int
     */
    public function getCurrentPaginationLimitValue()
    {
        $id = $this->grid->getGridName();

        $value = Cookie::getDefault('paginationLimitCookie', \DataSource\DataSource::DEFAULT_PAGE_LIMIT);
        $value = Request::getDefault('paginationLimit-' . $id, $value);

        return $value;
    }

    /**
     * Create pagination limit field
     *
     * @return \View\Select
     */
    public function createPaginationLimitField()
    {
        $value = $this->getCurrentPaginationLimitValue();
        Cookie::set('paginationLimitCookie', $value);

        $id = $this->grid->getGridName();

        $paginationLimit = new \View\Select('paginationLimit-' . $id, static::listPaginationLimitOptions(), $value, 'paginationLimit fr no-print-screen');
        $paginationLimit->setTitle('Limite de registros por página');
        $link = $this->getGrid()->getLink('listar');
        $paginationLimit->change("p('" . $link . "');");

        return $paginationLimit;
    }

    public function createPaginationFontSizeField()
    {
        return new \View\Button(null, 'Aa', 'grid.changeTextSize()', 'clean fr grid-change-text-size no-print-screen');
    }

    protected function createExportButton()
    {
        $url = $this->getGrid()->getLink('gridExportData');
        $exportBtn = new \View\Ext\LinkButton('gridExport', 'download', '', $url, 'clean fl icon-only');
        $exportBtn->setTitle('Exportar registros em forma de arquivos');
        return $exportBtn;
    }

    protected function getCaptionMessage()
    {
        $count = $this->getCount();
        $makePaginator = $this->getMakePaginator();
        $lastPage = $this->getLastPage();

        $pageName = lcfirst(self::getModelName());
        $pageNamePlural = lcfirst(self::getModelNamePlural());

        if ($this->getCountMessage())
        {
            $captionMessage = $this->getCountMessage();
        }
        else if ($lastPage > 0 && $makePaginator)
        {
            $captionMessage = "{$count} {$pageNamePlural}";
        }
        else
        {
            if ($count == 0)
            {
                $pageName = lcfirst($pageName);
                $captionMessage = "Nenhum registro encontrado.";
            }
            else
            {
                $captionMessage = "Mostrando $count {$pageNamePlural} (total).";
            }
        }

        return $captionMessage;
    }

    protected function getBtnFirt()
    {
        $urlExtra['orderBy'] = Request::get('orderBy');
        $urlExtra['orderWay'] = Request::get('orderWay');

        $urlExtra['page'] = 0;
        $url = $this->grid->getLink('listar', '', $urlExtra);

        $firts = new \View\A('first', '<i class="fa fa-angle-double-left">&nbsp;</i>', $url, 'btn clean first no-print-screen');

        return $firts;
    }

    protected function getBtnPrev()
    {
        $urlExtra['orderBy'] = Request::get('orderBy');
        $urlExtra['orderWay'] = Request::get('orderWay');
        $urlExtra['page'] = $this->getPrevPage();
        $url = $this->grid->getLink('listar', '', $urlExtra);

        return new \View\A('prev', '<i class="fa fa-angle-left">&nbsp;</i>', $url, 'btn clean prev no-print-screen');
    }

    protected function getBtnCurrent()
    {
        $currentPage = $this->getCurrentPage();
        $lastPage = $this->getLastPage();

        $urlExtra['orderBy'] = Request::get('orderBy');
        $urlExtra['orderWay'] = Request::get('orderWay');

        $currentPageShow = $currentPage + 1;
        $lastPageShow = $lastPage + 1;

        $urlExtra['page'] = $currentPage;
        $url = $this->grid->getLink('listar', '', $urlExtra);

        return new \View\A('current', "{$currentPageShow}/{$lastPageShow}", $url, 'btn clean no-print-screen');
    }

    protected function getBtnNext()
    {
        $urlExtra['orderBy'] = Request::get('orderBy');
        $urlExtra['orderWay'] = Request::get('orderWay');
        $urlExtra['page'] = $this->getNextPage();

        $url = $this->grid->getLink('listar', '', $urlExtra);

        return new \View\A('next', '<i class="fa fa-angle-right">&nbsp;</i>', $url, 'btn clean next no-print-screen');
    }

    protected function getBtnLast()
    {
        $urlExtra['orderBy'] = Request::get('orderBy');
        $urlExtra['orderWay'] = Request::get('orderWay');
        $urlExtra['page'] = $this->getLastPage();

        $url = $this->grid->getLink('listar', '', $urlExtra);

        return new \View\A('last', '<i class="fa fa-angle-double-right">&nbsp;</i>', $url, 'btn clean last no-print-screen');
    }

    public function onCreate()
    {
        $content = [];
        $content[] = $this->createPaginationLimitField();
        $content[] = $this->createPaginationFontSizeField();

        $count = $this->getCount();
        $lastPage = $this->getLastPage();

        if ($count > 0)
        {
            $content[] = $this->createExportButton();
        }

        $content[] = new \View\Span(null, $this->getCaptionMessage(), 'text');

        if ($lastPage > 0 && $this->getMakePaginator())
        {
            $link['first'] = $this->getBtnFirt();
            $link['prev'] = $this->getBtnPrev();
            $link['current'] = $this->getBtnCurrent();
            $link['next'] = $this->getBtnNext();
            $link['last'] = $this->getBtnLast();

            $content[] = new \View\Span('', $link, 'paginator', 'clearfix');
        }

        $this->append($content);

        return $this;
    }

    public static function listPaginationLimitOptions()
    {
        $options = [];
        $options[10] = 10;
        $options[15] = 15;
        $options[20] = 20;
        $options[30] = 30;
        $options[50] = 50;

        return $options;
    }

}
