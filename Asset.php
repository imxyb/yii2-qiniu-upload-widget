<?php
namespace xyb\qiniu;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $js = [
        'plupload.full.min.js',
        'qiniu.js',
        'ui.js',
        'i18n/zh_CN.js',
    ];

    public function init()
    {
        parent::init(); 
        $this->sourcePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
    }
}