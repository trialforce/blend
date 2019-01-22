<?php

/**
 * Mimics Javascript console.
 *
 * http://getfirebug.com/wiki/index.php/Console_API
 */
class Console
{

    /**
     * Make a log do javascript console.
     *
     * @param mixed $var
     * @return Console
     */
    protected static function generateLog($vars, $type = 'log')
    {
        if (is_array($vars))
        {
            foreach ($vars as $var)
            {
                if (is_object($var))
                {
                    //$var = \View\Script::treatStringToJs( print_r( $var, 1 ) );
                    $class = get_class($var);
                    $var = (array) $var;
                    $var = json_encode($var);
                    $var = "{  \"$class\": {$var}}";
                    \App::addJs("console.{$type}({$var});");
                }
                else if (is_array($var))
                {
                    $var = json_encode($var, TRUE);
                    \App::addJs("console.{$type}({$var});");
                }
                else
                {
                    $var = \View\Script::treatStringToJs($var);
                    \App::addJs("console.{$type}('{$var}');");
                }
            }
        }

        return TRUE;
    }

    public static function dir($var)
    {
        $vars = func_get_args();

        foreach ($vars as $var)
        {
            //$var = json_encode( $var );
            //$var = "{  \"Mail\": {$var}}";
            //$var =\View\Script::treatStringToJs( $var );
            //\App::addJs( "console.log(JSON.parse('{$var}'));" );
            //\App::addJs( "console.dir(JSON.parse('$var'));" );
        }
    }

    /**
     * Writes a message to the console.
     *
     * @param mixed $var
     * @return Console
     */
    public static function log($var = NULL)
    {
        return self::generateLog(func_get_args(), 'log');
    }

    /**
     * Writes a message to the console with the visual "info" icon
     * and color coding and a hyperlink to the line where it was called.
     *
     * @param mixed $var
     * @return Console
     */
    public static function info($var)
    {
        return self::generateLog(func_get_args(), 'info');
    }

    /**
     * Writes a message to the console, including a hyperlink to the line where it was called.
     *
     * @param mixed $var
     * @return Console
     */
    public static function debug($var)
    {
        return self::generateLog(func_get_args(), 'debug');
    }

    /**
     * Writes a message to the console with the visual "warning" icon
     * and color coding and a hyperlink to the line where it was called.
     *
     * @param mixed $var
     * @return Console
     */
    public static function warn($var)
    {
        return self::generateLog(func_get_args(), 'debug');
    }

    /**
     * Writes a message to the console with the visual "error" icon
     * and color coding and a hyperlink to the line where it was called.
     *
     * @param mixed $var
     * @return Console
     *
     */
    public static function error($var)
    {
        return self::generateLog(func_get_args(), 'info');
    }

    /**
     * Clears the console.
     *
     * @return Console
     */
    public static function clear()
    {
        \App::addJs('console.clear();');

        return TRUE;
    }

}
