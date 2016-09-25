<?php

namespace xyb\qiniu;
use yii\base\InvalidParamException;
use yii\bootstrap\InputWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * 封装七牛sdk组件
 */
class Qiniu extends InputWidget
{
    /**
     * @var array js参数,和七牛jssdk一致
     */
    public $jsOptions;

    /** 
     * @var string token url
     */
    public $uploadTokenAction = '/site/tokenAction';

    public $containerId;
    public $browseButtonId;
    public $dropElementId;

    public $html;

    public function init()
    {
        parent::init();

        $this->id = $this->options['id'];
        $this->value = $this->value ? $this->value : ($this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : '') ;
    }

    public function run()
    {
        $this->setDefaultJsOption();
        $this->registerHtml();
        $this->registerCss();
        $this->registerJs();

        return $this->html;
    }

    public function registerCss()
    {
        $css = <<<CSS
            .imageUploader-queue li {
                display: inline-block;
                border: solid 1px #e0e0e0;
                margin: 0 10px 10px 0;
                width: 100px;
                height: 100px;
                position: relative;
                padding: 0;
                border-radius: 4px;
                -webkit-border-radius: 4px;
                -moz-border-radius: 4px;
                background-color: #fff;
                -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
                -moz-box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
                box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
            }
            .imageUploader-queue li img{ width:100px;height:100px}
CSS;
        $this->view->registerCss($css);
    }

    public function registerJs()
    {
        Asset::register($this->view);

        $id = $var = str_replace('-', '', $this->options['id']);

        $core = <<<JS
        var uploader_{$var} = Qiniu.uploader({
            hiden_id:'{$id}',
            multi_selection:{$this->jsOptions['multi_selection']},
            unique_names:true,
            runtimes: 'html5,flash,html4',
            browse_button: '{$this->browseButtonId}',
            container: '{$this->containerId}',
            drop_element: '{$this->dropElementId}',
            max_file_size: '{$this->jsOptions['max_file_size']}',
            dragdrop: false,
            chunk_size: '4mb',
            uptoken_url: "{$this->jsOptions['uptoken_url']}",
            domain: '{$this->jsOptions['domain']}',
            filters: {
                max_file_size: '{$this->jsOptions['filters']['max_file_size']}',
                prevent_duplicates: '{$this->jsOptions['filters']['prevent_duplicates']}',
                mime_types: 
                [
                    {
                        title: '{$this->jsOptions['filters']['mime_types']['title']}',
                        extensions: '{$this->jsOptions['filters']['mime_types']['extensions']}'
                    }
                ],
            },
            auto_start: '{$this->jsOptions['auto_start']}',
            init:  {
                'FilesAdded': {$this->jsOptions['init']['FilesAdded']},
                'BeforeUpload': {$this->jsOptions['init']['BeforeUpload']},
                'UploadProgress': {$this->jsOptions['init']['UploadProgress']},
                'FileUploaded': {$this->jsOptions['init']['FileUploaded']},
                'Error': {$this->jsOptions['init']['Error']},
                'UploadComplete': {$this->jsOptions['init']['UploadComplete']}
            }
        });
JS;

        $this->view->registerJs($core);
    }

    public function registerHtml()
    {
        if(empty($this->html)) {
            $this->html = <<<HTML
            <div id="{!!containerId!!}">
                <a class="btn btn-default btn-sm " id="{!!browseButtonId!!}" href="#" >
                    <i class="glyphicon glyphicon-plus"></i>
                    <sapn>上传文件</sapn>
                </a>
                <input type="hidden" id="{$this->id}" name="{$this->name}" value="{$this->value}" />
            </div>
            <table id="J_pickTable{$this->id}" class="table table-striped table-hover text-left" style="margin-top:5px;display:none">
                <thead>
                  <tr>
                    <th class="col-md-4">文件名</th>
                    <th class="col-md-2">大小</th>
                    <th class="col-md-6">详情</th>
                  </tr>
                </thead>
                <tbody id="fsUploadProgress{$this->id}"></tbody>
            </table>
HTML;
        }
        $this->parseHtml();
    }

    public function setDefaultJsOption()
    {
        if(empty($this->options['id'])) {
            throw new InvalidParamException("参数'id'缺失");
        }

        $customJsOption = [];
        if(!empty($this->jsOptions)) {
            $customJsOption = $this->jsOptions;
        }

        $this->jsOptions = [
            'multi_selection' => new JsExpression('false'),
            'max_file_size' => '2mb',
            'domain' => '',
            'uptoken_url' => Url::toRoute(['/site/tokenAction']),
            'dragdrop' => new JsExpression('false'),
            'chunk_size' => '4mb',
            'filters' => [
                'mime_types' => [
                    'title' => 'Image files',
                    'extensions' => 'jpg,gif,png',
                ],
                'max_file_size' => '100mb',
                'prevent_duplicates' => new JsExpression('true')
            ],
            'auto_start' => new JsExpression('true'),
            'init' => [
                'FilesAdded' => new JsExpression("
                    function(up, files) {
                        $('#J_pickTable{$this->id}').show();
                        plupload.each(files, function(file) {
                            var progress = new FileProgress(file, 'fsUploadProgress{$this->id}');
                            progress.setStatus(\"等待...\");
                        });
                    }
                "),
                'BeforeUpload' => new JsExpression("
                    function(up, file) {
                        var progress = new FileProgress(file, 'fsUploadProgress{$this->id}',up);
                        var chunk_size = plupload.parseSize(this.getOption('chunk_size'));
                        if (up.runtime === 'html5' && chunk_size) {
                            progress.setChunkProgess(chunk_size);
                        }
                    }
                "),
                'UploadProgress' => new JsExpression("
                    function(up, file) {
                        var progress = new FileProgress(file, 'fsUploadProgress{$this->id}',up);
                        var chunk_size = plupload.parseSize(this.getOption('chunk_size'));
                        progress.setProgress(file.percent + \"%\", up.total.bytesPerSec, chunk_size);
                    }
                "),
                "FileUploaded" => new JsExpression("
                    function(up, file, info) {
                        var progress = new FileProgress(file, 'J_pickC{$this->id}');
                        progress.setComplete(up, info);
                        console.log(info);
                    }
                "),
                'Error' => new JsExpression("function(up, err, errTip) {
                    //上传出错时,处理相关的事情
                    $('#J_pickTable{$this->id}').show();
                    var progress = new FileProgress(err.file, 'fsUploadProgress{$this->id}',up);
                    progress.setError();
                    progress.setStatus(errTip);
                }"),
                'UploadComplete' => new JsExpression("
                    function() {}   
                ")
            ]
        ];

        $this->jsOptions = ArrayHelper::merge($this->jsOptions, $customJsOption);
    }

    public function parseHtml()
    {
        preg_match_all('/\{\!\!(.*?)\!\!\}/', $this->html, $match);
        if(!empty($match[1])) {
            foreach ($match[1] as $key => $ele) {
                if(property_exists($this, $ele)) {
                    $replace = $ele . '_' . $this->id;
                    $this->{$ele} = $replace;
                    $this->html = str_replace('{!!'.$ele.'!!}', $replace, $this->html);
                }
            }
        }
    }
}
