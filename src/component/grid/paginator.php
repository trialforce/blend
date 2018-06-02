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
        parent::__construct($id);
        $this->setGrid($grid);
    }

    /**
     * Return a grid
     *
     * @return \Component\Grid
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
            $pageName = $model->getLabel();
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
            $pageName = $model->getLabelPlural();
        }

        return $pageName;
    }

    /**
     * Return the current pagination limit value
     *
     * @return int
     */
    public static function getCurrentPaginationLimitValue()
    {
        $value = Cookie::get('paginationLimitCookie') ? Cookie::get('paginationLimitCookie') : \DataSource\DataSource::DEFAULT_PAGE_LIMIT;
        $value = Request::get('paginationLimit') ? Request::get('paginationLimit') : $value;

        return $value;
    }

    /**
     * Create pagination limit field
     *
     * @return \View\Select
     */
    public function createPaginationLimitField()
    {
        $options[10] = 10;
        $options[15] = 15;
        $options[20] = 20;
        $options[30] = 30;
        $options[50] = 50;

        $value = self::getCurrentPaginationLimitValue();
        Cookie::set('paginationLimitCookie', $value);

        $paginationLimit = new \View\Select('paginationLimit', $options, $value, 'fr');
        $paginationLimit->setTitle('Limite de registros por página');
        $link = $this->getGrid()->getLink('listar');
        $paginationLimit->change("p('" . $link . "');");

        return $paginationLimit;
    }

    public function onCreate()
    {
        $dataSource = $this->getDataSource();
        $makePaginator = $this->getMakePaginator();
        $currentPage = $this->getCurrentPage();
        $count = $this->getCount();
        $last = $dataSource->getOffset() + $dataSource->getLimit() + 1;

        if ($count < $last)
        {
            $last = $count;
        }

        $prevPage = $currentPage - 1 < 0 ? 0 : $currentPage - 1;
        $lastPage = (int) ( ($count - 1) / $dataSource->getPaginationLimit() );
        $nextPage = $currentPage + 1 > $lastPage ? $lastPage : $currentPage + 1;

        $currentPageShow = $currentPage + 1;
        $lastPageShow = $lastPage + 1;

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
                $captionMessage = "Nenhum {$pageName} encontrado.";
            }
            else
            {
                $captionMessage = "Mostrando $count {$pageNamePlural} (total).";
            }
        }

        if ($count > 0)
        {
            $url = $this->getGrid()->getLink('gridExportData');
            $content[] = $exportar = new \View\Ext\LinkButton('gridExport', 'download', '', $url, 'clean fl icon-only');
            $exportar->setTitle('Exportar registros em forma de arquivos');
        }

        $content[] = new \View\Span(null, $captionMessage, 'text');

        if ($lastPage > 0 && $makePaginator)
        {
            $linkMethod = 'listar';
            $urlExtra['orderBy'] = Request::get('orderBy');
            $urlExtra['orderWay'] = Request::get('orderWay');

            $urlExtra['page'] = 0;
            $url = $this->grid->getLink($linkMethod, '', $urlExtra);
            $link['first'] = new \View\A('first', '<i class="fa fa-angle-double-left">&nbsp;</i>', $url, 'btn clean first');

            $urlExtra['page'] = $prevPage;
            $url = $this->grid->getLink($linkMethod, '', $urlExtra);
            $link['prev'] = new \View\A('prev', '<i class="fa fa-angle-left">&nbsp;</i>', $url, 'btn clean prev');

            $urlExtra['page'] = $currentPage;
            $url = $this->grid->getLink($linkMethod, '', $urlExtra);
            $link['current'] = new \View\A('current', "{$currentPageShow}/{$lastPageShow}", $url, 'btn clean');

            $urlExtra['page'] = $nextPage;
            $url = $this->grid->getLink($linkMethod, '', $urlExtra);
            $link['next'] = new \View\A('next', '<i class="fa fa-angle-right">&nbsp;</i>', $url, 'btn clean next');

            $urlExtra['page'] = $lastPage;
            $url = $this->grid->getLink($linkMethod, '', $urlExtra);
            $link['last'] = new \View\A('last', '<i class="fa fa-angle-double-right">&nbsp;</i>', $url, 'btn clean last');

            $content[] = new \View\Span('', $link, 'paginator', 'clearfix');
        }

        //$content[] = new \View\Input( $this->getCountElementId(), \View\Input::TYPE_HIDDEN, $count );
        $this->append($this->createPaginationLimitField());
        $this->append($content);

        return $this;
    }

}