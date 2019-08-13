<?php

namespace Component;

use DataHandle\Request;

/**
 * Component is a simple class that represents a complex view element.
 * Used to Combos, grids and other elements that need a direct call to ajax.
 */
class Component
{

    /**
     * String identificator
     *
     * @var string
     */
    protected $id;

    /**
     * The component content
     *
     * @var array
     */
    protected $content;

    /**
     * Represents a complex view/element
     * When you call new \Component\Component it DOES NOT GO to html dom tree
     * You always need to call onCreate
     *
     * @param string $id must be entirely unique
     */
    public function __construct($id = null)
    {
        $this->setId($id);
    }

    /**
     * Get requested event, from url/post
     *
     * @return string
     */
    public function getEvent()
    {
        return Request::get('e');
    }

    /**
     * Get requested identifier, from url/post
     *
     * @return string
     */
    public function getIdentifier()
    {
        return Request::get('v');
    }

    /**
     * Return a link to a especific even, with event, value and extra params
     *
     * @param string $event the event
     * @param string $value the value
     * @param array $params params array
     * @param string $putUrl if is to put the current url as params
     * @return string the resultant link
     */
    public function getLink($event, $value, $params = null, $putUrl = false)
    {
        if ($putUrl)
        {
            $params = $this->getCurrentUrl($params);
        }

        if (is_array($params))
        {
            $params = http_build_query($params);
        }

        $params = $params ? '?' . $params : null;
        $component = \DataHandle\Config::get('use-module') ? 'component/' : '';
        return "$component{$this->getClassUrl()}/{$event}/{$value}{$params}";
    }

    /**
     * Get class url.
     * Auxiliary method to getLink
     *
     * @return string
     */
    public function getClassUrl()
    {
        $class = get_class($this);
        $withOut = str_replace('Component\\', '', $class);
        $classUrl = strtolower(str_replace('\\', '-', $withOut));

        return $classUrl;
    }

    /**
     * Get the current url and parse it in array, mixing with params if necessary.
     * Auxiliary method to getLink.
     *
     * @param array $params the fixed param array
     * @return array the resultant array
     */
    private function getCurrentUrl($params = null)
    {
        $queryString = null;
        parse_str(\DataHandle\Server::getInstance()->get('QUERY_STRING'), $queryString);

        //remove some unnecessary propertys
        unset($queryString['p']);
        unset($queryString['e']);
        unset($queryString['v']);
        unset($queryString['_']);
        unset($queryString['selectFilters']);
        unset($queryString['selectGroups']);

        if (!$params)
        {
            $params = $queryString;
        }
        else if (is_array($params))
        {
            $params = array_merge($queryString, $params);
        }

        return $params;
    }

    /**
     * Call some component event
     * An event is called from one url mount with getLink
     *
     * @return mixed the result, an array or a \View\View
     */
    public function callEvent()
    {
        $event = $this->getEvent();

        //only if not defined
        if (!$this->getId())
        {
            $this->setId($this->getIdentifier());
        }

        if ($event && method_exists($this, $event))
        {
            return $this->$event();
        }
    }

    /**
     * Return the string identificator
     *
     * @return string the string id of element
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Define the string identificator of this component
     *
     * @param string $id string identificator
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * The the component generated content
     *
     * @return array can be an array or a \View\View
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Define the content of the component
     *
     * @param mixed $content can be an array or a \View\View
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function getDom()
    {
        return \View\View::getDom();
    }

    public function byId($id)
    {
        return $this->getDom()->byId($id);
    }

    /**
     * Verify if component content is created
     * Used to avoid double creation
     *
     * @return bool
     */
    public function isCreated()
    {
        return $this->content ? true : false;
    }

    /**
     * Generate the view/element and put it on html dom tree
     *
     * @return \View\View
     */
    public function onCreate()
    {

    }

}
