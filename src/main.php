<?php

use SuvaxPHPTool\Dumper\Dumper;

class SuvaxDumper extends Dumper
{
}

/**
 * @return Dumper|void 提供参数时没有返回值
 */
function dumper(...$vars)
{
    if (!$vars) {
        return Dumper::class;
    }
    Dumper::dump(...$vars);
}

/**
 * 打印变量同时写入日志.
 */
function logDumper($var, $filename = '', $fileType = 'php')
{
    Dumper::dump($var);
    Dumper::log($var, $filename, $fileType);
}
