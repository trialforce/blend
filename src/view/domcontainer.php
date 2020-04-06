<?php

namespace View;

/**
 * Dom container.
 *
 * It is bridge to domElement
 *
 */
class DomContainer implements \Countable
{

    /**
     *
     * @var \DOMElement
     */
    protected $domElement;

    public function __construct(\DOMElement $domElement)
    {
        $this->setDomElement($domElement);
    }

    public function getDomElement()
    {
        return $this->domElement;
    }

    public function setDomElement($domElement)
    {
        $this->domElement = $domElement;
        return $this;
    }

    /**
     * Return the first chield element
     *
     * @return \View\DomContainer
     */
    public function first()
    {
        return new \View\DomContainer($this->domElement->firstChild);
    }

    /**
     * Get the first element of the tag name
     *
     * @param string $tag the tag name
     * @return \View\View the first element of the passed tag or null
     */
    public function byTag($tag)
    {
        $elements = $this->getElementsByTagName($tag);

        if (isset($elements[0]))
        {
            return \View\Document::toView($elements[0]);
        }

        return null;
    }

    public function getOutputJs()
    {
        return false;
    }

    public function setOutputJs()
    {
        return $this;
    }

    public function setIdAndName($idName)
    {
        if ($idName)
        {
            $this->setName($idName);
            $this->setId($idName);
        }

        return $this;
    }

    public function setName($name)
    {
        $this->setAttribute('name', $name);

        return $this;
    }

    public function __get($name)
    {
        return $this->domElement->$name;
    }

    public function __set($name, $value)
    {
        $this->domElement->$name = $value;
    }

    public function replace($newNode, $oldNode)
    {
        if ($oldNode instanceof \View\DomContainer)
        {
            $oldNode = $oldNode->getDomElement();
        }

        $this->domElement->replaceChild($newNode, $oldNode);
    }

    /**
     * Retorna o nome do elemento
     *
     * @return string
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }

    public function setAttribute($name, $value)
    {
        //verifica se o atributo possui algum valor
        if ($name && ( $value || $value === '' || $value === 0 || $value === '0' || $value === false ))
        {
            $this->domElement->setAttribute($name, $value);
        }

        return $this;
    }

    public function setValue($value)
    {
        //TODO VER NO futuro, é legal, mas impacta em outros lugares (grids de detalhes)
        //$idPadronizado = str_replace( array( '#', \View\View::REPLACE_SHARP ), '', $this->getId() );
        //set to request so other functions can get it
        //\DataHandle\Post::set( $idPadronizado, $value . '' );
        //\DataHandle\Request::set( $idPadronizado, $value . '' );

        return $this->setAttribute('value', $value);
    }

    public function val($value)
    {
        return $this->setValue($value);
    }

    public function getValue()
    {
        return $this->getAttribute('value');
    }

    public function setData($attribute, $value)
    {
        return $this->setAttribute('data-' . $attribute, $value);
    }

    public function getData($attribute)
    {
        return $this->getAttribute('data-' . $attribute);
    }

    public function setId($id)
    {
        if ($id)
        {
            $this->setAttribute('id', $id);

            //add to element list to can be finded in getElementById method
            if (\View\View::getDom() && $this instanceof \DomElement)
            {
                \View\View::getDom()->addToElementList($this);
            }
        }

        return $this;
    }

    /**
     * Retorna o nome do elemento
     *
     * @return string
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    public function setClass($class)
    {
        return $this->setAttribute('class', $class);
    }

    public function getClass()
    {
        return $this->getAttribute('class');
    }

    /**
     * Add a css class to element
     *
     * @param string $classToAdd class to add to element
     * @return $this
     */
    public function addClass($classToAdd)
    {
        $class = $this->getClass() . '';
        $classes = func_get_args();

        foreach ($classes as $var)
        {
            if (mb_strlen($class) > 0)
            {
                $class .= ' ';
            }

            $class .= $var;
        }

        $this->setClass($class);

        return $this;
    }

    public function removeClass($class)
    {
        $class = $this->getClass();
        $classes = func_get_args();

        foreach ($classes as $var)
        {
            if (mb_strlen($class) > 0)
            {
                $class = trim(preg_replace('/^' . $var . '| ' . $var . ' |' . $var . '$/', '', $class));
            }
        }

        if ($class)
        {
            $this->setClass($class);
        }
        else
        {
            $this->removeAttribute('class');
        }

        return $this;
    }

    public function setTitle($title)
    {
        return $this->setAttribute('title', $title);
    }

    public function getTitle()
    {
        return $this->getAttribute('title');
    }

    public function append($content)
    {
        return \View\View::sAppend($this->getDomElement(), $content);
    }

    public function clearChildren()
    {
        while ($this->hasChildNodes())
        {
            $this->removeChild($this->domElement->firstChild);
        }

        return $this;
    }

    protected function getSelector()
    {
        return "$('#" . $this->getId() . "')";
    }

    public function addStyle($property, $value = '')
    {
        if (!$property || !$value)
        {
            return $this;
        }

        $this->removeStyle($property);
        $style = $this->getStyle();

        //if not ends with ;
        if (strlen($style) > 0 && (substr($style, -strlen($style)) !== ';'))
        {
            $style = $style . ';';
        }

        return $this->setAttribute('style', $style . $property . ':' . $value . ';');
    }

    public function setStyle($style, $value = '')
    {
        return $this->setAttribute('style', $style . ':' . $value . ';');
    }

    public function removeStyle($property)
    {
        $style = $this->getStyle();

        if (!$style)
        {
            return $this;
        }

        $explode = explode(';', $style);

        foreach ($explode as $idx => $comparision)
        {
            if ($comparision == $property)
            {
                unset($explode[$idx]);
            }
        }

        $style = implode(';', $explode);
        $this->setStyle($style);

        return $this;
    }

    public function css($property, $value = NULL)
    {
        if (is_null($value))
        {
            return $this->getStyle();
        }
        else
        {
            return $this->addStyle($property, $value);
        }
    }

    public function getStyle()
    {
        return $this->getAttribute('style');
    }

    public function getReadOnly()
    {
        return $this->getAttribute('readonly');
    }

    public function setReadOnly($readOnly, $setInChilds = FALSE)
    {
        if ($readOnly)
        {
            $this->setAttribute('readonly', 'readonly');
        }
        else
        {
            $this->removeAttribute('readonly');
        }

        if ($setInChilds)
        {
            $this->setChildsReadOnly($readOnly);
        }

        return $this;
    }

    public function setDisabled($disabled)
    {
        if ($disabled)
        {
            $this->setAttribute('disabled', 'disabled');
        }
        else
        {
            $this->removeAttribute('disabled');
        }

        return $this;
    }

    public function setChildsReadOnly($readOnly)
    {
        \View\View::setChildrenReadOnly($this, $readOnly);
    }

    public function setTabIndex($tabIndex)
    {
        $this->setAttribute('tabIndex', $tabIndex);

        return $this;
    }

    public function getTabIndex()
    {
        return $this->getAttribute('tabIndex');
    }

    public function trigger($event)
    {
        \App::addJs($this->getSelector() . ".trigger('$event')");

        return $this;
    }

    public function play()
    {
        \App::addJs($this->getSelector() . ".play()");

        return $this;
    }

    public function change($event = NULL)
    {
        if ($event)
        {
            $onChange = $this->getAttribute('onchange');

            //suporta onchange que já existe
            if ($onChange)
            {
                $onChange = $onChange . '; ';
            }

            $this->setAttribute('onchange', $onChange . $this->verifyAjaxEvent($event));
        }
        else
        {
            \App::addJs($this->getSelector() . ".change();");
        }

        return $this;
    }

    public function blur($event)
    {
        $this->setAttribute('onblur', $this->verifyAjaxEvent($event));
        return $this;
    }

    protected function verifyAjaxEvent($event)
    {
        if (method_exists(\View\View::getDom(), $event))
        {
            return \Ajax::getAjax($event, '&t=' . $event);
        }

        return $event;
    }

    public function getChange()
    {
        return $this->getAttribute('onchange');
    }

    public function click($onClick)
    {
        $this->setAttribute('onclick', $this->verifyAjaxEvent($onClick));
        return $this;
    }

    public function getClick()
    {
        return $this->getAttribute('onclick');
    }

    public function keyDown($onKeyDown)
    {
        $this->setAttribute('onkeydown', $this->verifyAjaxEvent($onKeyDown));
        return $this;
    }

    public function getKeyDown()
    {
        return $this->getAttribute('onkeydown');
    }

    public function keyUp($onKeyUp)
    {
        $this->setAttribute('onkeyup', $this->verifyAjaxEvent($onKeyUp));
        return $this;
    }

    public function getKeyUp()
    {
        return $this->getAttribute('onkeyup');
    }

    public function keyPress($onKeyPress)
    {
        $this->setAttribute('onkeypress', $this->verifyAjaxEvent($onKeyPress));
        return $this;
    }

    public function getKeyPress()
    {
        return $this->getAttribute('onkeypress');
    }

    public function hide()
    {
        return $this->addStyle('display', 'none');
    }

    /**
     * Mostra elemento, como jquery
     * @return \View\View
     */
    public function show()
    {
        return $this->addStyle('display', 'block');
    }

    public function attr($attribute, $value = NULL)
    {
        if ($value || $value === '' || $value === '0' || $value === 0)
        {
            return $this->setAttribute($attribute, $value);
        }
        else
        {
            return $this->getAttribute($attribute);
        }
    }

    public function html($content = NULL)
    {
        //content is not used, is overwrite with func_get_args
        //$content = null;
        $this->clearChildren();
        $this->append(func_get_args());
        return $this;
    }

    /**
     * Disable the element
     *
     * @return \View\View
     */
    public function disable()
    {
        $this->setDisabled(TRUE);
    }

    /**
     * Enable the element
     *
     * @return \View\View
     */
    public function enable()
    {
        return $this->setDisabled(false);
    }

    public function remove()
    {
        $this->domElement->parentNode->removeChild($this->domElement);

        return $this;
    }

    public function focus()
    {
        \App::addJs("setTimeout(\"{$this->getSelector()}.focus();\", 150);");
    }

    public function setInvalid($invalid = TRUE, $message)
    {
        if ($invalid)
        {
            self::invalidate('#' . $this->getId(), $message);
        }
    }

    public static function invalidate($seletor, $message)
    {
        \App::addJs("invalidate('{$seletor}', null, '{$message}');");
    }

    public static function removeAllInvalidate()
    {
        \View\View::removeAllInvalidate();
    }

    public function __toString()
    {
        return $this->C14N(TRUE);
    }

    public function count()
    {
        return \View\View::countNodes($this->domElement);
    }

    public function C14N($exclusive = null, $with_comments = null, array $xpath = null, array $ns_prefixes = null)
    {
        return $this->domElement->C14N($exclusive, $with_comments, $xpath, $ns_prefixes);
    }

    public function C14NFile($uri, $exclusive = null, $with_comments = null, array $xpath = null, array $ns_prefixes = null)
    {
        return $this->domElement->C14NFile($uri, $exclusive, $with_comments, $xpath, $ns_prefixes);
    }

    public function appendChild(\DOMNode $newnode)
    {
        return $this->domElement->appendChild($newnode);
    }

    public function cloneNode($deep = null)
    {
        return $this->domElement->cloneNode($deep);
    }

    public function compareDocumentPosition(\DOMNode $other)
    {
        return $this->compareDocumentPosition($other);
    }

    public function getAttribute($name)
    {
        return $this->domElement->getAttribute($name);
    }

    public function getAttributeNS($namespaceURI, $localName)
    {
        return $this->domElement->getAttributeNS($namespaceURI, $localName);
    }

    public function getAttributeNode($name)
    {
        return $this->domElement->getAttributeNode($name);
    }

    public function getAttributeNodeNS($namespaceURI, $localName)
    {
        return $this->domElement->getAttributeNodeNS($namespaceURI, $localName);
    }

    public function getElementsByTagName($name)
    {
        return $this->domElement->getElementsByTagName($name);
    }

    public function getElementsByTagNameNS($namespaceURI, $localName)
    {
        return $this->domElement->getElementsByTagNameNS($namespaceURI, $localName);
    }

    public function getFeature($feature, $version)
    {
        return $this->domElement->getFeature($feature, $version);
    }

    public function getLineNo()
    {
        return $this->domElement->getLineNo();
    }

    public function getNodePath()
    {
        return $this->domElement->getNodePath();
    }

    public function getUserData($key)
    {
        return $this->domElement->getUserData($key);
    }

    public function hasAttribute($name)
    {
        return $this->domElement->hasAttribute($name);
    }

    public function hasAttributeNS($namespaceURI, $localName)
    {
        return $this->domElement->hasAttributeNS($namespaceURI, $localName);
    }

    public function hasAttributes()
    {
        return $this->domElement->hasAttributes();
    }

    public function hasChildNodes()
    {
        return $this->domElement->hasChildNodes();
    }

    public function insertBefore(\DOMNode $newnode, \DOMNode $refnode = null)
    {
        $this->domElement->insertBefore($newnode, $refnode);

        return $this;
    }

    /**
     * More inteligente version of insertBefore
     *
     * @param \View\View $newNode
     * @param \View\View $refnode
     *
     * @return \View\View
     */
    public function appendBefore($newNode, $refnode)
    {
        if (is_array($newNode))
        {
            foreach ($newNode as $node)
            {
                $this->appendBefore($node, $refnode);
            }

            return $this;
        }
        else
        {
            return $this->insertBefore($newNode, $refnode);
        }
    }

    public function isDefaultNamespace($namespaceURI)
    {
        return $this->domElement->isDefaultNamespace($namespaceURI);
    }

    public function isEqualNode(\DOMNode $arg)
    {
        return $this->domElement->isEqualNode($arg);
    }

    public function isSameNode(\DOMNode $node)
    {
        return $this->domElement->isSameNode($node);
    }

    public function isSupported($feature, $version)
    {
        return $this->domElement->isSupported($feature, $version);
    }

    public function lookupNamespaceUri($prefix)
    {
        return $this->domElement->lookupNamespaceUri($prefix);
    }

    public function lookupPrefix($namespaceURI)
    {
        return $this->domElement->lookupPrefix($namespaceURI);
    }

    public function normalize()
    {
        return $this->domElement->normalize();
    }

    public function removeAttribute($name)
    {
        return $this->domElement->removeAttribute($name);
    }

    public function removeAtrr($name)
    {
        return $this->removeAttribute($name);
    }

    public function removeAttributeNS($namespaceURI, $localName)
    {
        return $this->domElement->removeAttributeNS($namespaceURI, $localName);
    }

    public function removeAttributeNode(\DOMAttr $oldnode)
    {
        return $this->domElement->removeAttributeNode($oldnode);
    }

    public function removeChild(\DOMNode $oldnode)
    {
        return $this->domElement->removeChild($oldnode);
    }

    public function replaceChild(\DOMNode $newnode, \DOMNode $oldnode)
    {
        return $this->domElement->replaceChild($newnode, $oldnode);
    }

    public function setAttributeNS($namespaceURI, $qualifiedName, $value)
    {
        return $this->domElement->setAttributeNS($namespaceURI, $qualifiedName, $value);
    }

    public function setAttributeNode(\DOMAttr $attr)
    {
        return $this->domElement->setAttributeNode($attr);
    }

    public function setAttributeNodeNS(\DOMAttr $attr)
    {
        return $this->domElement->setAttributeNodeNS($attr);
    }

    public function setIdAttribute($name, $isId)
    {
        return $this->domElement->setIdAttribute($name, $isId);
    }

    public function setIdAttributeNS($namespaceURI, $localName, $isId)
    {
        return $this->domElement->setIdAttributeNS($namespaceURI, $localName, $isId);
    }

    public function setIdAttributeNode(\DOMAttr $attr, $isId)
    {
        return $this->domElement->setIdAttributeNode($attr, $isId);
    }

    public function setUserData($key, $data, $handler)
    {
        return $this->domElement->setUserData($key, $data, $handler);
    }

    public function parent()
    {

        return new \View\DomContainer($this->parentNode);
    }

}
