<?php

namespace View;

/**
 * Input html padrão.
 * Campo de entrada textual de dados
 *
 * TODO maxlength
 *
 */
class Input extends \View\View
{

    /**
     * Tipo texto
     */
    const TYPE_TEXT = 'text';

    /**
     * Tipo oculto
     */
    const TYPE_HIDDEN = 'hidden';

    /**
     * Type checkbox
     */
    const TYPE_CHECKBOX = 'checkbox';

    /**
     * Type radio
     */
    const TYPE_RADIO = 'radio';

    /**
     * Tipo senha
     */
    const TYPE_PASSWORD = 'password';

    /**
     * Tipo telefone
     */
    const TYPE_TEL = 'tel';

    /**
     * Tipo arquivo
     */
    const TYPE_FILE = 'file';

    /**
     * Tipo número
     */
    const TYPE_NUMBER = 'number';

    /**
     * Tipo busca
     */
    const TYPE_SEARCH = 'search';

    /**
     * Tipo intervalo
     */
    const TYPE_RANGE = 'range';

    /**
     * Tipo data
     */
    const TYPE_DATE = 'date';

    /**
     * Tipo cor
     */
    const TYPE_COLOR = 'color';

    /**
     * Type datetime
     */
    const TYPE_DATETIME = 'datetime';

    /**
     * Type submit
     */
    const TYPE_SUBMIT = 'submit';

    /**
     * Type datetime local
     */
    const TYPE_DATETIME_LOCAL = 'datetime-local';

    public function __construct($id = \NULL, $type = 'text', $value = \NULL, $class = \NULL, $placeholder = \NULL)
    {
        parent::__construct('input', $id, \NULL, $class);
        $this->setName($id);
        $this->setType($type);
        $this->setValue($value);
        $this->setAttribute('placeholder', $placeholder);
    }

    /**
     * Define o tipo
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        parent::setAttribute('type', $type);
        return $this;
    }

    /**
     * Retorna o tipo do input
     *
     * @return string
     */
    public function getType()
    {
        return $this->getAttribute('type');
    }

    /**
     * Ativa ou desativa o autocomplete padrão do browser
     *
     * @param string $autoComplete
     */
    public function setAutoComplete($autoComplete)
    {
        if (!$autoComplete)
        {
            $this->setAttribute('autocomplete', 'off');
        }
        else
        {
            $this->removeAttribute('autocomplete');
        }

        return $this;
    }

    /**
     * Ativa capitalização automática para
     *
     * @param string $selector
     */
    public static function autoCapitalize($selector)
    {
        \App::addJs("autoCapitalize('{$selector}')");
    }

}
