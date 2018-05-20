<?php

namespace View;

/**
 * Simple anchor
 *
 * @see http://www.w3.org/TR/html5/links.html
 * @see http://www.w3.org/TR/html401/struct/links.html
 */
class A extends \View\View
{

    /**
     * opens the linked document in a new window or tab
     */
    const TARGET_BLANK = '_blank';

    /**
     * Opens the linked document in the same frame as it was clicked (this is default)
     */
    const TARGET_SELF = '_self';

    /**
     * Opens the linked document in the parent frame
     */
    const TARGET_PARENT = '_parent';

    /**
     * Opens the linked document in the full body of the window
     */
    const TARGET_TOP = '_top';

    /**
     * Don't post data with the ajax link
     */
    const AJAX_NO_FORM_DATA = 'noFormData';

    public function __construct($id = \NULL, $label = \NULL, $href = '#', $class = \NULL, $target = NULL, $father = NULL)
    {
        parent::__construct('a', \NULL, $label, $class, $father);
        $this->setId($id)->setHref($href);

        //if href starts with tel:// it's not ajax
        if (!(stripos($href, 'tel://') === 0))
        {
            $this->setAjax(TRUE);
        }

        if ($target)
        {
            $this->setTarget($target);
        }
    }

    /**
     * Define if is an ajax link
     *
     * @param boolean $ajax
     * @return \View\A
     */
    public function setAjax($ajax = true)
    {
        if ($ajax)
        {
            return $this->setData('ajax', $ajax);
        }
        else
        {
            return $this->removeAttribute('data-ajax');
        }
    }

    /**
     * This attribute specifies the location of a Web resource,
     * thus defining a link between the current element (the source anchor)
     * and the destination anchor defined by this attribute.
     *
     * @param string $href
     * @return \View\A
     */
    public function setHref($href)
    {
        $href = self::arrayToLink($href);
        return $this->setAttribute('href', $href);
    }

    /**
     * Get href
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getAttribute('href');
    }

    /**
     * Set target
     *
     * @param string $target
     * @return \View\A
     */
    public function setTarget($target)
    {
        $this->setAttribute('target', $target);

        if ($target != \View\A::TARGET_SELF)
        {
            $this->setAjax(FALSE);
        }

        return $this;
    }

    /**
     * Set target
     *
     * @return String
     */
    public function getTarget()
    {
        return $this->getAttribute('target');
    }

    /**
     * Set rel.
     *
     * This attribute describes the relationship from the current document tothe anchor specified by the href attribute.
     * The value of this attribute is a space-separated list of link types.
     *
     * @param string $rel
     * @return \View\A
     */
    public function setRel($rel)
    {
        $this->setAttribute('rel', $rel);

        return $this;
    }

    /**
     * Get rel
     *
     * @return string
     */
    public function getRel()
    {
        return $this->getAttribute('rel');
    }

    /**
     * Array to link
     *
     * @param array $array
     * @param boolean $question
     * @return string
     */
    public static function arrayToLink($array, $question = TRUE)
    {
        if (is_string($array))
        {
            return $array;
        }

        $link = NULL;

        if (is_array($array))
        {
            foreach ($array as $key => $value)
            {
                if ($key && $value)
                {
                    $link[] = $key . '=' . $value;
                }
            }

            if ($question)
            {
                $link = '?' . implode('&', $link);
            }
            else
            {
                $link = implode('&', $link);
            }
        }

        return $link;
    }

}