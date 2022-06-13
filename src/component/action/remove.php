<?php

namespace Component\Action;

/**
 * The action to remove a register from a model
 */
class Remove extends \Component\Action\Action
{

    protected $modelName;
    protected $pk;

    public function __construct($modelName = null, $pk = null)
    {
        if (class_exists($modelName))
        {
            $label = lcfirst($modelName::getLabel());
            parent::__construct('btnRemover', 'trash', 'Remover ' . $label, 'remover', 'danger', 'Remove o registro atual do banco de dados!');

            $this->setModelName($modelName);
            $this->setPk($pk);
        }
        else
        {
            parent::__construct($modelName);
        }
    }

    public function getPk()
    {
        return $this->pk;
    }

    public function setPk($pk)
    {
        $this->pk = $pk;
        return $this;
    }

    public function getModelName()
    {
        return $this->modelName;
    }

    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
        return $this;
    }

    public function getModelNameForLink()
    {
        $modelName = $this->getModelName();
        $modelName = str_replace('\Model\\', '', $modelName);
        $modelName = str_replace('\\', '-', $modelName);

        return $modelName;
    }

    public function getUrl()
    {
        return "p('" . $this->getLink($this->getModelNameForLink(), $this->getPk()) . "');";
    }

    public function execute()
    {
        \App::dontChangeUrl();
        $confirmed = \DataHandle\Request::get('confirmed');

        if (!$this->getIdentifier())
        {
            throw new \UserException('É necessário informar um identificador para remoção!');
        }

        if ($confirmed == 1)
        {
            $this->confirmed();
        }
        else
        {
            $this->showConfirmation();
        }
    }

    protected function getPostedModelName()
    {
        $modelName = '\Model\\' . str_replace('-', '\\', $this->getEvent());
        return $modelName;
    }

    public function showConfirmation()
    {
        $modelName = $this->getPostedModelName();
        $label = lcfirst($modelName::getLabel());
        $yesLink = "p('" . $this->getLink($this->getEvent(), $this->getIdentifier(), array('confirmed' => '1')) . "');";

        $footer[1] = new \View\Button('cancelar', array(new \View\Ext\Icon('arrow-left'), 'Não'), \View\Blend\Popup::getJs('destroy'), 'btn');

        $footer[0] = new \View\Button('confirmaRemocao', array(new \View\Ext\Icon('trash'), 'Sim'), $yesLink, 'btn danger');
        $footer[0]->setAutoFocus();
        $footer[0]->focus();

        $body[] = 'Confirma remoção de ' . $label . '?';

        $popup = new \View\Blend\Popup('remocao', 'Atenção...', $body, $footer);
        $popup->show();
    }

    public function confirmed()
    {
        \View\Blend\Popup::delete();
        $modelName = $this->getPostedModelName();
        $pkValue = $this->getIdentifier();
        $model = new $modelName();
        $pk = $model->getPrimaryKey();

        if (!$pk)
        {
            throw new \UserException('Imposível encontrar chave primária do modelo!');
        }

        $model->setValue($pk, $pkValue);

        try
        {
            $ok = $model->delete();

            if ($ok)
            {
                toast('Registro removido com sucesso!!', 'success');

                $referer = \DataHandle\Server::getInstance()->getRefererUrl();

                //edit go back, other situation, only refresh screen
                if (stripos($referer, 'editar') > 0)
                {
                    \App::addjs('history.back(1);');
                }
                else
                {
                    \App::refresh(TRUE);
                }
            }
            else
            {
                toast('Problemas ao remover o registro!', 'danger');
            }

            return $ok;
        }
        catch (\UserException $exc)
        {
            toast($exc->getMessage(), 'danger');
            return false;
        }
        catch (\Exception $exc)
        {
            if ($exc instanceof \PDOException)
            {
                toast('Problemas ao remover o registro! <br>Verifique se não existe algum registro que depende deste cadastro!', 'danger');
            }
            else
            {
                throw $exc;
            }
        }
    }

}
