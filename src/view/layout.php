<?php

namespace View;

use \DataHandle\Server;
use \DataHandle\UserAgent;
use \DataHandle\Request;

/**
 * Default layout class
 */
class Layout extends \View\Document
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
        parent::__construct($layout, $setDom);

        if (isset($layout) && $layout)
        {
            $this->setLayoutFile($layout);
        }
    }

    /**
     * Return the textual content of some layout
     *
     * @param string $layout
     * @return mixed
     * @throws \Exception
     */
    public function getLayoutContent($layout)
    {
        $htmlFile = filePath($layout, 'html');

        if (file_exists($htmlFile))
        {
            //add suporte a UTF-8
            $content = mb_convert_encoding(file_get_contents($htmlFile), 'HTML-ENTITIES', "UTF-8");

            if (!$content)
            {
                throw new \Exception('Layout vazio em ' . $htmlFile);
            }

            return $content;
        }
        else
        {
            throw new \Exception('Layout não encontrado em ' . $htmlFile);
        }
    }

    /**
     * Load layout from file
     *
     * @param string $layout
     */
    public function loadFromFile($layout)
    {
        $content = $this->getLayoutContent($layout);
        $content = $this->parseIncludes($content);

        //desabilita erros chatos da libxml na leitura de layouts
        libxml_use_internal_errors(true);
        $this->strictErrorChecking = FALSE;
        $this->loadHTML($content);
        libxml_clear_errors();
    }

    /**
     * Return the current event
     *
     * @return string
     */
    public function getEvent()
    {
        $event = Request::get('e');

        if (!$event)
        {
            $event = Request::get('q') ? 'listar' : 'oncreate';
        }

        return $event;
    }

    /**
     * Executes the current event
     *
     * @return mixed
     * @throws \UserException
     */
    public function callEvent()
    {
        $event = $this->getEvent();

        if (!$event)
        {
            return false;
        }

        //TODO this piece of code need to be in other plase
        //adjust some events to avoid overhead of eventos
        $replace['oncreate'] = 'listar';
        $replace['confirmaExclusao'] = 'remover';
        $replace['salvar'] = 'adicionar';

        $parsed = str_replace(array_keys($replace), array_values($replace), $event);
        $canDo = $this->verifyPermission($parsed);

        if (!$canDo)
        {
            throw new \UserException('Sem permissão para acessar evento <strong>' . ucfirst($event) . '</strong> na página <strong>' . ucfirst($this->getPageUrl()) . '</strong>.');
        }

        //register the event in html, so when get it from js
        \App::addJs("document.querySelector('#content').setAttribute('event', '{$event}')");

        if (method_exists($this, $event))
        {
            $reflection = new \ReflectionMethod($this, $event);

            if (!$reflection->isPublic())
            {
                throw new \UserException("Acesso negado! Método " . $event . ' não é publico.');
            }

            return $this->$event();
        }
    }

    /**
     * Verify permission to event.
     * By default user has all permission.
     * You need to implement a Acl.
     *
     * @param string $event
     * @return boolean
     */
    public function verifyPermission($event)
    {
        return true;
    }

    /**
     * Called by system when the layout is created.
     * Called when is not ajax
     *
     */
    public function onCreate()
    {

    }

    /**
     * Define the title of layout
     *
     * @param string $title the page title
     *
     * @return \View\Layout
     */
    public function setTitle($title)
    {
        //try to get base theme, to change title in html, not js
        $theme = \App::getTheme();

        if (!$theme)
        {
            $theme = $this;
        }

        $element = $theme->getElementsByTagName('title')->item(0);

        if ($element instanceof \DOMElement)
        {
            $element->nodeValue = htmlspecialchars($title);
        }

        if (Server::getInstance()->isAjax() || !$element)
        {
            $title = \View\Script::treatStringToJs($title);
            \App::addJs("document.title = '{$title}'");
        }


        return $this;
    }

    /**
     * Return the page title
     *
     * @return string
     */
    public function getTitle()
    {
        $element = $this->getElementsByTagName('title')->item(0);

        if ($element instanceof \DOMElement)
        {
            return $element->nodeValue;
        }

        return '';
    }

    /**
     *
     * @param string $content
     * @return string
     */
    protected function parseIncludes($content)
    {
        //localiza includes no layout
        $regExp = "/<include>(.*)<\/include>/iu";
        preg_match_all($regExp, $content, $includes);

        //passa pelos include obtendo conteúdo
        if (is_array($includes[1]) && count($includes[1]) > 0)
        {
            foreach ($includes[1] as $line => $includeString)
            {
                $innerContent = $this->getLayoutContent($includeString);
                $content = str_replace($includes[0][$line], $innerContent, $content);
                $content = $this->parseIncludes($content);
            }
        }

        return $content;
    }

    /**
     * Define um arquivo html padrão para este layout
     *
     * @param string $layout caminho relativo
     */
    public function setLayoutFile($layout)
    {
        $this->loadFromFile($layout);
        $this->setBaseUrl();
    }

    /**
     * Adiciona uma planilha de estilos no layout
     *
     * @param $id
     * @param string $href
     * @param string|null $media
     * @param bool $addDefaultPath
     * @return Layout
     * @throws \Exception
     */
    function addStyleShet($id, $href, $media = NULL, $addDefaultPath = TRUE)
    {
        $heads = $this->getElementsByTagName('head');
        $head = $heads->item(0);

        if (!$head)
        {
            return $this;
        }

        $defaultPath = '';

        if ($addDefaultPath)
        {
            $defaultPath = APP_PATH . '/';
        }

        $file = new \Disk\File($defaultPath . $href);

        //auto optimize/minimize file if is needed
        if ($file->exists())
        {
            $mTime = $file->getMTime();
            $filePath = str_replace('.css', '', $file->getBasename(TRUE));
            $filePath = $filePath . '_' . $mTime . '.css';

            //$fileOptimize = \Disk\File::getFromStorage($file->getBasename(TRUE));
            $fileOptimize = \Disk\File::getFromStorage($filePath);

            if (!$fileOptimize->exists() || ( $mTime > $fileOptimize->getMTime() ))
            {
                $file->load();

                $fileOptimize->save(\Misc\CssMin::optimize($file->getContent()));
            }

            //avoid cache
            $href = $fileOptimize->getUrl();
        }

        $stylesheet = new \View\Link($id, $href, 'stylesheet', 'text/css', $media);
        $head->appendChild($stylesheet);

        return $this;
    }

    /**
     * Get a script file, considering modification data
     *
     * @param string $src file source/path
     * @return string url
     */
    protected function getScriptFile($src)
    {
        if (is_file($src))
        {
            $file = new \Disk\File($src);
        }
        else
        {
            $file = new \Disk\File(APP_PATH . '/' . $src);
        }

        //auto optimize/minimize file if is needed
        if ($file->exists())
        {
            $mTime = $file->getMTime();
            $filePath = str_replace('.js', '', $file->getBasename(TRUE));
            $filePath = $filePath . '_' . $mTime . '.js';

            $fileOptimize = \Disk\File::getFromStorage($filePath);

            $src = $fileOptimize->getUrl();

            if (!$fileOptimize->exists() || ( $file->getMTime() > $fileOptimize->getMTime() ))
            {
                $file->load();

                //TODO make js optimize
                $fileOptimize->save($file->getContent());
            }
        }

        return $src;
    }

    /**
     * Add a script to layout
     *
     * TODO make it work with ajax
     *
     * @param string $src used when is a external script
     * @param string $content used when is a inline script
     */
    function addScript($src = NULL, $content = NULL, $type = \View\Script::TYPE_JAVASCRIPT, $id = NULL, $async = FALSE)
    {
        $head = $this->getElementsByTagName('head')->item(0);
        $newSrc = $this->getScriptFile($src);

        $script = new \View\Script($newSrc, $content, $type, $async);
        $script->setId($id);

        if ($head)
        {
            $head->appendChild($script);
        }
        else
        {
            $this->append($script);
        }

        return $script;
    }

    /**
     * Add a javascript file in the end of page
     *
     * @param string $src source url
     * @param string $id script id
     * @param boolean $async async or not
     * @return \View\Script
     */
    function addScriptEnd($src, $id = NULL, $async = TRUE)
    {
        $newSrc = $this->getScriptFile($src);

        $script = new \View\Script($newSrc, null, \View\Script::TYPE_JAVASCRIPT, $async);
        $script->setId($id);
        $this->getHtml()->appendChild($script);

        return $script;
    }

    /**
     * Método responsável por renderizar conteúdo de
     * uma string HTML, e não simplesmente joga-la com formato
     * de texto puro.
     *
     * @param string $html
     * @return \DOMDocumentFragment
     */
    /* public function getHtmlElement($html)
      {
      if ($html && mb_strlen(trim($html)) > 0)
      {
      $fragment = $this->createDocumentFragment();
      @$fragment->appendXML($html);
      return $fragment;
      }

      return $html;
      } */

    /**
     * Return the body element
     *
     * @return \DomElement
     */
    public function getBody()
    {
        $bodys = $this->getElementsByTagName('body');

        // avoid hacking attempts
        if (!$bodys->item(0))
        {
            throw new \UserException('Sem permissão');
        }

        return new \View\DomContainer($bodys->item(0));
    }

    public function getFooter()
    {
        $footers = $this->getElementsByTagName('footer');

        return new \View\DomContainer($footers->item(0));
    }

    /**
     * Return the head element
     *
     * @return \View\DomContainer
     */
    public function getHead()
    {
        $heads = $this->getElementsByTagName('head');

        return new \View\DomContainer($heads->item(0));
    }

    /**
     * Return the html element
     * @return \View\DomContainer
     */
    public function getHtml()
    {
        $htmls = $this->getElementsByTagName('html');

        return new \View\DomContainer($htmls->item(0));
    }

    /**
     * Add class to navigator in body
     *
     * @return \View\Layout
     */
    public function setBodyDefaultClass()
    {
        $browser = new UserAgent();
        $body = $this->getBody();
        $name = $browser->getName();
        $version = $browser->getSimpleVersion();
        $class = $body->getAttribute('class') . ' ' . $name;

        if ($version)
        {
            $class .= ' ' . $name . '_' . $version;
        }

        $body->setAttribute('class', $class);

        return $this;
    }

    /**
     * Define the base url in base element
     * If base not exist it is created
     *
     * @return \View\Layout
     */
    public function setBaseUrl()
    {
        $server = Server::getInstance();
        $bases = $this->getElementsByTagName('base');
        $base = $bases->item(0);

        //if exist
        if ($base)
        {
            $base->setAttribute('href', $server->getHost());

            return $this;
        }

        $base = new \View\Base(NULL, $server->getHost());

        $heads = $this->getElementsByTagName('head');
        $head = $heads->item(0);

        if (is_object($head))
        {
            $head->appendChild($base);
        }

        return $this;
    }

    /**
     * Return the url of the page
     *
     * @return string
     */
    public function getPageUrl()
    {
        $className = strtolower(get_class($this));
        $useModule = \DataHandle\Config::get('use-module');
        $module = Request::get('m');

        if ($useModule)
        {
            $className = str_replace($module . '\\', '', $className);
            $module .= '/';
        }
        else
        {
            $module = '';
        }

        $moduleSeparator = '-';
        $class = str_replace('\\', $moduleSeparator, $className);
        $url = $module . str_replace(array('Page\\', 'page\\', 'page' . $moduleSeparator, 'Page' . $moduleSeparator), '', $class);

        return $url;
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

        $html = $this->saveHTML();

        return self::optimizeHtml($html);
    }

    /**
     * Optimize html
     * Remove comments and other unnecessary things
     *
     * @param string $html
     * @return string
     */
    public static function optimizeHtml($html)
    {
        //nobody likes html entities
        $html = html_entity_decode($html);
        //remove comments
        //$html = preg_replace('/<!--(?!<!)[^\[>].*?-->/Uis', '', $html);
        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);
        //trim all lines
        $html = implode(PHP_EOL, array_map('trim', explode(PHP_EOL, $html)));
        //breaks script in other tag
        $html = str_replace('</script>', "</script>\r\n", $html);
        $html = str_replace('</main>', "</main>\r\n", $html);
        $html = str_replace('</header>', "</header>\r\n", $html);
        $html = str_replace('</p>', "</p>\r\n", $html);
        $html = str_replace('<br>', "<br/>\r\n", $html);
        $html = str_replace('</a>', "\r\n</a>", $html);
        //padronize line endings
        $html = str_replace("\r\n", 'NEW_LINE', $html);
        $html = str_replace("\r\n", 'NEW_LINE', $html);
        $html = str_replace(array("\r", "\n"), 'NEW_LINE', $html);
        $html = str_replace(array("\r", "\n"), 'NEW_LINE', $html);
        $html = str_replace('NEW_LINE', "\r\n", $html);
        //remove two blank lines
        $html = str_replace("\r\n\r\n", "\r\n", $html);

        return $html;
    }

}
