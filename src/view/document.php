<?php

namespace View;

use \DataHandle\Request;

/**
 * Default layout class
 */
class Document extends \DomDocument implements \Countable
{

    /**
     * List of elements, to make easy to find
     *
     * @var array
     */
    protected $elementList;

    /**
     * Construct the layout
     *
     * @param string $layout relative to layout folder
     * @param boolean $setDom set this as default layout
     */
    public function __construct($layout = NULL, $setDom = FALSE)
    {
        parent::__construct('1.0', 'UTF-8');

        if ($setDom)
        {
            \View\View::setDom($this);
        }
    }

    public function loadXmlFromFile($layout)
    {
        if (!file_exists($layout))
        {
            throw new \Exception('Arquivo de layout não encontrado ' . $layout);
        }

        $content = file_get_contents($layout);

        //desabilita erros chatos da libxml na leitura de layouts
        libxml_use_internal_errors(true);
        $this->strictErrorChecking = FALSE;
        $options = LIBXML_VERSION >= 20900 ? LIBXML_PARSEHUGE : null;
        $this->loadXML($content, $options);

        $errors = libxml_get_errors();

        if (count($errors) > 0)
        {
            $error = $errors[0];
            libxml_clear_errors();
            throw new \Exception('Erro lendo XML: ' . $layout . ' - Erro: ' . $error->message);
        }

        libxml_clear_errors();
    }

    /**
     * Load layout from file
     *
     * @param string $layout
     */
    public function loadFromFile($layout)
    {
        $content = file_get_contents($layout);

        //desabilita erros chatos da libxml na leitura de layouts
        libxml_use_internal_errors(true);
        $this->strictErrorChecking = FALSE;
        $this->loadHTML($content);
        libxml_clear_errors();
    }

    public function loadHTML($source, $options = NULL)
    {
        $encoding = mb_detect_encoding($source, 'UTF-8,ISO-8859-1', true);

        if ($encoding == 'UTF-8')
        {
            $source = utf8_decode($source);
        }

        $ok = @parent::loadHTML($source);

        return $ok;
    }

    /**
     * Adiciona um elemento no elemento atual, caso for um texto cria um textNode.
     * Caso seja um array adiciona um por um.
     *
     * @param string $content
     * @return boolean
     *
     */
    public function append($content)
    {
        if (is_array($content))
        {
            foreach ($content as $info)
            {
                if ($info instanceof \DOMNode || is_string($info) || is_array($info))
                {
                    $this->append($info);
                }
                else
                {
                    throw new \Exception('Não é uma instância DOMNode');
                }
            }

            return true;
        }

        //caso não seja instancia de DomElement, cria um elemento de texto
        if (!$content instanceof \DomElement && !$content instanceof \DOMText && !$content instanceof \DOMDocumentFragment)
        {
            /* if (\View\View::isHtml($content))
              {
              $layout = new \View\Layout();
              $layout->loadHTML('<html><body><div id="importedContent">' . $content . '</div<</body></html>');
              $node = $layout->byId('importedContent')->childNodes[0];

              $this->importNode($node, true);
              $this->appendChild($node);
              }
              else
              { */
            $content = $this->createTextNode($content);
            //}
        }

        if ($content)
        {
            return parent::appendChild($content);
        }
    }

    /**
     * Coloca um layout dentro do outro
     *
     * @param string $primaryElementId
     * @param \View\Document $domInner \View\DomDocument or string
     * @throws \Exception
     */
    public function appendLayout($primaryElementId, $domInner)
    {
        //element of main/base layout
        $primaryContent = $this->getElementById($primaryElementId);

        if ($primaryContent instanceof \View\DomContainer)
        {
            $primaryContent = $primaryContent->getDomElement();
        }

        if (is_string($primaryContent))
        {
            $primaryContent->append($domInner);
            return $this;
        }

        if ($domInner instanceof \View\Document)
        {
            //quando o layout for criado via programação acessa o primeiro filho
            $innerContent = $domInner->firstChild;

            //para o caso de ser criado via html
            if ($innerContent instanceof \DOMDocumentType)
            {
                $html = $domInner->childNodes->item(1);
                $body = $html->childNodes->item(0);
                $innerContent = $body->childNodes->item(0);
            }

            //Quando não tiver nenhum conteúdo no layout não importa nada.
            if (!$innerContent)
            {
                return FALSE;
            }

            $innerContentMig = $this->importNode($innerContent, true); //importa o nodo

            if ($primaryContent && $innerContentMig)
            {
                $primaryContent->appendChild($innerContentMig);
            }
            else
            {
                $this->append($innerContentMig);
            }

            if (isset($innerContent->nextSibling))
            {
                $nodeToImport = $innerContent->nextSibling;
            }

            //import others nodes
            while (isset($nodeToImport))
            {
                $innerContentMig = $this->importNode($nodeToImport, TRUE);

                if ($primaryContent && $innerContentMig)
                {
                    $primaryContent->appendChild($innerContentMig);
                }
                else
                {
                    $this->append($innerContentMig);
                }

                $nodeToImport = $nodeToImport->nextSibling;
            }
        }

        return $this;
    }

    /**
     * Retorna o DomNode especifico para o id solicitado.
     *
     * Quando usado html 5, em função do PHPDom não conseguir validar o esquema,
     * algumas vezes o id não é encontrado pela função getElementById padrão.
     * Neste caso aplicamos um Xpath para encontrar.
     *
     * @param type $elementId
     * @return \View\View
     */
    public function getElementById($elementId, $class = NULL)
    {
        //without id, no element for you!
        if (!$elementId)
        {
            return NULL;
        }

        //compatibility with jquery
        $elementId = str_replace('#', '', $elementId);

        //tenta o atalho pelo elemento registrado
        if (isset($this->elementList[$elementId]))
        {
            return $this->elementList[$elementId];
        }

        //tenta pela função padrão, as vezes não pega
        $element = parent::getElementById($elementId);

        //caso não encontre pelo getElementById tenta pelo Xpath
        if (!$element)
        {
            $x = new \DOMXPath($this);
            $element = $x->query("//*[@id='{$elementId}']")->item(0);
        }

        //caso não encontre elemento cria um falso para não dar
        //erro e facilitar a programação
        if (!$element instanceof \DOMElement)
        {
            $dataServerClass = Request::get('data-server-class');
            $serverClass = isset($dataServerClass[$elementId]) ? $dataServerClass[$elementId] : NULL;

            $class = $class ? $class : $serverClass;
            $class = $class ? $class : '\View\Div';

            $element = new $class(\View\View::REPLACE_SHARP . $elementId);
            $element->setOutputJs(TRUE);
            //remove do dom para não reaparecer
            $element->parentNode->removeChild($element);
        }

        return $element;
    }

    /**
     * A fast byId but return a \DomElement
     *
     * @param string $elementId
     * @return \DomElement
     */
    public function byIdFast($elementId)
    {
        $x = new \DOMXPath($this);
        return $x->query("//*[@id='{$elementId}']")->item(0);
    }

    /**
     * Alias para getElementBy
     *
     * @param string $id
     * @return \View\View
     */
    public function byId($id, $class = NULL)
    {
        return self::toView($this->getElementById($id, $class));
    }

    /**
     * Get the first element of the tag name
     *
     * @param string $tag
     * @return \View\View
     */
    public function byTag($tag)
    {
        $elements = $this->getElementsByTagName($tag);

        if (isset($elements[0]))
        {
            return self::toView($elements[0]);
        }

        return null;
    }

    /**
     * Return an view element if is a dom element
     *
     * @param mixed $element \DomElement or \View\View
     * @return \View\View \View\View or \View\DomContainer
     */
    public static function toView($element)
    {
        if ($element instanceof \DOMElement && !$element instanceof \View\View)
        {
            $element = new \View\DomContainer($element);
        }

        return $element;
    }

    /**
     * Query dom elements using Css selector
     *
     * @param string $cssSelector
     * @return \DOMNodeList
     */
    public function query($cssSelector)
    {
        $xpath = new \DOMXPath($this);
        $result = $xpath->query(XPathToCss::convert($cssSelector));

        return $result;
    }

    /**
     * Return a seletor jquery
     *
     * @param string $selector
     * @return \View\Selector
     */
    public function jquery($selector)
    {
        return \View\Selector::get($selector);
    }

    /**
     * Adiciona um elemento a lista de elementos.
     * Dessa forma ele pode ser localizado pelo getElementById a qualquer momento
     *
     * @param DomElement $element
     */
    public function addToElementList(\DomElement $element)
    {
        $id = $element->getAttribute('id');
        $this->elementList[$id] = $element;
    }

    /**
     * Return string representation of layout.
     * Remove double spaces to otimize to page speed
     *
     * @return string
     */
    public function __toString()
    {
        $this->formatOutput = TRUE;

        return $this->saveHTML();
    }

    /**
     * Count all elements recursive
     *
     * @return int
     */
    public function count()
    {
        $count = $this->childNodes->length;
        $childNodes = $this->childNodes;

        foreach ($childNodes as $node)
        {
            if (!$node instanceof \View\View && $node instanceof \DOMElement)
            {
                $node = new \View\DomContainer($node);
            }

            $count += count($node);
        }

        return $count;
    }

}
