<?php

namespace ApiRest;

/**
 * Base Api Page
 */
class Page
{

    public function getPk()
    {

    }

    public function get()
    {

    }

    public function post()
    {

    }

    public function delete()
    {

    }

    public function callEvent()
    {
        $method = \ApiRest\App::getCurrentMethod();

        if (!method_exists($this, $method))
        {
            throw new \UserException('Impossible to find page/method: ' . $page->getPageUrl() . '/' . $method);
        }

        $replace = static::eventReplace();
        $parsed = str_replace(array_keys($replace), array_values($replace), $method);
        $canDo = $this->verifyPermission($parsed);

        if (!$canDo)
        {
            throw new \UserException('Sem permissão para acessar método ' . $parsed . ' na página ' . $this->getPageUrl() . '.');
        }

        return $this->$method();
    }

    public static function eventReplace()
    {
        $replace['getpk'] = 'listar';
        $replace['get'] = 'listar';
        $replace['delete'] = 'remover';
        $replace['post'] = 'adicionar';
        $replace['put'] = 'atualizar';

        return $replace;
    }

    public function verifyPermission($event)
    {
        if (!$this->isEventAcl($event))
        {
            return TRUE;
        }

        return false;
    }

    /**
     * List all acl events
     *
     * @return array
     */
    public static function listAclEvents()
    {
        $events['listar'] = 'Listar';

        return $events;
    }

    /**
     * Verify if event is acl controlled
     *
     * @param string $event
     * @return bool
     */
    public function isEventAcl($event)
    {
        return array_key_exists($event, static::listAclEvents());
    }

    public function getPageUrl()
    {
        $className = str_replace(['api\\', 'Api\\'], '', strtolower(get_class($this)));

        return $className;
    }

}
