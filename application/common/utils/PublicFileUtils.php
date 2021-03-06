<?php
/**
 * Created by PhpStorm.
 * User: Host-0034
 * Date: 2018/7/20
 * Time: 15:36
 */

namespace app\common\utils;


/*
 * 公共文件的处理工具类
 */
class PublicFileUtils
{

    /**
     * 生成上传文件的url
     * @param $file_path
     * @return string
     */
    public static function createUploadUrl($file_path = '')
    {
        if(strpos($file_path,"http") === 0){
            return $file_path;
        }else if(strpos($file_path,"/") === 0){
            return $file_path;
        }

        $url = config('public_file.upload_base_url') . $file_path;

        return $url;
    }

    /**
     * 生成静态资源的url
     * @param $asset_path
     * @return string
     */
    public static function createStaticUrl($asset_path = '')
    {
        if (empty($asset_path))
            return $asset_path;
        $url = config('public_file.static_base_url') . $asset_path;
        return $url;
    }

}