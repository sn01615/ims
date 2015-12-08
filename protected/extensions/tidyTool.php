<?php

/**
 * @desc tidy
 * @author YangLong
 * @date 2015-07-28
 */
class tidyTool
{

    /**
     * @desc 返回清理后的HTML
     * @param string $html
     * @author YangLong
     * @date 2015-07-28
     * @return tidyNode
     */
    static public function cleanRepair($html)
    {
        $tidyConfig = array(
            'indent' => false,
            'output-xhtml' => true,
            'wrap' => 0
        );
        $tidy = new tidy();
        $tidy->parseString($html, $tidyConfig, 'utf8');
        $tidy->cleanRepair();
        return $tidy->html();
    }
    
    /**
     * @desc 返回html的body
     * @param string $html
     * @author YangLong
     * @date 2015-07-28
     * @return string
     */
    static public function getBody($html)
    {
        $html = substr($html, stripos($html, '<body>') + 6);
        $html = substr($html, 0, stripos($html, '</body>'));
        return $html;
    }
    
}