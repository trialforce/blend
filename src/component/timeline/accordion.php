<?php

namespace Component\Timeline;

/**
 * An accordion with an inner timeline, created when accordion title is clicked.
 *
 * Very used for lazy loading information of a timeline
 *
 */
abstract class Accordion extends \Component\Component
{
    private $title;
    private $class;

    public function __construct($id = null, $title = null, $class = 'col-12')
    {
        parent::__construct($id);
        $this->setTitle($title);
        $this->setClass($class);
    }

    /**
     * Return the data used in time (after open accordion)
     * @return \Db\Collection
     */
    abstract public function getData();

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return Accordion
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     * @return Accordion
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    public function getAccordionId()
    {
        return 'timeline-accordion-' . $this->getId();
    }

    public function onCreate()
    {
        if ($this->isCreated())
        {
            return $this->getContent();
        }

        $id = $this->getAccordionId();
        $accordion = new \View\Ext\Accordion($id, $this->title, null, $this->class);
        $accordion->onOpen("p('".$this->getLink('update',$this->getId())."')");
        $this->byId($id.'-body')->css('padding-top','0');
        $this->setContent($accordion);

        return $accordion;
    }

    public function update()
    {
        \App::dontChangeUrl();
        $data = $this->getData();

        $timeline = new \Component\Timeline('timeline-acc-'.$this->getId(), null, $data);
        $id = $this->getAccordionId();

        $this->byId($id.'-body')->html($timeline);
    }

    public static function updateTimeline($id = null)
    {
        $idComponent = $id ? '?v=' . $id : '';
        $url = \Component\Component::getLinkForComponent('update', $id, $putUrl = false, get_called_class());

        \App::addJs("p('$url');");
    }
}