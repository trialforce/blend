<?php

namespace Component;

use DataHandle\Request;

class Component
{

    protected $id;
    protected $content;

    /**
     * Represent a complex view/element
     * When you call new \Component\Component it DOES NOT GO to html dom tree
     * You always need to call onCreate
     *
     * @param string $id must be enterelly unique
     */
    public function __construct($id)
    {
        $this->setId($id);
    }

    /**
     * Get requested event
     * @return string
     */
    public function getEvent()
    {
        return Request::get('e');
    }

    /**
     * Get reequested identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return Request::get('v');
    }

    /**
     * Get class url
     *
     * @return string
     */
    public function getClassUrl()
    {
        return strtolower(str_replace('\\', '-', str_replace('Component\\', '', get_class($this))));
    }

    public function getLink($event, $value, $params = null, $putUrl = false)
    {
        if ($putUrl)
        {
            $queryString = null;
            parse_str(\DataHandle\Server::getInstance()->get('QUERY_STRING'), $queryString);
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
        }

        if (is_array($params))
        {
            $params = http_build_query($params);
        }

        $params = $params ? '?' . $params : null;
        $module = \DataHandle\Config::get('use-module') ? 'component/' : '';
        return "$module{$this->getClassUrl()}/{$event}/{$value}{$params}";
    }

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

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Verify if content is created
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
