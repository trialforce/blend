<?php

namespace FastDom;

/**
 * The DOMDocument class represents an entire HTML or XML document; serves as the root of the document tree
 *
 * DOES NOT SUPPORT
 * createElementNS
 * createAttributeNS
 * getElementsByTagNameNS
 */
class Document extends \FastDom\Node
{
    /**
     * Actual encoding of the document, is a readonly equivalent to encoding
     * @var string
     */
    public $actualEncoding = 'UTF-8';

    /**
     * Encoding of the document, as specified by the XML declaration
     * @var string
     */
    public $encoding = 'UTF-8';

    /**
     * An attribute specifying, as part of the XML declaration, the encoding of this document.
     * @var string
     */
    public $xmlEncoding = 'UTF-8';
    /**
     * Configuration used when DOMDocument::normalizeDocument() is invoked.
     * @var string
     */
    public $config = '';

    /**
     * The Document Type Declaration associated with this document.
     * @var string
     */
    public $doctype = 'html';

    /**
     * Nicely formats output with indentation and extra space.
     * @TODO
     * @var bool
     */
    public $formatOutput = true;

    /**
     * Do not remove redundant white space. Default to TRUE.
     * @var bool
     */
    public $preserveWhiteSpace = true;

    /**
     * This is a convenience attribute that allows direct access to the child node that is the document element of the document.
     * @var
     */
    public $documentElement;

    /**
     * The location of the document or NULL if undefined.
     * ALWAYS NULL
     * @var NULL
     */
    public $documentURI = NULL;

    /**
     * The  DOMImplementation  object that handles this document.
     * ALWAYS NULL
     * @var NULL
     */
    public $implementation = NULL;

    /**
     * DOES NOT USE ALWAYS NULL
     * @var null
     */
    public $recover = NULL;

    /**
     * Set it to TRUE to load external entities from a doctype declaration.
     * This is useful for including character entities in your XML document.
     * @var NULL
     */
    public $resolveExternals = NULL;

    /**
     * Whether or not the document is standalone, as specified by the XML declaration, corresponds to xmlStandalone.
     * @var NULL
     */
    public $standalone;

    /**
     * Whether this document is standalone. This is FALSE when unspecified.
     * @var FALSE
     */
    public $xmlStandalone = FALSE;

    public $strictErrorChecking = true;

    public $substituteEntities;
    public $validateOnParse = false;

    /**
     * Version of XML, corresponds to xmlVersion
     * @var
     */
    public $version;

    /**
     * Version of XML, corresponds to xmlVersion
     * @var
     */
    public $xmlVersion;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create new element
     *
     * @param string $tagName tag name
     * @param string $nodeValue
     * @return Element
     */
    public function createElement($tagName, $nodeValue)
    {
        $element = new \FastDom\Element($tagName);
        $this->appendChild($element);
        return $element;
    }

    /**
     * Create new document fragment. Return the document (fallback);
     * @return \FastDom\Document
     */
    public function createDocumentFragment()
    {
        return $this;
    }

    /**
     * Create new text node
     * @param $text
     * @return mixed
     */
    public function createTextNode($text)
    {
        return $text;
    }

    /**
     * Create new comment node
     * @return void
     */
    public function createComment()
    {
        //NOTHING HERE PALL
    }

    public function createCDATASection()
    {
        //@TODO
    }

    public function importNode($node)
    {

    }

    public function adoptNode( $node)
    {
    }

    public function prepend($nodes)
    {

    }

    //LOAD
    //SAVE
    //loadXML
    //saveXML
    //loadHTMLFile
    //saveHTMLFile

    public function loadHTML($htmlString)
    {
        $nodes = \FastDom\Parser::parseHtml($htmlString);
        $this->append($nodes);

        return $this;
    }

    public function loadXML($htmlString)
    {
        $nodes = \FastDom\Parser::parseHtml($htmlString);
        $this->append($nodes);

        return $this;
    }

    function saveHTML()
    {
        $html = '<!DOCTYPE '.$this->doctype.'>';

        for ($i = 0; $i < count($this->childNodes); $i++)
        {
            $html .= $this->childNodes[$i].'';
        }

        return $html;
    }

    public function __toString()
    {
        return $this->saveHTML();
    }

    public function __get(string $name)
    {
        //$childElementCount;
        //$lastElementChild;
        //$firstElementChild;
    }
}