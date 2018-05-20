<?php

namespace Db;

/**
 * An action related to a model
 */
abstract class Action
{

    /**
     * Model
     * @var \Db\Model
     */
    protected $model;

    /**
     * The result of the action
     *
     * @var mixed
     */
    protected $result;

    public function __construct($model = null)
    {
        $this->setModel($model);
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * Execute the action
     */
    public abstract function execute();

    /**
     * After execute, you can get a formated result as a \View\View
     *
     * Used to show to user the formated result
     *
     * @return \View\View
     */
    public abstract function getResultView();

    /**
     * Execute a task in background
     *
     * @return TRUE
     */
    public function executeInBackGround()
    {
        $id = $this->getModel()->getId();
        $class = str_replace('Action\\', '', get_class($this));
        $explode = explode('\\', $class);

        //separa a action
        $action = strtolower($explode[count($explode) - 1]);

        unset($explode[count($explode) - 1]);
        $model = strtolower(implode('-', $explode));

        return self::executeBackGround($model, $action, $id);
    }

    /**
     * Execute an action in backround, static way, if neeed
     *
     * @param string $model
     * @param string $action
     * @param string $id
     * @return TRUE
     */
    public static function executeBackGround($model, $action, $id)
    {
        $url = \DataHandle\Server::getInstance()->getHost() . "/action/{$model}/{$action}/{$id}";
        return self::postAsync($url);
    }

    /**
     * Execute an async post, good to make background task
     *
     * @param string $url
     * @param string $params
     */
    public static function postAsync($url, $params = null)
    {
        $post_string = $params;

        if (is_array($params))
        {
            $post_string = http_build_query($params);
        }

        $parts = parse_url($url);

        $port = isset($parts['port']) ? $parts['port'] : 80;

        $fp = fsockopen($parts['host'], $port, $errno, $errstr, 30);

        //you can use POST instead of GET if you like
        $out = "GET " . $parts['path'] . "?$post_string" . " HTTP/1.1\r\n";
        $out .= "Host: " . $parts['host'] . "\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
        fclose($fp);

        return TRUE;
    }

}