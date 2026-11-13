<?php

namespace Db;

/**
 * An action related to a model
 */
#[\AllowDynamicProperties]
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

    /**
     * If is or not to register log, defaut true.
     * Overwrite in your task
     * @return true
     */
    public function isRegisterLog()
    {
        return true;
    }

    /**
     * Return the related data of the task
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Determine the related data of the task
     *
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Add a result message
     * Case is shell: an echo is make on the moment using ANSI color
     * Case is browser: the result uses the hexadecimal CSS color
     *
     * @param string $message message
     * @param \Media\Color|null $color terminal color
     * @return $this
     */
    public function addResult($message = null, ?\Media\Color $color = null)
    {
        $isShell = \DataHandle\Server::getInstance()->isShell();

        if (is_array($message))
        {
            foreach ($message as $msg)
            {
                $resultMessage = !$isShell && $color ? '<span style="color: ' . $color->getHex() . '">' . $msg . '</span>' : $msg;
                $this->result[] = $resultMessage;

                if ($isShell)
                {
                    $shellMessage = $color ? $color->getAnsi() . $msg . "\033[0m" : $msg;
                    echo $shellMessage."\r\n";
                }
            }
        }
        else
        {
            $resultMessage = !$isShell && $color ? '<span style="color: ' . $color->getHex() . '">' . $message . '</span>' : $message;
            $this->result[] = $resultMessage;

            if ($isShell)
            {
                $shellMessage = $color ? $color->getAnsi() . $message . "\033[0m" : $message;
                echo $shellMessage."\r\n";
            }
        }

        return $this;
    }

    /**
     * Return the result message
     *
     * @return array the result message
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Define the reult message
     *
     * @param array $result result message array
     * @return $this
     */
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
     * After execute, you can get a formatted result as text.
     *
     * Used to show in shell
     *
     * @return string
     */
    public function getResultText()
    {
        $result = [];

        if ($this->getResult() && is_array($this->getResult()))
        {
            $result = $this->getResult();
        }

        return implode("\r\n", $result);
    }

}
