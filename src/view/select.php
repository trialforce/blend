<?php

namespace View;

/**
 * Select html element
 *
 * Support multiple select
 *
 */
class Select extends \View\View
{

    /**
     * Construct select
     *
     * @param string $idName
     * @param array $options
     * @param string $value
     * @param string $class
     */
    public function __construct($idName = NULL, $options = NULL, $value = NULL, $class = NULL)
    {
        parent::__construct('select', $idName);

        if ($options)
        {
            $this->createOptions($options, $value);
        }

        $this->setClass($class);
    }

    /**
     * Define the value
     *
     * (non-PHPdoc)
     * @see i\View\Input::setValue()
     */
    public function setValue($value)
    {
        return self::defineValue($this, $value);
    }

    /**
     * Static version of set Value
     *
     * @param \View\View $element
     * @param string $value
     * @return \View\View
     */
    public static function defineValue($element, $value)
    {
        if ($value instanceof \Type\VectorKeys)
        {
            $value = $value->getValue();
        }
        //convert to array case not
        if (!is_array($value))
        {
            $value = array($value);
        }

        if ($element->hasChildNodes())
        {
            foreach ($element->childNodes as $option)
            {
                $option->removeAttribute('selected');
            }

            foreach ($value as $myValue)
            {
                if ($myValue instanceof \Db\Model)
                {
                    $myValue = $myValue->getOptionValue();
                }

                foreach ($element->childNodes as $option)
                {
                    if ($option->getAttribute('value') == $myValue)
                    {
                        $option->setAttribute('selected', 'selected');
                        $element->setAttribute('data-value', implode(',', $value));
                    }
                }
            }
        }

        return $element;
    }

    /**
     * Create options
     *
     * @param array $searchResult
     */
    public function createOptions($searchResult, $value = NULL, $putDefaultOption = TRUE)
    {
        return self::constructOptions($this, $searchResult, $value, $putDefaultOption);
    }

    /**
     * Static versin of create options
     *
     * @param \View\View $element
     * @param array $searchResult
     * @param string $value
     * @param string $putDefaultOption
     * @return \View\View
     */
    public static function constructOptions($element, $searchResult, $value = NULL, $putDefaultOption = TRUE)
    {
        $element->clearChildren();

        //if has default option in array, deactive it
        if (is_array($searchResult) && array_key_exists('', $searchResult))
        {
            $putDefaultOption = false;
        }

        if ($putDefaultOption)
        {
            $defaultValue = '';

            if (is_string($putDefaultOption))
            {
                $defaultValue = $putDefaultOption;
            }

            $option = new \View\Option('', $defaultValue, FALSE, $element);
            $option->setId('select-null-option');
        }

        if (isIterable($searchResult))
        {
            foreach ($searchResult as $index => $item)
            {
                $option = \View\Option::createOption($item, $index, $element);
            }
        }

        if (isset($value))
        {
            self::defineValue($element, $value);
        }

        return $element;
    }

    /**
     * Add an option
     *
     * @param string $value
     * @param string $label
     */
    public function addOption($value = NULL, $label = NULL, $selected = FALSE)
    {
        new \View\Option($value . '', $label . '', $selected, $this);

        return $this;
    }

    /**
     * Return the value
     *
     * @return string
     */
    public function getValue()
    {
        if ($this->getMultiple())
        {
            return explode(',', $this->getAttribute('data-value'));
        }
        else
        {
            return $this->getAttribute('data-value');
        }
    }

    /**
     * Mark first value as selected
     *
     * @return \View\Select select
     */
    public function selectFirst()
    {
        $childs = $this->childNodes;

        if ($childs->item(1))
        {
            $this->val($childs->item(1)->getAttribute('value'));
        }

        return $this;
    }

    /**
     * Define if the select if multiple
     *
     * @param boolean $multiple
     * @return \View\Select
     *
     */
    public function setMultiple($multiple)
    {
        if ($multiple)
        {
            $this->setAttribute('multiple', 'multiple');
        }
        else
        {
            $this->removeAttribute('multiple');
        }

        if (!stripos($this->getId(), '['))
        {
            $this->setIdAndName($this->getId() . '[]');
        }

        return $this;
    }

    /**
     * Verify if select is multiple
     *
     * @return boolean
     */
    public function getMultiple()
    {
        return $this->getAttribute('multiple');
    }

}
