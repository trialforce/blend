<?php

use \DataHandle\Request;
use \DataHandle\Server;
use \DataHandle\Config;

/**
 * Blend APP class.
 * Controls the base flow of Blend Framework
 */
class App
{

    const RESPONSE_TYPE_HTML = 'html';
    const RESPONSE_TYPE_APPEND = 'append';

    /**
     * Current page
     * @var \Page\Page
     */
    protected $page;

    /**
     * Current theme
     *
     * @var \View\Layout
     */
    private static $theme;

    /**
     * Current app instance
     * @var App
     */
    private static $instance;

    /**
     *
     * @var array of string
     */
    protected static $js = array();

    /**
     * Construct the webapp
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * Get current instance of app
     * @return App
     */
    public static function getInstance()
    {
        if (!self::$instance)
        {
            //can instance extended classes
            $class = get_called_class();
            self::$instance = new $class;
        }

        return self::$instance;
    }

    /**
     * Return the raw representantion of current page
     *
     * @return string
     */
    public function getCurrentPageRaw()
    {
        $page = Request::get('p');

        //page is not an array, avoid hack attempts
        if (is_array($page))
        {
            return null;
        }

        return $page;
    }

    /**
     * Get page/url extension
     * @return string or null
     */
    public function getCurrentPageExtension()
    {
        $requestUri = Server::getInstance()->getRequestUri(false);
        $explode = explode('.', $requestUri);

        if (count($explode) > 1 && isset($explode[count($explode) - 1]))
        {
            return mb_strtolower($explode[count($explode) - 1]);
        }

        return null;
    }

    /**
     * Return the current page name based on current URL
     *
     * @return string
     */
    public function getCurrentPage()
    {
        $page = $this->getCurrentPageRaw();
        $page = self::getPageClassFromUrl($page);

        if (!$page)
        {
            $page = \DataHandle\Config::getDefault('defaultPage', \DataHandle\Session::get('user') ? 'Page\Main' : NULL);
        }

        return $page;
    }

    public function handle()
    {
        ob_start();
        $page = $this->getCurrentPage();

        //single page
        //FIXME resolve this crappy situation, is used to login
        if ($page === NULL)
        {
            $this->getTheme();
            $this->handleSinglePage();
            return true;
        }

        //case page exists, avoid throw error
        if (class_exists($page))
        {
            $content = $this->handleInnerPage();
        }
        //case page not exists, try to instanciate a component
        else
        {
            $content = $this->handleInnerComponent();
        }

        if ($content)
        {
            return $this->handleResult($content);
        }

        return false;
    }

    protected function handleInnerPage()
    {
        $page = $this->getCurrentPage();
        //create the theme, so we can use it's object in inner layout
        //it's okay, it's cached
        $this->getTheme();
        //create page
        $content = new $page();

        return $content;
    }

    protected function handleInnerComponent()
    {
        //try to locate inside module folder
        $component = Request::get('p');
        $explode = explode('-', $component);
        $module = $explode[0];
        array_shift($explode);
        $componentClassName = $module . '\Component\\' . implode('\\', $explode);

        $content = null;

        if (class_exists($componentClassName))
        {
            $content = new \View\Layout(null, TRUE);
            $component = new $componentClassName();
            $component->callEvent();
        }
        //if don't find look without module
        else
        {
            $componentClassName = '\component\\' . str_replace('-', '\\', $component);

            if (class_exists($componentClassName))
            {
                $content = new \View\Layout(null, TRUE);
                $component = new $componentClassName();
                $component->callEvent();
            }
        }

        return $content;
    }

    /**
     * Return the string representantion of theme class
     *
     * @return string
     */
    public static function getThemeClass()
    {
        return Config::getDefault('theme', 'SimpleTheme');
    }

    /**
     * Return the current them
     *
     * @return \View\Layout
     */
    public static function getTheme()
    {
        //ajax request don't get theme
        if (Server::getInstance()->isAjax())
        {
            return null;
        }

        //get from cache
        if (self::$theme)
        {
            return self::$theme;
        }

        $themeClass = self::getThemeClass();

        //if not ajax call on create
        if (class_exists($themeClass))
        {
            self::$theme = new $themeClass();
            self::$theme->onCreate();
        }

        return self::$theme;
    }

    public function handleResult($content, $page404 = false)
    {
        \DataHandle\Session::writeClose();

        if (Server::getInstance()->isAjax())
        {
            $defaultResponse = Config::getDefault('response', 'content');
            echo static::prepareResponse($defaultResponse, $content);
        }
        else
        {
            $theme = self::getTheme();

            if (!$theme && $content instanceof \View\Document)
            {
                \View\View::setDom($content);
                $theme = $content;
            }

            $this->handleResultOutput($theme, $content, $page404);
        }

        return true;
    }

    protected function handleResultOutput(\View\Layout $theme, $content,$page404 = false)
    {
        $defaultResponse = Config::getDefault('response', 'content');

        \View\View::setDom($theme);
        $theme->appendLayout($defaultResponse, $content);
        $this->addJsToLayout($theme);

        if ($page404)
        {
            // Send 404 response to client
            http_response_code(404);
        }

        echo $theme;
    }

    /**
     * Handle a single page like login
     */
    protected function handleSinglePage()
    {
        $themeClass = self::getThemeClass();

        if (!Server::getInstance()->isAjax() && class_exists($themeClass))
        {
            $theme = new $themeClass();
            $theme->onCreate();
            \View\View::setDom($theme);
            $this->addJsToLayout($theme);
            echo $theme;
        }
        else
        {
            echo App::prepareResponse(Config::getDefault('response', 'content'), $this->page);
        }
    }

    /**
     * Prepare an ajax response
     *
     * @param string $response
     * @param string $html
     * @return string json
     */
    public static function prepareResponse($response = NULL, $html = NULL)
    {
        //if someting has beem echoed, respect it and avoid default flux
        $echoed = ob_get_contents();
        @ob_end_clean();

        if ($echoed)
        {
            return $echoed;
        }

        $cmds = [];
        $html = trim($html . '');

        if ($html)
        {
            $content = new stdClass();
            $content->selector = $response;
            $content->cmd = Config::getDefault('responseType', 'html');
            $content->content = $html;

            $cmds[] = $content;
        }

        $jss = App::getJs();

        foreach ($jss as $js)
        {
            $content = new stdClass();
            $content->selector = 'body';
            $content->cmd = 'script';
            $content->content = $js;

            $cmds[] = $content;
        }

        $result = [];
        $result['pushState'] = Config::get('pushState');
        $result['cmds'] = $cmds;

        return json_encode($result);
    }

    /**
     * Define the id of element of default response
     *
     * @param string $element
     * @param string $type
     */
    public static function setResponse($element = NULL, $type = NULL, $pushState = NULL)
    {
        if ($element)
        {
            Config::set('response', $element);
        }

        if ($type)
        {
            Config::set('responseType', $type);
        }

        if ($pushState)
        {
            App::setPushState($pushState);
        }
    }

    /**
     * Define url to push state
     *
     * @param string $url
     */
    public static function setPushState($url)
    {
        Config::set('pushState', $url);
    }

    /**
     * Don't change url, for this request
     *
     */
    public static function dontChangeUrl()
    {
        Config::set('pushState', 'undefined');
    }

    /**
     * Verify if url is changed or not in this request
     *
     * @return bool
     */
    public static function isUrlChanged()
    {
        if ( \DataHandle\Server::getInstance()->isAjax() )
        {
            return Config::get('pushState') != 'undefined';
        }
        else
        {
            return true;
        }
    }

    /**
     * Manually change the url
     *
     * @param string $url
     */
    public static function updateUrl($url)
    {
        \App::addJs("updateUrl('$url')");
    }

    /**
     * Add stored js to one layout
     *
     * @param \View\Layout $layout
     * @throws Exception
     */
    public function addJsToLayout($layout)
    {
        if (count(self::$js) == 0)
        {
            return;
        }

        $myJs = implode("\r\n", self::$js);
        $myJs = "function blendOnLoadJs() {\n$myJs\n};\nwindow.addEventListener('load', function() { blendOnLoadJs() }, false)";
        $js = new \View\Script(null, $myJs, \View\Script::TYPE_JAVASCRIPT);
        $js->setId('blend-js');
        $layout->getHtml()->append($js);
    }

    /**
     * Return the list of javascript commands to execute
     *
     * @return array
     */
    public static function getJs()
    {
        return self::$js;
    }

    /**
     * Make a simple echo of all js
     */
    public static function getJsScript()
    {
        $jss = \App::getJs();

        $html = '';

        if (count($jss) > 0)
        {
            $html .= "<script>\r\n";

            foreach ($jss as $js)
            {
                $html .= $js . "\r\n";
            }

            $html .= "</script>\r\n";
        }

        return $html;
    }

    /**
     * Add some javascript to be executed in browser
     *
     * @param string $js
     */
    public static function addJs($js)
    {
        if (trim($js))
        {
            self::$js[] = trim($js) . ';';
        }
    }

    /**
     * Add a external script to the page trough js and call a callback
     * fucntion when is load.
     *
     * If it's is allready loadead/added call the callback anyway;
     *
     * @param string $scriptUrl the url of the script
     * @param string $callBack the call back function
     */
    public static function addScriptOnce($scriptUrl, $callBack = null)
    {
        \App::addJs("addScriptOnce('$scriptUrl', function(){{$callBack}} )");
    }

    /**
     * Redirect to some url
     *
     * @param string $location
     * @param boolean $ajax if is ajax redirect
     * @param int $waitTime time to wait before redirect
     */
    public static function redirect($location, $ajax = false, $waitTime = 0)
    {
        if ($ajax)
        {
            $js = "p('$location');";
        }
        else
        {
            $js = "window.location='$location';";
        }

        if ($waitTime > 0)
        {
            $js = "setTimeout(\"$js\", $waitTime);";
        }

        App::addJs($js);
    }

    /**
     * Javascript Window open to be executed in browser
     *
     * @param string $location
     */
    public static function windowOpen($location)
    {
        App::addJs("window.open('" . addslashes($location) . "');");
    }

    /**
     * Reload current page
     */
    public static function refresh($ajax = FALSE)
    {
        if ($ajax)
        {
            self::addJs("p(window.location.href);");
        }
        else
        {
            self::addJs("window.location=window.location;");
        }
    }

    public static function sanitizePage($page)
    {
        $replaced = preg_replace("/[^a-zA-Z0-9_\-@]/", "", $page);

        if ($page != $replaced)
        {
            return '';
        }

        return $page;
    }

    /**
     * Receives one url, and return the complete class with namespaces
     *
     * @param $page string page url
     * @return string the resultant file class
     */
    public static function getPageClassFromUrl($page)
    {
        $page = self::sanitizePage($page);

        if (!$page)
        {
            return '';
        }

        //add support for module in old projects
        $explode = explode('-', $page);
        $module = $explode[0];
        array_shift($explode);
        $modulePage = $module . '\page\\' . implode('\\', $explode);

        if (class_exists($modulePage))
        {
            $page = $modulePage;
        }
        else
        {
            $page = '\page\\' . str_replace('-', '\\', $page);
        }

        return $page;
    }

}
