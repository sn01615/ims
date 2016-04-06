<?php

/**
 * @desc 图片上传类
 * @author YangLong
 * @date 2015-09-19
 */
class UploadModel extends BaseModel
{

    /**
     * @desc 覆盖父方法,返回当前类的(单)实例
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-09-20
     * @return UploadModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 上传图片
     * @param string $path
     * @param int $refid
     * @param string $src
     * @author YangLong
     * @date 2015-09-19
     * @return mixed
     */
    public function uploadImage($path, $refid, $src)
    {
        if (! empty($_FILES)) {
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $fileTypes = array(
                'jpg',
                'bmp',
                'gif',
                'png',
                'tif'
            );
            
            $fileName = $_FILES["Filedata"]["name"];
            $filetype = pathinfo($fileName, PATHINFO_EXTENSION);
            if (! is_dir($path)) {
                mkdir($path);
            }
            $newPath = $path . gmdate('Y') . '/';
            file_exists($newPath) ? null : mkdir($newPath);
            $newPath .= gmdate('m') . '/';
            file_exists($newPath) ? null : mkdir($newPath);
            $newPath .= gmdate('d') . gmdate('h') . gmdate('i') . gmdate('s') . rand(10000, 99999) . "." . $filetype;
            $fileUrl = $newPath;
            
            $result = array();
            if (move_uploaded_file($tempFile, $fileUrl)) {
                
                $key = 'uploadImage';
                $_value = array();
                $_value['fileUrl'] = $fileUrl;
                $_value['refid'] = $refid;
                $_value['src'] = $src;
                $_value['fileName'] = $fileName;
                iMemQueue::getInstance()->push($key, $_value);
                
                $result['filepath'] = $fileUrl;
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
            } else {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE);
            }
        }
    }

    /**
     * @desc 上传Excel文件
     * @param string $path
     * @param int $sellerId
     * @author YangLong
     * @date 2015-11-24
     * @return mixed
     */
    public function uploadFile($path, $sellerId)
    {
        if (! empty($_FILES)) {
            $tempFile = $_FILES['file']['tmp_name'];
            $fileTypes = array(
                'xls',
                'xlsx'
            );
            
            $fileName = $_FILES["file"]["name"];
            $filetype = pathinfo($fileName, PATHINFO_EXTENSION);
            if (! is_dir($path)) {
                mkdir($path);
            }
            $newPath = $path . 'file#' . gmdate('Y') . '/';
            file_exists($newPath) ? null : mkdir($newPath);
            $newPath .= gmdate('m') . '/';
            file_exists($newPath) ? null : mkdir($newPath);
            $newPath .= gmdate('d') . gmdate('h') . gmdate('i') . gmdate('s') . rand(10000, 99999) . "." . $filetype;
            $fileUrl = $newPath;
            
            $result = array();
            if (move_uploaded_file($tempFile, $fileUrl)) {
                $result['filepath'] = $fileUrl;
                $result['filename'] = $fileName;
                $result['sellerId'] = $sellerId;
                
                $key = md5($tempFile . $fileUrl);
                iMemcache::getInstance()->set($key, $result, 3600);
                $result['filepath'] = $key;
                unset($result['sellerId']);
                
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
            } else {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE);
            }
        }
    }
}
