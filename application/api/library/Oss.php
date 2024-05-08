<?php
namespace app\api\library;

use Aws\Credentials\Credentials;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use think\Exception;

/**
 * Amazon S3 Oss
 * Class Oss
 * @package app\api\library
 */
class Oss
{

    public static function initS3Client()
    {
        $accessKeyId = config('oss.ossKeyId');
        $accessKeySecret = config('oss.ossKeySecret');
        $endpoint = config('oss.endpoint');

        $credentials = new Credentials($accessKeyId, $accessKeySecret);
        $s3Client = new S3Client([
            'credentials' => $credentials,
            'version' => 'latest',
            'region' => 'us-east-1',
            'endpoint' => $endpoint,
            "use_path_style_endpoint"=>true
        ]);

        return $s3Client;
    }

    public static function upload($fileName, $filePath)
    {
        $bucket= config('oss.bucket');
        $date = date("Y-m-d");
        $file = $date."_".$fileName;

        try {
            //创建S3客户端
            $s3Client = self::initS3Client();
            //上传文件到桶
            $result = $s3Client->putObject([
                'Bucket' => $bucket,
                'Key' => $file,
                'SourceFile' => $filePath,
//                'ContentType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

            //打印上传结果
            if ($result['@metadata']['statusCode'] != 200) {
                throw new Exception('上传失败');
            }

            // 获取文件
//            $fileOss = $result['ObjectURL'];
            unlink($filePath);
        } catch(S3Exception $e) {
            app_exception($e->getMessage());
            return [];
        }
        return ['FILE_SRC' => $file];
    }

    public static function getObject($file, $expires = '+10 minutes')
    {
        $bucket= config('oss.bucket');

        try {
            //创建S3客户端
            $s3Client = self::initS3Client();
            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key' => $file, //地址
                'ResponseContentDisposition' => 'attachment; filename='.$file,//访问链接直接下载
                ]);
            $request = $s3Client->createPresignedRequest($cmd, $expires);
            //创建预签名 URL
            return (string)$request->getUri();
        } catch(S3Exception $e) {
            app_exception($e->getMessage());
            return '';
        }
    }
}