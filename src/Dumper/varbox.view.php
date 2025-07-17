<?php

use SuvaxPHPTool\Dumper\Dumper;

if (!Dumper::$boxcss) {?>
<style>
    .imdumper-box {
        background-color: #f9f9f9;
        border-left: 3px solid #5ea3aa;
        padding: 5px 10px;
        color: #666c70;
    }

    .imdumper-box+.imdumper-box {
        border-top: 1px solid #eee;
    }

    .imdumper-hide {
        display: none;
    }

    .imdumper-box ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .imdumper-ref>ul {
        display: none;
        margin-left: 22px;
    }

    .imdumper-note {
        color: #ff5e00;
        cursor: pointer;
    }

    .imdumper-key {
        color: #21c773;
    }

    .imdumper-mod {
        color: #c51777;
    }

    .imdumper-method {
        color: #1590cb;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.imdumper-ref').forEach(function(el) {
            el.querySelector('.imdumper-note').addEventListener('click', function(e) {
                var ul = el.querySelector('ul')
                ul.style.display = ul.style.display == 'block' ? 'none' : 'block'
                e.stopPropagation()
            }, false)
        })
    })
</script>
<?php Dumper::$boxcss = true; ?>
<?php }?>
<?php if (empty($data)) {
    return;
}?>
<div class="imdumper-box <?php echo $class; ?>">
    <?php Dumper::varHTML($var); ?>
</div>