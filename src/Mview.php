<?php
namespace lyhiving\mmodel;

use lyhiving\mmodel\Mfolder;

class Mview extends Mobject
{
    public $dir,
    $file,
    $ext = '.php';

    protected $_vars;

    public function __construct(array $config = null)
    {
        if (is_array($config)) {
            foreach ($config as $key => $value) {
                $this->{$key} = $value;
            }
        }
        $this->clean_vars();
    }

    public function set_view($view, $app = null)
    {
        $view = $this->viewname($view);
        if (is_null($app)) {
            $app = $this->app;
        }
        $this->file = $this->set_app_view($view, $app);
        return $this;
    }

    public function set_app_view($view, $app = null)
    {
        $this->file = file_exists($this->dir . $view) ? $this->dir . $view : $this->default_dir . $view;

        if (is_null($app)) {
            $file = $this->dir . $view;
            $file_default = $this->default_dir . $view;
        } else {
            $file = dirname($this->dir . $view) . '/' . $app . '/' . basename($this->dir . $view);
            $file_default = dirname($this->default_dir . $view) . '/' . $app . '/' . basename($this->default_dir . $view);
        }
        return is_file($file) ? $file : $file_default;
    }

    public function set_dir($dir)
    {
        $this->dir = $dir;
        return $this;
    }

    public function assign($key, $data = null)
    {
        if (is_array($key)) {
            $this->_vars = array_merge($this->_vars, $key);
        } elseif (is_object($key)) {
            $this->_vars = array_merge($this->_vars, (array) $key);
        } else {
            $this->_vars[$key] = $data;
        }
        return $this;
    }

    public function clean_vars()
    {
        $this->_vars = array();
        return $this;
    }

    public function display($view, $app = null)
    {
        $view = $this->viewname($view);
        echo $this->fetch($view, $app);
    }

    public function viewname($view)
    {
        $view = str_replace(array(':', '*', '?', '"', '<', '>', '|'), '-', $view);
        if (strpos($view, '.') === false || ($this->ext && strpos($view, $this->ext) === false)) {
            $view .= $this->ext;
        }
        return $view;
    }

    public function touch($view, $app = null)
    {
        $view = $this->viewname($view);
        $this->set_view($view, $app);
        return file_exists($this->file);

    }

    public function fetch($view, $app = null)
    {
        $this->set_view($view, $app);
        $this->_before_render($view);
        if ($_REQUEST) {
            extract($_REQUEST);
        }

        if ($this->_vars) {
            extract($this->_vars);
        }

        ob_start();

        try {
            include $this->_file();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
        $output = ob_get_clean();
        $this->_after_render($output);
        return $output;
    }

    protected function _before_render($view)
    {
    }

    protected function _after_render(&$output)
    {
    }

    protected function _file()
    {
        return $this->file;
    }

    /**
     * 写入文件
     *
     * @param string $file 文件名
     * @param string $data 文件内容
     * @param boolean $append 是否追加写入
     * @return int
     */
    public function write_file($file, $data, $append = false)
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            Mfolder::create($dir);
        }

        $result = false;
        $fp = @fopen($file, $append ? 'ab' : 'wb');
        if ($fp && @flock($fp, LOCK_EX)) {
            $result = @fwrite($fp, $data);
            @flock($fp, LOCK_UN);
            @fclose($fp);
            @chmod($file, 0777);
        }

        return $result;
    }
}
