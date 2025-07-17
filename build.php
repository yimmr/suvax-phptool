<?php

$pharFile = 'dist/suvax-phptool.phar';

if (!is_dir('dist')) {
    mkdir('dist');
}

if (file_exists($pharFile)) {
    unlink($pharFile);
}

$phar = new Phar($pharFile);

$phar->buildFromDirectory(__DIR__, '/^'.preg_quote(__DIR__, '/').'\/(src|vendor)\//');
$phar->setStub($phar->createDefaultStub('vendor/autoload.php'));

echo "Phar 包已生成: $pharFile\n";
