<?php

use SuvaxPHPTool\Archive;

require_once __DIR__.'/../vendor/autoload.php';

// 简单添加一个目录后关闭
Archive::create()->addDir(__DIR__.'/../vendor')->close();

// 可选择压缩的文件并放到压缩包的site目录下
$file = Archive::create()->build([
    __DIR__.'/../vendor',
    __DIR__.'/../composer.json',
], 'site');

// 响应前端的下载请求
Archive::zipFileResponse($file);

// 可选使用系统命令压缩文件
// Archive::archiveByCMD('examples', null, 'tar');
