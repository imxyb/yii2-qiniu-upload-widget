<?php
namespace xyb\qiniu;

use yii\base\InvalidParamException;

/** 七牛token url
 * Class TokenAction
 * @author xyb
 * @package xyb\qiniu
 */
class TokenAction extends \yii\base\Action
{
    /**
     * @var string 七牛accessKey
     */
    public $ak;
    /**
     * @var string 七牛secretKey
     */
    public $sk;

    /**
     * @var string 上传目标地址
     */
    public $domain;

    /**
     * @var string 七牛bucket
     */
    public $bucket;

    /**
     * @var array 七牛flags
     *
     */
    public $flags;

    public function run()
    {
        if(empty($this->ak)) {
            throw new InvalidParamException("参数'accessKey'缺失");
        } else if(empty($this->sk)) {
            throw new InvalidParamException("参数'secretKey'缺失");
        } else if(empty($this->bucket)) {
            throw new InvalidParamException("参数'bucket'缺失");
        } else if(empty($this->domain)) {
            throw new InvalidParamException("参数'domain'缺失");
        }

        // 默认一个小时失效
        if (!isset($this->flags['deadline'])) {
            $this->flags['deadline'] = 3600 + time();
        }

        $this->flags['scope'] = $this->bucket;

        $encodedFlags = $this->base64Encode(json_encode($this->flags));
        $sign = hash_hmac('sha1', $encodedFlags, $this->sk, true);
        $encodedSign =$this->base64Encode($sign);
        $token = $this->ak . ':' . $encodedSign . ':' . $encodedFlags;

        exit(json_encode(['uptoken' => $token]));
    }

    public function base64Encode($str)
    {
        $find = array("+", "/");
        $replace = array("-", "_");
        return str_replace($find, $replace, base64_encode($str));
    }
}