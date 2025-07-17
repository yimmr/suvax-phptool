<?php

require_once __DIR__.'/../vendor/autoload.php';

dumper(123);
dumper('123');
dumper(true);
dumper('嵌套数组', [1, 2, ['path' => '/tmp']]);
dumper('包含对象的数组', [1, new SuvaxDumper()]);
dumper(new SuvaxDumper());

// 自定义日志文件名
dumper()::log([1, 2, 3], 'dd');
dumper()::log([1, 2, ['path' => '/tmp']], '', 'json');
// 记录打印的HTML
dumper()::logHTML([new SuvaxDumper(), 789]);
dumper()::logHTML(new SuvaxDumper());

// 渲染已保存的日志
SuvaxDumper::index();
// 清空日志
SuvaxDumper::clean();
