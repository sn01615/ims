<?php

/**
 * @desc 写文件日志 
 * @author YangLong
 * @date 2015-09-09
 */
class FileLog
{

    private $path;

    private static $_instance;

    private $depth = 2;

    /**
     * @desc 获取单实例
     * @author YangLong
     * @date 2015-09-09
     * @return FileLog
     */
    public static function getInstance()
    {
        if (! (self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        $this->path = BASE_PATH . '/filelog';
    }

    /**
     * @desc 写日志文件
     * @param string $dir
     * @param string $filename
     * @param string $content
     * @author YangLong
     * @date 2015-09-09
     * @return number
     */
    public function write($dir, $filename, $content)
    {
        $hash = md5($dir . $filename);
        $_tempArray = str_split($hash);
        $_tempArray = array_slice($_tempArray, 0, $this->depth);
        $dir = $dir . '/' . implode('/', $_tempArray);
        $path = $this->checkdir($dir);
        $path = $path . '/' . substr($hash, $this->depth);
        $content = gzcompress($content);
        return file_put_contents($path, $content);
    }

    /**
     * @desc 读取日志文件
     * @param string $dir
     * @param string $filename
     * @author YangLong
     * @date 2015-09-09
     * @return string
     */
    public function read($dir, $filename)
    {
        $hash = md5($dir . $filename);
        $_tempArray = str_split($hash);
        $_tempArray = array_slice($_tempArray, 0, $this->depth);
        $path = $this->path . '/' . $dir . '/' . implode('/', $_tempArray) . '/' . substr($hash, $this->depth);
        if (! file_exists($path)) {
            return false;
        }
        return gzuncompress(file_get_contents($path));
    }

    /**
     * 删除日志文件
     * @param string $dir
     * @param string $filename
     * @author YangLong
     * @date 2016-03-22
     * @return boolean
     */
    public function delete($dir, $filename, $removedir = true)
    {
        $hash = md5($dir . $filename);
        $_tempArray = str_split($hash);
        $_tempArray = array_slice($_tempArray, 0, $this->depth);
        $path = $this->path . '/' . $dir . '/' . implode('/', $_tempArray) . '/' . substr($hash, $this->depth);
        if (file_exists($path)) {
            unlink($path);
        }
        if ($removedir) {
            for ($i = 0; $i < $this->depth; $i ++) {
                $path = substr($path, 0, strrpos($path, '/'));
                if (count(scandir($path)) === 2) {
                    rmdir($path);
                } else {
                    break;
                }
            }
        }
        return true;
    }

    /**
     * @desc 创建文件夹
     * @param string $dir
     * @author YangLong
     * @date 2015-09-09
     * @return string
     */
    private function checkdir($dir)
    {
        $fulldir = $this->path;
        $dirs = explode('/', $dir);
        foreach ($dirs as $key => $value) {
            $fulldir = $fulldir . '/' . $value;
            if (! is_dir($fulldir)) {
                mkdir($fulldir);
            }
        }
        return $fulldir;
    }
}