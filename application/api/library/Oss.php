<?php
namespace app\api\library;

use OSS\Core\OssException;
use OSS\OssClient;

class Oss
{
    public static function upload($fileName, $filePath)
    {
        $accessKeyId = config('oss.ossKeyId');
        $accessKeySecret = config('oss.ossKeySecret');
        $endpoint = config('oss.endpoint');
        $bucket= config('oss.bucket');

        $date = date("Y-m-d");
        $file = "export/room/".$date."_".$fileName;

        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->uploadFile($bucket, $file, $filePath);
            unlink($filePath);
        } catch(OssException $e) {
            app_exception($e->getMessage());
            return [];
        }
        return ['FILE_SRC' => config('oss.host')."/".$file];
    }
}