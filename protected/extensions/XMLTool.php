<?php

/**
 * @desc 判断是否为合法的XML
 * @author YangLong
 * @date 215-07-20
 */
class XMLTool
{

    /**
     * @desc 判断是否为合法的XML
     * @param string $str            
     * @author YangLong
     * @date 215-07-20
     * @return boolean
     */
    public static function IsXML($str)
    {
        // 移除非法字符
        $str = imsTool::removeNonPrintable($str, 'IsXML');
        
        $xml_parser = xml_parser_create();
        $start = 0;
        $blocksize = 1024 * 1024;
        while (true) {
            $_str = substr($str, $start, $blocksize);
            $start += $blocksize;
            if ($_str !== false) {
                xml_parse($xml_parser, $_str);
            } else {
                if (! xml_parse($xml_parser, $_str, true)) {
                    xml_parser_free($xml_parser);
                    return false;
                }
                xml_parser_free($xml_parser);
                return true;
            }
        }
    }
}
