<?php

namespace ApiRest;

/**
 * App for Api REST
 */
class App extends \App
{

    public function handle()
    {
        $page = $this->getCurrentPage();
        $result = $page->callEvent();

        header('Content-Type: application/json');
        $result = \Disk\Json::encode($result);

        //clear api session each request
        \DataHandle\Session::getInstance()->destroy();

        echo $result;
    }

    /**
     * Return the current page object
     *
     * @return \ApiRest\Page
     * @throws \UserException
     */
    public function getCurrentPage()
    {
        $pageRaw = $this->getCurrentPageRaw();

        if (!$pageRaw)
        {
            throw new \UserException('Impossible to find page: ' . $pageRaw);
        }

        $className = '\Api\\' . str_replace('-', '\\', $pageRaw);

        if (!class_exists($className))
        {
            throw new \UserException('Page does not exists: ' . $pageRaw);
        }

        return new $className;
    }

    public static function getCurrentMethod()
    {
        $event = \DataHandle\Request::get('e');
        $requestMethod = \DataHandle\Server::getInstance()->getRequestMethod();

        //1 - event is the method api/class/event
        $method = $event;

        //2 - get by id case of api/class/1
        if (is_numeric($method))
        {
            if ($requestMethod == 'GET')
            {
                $method = 'getpk';
            }
            else
            {
                $method = strtolower($requestMethod);
            }
        }

        //case without event
        if (!$method)
        {
            $method = strtolower($requestMethod);
        }

        return $method;
    }

    public static function getPostData()
    {
        $postdata = file_get_contents("php://input");

        $json = \Disk\Json::decode($postdata);

        return $json;
    }

    public static function exception(\Throwable $exception)
    {
        header('Content-Type: application/json');
        $stdClass = new \stdClass();
        $stdClass->error = $exception->getMessage();
        $stdClass->code = $exception->getCode();

        echo \Disk\Json::encode($stdClass);
    }

}
