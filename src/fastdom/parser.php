<?php
namespace FastDom;

class Parser
{
    public static function isHtml($content)
    {
        return preg_match('/<\w+/', $content);
    }

    public static function starWithHTML($content)
    {
        return preg_match('/^[\s\r\n]*<\s*\w+/', $content);
    }

    public static function parseHtml($html)
    {
        $dom = new \DOMDocument('1.0','utf-8');
        $dom->loadHTML(utf8_decode($html));

        return self::convertToFast($dom->documentElement);
    }

    public static function convertToFast(\DOMNode $element)
    {
        $result = [];

        if ($element instanceof \DOMText)
        {
            return $element->textContent;
        }
        else if ($element instanceof \DOMComment)
        {
            return $result;
        }

        $fastElement = new \FastDom\Element($element->nodeName);

        if($element->hasAttributes())
        {
            foreach($element->attributes as $attribute)
            {
                $fastElement->setAttribute($attribute->name, $attribute->value);
                //$array['_attributes'][$attribute->name] = $attribute->value;
            }
        }

        foreach ($element->childNodes as $child)
        {
            $fastElement->append(self::convertToFast($child));
        }

        return $fastElement;
    }

    public static function parseHtmlFast($html)
    {
        $result = [];

        //tag with content <tag>content</tag>
        $regExpWithContent ='<(\w+)([^>]*)>(.*?)<\/\1>';
        $rexExpWithoutContent = '<(\w+)([^>]*)\/>';
        $pattern = '/'.$regExpWithContent.'|'.$rexExpWithoutContent.'/s';
        //$pattern = '/'.$regExpWithContent.'/s';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match)
        {
            $entireElement = $matches[0];
            $tagName = $match[1];
            $attributes = $match[2];
            $childNodes = $match[3];

            //second regeexp
            if (isset($match[4]) && $match[4])
            {
                $tagName = $match[4];
                $attributes = $match[5];
                $childNodes = '';
            }

            $element = new \FastDom\Element($tagName);
            $element->parseAttributes($attributes);

            if (!$childNodes)
            {
                //optimization for empty chidlnodes
            }
            // check if content starts with html or plain text
            else if (self::starWithHTML($childNodes))
            {
                $element->append(self::parseHtml($childNodes));
            }
            else //plain text
            {
                $parts = preg_split('/(<\/?\w+[^>]*>[^<]*<\/\w+>)/', $childNodes, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

                foreach ($parts as $part)
                {
                    if (self::isHTML($part))
                    {
                        $element->append(self::parseHtml($part));
                    }
                    else
                    {
                        $element->append($part);
                    }
                }
            }

            $result[] = $element;
        }

        return $result;
    }
}