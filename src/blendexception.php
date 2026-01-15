<?php

/**
 * Blend Exception
 */
class BlendException extends \Exception
{
    /**
     * If is to make the default log or not
     * @var bool
     */
    protected bool $defaultLog = true;

    /**
     * Any extra data you need in exception
     * @var mixed
     */
    protected mixed $data;

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null, mixed $data = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setData($data);
    }

    /**
     * Return if it to make the defautl log
     * @return bool
     */
    public function getDefaultLog(): bool
    {
        return $this->defaultLog;
    }

    /**
     * Set if is to make the defaut log
     * @param bool $defaultLog
     * @return $this
     */
    public function setDefaultLog(bool $defaultLog): BlendException
    {
        $this->defaultLog = $defaultLog;
        return $this;
    }

    /**
     * Return the extra data from the exception
     *
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Define some extra data to exception
     * @param mixed $data
     * @return $this
     */
    public function setData(mixed $data): BlendException
    {
        $this->data = $data;
        return $this;
    }

}