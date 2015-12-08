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
    static public function IsXML($str)
    {
        // 移除非法字符
        $str = imsTool::removeNonPrintable($str, 'IsXML');
        
        $xml_parser = xml_parser_create();
        if (! xml_parse($xml_parser, $str, true)) {
            xml_parser_free($xml_parser);
            return false;
        } else {
            return true;
        }
    }
    
}
