<?php

namespace View\Ext;

/**
 * Simple badge
 *
 * Remenber to add badge.css to your theme
 */
class Badge extends \View\Span
{

    /**
     * Construct a Badge
     *
     * @param string $id the attr id from html
     * @param mixed $innerHtml content of the badge
     * @param string $class extras css class, can be color or size
     * @throws \Exception
     */
    public function __construct($id = \NULL, $innerHtml = \NULL, $class = \NULL)
    {
        parent::__construct($id, $innerHtml, 'badge ' . $class);
    }

    /**
     * Create a color bagde based on a \Db\ConstantValues and a selected values
     *
     * @param \Db\ConstantValues $cValues the constant values
     * @param string $value the selected value
     * @return \View\Ext\Badge the created badge
     * @throws \Exception
     */
    public static function createFromConstantValues(\Db\ConstantValues $cValues, $value)
    {
        $vector = $cValues->getArray();

        $class = get_class($cValues);
        $colors = $class::getColors();

        $badge = new \View\Ext\Badge(null, $vector[$value]);
        $badge->css('background-color', $colors[$value]);

        return $badge;
    }

}
