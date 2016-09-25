七牛jssdk组件的yii2部件
================
用于上传到七牛的小部件

安装
---

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist xyb/yii2-qiniu-upload-widget "dev-master"
```

or add

```
"xyb/yii2-qiniu-upload-widget": "dev-master"
```

to the require section of your `composer.json` file.


基本用法
----

使用如下:

js参数和七牛的jssdk参数一致,使用数组方式传递

1.在view视图输出,domain参数要填好,也就是的上传目的地址
```php
<?= \xyb\qiniu\Qiniu::widget([
    'name' => 'name',
    'id' => 'id',
    'jsOptions' => [
        'domain' => 'http://static.xxx.net/',   // 最后反斜杠不要忘了
    ]
]) ?>
```

2.加载uploadToken的action,默认路由是/site/tokenAction,eg:在siteController的actions方法添加,action的参数必须填写好七牛的accessKey,secretKey,bucker,domain
```php
public function actions()
{
    return [
        'error' => [
            'class' => 'yii\web\ErrorAction',
        ],
        'captcha' => [
            'class' => 'yii\captcha\CaptchaAction',
            'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
        ],
        'tokenAction' => [
            'class' => 'xyb\qiniu\TokenAction',
            'ak' => Yii::$app->params['qiniu']['ak'],
            'sk' => Yii::$app->params['qiniu']['sk'],
            'domain' => Yii::$app->params['qiniu']['domain'],
            'bucket' => Yii::$app->params['qiniu']['bucket']
        ],
    ];
}
```

额外用法
----
可以传递自定义的html作为上传组件的界面,定义containerId和browseButtonId只需要在节点定义,如
```<div id='{!!containerId!!}'><button id='{!!browseButtonId!!}'></button></div>```
其中,{!!containerId!!}会动态解析为基于组件传递的id的containerId,{!!browseButtonId!!}亦同理,更多请参考源码注释