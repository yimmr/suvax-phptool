<?php

namespace SuvaxPHPTool;

class Archive
{
    protected $zip;

    protected $filename;

    protected $entrydir = '';

    public function __construct()
    {
        $this->zip = new \ZipArchive();
    }

    public function openZipFile($filename = null, $flags = \ZipArchive::CREATE)
    {
        $this->filename = empty($filename) ? time().'.zip' : $filename;

        if (true !== $this->zip->open($this->filename, $flags)) {
            throw new \Exception("Cannot open <$this->filename>\n");
        }

        return $this;
    }

    public static function create($filename = null, $flags = \ZipArchive::CREATE)
    {
        $instance = new static();
        $instance->openZipFile($filename, $flags);
        return $instance;
    }

    /**
     * 构建压缩包.
     *
     * @param  string[]|string $from     要添加到压缩包中的文件或目录，可以是字符串（单个路径）或字符串数组（多个路径）
     * @param  string|null     $entrydir 可选，压缩包内的根目录名，所有文件将放在此目录下
     * @return string          返回生成的压缩包文件名
     */
    public function build($from, $entrydir = null)
    {
        empty($entrydir) || $this->setEntrydir($entrydir);
        $this->addFiles($from);
        $this->zip->close();
        return $this->getFilename();
    }

    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string[]|string $from
     */
    public function addFiles($from)
    {
        $from = is_array($from) ? $from : [$from];
        foreach ($from as $file) {
            $this->addFrom($file);
        }
        return $this;
    }

    public function addFrom($from)
    {
        if (is_file($from)) {
            $this->zip->addFile($from, $this->getEntryname(basename($from)));
        } elseif (is_dir($from)) {
            $this->addDir($from);
        }

        return $this;
    }

    public function addDir($dir, $hasSelf = true)
    {
        $dir = realpath($dir);
        $removePath = $hasSelf ? dirname($dir) : $dir;

        // 创建递归目录迭代器，可遍历目录下的所有文件
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $this->zip->addFile($filePath, $this->getEntryname(substr($filePath, strlen($removePath) + 1)));
            }
        }

        return $this;
    }

    public function getEntryname($subentryname)
    {
        return ($this->entrydir ? $this->entrydir.'/' : '').$subentryname;
    }

    public function setEntrydir($dirname)
    {
        if (!$this->entrydir && $dirname) {
            $this->zip->addEmptyDir($this->entrydir);
            $this->entrydir = $dirname;
        }

        return $this;
    }

    public function close()
    {
        $this->zip->close();
    }

    public function getZip()
    {
        return $this->zip;
    }

    public function __call($name, $arguments)
    {
        return $this->zip->{$name}(...(array) $arguments);
    }

    public static function zipFileResponse($file)
    {
        self::fileResponse($file, 'application/zip');
    }

    public static function fileResponse($file, $contentType = 'application/zip')
    {
        header('Content-type: '.$contentType);
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-length: '.filesize($file));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($file);
        exit;
    }

    /**
     * 使用系统命令压缩文件，需开启 `exec` 函数.
     *
     * @param string|string[] $source 要压缩的文件
     * @param string          $target 文件名
     * @param string          $ext    扩展名
     */
    public static function archiveByCMD($source, $target = null, $ext = 'zip')
    {
        $target = $target ? $target : time().'.'.$ext;

        if (file_exists($target)) {
            return false;
        }

        $source = is_array($source) ? implode('" "', $source) : $source;

        switch ($ext) {
            case 'zip':
                $command = "zip -r \"$target\" \"$source\"";
                break;
            case 'tar':
                $command = "tar -cvf \"$target\" \"$source\"";
                break;
            case 'tar.gz':
                $command = "tar -czvf \"$target\" \"$source\"";
                break;
            case 'tar.bz2':
                $command = "tar -cjvf \"$target\" \"$source\"";
                break;
            default:
                return false;
        }

        exec($command);

        if (file_exists($target)) {
            return $target;
        }

        return false;
    }
}
