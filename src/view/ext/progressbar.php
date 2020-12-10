<?php

namespace View\Ext;

/**
 * A simple progress bar
 *
 * Remember to add /css/progressbar.css to your theme
 */
class ProgressBar extends \View\Div implements \View\Chart\Chart
{

    const DEFAULT_COLOR = '#69b1ea';

    public function __construct($id, $percent = null, $extraClass = null)
    {
        parent::__construct($id, null, $extraClass);
        $this->addClass('progress-bar')->addClass($extraClass);

        if ($percent)
        {
            $percent = intval(\Type\Decimal::get($percent)->toDb());
            $this->addSegment('progress-bar-value-' . $id, null, $percent, 0, true);
            $this->addLabel('progress-bar-label-' . $id, $percent);
        }
    }

    public function addSegment($id = null, $color = null, $percent, $offset = 0, $animated = true)
    {
        //needs an id to be animated
        $id = $id ? $id : 'progrss-bar-value-segment-' . rand();
        $percent = $percent > 100 ? 100 : $percent;

        $bar = new \View\Div($id);
        $bar->addClass('progress-bar-value');

        //if animated do trough JS, to be apply 1 moment later and let animation works
        if ($animated)
        {
            $bar->css('width', '0');
            \App::addJs("setTimeout( function(){ $('#{$id}').css('width','{$percent}%'); }, 300);");

            if ($offset > 0)
            {
                $bar->css('left', '0');

                \App::addJs("setTimeout( function(){ $('#{$id}').css('left','{$offset}%'); }, 300);");
            }
        }
        else
        {
            $bar->css('width', $percent . '%');

            if ($offset > 0)
            {
                $bar->css('left', $offset . '%');
            }
        }

        if ($color)
        {
            $bar->css('background-color', $color);
        }

        $bar->setTitle($percent . '%');
        $this->append($bar);

        return $bar;
    }

    public function addLabel($id, $text)
    {
        $label = new \View\Div($id);
        $label->addClass('progress-bar-label')
                ->attr('title', $text . '%')
                ->html($text . '%');

        $this->append($label);

        return $label;
    }

    public static function createFromCollection($id = null, \Db\Collection $data, $extraClass = null)
    {
        $pg = new \View\Ext\ProgressBar($id, null, $extraClass);
        $sum = $data->sum('value');

        $offset = 0;

        foreach ($data as $item)
        {
            if (!isset($item->percent))
            {
                $item->percent = \Type\Decimal::get($item->value / $sum * 100, 2)->toDb();
            }

            if (!$item->color)
            {
                $item->color = \Media\Color::rand();
            }

            $item->offset = $offset;

            $offset += $item->percent;
        }

        foreach ($data as $item)
        {
            $segment = $pg->addSegment($item->id, $item->color, $item->percent, $item->offset, true);
            $segment->setTitle($item->label . ' - ' . $item->value . ' (' . $item->percent . '%)');
        }

        return $pg;
    }

}
