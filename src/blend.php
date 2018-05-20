<?php

use \DataHandle\Request;
use \DataHandle\Server;
use \DataHandle\Config;
/* Active php gzip */
ini_set('zlib.output_compression', 'On');

//System constants
define('DS', '/'); //DIRECTORY_SEPARATOR
define('NOW', date('d/m/Y H:i:s'));

require 'autoload.php';

/**
 * Adjust path to system bar
 *
 * @param string $path
 * @return string
 */
function adjusthPath($path)
{
    return str_replace(array('\\', '/'), DS, $path);
}

/**
 * Default app classes, extends to add your functions
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
     *
     * @return type
     */
    public function getCurrentPage()
    {
        $page = Request::get('p');

        if ($page)
        {
            $page = '\page\\' . str_replace('-', '\\', $page);
        }
        else
        {
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

    public static function getThemeClass()
    {
        return Config::getDefault('theme', 'SimpleTheme');
    }

    public function handle()
    {
        $page = $this->getCurrentPage();

        //SINGLE PAGE
        if ($page === NULL)
        {
            return $this->handleSinglePage();
        }

        $content = null;

        //avoid throw error
        if (class_exists($page))
        {
            $this->page = $content = new $page();
        }
        else
        {
            $requestP = Request::get('p');

            $class = '\component\\' . str_replace('-', '\\', $requestP);

            if (class_exists($class))
            {
                $content = new \View\Layout(null, TRUE);
                $component = new $class();
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
     * Handle a service
     */
    protected function handleService()
    {
        $service = Request::get('s');
        $event = Request::get('e');

        $service::$event();
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
     * Define response and type
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
     * Adiciona os javascript gerais para um layout especifico
     *
     * @param Layoyt $layout
     */
    public function addJsToLayout($layout)
    {
        if (count(self::$js) > 0)
        {
            $js = new \View\Script(null, self::$js);

            //TODO adicionar no lugar certo
            $layout->getBody()->append($js);
        }
    }

    public static function getJs()
    {
        return self::$js;
    }

    /**
     * Adiciona um script js
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
     * Redireciona a página para alguma url
     *
     * @param string $location
     * @param boolean, caso seja ajax
     * @param int milesegundos de espera
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
     * Window open
     *
     * @param string $location
     */
    public static function windowOpen($location)
    {
        $location = addslashes($location);
        $js = "window.open('$location');";

        App::addJs($js);
    }

    /**
     * Recarrega a página
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

/**
 * Reproduces javascript function alert, called from php
 *
 * @param string $message the message itself
 *
 */
function alert($message)
{
    \App::addJs('alert(\'' . $message . '\');');
}

/**
 * Make a Simple js toast.
 *
 * Type valid values:
 * NULL
 * danger
 * primary
 * info
 * alert
 * success
 *
 * @param string $message toast message, can be html
 * @param string $type a custom css type, in case a extra class in css.
 * @param int $duration default 4000 mileseconds
 */
function toast($message = NULL, $type = NULL, $duration = 4000)
{
    $message = \View\Script::treatStringToJs($message);

    //play error sound
    if (stripos(' ' . $type, 'danger') > 0)
    {
        \View\Audio::playSoundOnce('theme/audio/error.mp3');
    }

    \App::addJs("toast('{$message}', '{$type}', {$duration} )");
}

/**
 * Exception to user, do not log in file
 */
class UserException extends Exception
{

}

/**
 * Glob recursive
 * Does not support flag GLOB_BRACE
 *
 * @param string $pattern
 * @param int $flags
 * @return array
 */
function globRecursive($pattern, $flags = 0)
{
    $files = glob($pattern, $flags);

    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir)
    {
        $files = is_array($files) ? $files : array();
        $fileRecursive = globRecursive($dir . '/' . basename($pattern), $flags);
        $fileRecursive = is_array($fileRecursive) ? $fileRecursive : array();
        $files = array_merge($files, $fileRecursive);
    }

    return $files;
}
