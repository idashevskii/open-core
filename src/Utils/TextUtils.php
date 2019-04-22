<?php

namespace OpenCore\Utils;

class TextUtils {

    // cyrillic alphabet
    private static $alphabetCyr = [
        'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й',
        'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф',
        'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
        'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й',
        'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф',
        'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'
    ];
    // cyrillic to lating alphabet transliteration
    private static $alphabetLat = [
        'A', 'B', 'V', 'G', 'D', 'E', 'IO', 'ZH', 'Z', 'I', 'I',
        'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F',
        'H', 'C', 'CH', 'SH', 'SH', '`', 'Y', '\'', 'E', 'IU', 'IA',
        'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'i',
        'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f',
        'h', 'c', 'ch', 'sh', 'sh', '`', 'y', '\'', 'e', 'iu', 'ia'
    ];

    public static function generateCode(int $length = null) {
        return substr(md5(uniqid(mt_rand(), true)), 0, $length ? $length : 8);
    }

    public static function toLowerCase(string $str = null) {
        return mb_strtolower($str, 'UTF-8');
    }

    public static function fixInvalidChars($content) {

        // reject overly long 2 byte sequences, as well as characters above U+10000
        $content = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]' .
                '|[\x00-\x7F][\x80-\xBF]+' .
                '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*' .
                '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})' .
                '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S', '', $content);

        // reject overly long 3 byte sequences and UTF-16 surrogates
        $content = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]' .
                '|\xED[\xA0-\xBF][\x80-\xBF]/S', '', $content);

//        if(!preg_match('//u', $content)){ // check for invalid utf-8
//            $content=iconv("UTF-8", "UTF-8//IGNORE", $content);
//        }
        return $content;
    }

//    public static function clearHtml($html, array $allowedTags=[], array $allowedAttrs=[]){
//        $dom=new DOMDocument();
////        libxml_use_internal_errors(true);
    /*        $dom->loadXML('<?xml version="1.0" encoding="utf-8"?><!DOCTYPE root [<!ENTITY nbsp "&#160;">]>'.'<root>'.$html.'</root>'); */
//        libxml_clear_errors();
//        $rootElement=$dom->documentElement;
//        $tagSet=array_flip($allowedTags);
//        $attrsSet=array_flip($allowedAttrs);
//        $recursiveFn=function(DOMElement $element)use($tagSet, $attrsSet, &$recursiveFn){
//            // strip attrs
//            foreach(Collections::toList($element->attributes) as $attr){
//                /* @var $attr DOMAttr */
//                if(!isset($attrsSet[$attr->nodeName])){
//                    $element->removeAttribute($attr->nodeName);
//                }
//            }
//            // strip tags
//            foreach(Collections::toList($element->childNodes) as $child){
//                /* @var $child DOMNode */
//                if($child->nodeType===XML_ELEMENT_NODE && !isset($tagSet[$child->nodeName])){
//                    foreach(Collections::toList($child->childNodes) as $childOfChild){
//                        /* @var $childOfChild DOMNode */
//                        $element->insertBefore($childOfChild, $child);
//                    }
//                    $element->removeChild($child);
//                }
//            }
//            // walk recursive
//            foreach($element->childNodes as $child){
//                /* @var $child DOMNode */
//                if($child->nodeType===XML_ELEMENT_NODE){
//                    $recursiveFn($child);
//                }
//            }
//        };
//        $recursiveFn($rootElement);
//        $ret='';
//        foreach($rootElement->childNodes as $mainChild){
//            $ret.=$dom->saveXML($mainChild);
//        }
//        return $ret;
//    }

    public static function escapeText($text, $maxLength = null) {
        return strip_tags(self::baseEscape($text, $maxLength));
    }

    public static function stripAttributes($html, $except) {
        return preg_replace("/<([a-zA-Z][a-zA-Z0-9]*)(?:[^>]*(\s(?:" . $except . ")=['\"][^'\"]*['\"]))?[^>]*?(\/?)>/u", '<$1$2$3>', $html);
    }

    public static function escapePostBody($content, $maxLength = null) {
        $allowedTags = '<p><strong><em><b><i><u><img><a><ul><ol><li>';
        $allowedAttributes = 'src|href';
        return self::stripAttributes(strip_tags(self::baseEscape($content, $maxLength ? $maxLength : 10000000), $allowedTags), $allowedAttributes);
    }

    public static function baseEscape($str, $maxLength = null) {
        return trim(self::fixInvalidChars(mb_substr($str, 0, $maxLength ? $maxLength : 2000)));
    }

    public static function translitCyrToLat($text) {
        return str_replace(self::$alphabetCyr, self::$alphabetLat, $text);
    }

    public static function translitLatToCyr($text) {
        return str_replace(self::$alphabetLat, self::$alphabetCyr, $text);
    }

    public static function toCamelCase(string $str) {
        return lcfirst(self::toPascalCase($str));
    }

    public static function toPascalCase(string $str) {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], [' ', ' '], $str)));
    }

    public static function makeKeywordsFromLabels(array $labels) {
        $res = [];
        foreach ($labels as $label) {
            if ($label) {
                $res[] = $label; // including original label
                $res[] = self::translitCyrToLat($label);
                $res[] = self::translitLatToCyr($label);
            }
        }
        return mb_strtolower(implode(', ', $res));
    }

    public static function splitBySentences(string $str) {
        return preg_split('/(?<=[.?!])\s+(?=[a-zа-яё])/iu', $str);
    }

    public static function splitByParagraphs(string $str) {
        return preg_split('/[\\n\\r]+/u', $str);
    }

}
