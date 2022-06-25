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
    public function getResultView()
    {
        return new \View\Pre($this->getResult());
    }

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
     * Simple execute an action now and get it's result
     *
     * @param \Db\Model $model
     * @return mixed
     */
    public static function executeNow($model)
    {
        $className = get_called_class();
        $action = new $className($model);
        return $action->execute();
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
        $host = defined('ADM_URL') ? ADM_URL : \DataHandle\Server::getInstance()->getHost();

        $url = $host . "/action/{$model}/{$action}/{$id}";

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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);

        //less then 200 maybe will be problem in big ping/latency
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 10);

        if (stripos($url, 'localhost:8080') !== false)
        {
            curl_setopt($ch, CURLOPT_PORT, 80);
        }

        curl_exec($ch);
        curl_close($ch);

        return TRUE;
    }

}
