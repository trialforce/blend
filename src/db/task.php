<?php

namespace Db;

/**
 * An action related to a model
 */
abstract class Task
{

    protected $data;

    /**
     * The result of the action
     *
     * @var mixed
     */
    protected $result;

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
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
        $result = is_array($this->getResult()) ? implode('<br/>', $this->getResult()) : $this->getResult();
        return new \View\Div(null, nl2br($result));
    }

    /**
     * After execute, you can get a formated result as text.
     *
     * Used to show in shell
     *
     * @return string
     */
    public function getResultText()
    {
        return $this->getResult();
    }

}
