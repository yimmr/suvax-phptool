<?php

namespace SuvaxPHPTool\Dumper;

class Dumper
{
    public static $boxcss = false;
    private static $show = true;
    private static $logDir;
    private static $view = __DIR__.'/varbox.view.php';

    /**
     * 自定义配置.
     *
     * @param array $config 配置项数组
     *                      - logDir 日志保存的目录，要记录信息须指定此项
     *                      - view 自定义打印变量的视图文件，必须是存在的文件
     */
    public static function config(array $config)
    {
        static::$logDir = $config['logDir'] ?? static::$logDir;
        if (!empty($config['view']) && is_file($config['view'])) {
            static::$view = $config['view'];
        }
    }

    public static function logPath($path = '')
    {
        if (!static::$logDir) {
            static::$logDir = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'suvax-dumper';
            file_exists(static::$logDir) || mkdir(static::$logDir, 0777);
        } elseif (!file_exists(static::$logDir)) {
            mkdir(static::$logDir, 0755, true);
        }

        return static::$logDir.($path ? \DIRECTORY_SEPARATOR.$path : '');
    }

    /** 全局显示输出 */
    public static function show()
    {
        static::$show = true;
    }

    /** 全局隐藏输出 */
    public static function hide()
    {
        static::$show = false;
    }

    public static function dump(...$vars)
    {
        foreach ($vars as $var) {
            static::view('varbox', [
                'class' => static::$show ? 'imdumper-display' : 'imdumper-hide',
                'var'   => $var,
            ]);
        }
    }

    /**
     * 清空日志目录.
     */
    public static function clean()
    {
        static::emptyDir(static::logPath());
    }

    protected static function emptyDir($dir)
    {
        $files = glob($dir.'/*');
        foreach ($files as $file) {
            is_dir($file) ? static::emptyDir($file) : unlink($file);
            if (is_dir($file)) {
                rmdir($file);
            }
        }
    }

    public static function logHTML($var, $filename = '')
    {
        static::log($var, $filename, 'html');
    }

    /**
     * @param string $filename 未指定时自动生成
     * @param string $fileType php和json可格式化保存
     */
    public static function log($var, $filename = '', $fileType = 'php')
    {
        $ext = '.'.$fileType;

        date_default_timezone_set('Asia/ShangHai');

        if (!$filename) {
            $filename = date('Y_m_d-h_i_s', time());
            while (@file_exists(static::logPath($filename.$ext))) {
                $arrOfName = explode('-', $filename);
                $lastidx = count($arrOfName) - 1;
                if (is_numeric($arrOfName[$lastidx])) {
                    ++$arrOfName[$lastidx];
                } else {
                    $arrOfName[] = 1;
                }
                $filename = implode('-', $arrOfName);
            }
        }

        $file = static::logPath($filename.$ext);

        if ('php' === $fileType) {
            file_put_contents($file, "<?php\r\n\r\nreturn ".var_export($var, true).';');
        } elseif ('json' === $fileType) {
            file_put_contents($file, json_encode($var, JSON_PRETTY_PRINT));
        } else {
            ob_start();
            $boxcss = static::$boxcss;
            $show = static::$show;
            static::$boxcss = true;
            static::$show = true;
            static::dump($var);
            static::$boxcss = $boxcss;
            static::$show = $show;
            file_put_contents($file, ob_get_clean());
        }
    }

    protected static function view($name, $data = [])
    {
        if (is_array($data)) {
            extract($data);
        }
        include static::$view;
    }

    /**
     * 输出美化后的HTML.
     */
    public static function varHTML($var, $before = '')
    {
        if (is_array($var)) {
            static::arrayHTML($var, $before);
        } elseif (is_object($var)) {
            static::objectHTML($var, $before);
        } else {
            echo $before.htmlspecialchars(var_export($var, true));
        }
    }

    public static function arrayHTML($array, $before = '')
    {
        echo '<div class="imdumper-ref">';
        echo $before.'<span class="imdumper-note">array:'.count($array).' </span>';
        echo '[';
        echo '<ul>';
        foreach ($array as $key => $value) {
            echo '<li>';
            $before = '<span class="imdumper-key">'.var_export($key, true).'</span> =&gt; ';
            static::varHTML($value, $before);
            echo '</li>';
        }
        echo '</ul>';
        echo ']';
        echo '</div>';
    }

    public static function objectHTML($object, $before = '')
    {
        if (!($object instanceof RefObjectWrapper)) {
            $object = static::buildRefOBJ($object);
        }

        echo '<div class="imdumper-ref">';
        echo "{$before}<span class=\"imdumper-note\">{$object->name}</span> #{$object->id} ";
        echo '{';
        echo '<ul>';

        foreach (array_merge($object->getProperties(), $object->getMethods()) as $value) {
            $before = sprintf('<span class="imdumper-mod">%s </span>', implode(' ', \Reflection::getModifierNames($value->getModifiers())));

            echo '<li>';
            if (method_exists($value, 'getValue')) {
                $before .= '<span class="imdumper-key">'.$value->getName().'</span> : ';

                try {
                    $val = $value->getValue($object->origin);
                    static::varHTML($val, $before);
                } catch (\Throwable $th) {
                    echo $before.'--';
                }
            } else {
                echo $before.'<span class="imdumper-method">'.$value->getName().'()</span>';
            }
            echo '</li>';
        }

        echo '</ul>';
        echo '}';
        echo '</div>';
    }

    public static function buildRefOBJ($obj)
    {
        ob_start();
        var_dump($obj);
        $id = preg_replace('/.*#(\d+).*/s', '$1', ob_get_clean());
        return new RefObjectWrapper($obj, $id);
    }

    public static function parseVar($var, $dep = 3, $start = 1)
    {
        $type = gettype($var);

        if ('array' === $type) {
            if ($start <= $dep) {
                foreach ($var as $i => $val) {
                    $var[$i] = static::parseVar($val, $dep, $start + 1);
                }
            }
        } elseif ('object' === $type) {
            $var = static::buildRefOBJ($var);
        }

        return 1 == $start ? ['type' => $type, 'value' => $var] : $var;
    }

    /**
     * 一个简单单页，显示所有日志等内容.
     */
    public static function index()
    {
        header('content-type: text/html; charset=utf-8');

        $logDir = static::logPath();

        echo '<style>body{margin:0;background:#f8f8f8}section{background:#fff;margin:1rem 10%;padding:0 1rem 1rem}h3{margin:0 0 1rem;padding:1rem 0;border-bottom:1px solid #eee}</style>';

        static::view('varbox');

        if (!is_dir($logDir)) {
            echo '<section>';
            echo '<h3>暂无日志文件</h3>';
            echo '</section>';
            return;
        }

        $dir = opendir($logDir);
        while (false !== ($filename = readdir($dir))) {
            if ('.' == $filename || '..' == $filename) {
                continue;
            }

            $ext = pathinfo($filename)['extension'];
            $file = static::logPath($filename);

            echo '<section>';
            echo "<h3>$filename <span style=\"font-size:12px;color:gray;font-weight:300;\">$file</span></h3>";
            if ('php' == $ext) {
                try {
                    $data = include $file;
                    static::view('varbox', ['class' => '', 'var' => $data]);
                } catch (\Throwable $th) {
                    echo '<pre>';
                    echo rtrim(ltrim(file_get_contents($file), "<?php\r\nreturn"), ";\?\>");
                    echo '</pre>';
                }
            } else {
                echo file_get_contents($file);
            }
            echo '</section>';
        }
    }
}
