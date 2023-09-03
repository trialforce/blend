<?php

namespace Component;

/**
 * Represents a visual time line component
 */
class Timeline extends \Component\Component
{
    private $title;
    private \Db\Collection $data;

    public function __construct($id = null, $title = null, $data = null)
    {
        parent::__construct($id);
        $this->setData($data);
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return Timeline
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        if ($data instanceof \Db\Collection)
        {
            $this->data = $data;
        }
        else if (is_array($data))
        {
            $this->data = new \Db\Collection($data);
        }
        else
        {
            $this->data = new \Db\Collection([$data]);
        }

        return $this;
    }

    public function onCreate()
    {
        if ($this->isCreated())
        {
            return $this->getContent();
        }

        $data = $this->getData();
        $content = new \View\Div($this->getId(),null, 'timeline col-12');
        $items = [];

        if ($this->getTitle())
        {
            $items[] = new \View\H3(null, $this->getTitle());
        }

        if ( $data->length() ==0 )
        {
            $items[] = $this->onCreateEmpty();
        }
        else
        {
            foreach ($data as $item)
            {
                $items[] = $this->onCreateItem($item);
            }
        }

        $content->html($items);

        $this->setContent($content);

        return $this->getContent();
    }

    public function onCreateEmpty()
    {
        return new \View\Div(null, 'Ops, nenhuma movimentação encontrada.','timeline-item-empty');
    }

    protected function onCreateItem(\Component\Timeline\DataItem $item )
    {
        $dateTime = $item->getTimelineDateTime();
        $link = $item->getTimelineLink();

        $dateTimeView = new \View\Div(null, $dateTime, 'timeline-item-datetime');

        if ($dateTime instanceof \Type\DateTime)
        {
            $dateTimeView->html( $dateTime->format('d/m').'<br/>'.$dateTime->format('H:i'));
            $dateTimeView->setTitle($dateTime->toHuman());
        }

        $title = $item->getTimelineTitle();

        if ($link)
        {
            $title = new \View\A(null,$title, $link);
        }

        $title->css('color', $item->getTimelineColor());

        $right = [];
        $right[] = new \View\Div(null, $title, 'timeline-item-title');
        $right[] = new \View\Div(null, $item->getTimelineContent(), 'timeline-item-content');

        $rightOutter = new \View\Div(null, $right,'timeline-item-right');

        if ($item->getTimelineColor())
        {
            $rightOutter->css('border-top-color', $item->getTimelineColor());
        }

        $content = [];

        if ($item->getTimelineIcon())
        {
            $content[] = $icon = new \View\Div(null, $item->getTimelineIcon(), 'timeline-item-icon');

            if ($item->getTimelineColor())
            {
                $icon->css('color', $item->getTimelineColor());
                $icon->css('border-color', $item->getTimelineColor());
            }
        }
        else
        {
            $content[] = $point = new \View\Div(null, '', 'timeline-item-point');

            if ($item->getTimelineColor())
            {
                $point->css('background-color', $item->getTimelineColor());
            }
        }

        $content[] = $dateTimeView;
        $content[] = new \View\Div(null, $rightOutter, 'timeline-item-right-outter');

        $extraClass = get_class($item);
        $extraClass = str_replace('Model\\','', $extraClass);
        $extraClass = str_replace('\\','-',$extraClass);
        $extraClass = 'timeline-item-'.strtolower($extraClass);

        return new \View\Div(null, $content,'timeline-item '.$extraClass);
    }
}