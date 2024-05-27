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
     * @param string|null $id
     * @param mixed|null $options
     * @param string|null $value
     * @param string|null $class
     * @throws \Exception
     */
    public function __construct($id = NULL, $options = NULL, $value = NULL, $class = NULL)
    {
        parent::__construct('select', $id);
        $this->setName($id);

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
     * @see \View\Input::setValue()
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
     * @param iterable $searchResult
     * @param null $value
     * @param bool $putDefaultOption
     * @return View
     * @throws \Exception
     */
    public function createOptions($searchResult, $value = NULL, $putDefaultOption = TRUE)
    {
        return self::constructOptions($this, $searchResult, $value, $putDefaultOption);
    }

    /**
     * Add an option
     *
     * @param string|null $value
     * @param string|null $label
     * @param bool $selected
     * @return Select
     * @throws \Exception
     */
    public function addOption($value = NULL, $label = NULL, $selected = FALSE)
    {
        new \View\Option($value . '', $label . '', $selected, $this);

        return $this;
    }

    /**
     * Return the value
     *
     * @return string|string[]
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
            $this->setAttribute('size', '2');
        }
        else
        {
            $this->removeAttribute('size');
        }

        if (!stripos($this->getId(), '['))
        {
            $this->setId($this->getId() . '[]');
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
        return (bool) $this->getAttribute('size');
    }

    /**
     * Static versin of create options
     *
     * @param View $element
     * @param iterable $searchResult
     * @param string|null $value
     * @param bool $putDefaultOption
     * @return View
     * @throws \Exception
     */
    public static function constructOptions($element, $searchResult, $value = NULL, $putDefaultOption = TRUE)
    {
        $element->clearChildren();

        //if has default option in array, deactive it
        if (isIterable($searchResult) && isset($searchResult['']))
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

}
