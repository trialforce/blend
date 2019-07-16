<?php

namespace Service;

use DataHandle\Request;
use DataHandle\Session;

/**
 * Simple JSON api
 */
class Api
{

    static $log = TRUE;

    static function setLog($log)
    {
        self::$log = $log;
    }

    function getWithoutLoginList()
    {
        return array();
    }

    function getModelClass()
    {
        $model = Request::get('p');
        return '\\Api\\' . str_replace('-', '\\', $model);
    }

    function verifyPermission()
    {
        $withoutLogin = $this->getWithoutLoginList();

        $model = Request::get('p');
        $metodo = Request::get('e');

        $modelMetodo = $model . '.' . $metodo;

        //verify if user is looged
        if (!Session::get('user') && (!in_array($modelMetodo, $withoutLogin)))
        {
            //not logged
            throw new \UserException('Usuário nao está logado!', ERROR_CODE_EXCEPTION_LOGOUT);
        }
    }

    function execute()
    {
        $model = Request::get('p');
        $metodo = Request::get('e');
        $this->verifyPermission();

        $modelClass = $this->getModelClass();

        //verifica se modelo existe
        if (!class_exists($modelClass))
        {
            throw new \Exception('Modelo API inexistente!');
        }

        $obj = new $modelClass();

        if (!method_exists($obj, $metodo))
        {
            throw new \Exception('Método inexistente: ' . $metodo);
        }

        //begin and commit to avoid erros
        //get default connection, may not work every time
        $conn = \Db\Conn::getInstance();
        $conn->beginTransaction();
        $request = Request::getInstance();

        //add support for simple array of params
        if (is_array($request->get('params')))
        {
            $params = $request->get('params');
        }
        else
        {
            $params[] = $request;
        }

        $result = call_user_func_array(array($obj, $metodo), $params);

        if ($conn->inTransaction())
        {
            $conn->commit();
        }

        // se call for one model, extract his values to return to JSON
        if ($result instanceof \Db\Model)
        {
            $result = (object) $result->getArray();
        }

        //log if needed
        if (self::$log)
        {
            $this->log($metodo, $model, $result);
        }

        echo json_encode(array('result' => $result));
    }

    public function log($method, $model, $result)
    {

    }

    public static function logError($code, $message, $line, $file)
    {
        http_response_code(200);
        \Log::error('Error', $message, $line, $file, 'api.txt');

        echo json_encode(
                array(
                    'error' => \View\Script::treatStringToJs($message),
                    'code' => $code,
                    'line' => $line,
                    'file' => str_replace('.php', '', basename($file))
                )
        );
    }

}
