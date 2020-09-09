<?php

namespace Misc;

/**
 * Simple hook system
 */
class Hook
{

    /**
     * List hook
     * @var array
     */
    private static $hooks = [];

    /**
     * Return the complete list of hooks
     *
     * @return array
     */
    public static function listHooks()
    {
        return static::$hooks;
    }

    /**
     * Add a hook
     *
     * @param string $class the class where you want the hook
     * @param string $method the method in class that you want the hook
     * @param string $modifier the modifier of the hook, normally before or after
     * @param mixed $hook the hook, can be a string with method name or you can pass a function
     */
    public static function add($class, $method, $modifier, $hook)
    {
        static::$hooks[$class][$method][$modifier][] = $hook;
    }

    /**
     * Execute a defined hook for passed parameters
     *
     * @param string $class the class where you want the hook
     * @param string $method the method in class that you want the hook
     * @param string $modifier the modifier of the hook, normally before or after
     * @param string $param the current object where the hook in to be executed
     *
     * @return array
     */
    public static function execute($class, $method, $modifier, $param)
    {
        if (!(isset(static::$hooks[$class]) && isset(static::$hooks[$class][$method]) && isset(static::$hooks[$class][$method][$modifier])))
        {
            return [false];
        }

        $hooks = static::$hooks[$class][$method][$modifier];
        $result = [];

        foreach ($hooks as $hook)
        {
            if ($hook instanceof \Closure)
            {
                $result[] = $hook($param);
            }
            else if (is_string($hook))
            {
                $result[] = $hook($param);
            }
        }

        return $result;
    }

    /**
     * Execute the hook of current class and method that you are in the code
     *
     * @param string $modifier the modifier of the hook, normally before or after
     *
     */
    public static function exec($modifier = 'after')
    {
        $backtrace = debug_backtrace();

        if (isset($backtrace[1]))
        {
            $trace = $backtrace[1];
            $class = $trace['class'];
            $method = $trace['function'];
            $param = isset($trace['object']) ? $trace['object'] : null;
            return static::execute($class, $method, $modifier, $param);
        }
    }

}
