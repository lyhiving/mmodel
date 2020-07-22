<?php
namespace lyhiving\mmodel;

class Mtemplate extends Mview
{
    public $app,
    $name,
    $default_dir,
    $compile_dir,
    $compile_file,
    $compile_force = false,
    $compile_check = true;

    protected $funcs = null,
    $tags = null,
    $rules = array(
        // strip comment
        '/\<\!\-\-#[^#]+?#\-\-\>/s' => '',
//        '/(\<\!\-\-)?{(.+?)}(\-\-\>)?/s' => '{$2}',
        '/\<\!\-\-\{(.*)}\-\-\>/s' => '{$1}',
        // [class::]$inst[->prop|['prop']...]
        '/{template\s+(.+?)}/i' => '<?php $this->display($1); ?>',
        '/{(switch|if|while|for)\s+(.+?)}/is' => '<?php \1 (\2) { ?>',
        '/{elseif\s+(.+?)}/is' => '<?php } elseif (\1) { ?>',
        '/{else}/i' => '<?php } else { ?>',
        '/{(default)}/' => '<?php \1: ?>',
        '/{case\s+(.+?)}/is' => '<?php case \1: ?>',
        '/{\/(?:switch|for|while|if|loop|foreach)}/i' => '<?php } ?>',
        '/{(?:loop|foreach)\s+(\S+)\s+(\S+)}/is' => '<?php if(is_array($1)) foreach($1 as $2) { ?>',
        '/{(?:loop|foreach)\s+(\S+)\s+(\S+)\s+(\S+)}/is' => '<?php if(is_array($1)) foreach($1 as $2 => $3) { ?>',
        // {[class::]CONST}
        '/{((?:\w+\:{2})?[A-Z0-9\_]+)}/s' => '<?php echo $1;?>',
        // tablename(rownum)->key
        '/{(\w+)\((.+)\)->(\w+);? *}/' => '<?php echo table(\'$1\', $2, \'$3\');?>',
        // {[class::][$ins->][$]func([arg...])}
        // {(simple expression)}
        '/{((?:(?:\w+\:{2})?(?:\$\w+\-\>)?\$?\w+ *)?\(.*?\));? *}/' => '<?php echo $1;?>',
        '/\<\?=(.+?);? *\?\>/' => '<?php echo \1; ?>',
        //简写php
        '/\{php\s+(.+)\}/' => '<?php echo \1; ?>',
    );

    public function __construct($config = null)
    {
        parent::__construct($config);

        if (!$this->default_dir) {
            $this->default_dir = dirname($this->dir) . '/default/';
        }

        $this->dir .= $this->name . '/';
        $this->set_rule('/{\/(' . $this->tags . ')}/', '<?php endforeach; unset(\$_$1); ?>');
    }

    public function set_rule($pattern, $replacement)
    {
        $this->rules[$pattern] = $replacement;
    }

    public function set_view($view, $app = null)
    {
        //判断避免重复加文件后缀
        $view = $this->viewname($view);
        if (is_null($app)) {
            $app = $this->app;
        }
        if ($view[0] == '/' || preg_match('#^[A-Z]:[\\\/]#i', $view)) {
            $this->file = $this->set_app_view($view, $app);
        } else {
            //针对手机优化 START
            $getviewer = Mrequest::get_viewer();        
            if (($getviewer && $getviewer != 'web') && (!defined('VIEWER_FIT') || VIEWER_FIT != false)) {
                if (strpos($view, '/')) {
                    $views = explode("/", $view);
                    $count = count($views);
                    $count_last = $count - 1;
                    $views[$count_last] = $getviewer . '/' . $views[$count_last];
                    $mview = implode("/", $views);
                } else {
                    $mview = $getviewer . '/' . $view;
                }
                if (file_exists($this->dir . $mview)) {
                    $view = $mview;
                }
            }
            $this->file = $this->set_app_view($view, $app);
            //针对手机优化 END
        }
        $this->compile_file = $this->compile_dir . str_replace(array('/', '\\'), ',', $this->name."/".($app ? $app.'/'.$view:$view)) . '.php';
        return $this;
    }

    public function set_app($app)
    {
        $this->app = $app;
        return $this;
    }

    public function dir_compile($dir = null)
    {
        if (is_null($dir)) {
            $dir = $this->dir;
        }

        $files = glob($dir . '*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->dir_compile($file);
            } else {
                $this->_compile(substr($file, strlen($this->dir)));
            }
        }
    }

    public function clear_compile()
    {
        $files = glob($this->compile_dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }

        }
    }

    protected function _file()
    {
        if ($this->compile_force || ($this->compile_check && (!file_exists($this->compile_file) || @filemtime($this->file) > @filemtime($this->compile_file)))) {
            $this->_compile();
        }
        return $this->compile_file;
    }

    protected function _compile($view = null)
    {
        if ($view) {
            $this->set_view($view);
        }
        $data = file_get_contents($this->file);
        if ($data === false) {
            return false;
        }

        $data = $this->_parse($data);
        if (false === $this->write_file($this->compile_file, $data)) {
            throw new ct_exception("$this->compile_file file is not writable");
        }
        @chmod($this->compile_file, 0777);
        return true;
    }

    private function _parse($string)
    {
        $string = preg_replace_callback('/{((?:\w+\:{2})?\$\w+(?: *\-\>\w+| *\[[^\]\n]+\])*);? *}/s', function ($r) {
            return self::_addquote('<?php echo ' . $r[1] . ';?>');
        }, $string);
        $string = preg_replace_callback('/{(' . $this->tags . ')(\s+[^}]+?)(\/?)}/', function ($r) {
            return self::_tag_parse($r[1], $r[2], $r[3]);
        }, $string);
        $string = preg_replace(array_keys($this->rules), $this->rules, $string);
        return preg_replace('/\s+\?\>(\s*)\<\?php/is', '', $string);
    }

    private static function _addquote($var)
    {
        return preg_replace('/\[([\w\-\.\x7f-\xff]+)\]/s', '["\1"]', stripslashes($var));
    }

    private static function _tag_parse($tag, $str, $end)
    {
        $return = 'r';
        $array = '';
        preg_match_all('/\s+([a-z_]+)\s*\=\s*([\"\'])(.*?)\2/i', stripslashes($str), $matches, PREG_SET_ORDER);
        foreach ($matches as $k => $v) {
            if ($v[1] == 'return') {
                $return = $v[3];
                continue;
            }
            $array .= ($k ? ',' : '') . "'" . $v[1] . "'" . ' => ' . $v[2] . $v[3] . $v[2];
        }
        $string = '<?php' . "\n\$_$tag = tag_$tag(array($array));\n";
        $string .= $end ? ("$$return = & \$_$tag;\n" . '?>') : ("if (isset(\$_{$tag}['total'])): extract(\$_$tag); \$_$tag = \$data;endif;\nforeach(\$_$tag as \$i=>\$$return): \$i++;\n" . '?>');
        return $string;
    }

    public function test($viewOrData, $isdata = true)
    {
        if ($isdata) {
            $data = $viewOrData;
        } else {
            $this->set_view($viewOrData);
            $data = file_get_contents($this->file);
            if ($data === false) {
                return false;
            }

        }
        return $this->_syntax_error($this->_parse($data));
    }

    /**
     * 语法检查，运行时错误不会检查，
     * 比如函数是否存在、函数调用是否正确、变量是否存在、类型是否正确、包含文件等暂时无法检查
     *
     * 修改自 {@link http://www.php.net/manual/en/function.php-check-syntax.php}
     */
    private function _syntax_error($code)
    {
        $braces = 0;
        $inString = 0;
        $parsed = token_get_all($code);
        $code = array();
        foreach ($parsed as $token) {
            if (is_array($token)) {
                switch ($token[0]) {
                    case T_INLINE_HTML:
                    case T_OPEN_TAG:
                        $code[] = preg_replace('/[^\n]*/', '', $token[1]);
                        break;
                    case T_OPEN_TAG_WITH_ECHO:
                        $code[] = preg_replace('/[^\n]*/', '', $token[1]) . ' echo ';
                        break;
                    case T_CLOSE_TAG:
                        $code[] = '; ' . preg_replace('/[^\n]*/', '', $token[1]);
                        break;
                    case T_CURLY_OPEN:
                    case T_DOLLAR_OPEN_CURLY_BRACES:
                    case T_START_HEREDOC:
                        ++$inString;
                        $code[] = $token[1];
                        break;
                    case T_END_HEREDOC:
                        --$inString;
                        $code[] = $token[1];
                        break;
                    default:
                        $code[] = $token[1];
                        break;
                }
            } else {
                $code[] = $token;
                if ($inString & 1) {
                    switch ($token) {
                        case '`':
                        case '"':
                            --$inString;
                            break;
                    }
                } else {
                    switch ($token) {
                        case '`':
                        case '"':
                            ++$inString;
                            break;
                        case '{':
                            ++$braces;
                            break;
                        case '}':
                            if ($inString) {
                                --$inString;
                            } else {
                                --$braces;
                                if ($braces < 0) {
                                    break 2;
                                }

                            }
                            break;
                    }
                }
            }
        }

        $code = implode('', $code);
        if (trim($code) === '') {
            return false;
        }

        // Display parse error messages and use output buffering to catch them
        $origErrorLogSet = @ini_set('log_errors', false);
        $origErrorHtmlSet = @ini_set('html_errors', false);
        $origErrorDisplaySet = @ini_set('display_errors', true);
        $origErrorReportSet = @error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT ^ E_WARNING);

        // If $braces is not zero, then we are sure that $code is broken.
        // We run it anyway in order to catch the error message and line number.

        // Else, if $braces are correctly balanced, then we can safely put
        // $code in a dead code sandbox to prevent its execution.
        // Note that without this sandbox, a function or class declaration inside
        // $code could throw a "Cannot redeclare" fatal error.

        if ($braces == 0) {
            $code = "if(0){{$code}\n}";
        }
        $ret = false;
        ob_start();
        if (false === eval($code)) {
            if ($braces) {
                $braces = PHP_INT_MAX;
            } else {
                // Get the maximum number of lines in $code to fix a border case
                false !== strpos($code, "\r") && $code = strtr(str_replace("\r\n", "\n", $code), "\r", "\n");
                $braces = substr_count($code, "\n");
            }

            $error = ob_get_clean();

            // Get the error message and line number
            if (preg_match("'syntax error, (.+) in .+ on line (\d+)$'s", $error, $match)) {
                $match[2] = (int) $match[2];
                $ret = $match[2] <= $braces
                ? array($match[1], $match[2])
                : array('unexpected $end' . substr($match[1], 14), $braces);
            } else {
                $ret = array('syntax error', 'unknown');
            }
        } else {
            ob_end_clean();
        }

        @ini_set('display_errors', $origErrorDisplaySet);
        @ini_set('html_errors', $origErrorHtmlSet);
        @ini_set('log_errors', $origErrorLogSet);
        @error_reporting($origErrorReportSet);

        return $ret;
    }

    public function showmessage($message, $url = null, $ms = 2000, $success = false)
    {
        $this->nocache();
        if (!$ms) {
            $ms = 2000;
        }

        if (is_array($message)) {
            $message = implode('<br />', $message);
        }

        $this->assign('message', $message);
        $this->assign('url', $url);
        $this->assign('ms', $ms);
        $this->assign('success', $success);
        $this->display('showmessage');
        exit;
    }

    public function redirect($url)
    {
        $this->nocache();
        header("location:$url");
        exit;
    }

    public function nocache()
    {
        @header('Cache-Control:no-cache,must-revalidate');
        @header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        @header('Pragma:no-cache');
    }
}
