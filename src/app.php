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
     *
     * @var \Page\Page
     */
    protected $page;

    /**
     *
     * @var array of string
     */
    protected static $js = array();

    /**
     * Return the current page name based on current URL
     *
     * @return string
     */
    public function getCurrentPage()
    {
        $page = Request::get('p');

        //if a url exists in request
        if ($page)
        {
            $page = '\page\\' . str_replace('-', '\\', $page);
        }
        else
        {
            //if default page is null return null
            //TODO verify where this is used
            if (\DataHandle\Config::get('defaultPage') === NULL)
            {
                $page = NULL;
            }
            else
            {
                $page = \DataHandle\Config::getDefault('defaultPage', '\Page\Main');
            }
        }

        return $page;
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

    public function handle()
    {
        $page = $this->getCurrentPage();

        //single page
        //FIXME resolve this crappy situation, is used to login
        if ($page === NULL)
        {
            return $this->handleSinglePage();
        }

        $content = null;

        //case page exists, avoid throw error
        if (class_exists($page))
        {
            //create page
            $content = new $page();
        }
        //case page not exists, try to instanciate a component
        else
        {
            $componentClassName = '\component\\' . str_replace('-', '\\', Request::get('p'));

            if (class_exists($componentClassName))
            {
                $content = new \View\Layout(null, TRUE);
                $component = new $componentClassName();
                $component->callEvent();
            }
        }

        if ($content)
        {
            $this->handleResult($content);
        }
    }

    public function handleResult($content)
    {
        $themeClass = self::getThemeClass();

        //if not ajax call on create
        if (!Server::getInstance()->isAjax() && class_exists($themeClass))
        {
            $theme = new $themeClass();
            $theme->onCreate();
        }
        else
        {
            \View\View::setDom($content);
            $theme = $content;
        }

        if (!Server::getInstance()->isAjax())
        {
            \View\View::setDom($theme);

            $theme->appendLayout(Config::getDefault('response', 'content'), $content);
            $this->addJsToLayout($theme);
            echo $theme;
        }
        else
        {
            echo App::prepareResponse(Config::getDefault('response', 'content'), $content);
        }
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
        ob_end_clean();

        if ($echoed)
        {
            return $echoed;
        }

        $result['response'] = $response;
        $result['responseType'] = Config::getDefault('responseType', 'html');
        $result['pushState'] = Config::get('pushState');
        $result['content'] = trim($html . ''); //so you can verify response in js
        $result['script'] = implode(' ', App::getJs());

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
     * @param string $url
     */
    public static function dontChangeUrl()
    {
        Config::set('pushState', 'undefined');
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
     * @param Layoyt $layout
     */
    public function addJsToLayout($layout)
    {
        if (count(self::$js) > 0)
        {
            $js = new \View\Script(null, self::$js);

            //TODO add in right place
            $layout->getBody()->append($js);
        }
    }

    /**
     * Return the list of javascript commands to execute
     *
     * @return string
     */
    public static function getJs()
    {
        return self::$js;
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
     * Redirect to some url
     *
     * @param string $location
     * @param boolean, if is ajax
     * @param int time to wait before redirect
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

}
