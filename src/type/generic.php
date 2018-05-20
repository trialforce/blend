<?php

namespace Type;

/**
 * Generic data type
 */
interface Generic
{

    /**
     * Construct with its default value
     */
    public function __construct($value);

    /**
     * Set the default value of datatype
     * @param mixed $value
     */
    public function setValue($value);

    /**
     * Return the value datatype
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Return the string representation of the datatype
     */
    public function __toString();

    /**
     * Return the string representation of this type to human,
     * used in grid, list e etc
     */
    public function toHuman();

    /**
     * Return the value parsed to database
     */
    public function toDb();

    /**
     * Return a instance of the data type
     */
    public static function get($value);

    /**
     * Get instance and returns it's default value
     *
     * @param mixed $value value
     */
    public static function value($value);
}