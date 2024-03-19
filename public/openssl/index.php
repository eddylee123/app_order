<?php
/**
 * 公钥加密
 *
 * @param string 明文
 * @return string 密文（base64编码）
 */
function encodeing($sourcestr)
{
    $key_content = file_get_contents('./_test_public.key');
    $pubkeyid    = openssl_get_publickey($key_content);

    if (openssl_public_encrypt($sourcestr, $crypttext, $pubkeyid))
    {
        return base64_encode("".$crypttext);
    }
}

/**
 * 私钥解密
 *
 * @param string 密文（二进制格式且base64编码）
 * @param string 密文是否来源于JS的RSA加密
 * @return string 明文
 */
function decodeing($crypttext)
{
    $key_content = file_get_contents('./_test.key');
    $prikeyid    = openssl_get_privatekey($key_content);
    $crypttext   = base64_decode($crypttext);

    if (openssl_private_decrypt($crypttext, $sourcestr, $prikeyid, OPENSSL_PKCS1_PADDING))
    {
        return "".$sourcestr;
    }
    return ;
}

echo $key = encodeing('罗源县中华失联飞机安抚拉斯加 大是的发生两地分居阿斯蒂芬');
echo "\r\n";
echo '加密字符串：'.decodeing($key);
echo "\r\n";

if(isset($_POST['password']))
{
	$txt = decodeing($_POST['password']);
	die('解密字符串：'.$txt);
}
