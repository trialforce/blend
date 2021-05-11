<?php

namespace Component\Grid;

/**
 * Simple edit popup grid paginator
 * 
 */
class EditPopupPaginator extends \Component\Grid\Paginator
{

    public function createPaginationLimitField()
    {
        return null;
    }

    public static function getCurrentPaginationLimitValue()
    {
        return \DataSource\DataSource::DEFAULT_PAGE_LIMIT;
    }

}
