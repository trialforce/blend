<?php

namespace FastDom;

class Parser
{
    const HTML_REGEX_PATTERN = '/<([a-zA-Z]+)(?:\s([a-zA-Z]+(?:=(?:".+")|(?:[0-9]+))))*(?:(?:\s\/>)|(?:>(.*)<\/\1>))/';

    public static function isHtml($text)
    {
        return preg_match(self::HTML_REGEX_PATTERN, $text);
    }

    public static function hasHtmlTag($text)
    {
        return preg_match_all(self::HTML_REGEX_PATTERN, $text);
    }

    public static function parseHtml($html)
    {
        $tags = array();
        $tempHtml = $html;

        while (strlen($tempHtml) > 0)
        {
            // Check if the string includes a html tag
            if (preg_match_all(self::HTML_REGEX_PATTERN, $tempHtml, $matches))
            {
                $tagOffset = strpos($tempHtml, $matches[0][0]);
                // Check if the string starts with the html tag
                if ($tagOffset > 0)
                {
                    // Push the text infront of the html tag to the result array
                    array_push($tags, array(
                        'text' => substr($tempHtml, 0, $tagOffset)
                    ));
                    // Remove the text from the string
                    $tempHtml = substr($tempHtml, $tagOffset);
                }

                // Extract the attribute data from the html tag
                $explodedAttributes = strlen($matches[2][0]) > 0 ? explode(' ', $matches[2][0]) : array();
                $attributes = array();

                // Store each attribute with its name in the $attributes array
                for ($i = 0; $i < count($explodedAttributes); $i++)
                {
                    $attribute = trim($explodedAttributes[$i]);
                    // Check if the attribute has a value (like style="") or has no value (like required)
                    if (strpos($attribute, '=') !== false)
                    {
                        $splitAttribute = explode('=', $attribute);
                        $attrName = trim($splitAttribute[0]);
                        $attrValue = trim(str_replace('"', '', $splitAttribute[1]));

                        // Store the value directly in the $attributes array if this is not the style attribute
                        $attributes[$attrName] = $attrValue;
                    }
                    else
                    {
                        $attributes[trim($attribute)] = true;
                    }
                }

                $tagName = $matches[1][0];
                $innerText = strip_tags($matches[3][0]);
                $childNodes = self::hasHtmlTag($matches[3][0]) ? self::parseHtml($matches[3][0]) : null;

                $element  =array(
                    'name' => $matches[1][0],
                    'attributes' => $attributes,
                    'innerText' => $innerText,
                    'children' => $childNodes
                );

                $element = new \FastDom\Element($tagName);
                $element->attributes = $attributes;

                //$element->parseAttributes($attributes);
                $element->append($childNodes);
                $element->append($innerText);
                // Push the html tag data to the result array
                array_push($tags, $element);
                // Remove the processed html tag from the html string
                $tempHtml = substr($tempHtml, strlen($matches[0][0]));
            }
            else
            {
                array_push($tags, array(
                    'text' => $tempHtml
                ));
                $tempHtml = '';
            }
        }
        return $tags;
    }

}