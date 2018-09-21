<?php

namespace View;

use DataHandle\Request;

/**
 * Represent a generic html view/element
 * When you call new \View\View it goes direct to html dom tree
 */
class View extends \DomElement implements \Countable, \Disk\JsonAvoidPropertySerialize
{

    const REPLACE_SPACE = '_space_';
    const REPLACE_SHARP = '_sharp_';

    /**
     * Documento dom
     *
     * @var DomDocument
     */
    protected static $dom;
    protected $label;
    protected $contain;

    /**
     * Construct a view
     *
     * @param string $domName
     * @param string $idName
     * @param mixed $innerHtml
     * @param string $class
     * @param \DOMElement $father
     * @throws \Exception
     */
    public function __construct($domName, $idName = \NULL, $innerHtml = NULL, $class = NULL, $father = NULL)
    {
        if (!$domName)
        {
            throw new \Exception('View sem tipo definido! Id =' . $idName);
        }

        parent::__construct($domName);

        if (!self::getDom())
        {
            throw new \Exception('Impossível encontrar layout para ' . $domName . ' - ' . get_class($this));
        }

        if ($father)
        {
            $father->append($this);
        }
        else
        {
            self::getDom()->append($this);
        }

        $this->setIdAndName($idName);
        $this->append($innerHtml);
        $this->setClass($class);
    }

    /**
     * Define the id and name of html element
     *
     * Only set name if element need it
     *
     * @param string $idName
     */
    public function setIdAndName($idName)
    {
        if ($idName)
        {
            $tagName = $this->tagName;

            $putName[] = 'input';
            $putName[] = 'textarea';
            $putName[] = 'select';

            if (in_array($tagName, $putName))
            {
                $this->setName($idName);
            }

            $this->setId($idName);
        }

        return $this;
    }

    /**
     * Define o nome do elemento
     *
     * @param string $name
     */
    public function setName($name)
    {
        parent::setAttribute('name', $name);

        return $this;
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

    /**
     * Define um atributo qualquer, somente se tiver algum valor
     *
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute($name, $value)
    {
        $value = is_array($value) ? implode(' ', $value) : $value;

        //verify if atribute has some value
        if ($name && ( $value || $value === '' || $value === 0 || $value === '0' || $value === false ))
        {
            //add support for array values
            parent::setAttribute($name, $value . '');

            //make the output to js,don't output js the outputjs attribute
            if ($this->getOutputJs() && $name !== 'data-outputjs')
            {
                $jsValue = self::treatStringToJs($value);

                if (strlen(trim($this->getSelector())) > 0)
                {
                    \App::addJs($this->getSelector() . ".attr('$name','$jsValue')");
                }
            }
        }

        return $this;
    }

    /**
     * Define o valor do campo
     *
     * @param string $value
     * @return \View\Input
     */
    public function setValue($value)
    {
        //avoid errors
        if (is_array($value))
        {
            $value = $value[0];
        }

        //TODO VER NO futuro, é legal, mas impacta em outros lugares (grids de detalhes)
        //$idPadronizado = str_replace( array( '#', \View\View::REPLACE_SHARP ), '', $this->getId() );
        //set to request so other functions can get it
        //\DataHandle\Post::set( $idPadronizado, $value . '' );
        //\DataHandle\Request::set( $idPadronizado, $value . '' );

        parent::setAttribute('value', $value);

        if ($this->getOutputJs())
        {
            $valueSlashes = self::treatStringToJs($value);
            \App::addJs($this->getSelector() . ".val('$valueSlashes')");
        }

        return $this;
    }

    /**
     * Define o valor do campo
     *
     * @param string $value
     */
    public function val($value = NULL)
    {
        return $this->setValue($value);
    }

    /**
     * Retorna o valor do campo
     *
     * @return string
     */
    public function getValue()
    {
        //se é ajax pega do post
        if ($this->getOutputJs())
        {
            $idPadronizado = str_replace(array('#', self::REPLACE_SHARP), '', $this->getId());

            $value = Request::get($idPadronizado);
        }
        else
        {
            $value = $this->getAttribute('value');
        }

        return $value;
    }

    /**
     * Seta o foco no campo
     */
    public function focus()
    {
        \App::addJs("setTimeout(\"{$this->getSelector()}.focus();\", 150);");
    }

    /**
     * Set a data atribute
     *
     * @param string $attribute
     * @param string $value
     * @return \View\View
     */
    public function setData($attribute, $value)
    {
        return $this->setAttribute('data-' . $attribute, $value);
    }

    /**
     * Get data attribute
     *
     * @param string $attribute
     * @return string
     */
    public function getData($attribute)
    {
        return $this->getAttribute('data-' . $attribute);
    }

    /**
     * Define server class
     *
     * This attribute is used by ->byId to know the server class of
     * an id when in a ajax request
     *
     * If pass NULL set current class
     *
     * @param string $class
     * @return string
     */
    public function setServerClass($class = NULL)
    {
        $class = $class ? $class : get_class($this);
        return $this->setData('server-class', $class);
    }

    /**
     * Return Server class
     *
     * This attribute is used by ->byId to know the server class of
     * an id when in a ajax request
     *
     * @return string
     */
    public function getServerClass()
    {
        if ($this->getOutputJs())
        {
            $dataServerClass = Request::get('data-server-class');
            return $dataServerClass[$this->getId()];
        }

        return $this->getData('server-class');
    }

    /**
     * Define o nome do elemento
     *
     * @param string $name
     */
    public function setId($id)
    {
        if ($id)
        {
            parent::setAttribute('id', $id);

            //adiciona a lista de elementos para achar a classe corretamente no getElementById
            \View\View::getDom()->addToElementList($this);
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

    /**
     * Define classe css do elemento
     * @param string $class
     */
    public function setClass($class)
    {
        return $this->setAttribute('class', $class);
    }

    /**
     * Adiciona uma classe css ao elemento.
     * Guarda as que já existem.
     *
     * Pode adicionar várias classes, nos parâmetros da função
     *
     * @param string $args
     *
     * @return \View\View
     */
    public function addClass($args)
    {
        //args is not used, is overwritred with func_get_args
        //$args = null;
        $class = $this->getClass();
        $classes = func_get_args();

        foreach ($classes as $var)
        {
            $class .= ' ' . $var;
        }

        $this->setClass(trim($class));

        return $this;
    }

    /**
     * Remove uma classe css ao elemento.
     *
     * Pode adicionar várias classes, nos parâmetros da função
     *
     * @param string $class
     *
     * @return \View\View
     */
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

    /**
     * Retorna a string com as classes css atuais
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getAttribute('class');
    }

    /**
     * Define html title attribute
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        //title html don't suppor html
        if (is_string($title))
        {
            $title = strip_tags($title);
        }

        return $this->setAttribute('title', $title);
    }

    /**
     * Retorna o atributo html title
     *
     * @return string
     *
     */
    public function getTitle()
    {
        return $this->getAttribute('title');
    }

    /**
     * Adiciona um elemento no elemento atual, caso for um texto cria um textNode.
     * Caso seja um array adiciona um por um.
     *
     * @param string $content
     * @return boolean
     */
    public function append($content = null)
    {
        if ($this->getOutputJs())
        {
            $this->appendJs($content);
        }

        return self::sAppend($this, $content);
    }

    /**
     * Static Append to avoid duplicate code between view and dom container
     *
     * @param \DomElement $element
     * @param mixed $content
     * @param \DomElement $treatHtml
     * @return boolean
     */
    public static function sAppend($element, $content, $treatHtml = TRUE)
    {
        //if not element, nothing to do
        if (!$element || !($content || $content == '0'))
        {
            return $element;
        }
        //if is array call recursive
        else if (is_array($content))
        {
            foreach ($content as $info)
            {
                self::sAppend($element, $info);
            }
        }
        //dom container
        else if ($content instanceof \View\DomContainer)
        {
            $element->appendChild($content->getDomElement());
        }
        //normal dom elements
        else if ($content instanceof \DOMNode || $content instanceof \DOMElement || $content instanceof \DOMDocumentFragment)
        {
            $element->appendChild($content);
        }
        //if is Node list, append it all
        else if ($content instanceof \DOMNodeList)
        {
            for ($i = $content->length; --$i >= 0;)
            {
                $field = $content->item($i);
                $element->appendChild($field);
            }
        }
        else if ($content instanceof \Component\Component)
        {
            $content = $content->onCreate();
        }
        //if is text, verify html or normal text
        else
        {
            //convert to string if is object
            $content = $content . '';

            if ($treatHtml && self::isHtml($content) && $element->tagName !== 'pre')
            {
                \View\View::appendHtmlText($element, $content);
            }
            else
            {
                $element->appendChild(new \DOMText($content));
            }
        }

        return $element;
    }

    /**
     * Append via js
     *
     * @param mixed $content
     * @param string $method
     * @return \View\View
     */
    protected function appendJs($content, $method = 'append')
    {
        if (!$content)
        {
            return $this;
        }

        $html = '';

        //TODO converter content para html caso seja um objeto
        if (is_array($content))
        {
            foreach ($content as $obj)
            {
                if (is_object($obj))
                {
                    $html .= $obj->__toString();
                    //remove from dom
                    $obj->remove();
                }
                else
                {
                    if (is_array($obj))
                    {
                        $this->appendJs($obj);
                    }
                    else
                    {
                        $html .= $obj;
                    }
                }
            }
        }
        else if (is_object($content))
        {
            $html = $content->__toString();

            $content->remove();
        }
        else
        {
            $html = $content;
        }

        $html = json_encode($html);

        \App::addJs($this->getSelector() . ".{$method}({$html});");

        return $this;
    }

    /**
     * Limpa todos os filhos do elemento
     */
    public function clearChildren()
    {
        while ($this->hasChildNodes())
        {
            $this->removeChild($this->firstChild);
        }

        if ($this->getOutputJs())
        {
            \App::addJs($this->getSelector() . ".html('')");
        }

        return $this;
    }

    /**
     * Retorna a label relacionada ao elemento atual
     *
     * @return \View\View
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Define a label relacionada ao elemento atual
     *
     * @param \View\View $label
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Retorna o container relacionado ao elemento atual
     *
     * @return \View\View
     */
    public function getContain()
    {
        return $this->contain;
    }

    /**
     * Define o container relacionado ao elemento atual
     *
     * @param \View\View $contain
     */
    public function setContain(\View\View $contain)
    {
        $this->contain = $contain;

        return $this;
    }

    /**
     * Define o estilo css ao elemento
     * Sobreescreve o atributo.
     * Use css instead
     *
     * @deprecated since version 20/2/2014
     *
     * @param string $style
     * @param string $value
     * @return \View\View
     */
    public function setStyle($style, $value = '')
    {
        return $this->setAttribute('style', $style . ':' . $value . ';');
    }

    /**
     * Define if is um ouputjs
     */
    public function setOutputJs($outputJs = TRUE)
    {
        $this->data('outputjs', $outputJs);

        return $this;
    }

    /**
     * Verifica se é para fazer output de js
     *
     * @return boolean
     */
    public function getOutputJs()
    {
        $outputJs = $this->getData('outputjs');
        $parent = $this->parent();

        //verify parent
        if (!$outputJs && $parent)
        {
            $outputJs = $parent->getData('outputjs');

            if ($outputJs == TRUE)
            {
                $this->setOutputJs($outputJs);
            }
        }

        return $outputJs;
    }

    /**
     * Obtem um seletor jquery desse elemento
     *
     * @return string
     */
    protected function getSelector()
    {
        //avoid wrong selector
        if (!$this->getId())
        {
            return FALSE;
        }

        $selector = str_replace(array(self::REPLACE_SPACE, self::REPLACE_SHARP), array(' ', '#'), $this->getId());
        $selector = str_replace('\\', '\\\\\\\\', $selector);
        //add support for array fields
        //$selector = str_replace(array('[',']'), array('\\\\\\[','\\\\\\]'), $selector);
        //put # on start if not
        if (stripos($selector, '#') !== 0)
        {
            $selector = '#' . $selector;
        }

        return "$('{$selector}')";
    }

    public function getRealId()
    {
        return str_replace(array(self::REPLACE_SPACE, self::REPLACE_SHARP), array(' '), $this->getId());
    }

    /**
     * Adiciona um estilo css
     *
     * @param string $property
     * @param strng $value
     * @return \View\View
     */
    public function addStyle($property, $value = '')
    {
        //TODO suportar estilos css diretos e vários estilos ao mesmo tempo
        $style = $this->getStyle();
        parent::setAttribute('style', $style . $property . ':' . $value . ';');

        if ($this->getOutputJs())
        {
            \App::addJs($this->getSelector() . ".css('$property','$value');");
        }

        return $this;
    }

    /**
     * Emula o get/set css do jquery
     *
     * @param string $property
     * @param string $value
     * @return string
     */
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

    /**
     * Retorna o conteúdo do estilo css
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->getAttribute('style');
    }

    /**
     * Define the width
     *
     * @param float $width
     * @param string $unit
     *
     * @return \View\View
     */
    public function setWidth($width, $unit = 'px')
    {
        $this->css('width', $width . $unit);

        return $this;
    }

    /**
     * Return the width of element
     *
     * @return string
     */
    public function getWidth()
    {
        return $this->getStyle('width');
    }

    /**
     * Define the height
     *
     * @param float $height
     * @param string $unit
     *
     * @return \View\View
     */
    public function setHeight($height, $unit = 'px')
    {
        $this->css('height', $height . $unit);

        return $this;
    }

    /**
     * Return the width of element
     *
     * @return string
     */
    public function getHeight()
    {
        return $this->getStyle('height');
    }

    /**
     * Retorna se o campo é somente leitura
     *
     * @return boolean
     */
    public function getReadOnly()
    {
        return $this->getAttribute('readonly');
    }

    /**
     * Define o campo como somente leitura
     *
     * @param string $readOnly
     * @return \View\View
     */
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

    /**
     * Define todos os filhos do objeto como somente leitura.
     *
     * A função deve ser estática para suportar \DomElement
     *
     * @param boolean $readOnly
     */
    protected static function setChildrenReadOnly($view, $readOnly)
    {
        $children = $view->childNodes;

        if (count($children) > 0)
        {
            foreach ($children as $childField)
            {
                if ($childField instanceof \View\View)
                {
                    $childField->setReadOnly($readOnly, TRUE);
                }
                else if ($childField instanceof \DOMElement)
                {
                    if ($readOnly)
                    {
                        $view->setAttribute('readonly', 'readonly');
                    }
                    else
                    {
                        $view->removeAttribute('readonly');
                    }

                    \View\View::setChildrenReadOnly($childField, $readOnly);
                }
            }
        }
    }

    /**
     * Define como somente leitura os filhos, recursivamente.
     * @param boolean $readOnly
     */
    public function setChildsReadOnly($readOnly)
    {
        \View\View::setChildrenReadOnly($this, $readOnly);
    }

    /**
     * Define atributo tabIndex
     *
     * @param string $tabIndex
     * @return \View\View
     */
    public function setTabIndex($tabIndex)
    {
        $this->setAttribute('tabIndex', $tabIndex);

        return $this;
    }

    /**
     * Retorna atributo tabIndex
     *
     * @return string
     */
    public function getTabIndex()
    {
        return $this->getAttribute('tabIndex');
    }

    /**
     * Chama um evento de um campo
     *
     * @param string $event
     */
    public function trigger($event)
    {
        \App::addJs($this->getSelector() . ".trigger('$event')");

        return $this;
    }

    /**
     * Set on press enter
     *
     * @param string $event
     */
    public function onPressEnter($event)
    {
        return $this->setData('on-press-enter', $this->verifyAjaxEvent($event));
    }

    /**
     * Define o attributo onchange
     *
     * @param string $event
     * @return \View\View
     */
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
            \App::addJs($this->getSelector() . ".change()");
        }

        return $this;
    }

    /**
     * Define o attributo blur
     *
     * @param string $event
     * @return \View\View
     */
    public function blur($event)
    {
        $this->setAttribute('onblur', $this->verifyAjaxEvent($event));
        return $this;
    }

    /**
     * Verifica se o evento passado é uma função php, neste caso faz ajax automaticamente
     * @param string $event
     * @return string
     */
    protected function verifyAjaxEvent($event)
    {
        $dom = \View\View::getDom();

        if (method_exists($dom, $event))
        {
            //novo sistema de evento, possibilitando controle de permissões
            return 'p(\'' . $dom->getPageUrl() . '/' . $event . '\')';
        }

        return $event;
    }

    /**
     * Retorna o conteúdo do atributo onchange
     *
     * @return string
     */
    public function getChange()
    {
        return $this->getAttribute('onchange');
    }

    /**
     * Emulates jquery click
     *
     * @param string $onClick
     * @return \View\View
     */
    public function click($onClick = NULL)
    {
        if ($onClick)
        {
            $dom = \View\View::getDom();

            if (method_exists($dom, $onClick))
            {
                if (!$dom->verifyPermission($dom->parseEvent($onClick)))
                {
                    $this->setAttribute('onclick', "toast('Ação desabilitada!');");
                }
            }

            $this->setAttribute('onclick', $this->verifyAjaxEvent($onClick));
        }
        else
        {
            \App::addJs($this->getSelector() . ".click()");
        }

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

    /**
     * Retorna o objeto Dom/Layout do elemento
     * @return \View\Layout
     */
    public static function getDom()
    {
        return self::$dom;
    }

    /**
     * Define o objeto Dom/Layot do elemento
     *
     * @param DomDocument $dom
     * @return \View\View
     */
    public static function setDom(\DomDocument $dom)
    {
        self::$dom = $dom;
    }

    /**
     * Esconde elemento, como Jquery
     *
     * @return \View\View
     */
    public function hide()
    {
        return $this->addStyle('display', 'none');
    }

    /**
     * Show element, jquery like
     *
     * @return \View\View
     */
    public function show($param = FALSE)
    {
        if ($param == 'inline')
        {
            return $this->addStyle('display', 'inline-block');
        }
        else
        {
            return $this->addStyle('display', 'block');
        }
    }

    /**
     * Set disabled attribute
     *
     * @param boolean $disabled
     * @return \View\View
     */
    public function setDisabled($disabled = TRUE)
    {
        if ($disabled)
        {
            return $this->setAttribute('disabled', 'disabled');
        }
        else
        {
            return $this->removeAttribute('disabled');
        }
    }

    /**
     * Disable the element
     *
     * @return \View\View
     */
    public function disable()
    {
        return $this->setDisabled(TRUE);
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

    /**
     * Extends to make it chain
     *
     * @param type $name
     * @return \View\View
     */
    public function removeAttribute($name)
    {
        parent::removeAttribute($name);

        if ($this->getOutputJs())
        {
            \App::addJs($this->getSelector() . ".removeAttr('$name');");
        }

        return $this;
    }

    /**
     * Remove attribute from element, jquery style
     *
     * @param string $name
     * @return string
     */
    public function removeAttr($name)
    {
        return $this->removeAttribute($name);
    }

    /**
     * Função que emula o funcionamento da função attr do Jquery
     *
     * Caso o value seja passado a função trabalha como get
     * Caso contrário é um set.
     *
     * @param string $name
     * @param string $value
     */
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

    /**
     * Emultates jquery data function
     *
     * @param string $data
     * @param string $value
     *
     * @return string
     */
    public function data($data, $value)
    {
        return $this->attr('data-' . $data, $value);
    }

    /**
     * Tem funcionalidade semelhante ao appendChild.
     * O diferencial é que ela limpa todo conteúdo interno
     * e adiciona o novo.
     *
     * Emula função html do jQuery
     *
     * @param mixed $content
     */
    public function html($content = NULL)
    {
        //content is not used, is overwrite with func_get_args
        //$content = NULL;
        $this->clearChildren();
        $args = func_get_args();
        $ok = null;

        foreach ($args as $arg)
        {
            $ok = $this->append($arg);
        }

        return $ok;
    }

    /**
     * Overritgh do make chain
     *
     * @param \DOMNode $newnode
     * @param \DOMNode $refnode
     * @return \View\View
     */
    public function insertBefore(\DOMNode $newnode, \DOMNode $refnode = null)
    {
        parent::insertBefore($newnode, $refnode);

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
    public function appendBefore($newNode, $refnode = NULL)
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

        return $this;
    }

    /**
     * Remove o elemento do layout
     */
    public function remove()
    {
        if ($this->parentNode)
        {
            $this->parentNode->removeChild($this);
        }

        //TODO getOutput js make recursive overflow
        if ($this->getData('outputjs') && $this->getId())
        {
            \App::addJs($this->getSelector() . ".remove()");
        }

        return $this;
    }

    /**
     * Adiciona um conteúdo em texto html
     *
     * @param string $htmlText
     */
    public static function appendHtmlText($element, $htmlText)
    {
        if (!$element->getAttribute('id'))
        {
            $element->setAttribute('id', rand());
        }

        if ($htmlText)
        {
            if (function_exists('tidy_repair_string'))
            {
                //só o que tiver dentro do body
                $config['show-body-only'] = TRUE;
                $htmlText = \tidy_repair_string($htmlText, $config, 'utf8');
            }

            $layout = new \View\Layout();
            $layout->loadHTML('<html><body>' . $htmlText . '</body></html>');

            \View\View::getDom()->appendLayout($element->getAttribute('id'), $layout);
        }

        return $element;
    }

    /**
     * Coloca um campo como inválido.
     *
     * @param string $message
     */
    public function setInvalid($invalid = TRUE, $message = NULL)
    {
        if (!$invalid)
        {
            $this->removeAttribute('data-invalid');
        }
        else
        {
            parent::setAttribute('data-invalid', $invalid);

            if ($this->getOutputJS())
            {
                $name = $this->getRealId();

                //TODO not cool!
                if (method_exists($this->getDom(), 'getFormName') && $this->getDom()->getFormName())
                {
                    $name = $this->getDom()->getFormName() . '\\\\[' . $name . '\\\\]';
                }

                \App::addJs("$('[name={$name}]').attr('data-invalid', '{$invalid}');");
                \App::addJs("$('[data-invalid-id={$name}]').attr('data-invalid', '{$invalid}');");
            }

            if ($message)
            {
                if (is_array($message))
                {
                    $message = implode(' ', $message);
                }

                \App::addJs("$('[name={$name}]').attr('title','{$message}');");
                \App::addJs("$('[data-invalid-id={$name}]').attr('title','{$message}');");
                parent::setAttribute('title', $message);
            }
        }

        return $this;
    }

    /**
     * Return if field is invalid
     *
     * @return strning if field is invalid
     */
    public function getInvalid()
    {
        return $this->getAttribute('data-invalid');
    }

    /**
     * Invalida um elemento
     * //TODO usar setInvalid
     * @deprecated
     */
    public static function invalidate($seletor, $message)
    {
        \App::addJs("$('{$seletor}').attr('data-invalid', '1').attr('title','{$message}');");
    }

    /**
     * Remove todas invalidações
     */
    public static function removeAllInvalidate()
    {
        \App::addJs("removeDataInvalid()");
    }

    /**
     * Retorna a notação html do elemento
     *
     * @return elemento
     */
    public function __toString()
    {
        //Canoniza o elemento, nome de função maneiro.
        return $this->C14N(TRUE);
    }

    /**
     * Return childNodes count recursive
     *
     * @return int
     */
    public function count()
    {
        return \View\View::countNodes($this);
    }

    /**
     * Play sound element
     */
    public function play()
    {
        \App::addJs($this->getSelector() . ".play()");
    }

    /**
     * Add or remove auto focus
     *
     * @param boolean $autoFocus
     * @return \View\View
     */
    public function setAutoFocus($autoFocus = TRUE)
    {
        if ($autoFocus)
        {
            return $this->setAttribute('autofocus', '');
        }
        else
        {
            return $this->removeAttribute('autofocus');
        }
    }

    /**
     * Form changed adviced
     *
     * @return string
     */
    public function formChangedAdvice($advice = true)
    {
        return $this->data('form-changed-advice', $advice);
    }

    /**
     * Count nodes of an element
     *
     * @param \DOMElement $element
     * @return int
     */
    public static function countNodes($element)
    {
        $count = 1;

        if ($element->childNodes instanceof \DOMNodeList)
        {
            foreach ($element->childNodes as $node)
            {
                if (!$node instanceof \DOMText)
                {
                    $count += \View\View::countNodes($node);
                }
            }
        }

        return $count;
    }

    /**
     * Treat string to be outputed as JS.
     *
     * @param string $var
     * @return string
     */
    public static function treatStringToJs($var)
    {
        return addslashes(str_replace(array(PHP_EOL, "\r", "\n", "\t"), ' ', $var));
    }

    /**
     * Verify if some text has html
     *
     * @param string $string
     * @return boolean
     */
    public static function isHtml($string)
    {
        if (strlen($string) <= 3)
        {
            return false;
        }

        return preg_match('/<\s?[^\>]*\/?\s?>/i', $string);

        /* if ($string != strip_tags($string))
          {
          return true;
          }

          return false; */
    }

    public function listAvoidPropertySerialize()
    {
        $avoid[] = 'dom';
        $avoid[] = 'label';
        $avoid[] = 'contain';

        return $avoid;
    }

    /**
     * Emulates jquery parent function
     * @return \View\View
     */
    public function parent()
    {
    $parent = $this->parentNode;

    if ($parent)
    {
        if ($parent instanceof \DOMElement)
        {
            $parentId = $parent->getAttribute('id');

            return self::getDom()->byId($parentId);
        }
    }
    else
    {
        //cria um nulo pra não dar pau
        $element = new \View\Div( );
        //remove do dom para não reaparecer
        $element->remove();

        return $element;
    }
}

}
