<?php

namespace Component\Grid;

/**
 * Simple edit popup grid paginator
 *
 */
class EditPopupPaginatorAccordion extends \Component\Grid\Paginator
{
    /* public static function getCurrentPaginationLimitValue()
      {
      return \DataSource\DataSource::DEFAULT_PAGE_LIMIT;
      } */

    protected function createExportButton()
    {
        return null;
    }

    public function createPaginationFontSizeField()
    {
        return null;
    }

}
