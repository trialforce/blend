<?php
namespace FastDom;

/**
 * Represents a basic nome
 */
class Node
{
    public $tagName = 'div';

    /**
     * Returns the most accurate name for the current node type (TAG NAME?)
     * @TODO
     * @var string
     */
    public $nodeName;
    /**
     * The value of this node, depending on its type
     * @TODO
     * @var string|null
     */
    public $nodeValue;
    /**
     * Gets the type of the node. One of the predefined XML_xxx_NODE constants
     * XML_ELEMENT_NODE = 1;
     * @var
     */
    public $nodeType = 1;

    /**
     * A array that contains all children of this node. If there are no children, this is an empty array .
     * @var array
     */
    public array $childNodes = [];

    /**
     * The Document object associated with this node, or NULL if this node is a Document .
     * @var \FastDom\Document|null
     */
    public $ownerDocument;

    /**
     * The parent of this node. If there is no such node, this returns NULL.
     * @var \FastDom\Element|null
     */
    public $parentNode;

    /**
     * The namespace URI of this node, or NULL if it is unspecified.
     * ALWAIS NULL IS THIS CASE
     * @var null
     */
    public $namespaceURI = null;

    /**
     * The namespace prefix of this node, or NULL if it is unspecified.
     * ALWAIS NULL IS THIS CASE
     * @var null
     */
    public $prefix = null;

    /**
     * Rturns the local part of the qualified name of this node.
     * ALWAIS NULL IS THIS CASE
     * @var null
     */
    public $localName = null;

    /**
     * The absolute base URI of this node or NULL if the implementation wasn't able to obtain an absolute URI.
     * ALWAIS NULL IS THIS CASE
     * @var null
     */
    public $baseURI = null;


    public function __construct()
    {

    }

    /**
     * Checks if node has attributes
     *
     * @return bool true on success or false on failure.
     */
    public function hasAttributes()
    {
        return false;
    }

    /**
     * Appends one or many nodes to the list of children behind the last child node.
     *
     * @param mixed $content
     *
     */
    public function append($content)
    {
        if (is_null($content))
        {
            return $this;
        }
        elseif (is_array($content))
        {
            foreach($content as $node)
            {
                $this->appendOne($node);
            }
        }
        else
        {
            $this->appendOne($content);
        }

        return $this;
    }

    public function appendOne($content)
    {
        if (is_object($content))
        {
            $this->appendChild($content);
        }

        return $this->appendHtml($content);
    }

    public function appendHtml($html)
    {
        if (!is_string($html))
        {
            return $this;
        }

        if (\FastDom\Parser::isHtml($html))
        {
            $this->append(\FastDom\Parser::parseHtmlFast($html));
        }
        else //plaintext
        {
            $this->appendChild($html);
        }

        return $this;
    }

    public function appendChild($element)
    {
        if (is_null($element))
        {
            return $this;
        }

        if (is_string($element) && strlen(trim($element)) ==0)
        {
            return $this;
        }

        if ($element instanceof \FastDom\Node)
        {
            $element->parentNode = $this;

            if ($this instanceof \FastDom\Document)
            {
                $element->ownerDocument = $this;
            }
            else
            {
                $element->ownerDocument = $this->ownerDocument;
            }
        }

        $this->childNodes[] = $element;

        return $this;
    }

    /**
     * Checks if node has children
     *
     * @return bool true if has child nodes
     */
    public function hasChildNodes()
    {
        return count($this->childNodes) > 0;
    }

    /**
     * Indicates if two nodes are the same node
     *
     * @param Element $element
     * @return bool true if both are the same
     */
    public function isSameNode(\FastDom\Element $element)
    {
        return $element === $this;
    }

    /**
     * Indicates if two nodes are the same node
     * @param Element $element
     * @return bool true if both are the same
     */
    public function isEqualNode(\FastDom\Element $element)
    {
        return $this->isSameNode($element);
    }

    public function insertBefore(\FastDom\Element $newnode, \FastDom\Element $refnode )
    {
        throw new \Exception('insertBefore not implement yet');
    }

    public function replaceChild(\FastDom\Element $old, \FastDom\Element $new)
    {
        throw new \Exception('replaceChild not implement yet');
    }

    public function removeChild($element)
    {
        foreach ($this->childNodes as $position => $node)
        {
            if ($element == $node)
            {
                unset($this->childNodes[$position]);
            }
        }
    }

    /**
     * Return a list of elements of the tag name
     *
     * @param string $tagName
     * @return array
     */
    public function getElementsByTagName(string $tagName)
    {
        $result = [];

        //verify itself is from tagName
        if ($this->tagName == $tagName)
        {
            $result[] = $this;
        }

        if (!is_array($this->childNodes))
        {
            return $result;
        }

        /* @var $node \FastDom\Node */
        foreach ($this->childNodes as $node)
        {
            if ($node instanceof \FastDom\Node)
            {
                $result = array_merge($result,$node->getElementsByTagName($tagName));
            }
        }

        return array_filter($result);
    }

    public function getElementById($id)
    {
        //verify itself is from tagName
        if ($this instanceof \FastDom\Element)
        {
            if ($this->getAttribute('id') == $id)
            {
                return $this;
            }
        }

        if (!is_array($this->childNodes))
        {
            return null;
        }

        /* @var $node \FastDom\Node */
        foreach ($this->childNodes as $node)
        {
            if ($node instanceof \FastDom\Node)
            {
                $search = $node->getElementById($id);

                if ($search)
                {
                    return $search;
                }
            }
        }

        return null;
    }

    function item($position)
    {
        if (isset($this->childNodes[$position]))
        {
            return $this->childNodes[$position];
        }

        return null;
    }

    //cloneNode
    //normalize
    //isSupported
    //getFeature
    //compareDocumentPosition
    //lookupPrefix
    //isDefaultNamespace
    //lookupNamespaceURI
    //lookupNamespaceUri
    //setUserData
    //getUserData
    //getNodePath
    //getLineNo
    //C14NFile

    function __get(string $name)
    {
        //textContent
        //firstChild
        //lastChild
        //previousSibling
        //nextSibling
    }

    //chame no var_dump
    public function __debugInfo()
    {
        $result = (array)$this;

        unset($result['nodeName']);
        unset($result['nodeType']);
        unset($result['ownerDocument']);
        unset($result['parentNode']);
        unset($result['namespaceURI']);
        unset($result['prefix']);
        unset($result['localName']);
        unset($result['baseURI']);
        unset($result['nodeValue']);

        return $result;
        /*return [
            'propSquared' => $this->prop ** 2,
        ];*/
    }
}