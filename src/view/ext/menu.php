<?php

namespace View\Ext;

use View\A;
use View\Li;
use View\Div;
use View\Span;

/**
 * A simple menu with submenu support
 */
class Menu extends \View\View
{

    /**
     * Construct the menu
     *
     * @param string $idName
     * @param array $innerHtml
     * @param string $class
     */
    function __construct($idName = 'menu', $innerHtml = NULL, $class = 'menuDesktop')
    {
        $innerHtml = self::makeMenu($innerHtml);
        parent::__construct('ul', $idName, $innerHtml, $class);
    }

    /**
     * Construct the menu
     *
     * @param array $views
     * @return array
     */
    public static function makeMenu($views)
    {
        if (!is_array($views))
        {
            return $views;
        }

        foreach ($views as $value => $view)
        {
            if (is_scalar($view))
            {
                if ($view === 'separator')
                {
                    $views[$value] = new \View\Hr();
                }
                else
                {
                    $link = new A(self::parseMenuItemId('menuItem_' . $value), $view, $value);
                    $link->setAjax(A::AJAX_NO_FORM_DATA);
                    $link->click('closeMenu()');
                    $views[$value] = new Li('li_' . $value, $link);
                    $views[$value]->click("return openSubMenu(this);");
                }
            }
            else if (is_array($view))
            {
                $views[$value] = self::makeSubMenu($value, $view);
            }
        }

        return $views;
    }

    /**
     * Create a sub menu
     *
     * @param string $id
     * @param array $subMenu
     * @return \View\Li
     */
    public static function makeSubMenu($id, $subMenu)
    {
        $title = $subMenu[0];
        $itens = $subMenu[1];

        $link = new Span(self::parseMenuItemId('menuItem_' . $id), $title, $id);
        $link->click("return openSubMenu(this);");
        $view = new Li('li_' . $id, $link, 'subMenuContainer');

        if (is_array($itens))
        {
            foreach ($itens as $value => $item)
            {
                if ($item === 'separator')
                {
                    $itens[$value] = new \View\Hr();
                }
                else if (is_scalar($item))
                {
                    $link = new A(null, $item, $value);
                    $link->setAjax(A::AJAX_NO_FORM_DATA);
                    $link->click('closeMenu()');
                    $itens[$value] = new Div(self::parseMenuItemId('menuItem_' . $value), $link);
                }
            }

            $view->append(new Div($id . '_itens', $itens, 'subMenu'));
        }

        return $view;
    }

    public static function parseMenuItemId($idItem)
    {
        return str_replace('/', '_', $idItem);
    }

}
