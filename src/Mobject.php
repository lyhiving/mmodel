<?php
namespace lyhiving\mmodel;

abstract class Mobject
{
    protected $errno, $error;

    public function __construct(array $arguments = array())
    {
        if (!empty($arguments)) {
            foreach ($arguments as $property => $argument) {
                $this->{$property} = $argument;
            }
        }
    }

    public function __call($method, $arguments)
    {
        $arguments = array_merge(array("stdObject" => $this), $arguments); // Note: method argument 0 will always referred to the main class ($this).
        if (isset($this->{$method}) && is_callable($this->{$method})) {
            return call_user_func_array($this->{$method}, $arguments);
        } else {
            throw new Exception("Fatal error: Call to undefined method stdObject::{$method}()");
        }
    }

    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __isset($name)
    {
        return isset($this->$name);
    }

    public function __unset($name)
    {
        unset($this->$name);
    }

    public function __toString()
    {
        return get_class($this);
    }

    public function errno()
    {
        return $this->errno;
    }

    public function error()
    {
        return $this->error;
    }

    /**
     * 仅执行第一次匹配替换
     * @param string $search 查找的字符串
     * @param string $replace 执行替换的字符串
     * @param string $subject 原字符串
     * @return string
     */
    public function str_replace_once($search, $replace, $subject)
    {
        $pos = strpos($subject, $search);
        if ($pos === false) {
            return $subject;
        }
        return substr_replace($subject, $replace, $pos, strlen($search));
    }

    /**
     * @param array $data 原始数组
     * @param array $keys 过滤key
     * @param bool $ismulti 是否是多维数组
     * @return mixed
     */
    public function cleanout($data, $keys, $ismulti = false)
    {
        if (!$data || !is_array($data)) {
            return $data;
        }

        if (!$ismulti) {
            if (is_string($keys)) {
                $result = $data[$keys];
            } else {
                foreach ($keys as $i => $k) {
                    if (strpos($k, ':')) {
                        $ks = explode(":", $k);
                        if ($ismulti === 0) { //此处为调转kv
                            $_ks = $ks;
                            $ks[0] = $_ks[1];
                            $ks[1] = $_ks[0];
                        }
                        if (is_numeric($ks[1]) || $ks[1] == 'null' || $ks[1] == 'array' || $ks[1] == 'object' || $ks[1] == 'index' || $ks[1] == 'string') {
                            if (is_numeric($ks[1])) {
                                $data[$ks[0]] = intval($ks[1]);
                            }

                            if ($ks[1] == 'null') {
                                $data[$ks[0]] = null;
                            }

                            if ($ks[1] == 'array') {
                                $data[$ks[0]] = array();
                            }

                            if ($ks[1] == 'object') {
                                $data[$ks[0]] = new object();
                            }

                            if ($ks[1] == 'index') {
                                $data[$ks[0]] = $i;
                            }
                            if ($ks[1] == 'string') {
                                $data[$ks[0]] = $i . "";
                            }

                        } else {
                            if ($ks[1] == '@thumb') {
                                $result[$ks[0]] = function_exists('mmodel_thumb') ? mmodel_thumb($data[$ks[0]]) : $data[$ks[0]];
                            } else {
                                $result[$ks[1]] = $data[$ks[0]];
                            }
                        }
                    } else {
                        $result[$k] = $data[$k];
                    }
                }
            }
        } else {
            if (is_string($keys)) {
                $_key = $keys[0] == '@' ? $this->str_replace_once('@', '', $keys) : ''; //@key, @key:valuekey
                foreach ($data as $k => $v) {
                    if ($_key) {
                        if(strpos($_key,':')){
                            $_keys = explode(":", $_key);
                            $result[$v[$_keys[0]]] = $v[$_keys[1]];
                        }else{
                            $result[$v[$_key]] = $v;
                        }
                    } else {
                        $result[] = $v[$keys];
                    }
                }
            } else {
                $i = 0;
                foreach ($data as $_k => $_v) {
                    foreach ($keys as $k) {
                        if (strpos($k, ':')) {
                            $ks = explode(":", $k);
                            if ($ismulti === 1) { //此处为调转kv
                                $_ks = $ks;
                                $ks[0] = $_ks[1];
                                $ks[1] = $_ks[0];
                            }
                            if (is_numeric($ks[1]) || $ks[1] == 'null' || $ks[1] == 'array' || $ks[1] == 'object' || $ks[1] == 'index') {
                                if (is_numeric($ks[1])) {
                                    $result[$_k][$ks[0]] = intval($ks[1]);
                                }

                                if ($ks[1] == 'null') {
                                    $result[$_k][$ks[0]] = null;
                                }

                                if ($ks[1] == 'array') {
                                    $result[$_k][$ks[0]] = array();
                                }

                                if ($ks[1] == 'object') {
                                    $result[$_k][$ks[0]] = new object();
                                }

                                if ($ks[1] == 'index') {
                                    $result[$_k][$ks[0]] = $_k;
                                }

                            } else {
                                if ($ks[1] == '@thumb') {
                                    $result[$_k][$ks[0]] = function_exists('mmodel_thumb') ? mmodel_thumb($_v[$ks[0]]) : $_v[$ks[0]];
                                } else {
                                    $result[$_k][$ks[1]] = $_v[$ks[0]];
                                }
                            }
                        } else {
                            $result[$_k][$k] = $_v[$k];
                        }
                        $i++;
                    }
                }
            }
        }
        return $result;
    }
}
