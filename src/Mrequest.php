<?php
namespace lyhiving\mmodel;

class Mrequest
{
    static $_request_url,
    $_request_uri,
    $_request_base,
    $_request_viewer,
    $_pathinfo;

    public static function &get_instance()
    {
        static $instance;
        if (!is_null($instance)) {
            return $instance;
        }
        $instance = new Mrequest();
        return $instance;
    }

    public static function get_method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function get_base()
    {
        if (!is_null(self::$_request_base)) {
            return self::$_request_base;
        }

        $base = self::is_ssl() ? 'https://' : 'http://';
        $base .= self::get_host();
        self::$_request_base = $base;
        return $base;
    }

    public static function get_url()
    {
        if (!is_null(self::$_request_url)) {
            return self::$_request_url;
        }

        $url = self::is_ssl() ? 'https://' : 'http://';
        $url .= self::get_host();
        $url .= self::get_uri();
        self::$_request_url = $url;
        return $url;
    }

    public static function get_uri()
    {
        if (!is_null(self::$_request_uri)) {
            return self::$_request_uri;
        }

        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $uri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            $uri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $uri .= '?' . $_SERVER['QUERY_STRING'];
            }
        } else {
            $uri = '';
        }
        self::$_request_uri = $uri;
        return $uri;
    }

    public static function get_querystring()
    {
        return $_SERVER['QUERY_STRING'];
    }

    public static function get_pathinfo()
    {
        if (!is_null(self::$_pathinfo)) {
            return self::$_pathinfo;
        }

        if (!empty($_SERVER['PATH_INFO'])) {
            self::$_pathinfo = $_SERVER['PATH_INFO'];
            return $_SERVER['PATH_INFO'];
        }
        $pathinfo = substr(self::get_uri(), strlen(self::get_scriptname()));
        if (substr($pathinfo, 0, 1) == '/') {
            if ($_SERVER['QUERY_STRING']) {
                $pathinfo = substr($pathinfo, 0, strpos($pathinfo, '?'));
            }

            self::$_pathinfo = $pathinfo;
        }
        return self::$_pathinfo;
    }

    public static function get_scriptname()
    {
        $script = self::get_env('SCRIPT_NAME');
        return $script ? $script : self::get_env('ORIG_SCRIPT_NAME');
    }

    public static function get_referer()
    {
        $referer = self::get_env('HTTP_REFERER');
        $urls = parse_url($referer);
        if ($urls['host']) {
            return $referer;
        }

        return self::get_base() . "/" . $referer;
    }

    public static function get_host()
    {
        //阿里云
        if (0) {
            if (self::get_env('HTTP_ALI_SWIFT_RANGE_CACHE') == 'on' && self::get_env('HTTP_ALI_SWIFT_ORIGIN_HOST')) {
                return self::get_env('HTTP_ALI_SWIFT_ORIGIN_HOST');
            }
            //回源host
        }
        if (self::get_env('HTTP_ALI_SWIFT_LOG_HOST')) {
            return self::get_env('HTTP_ALI_SWIFT_LOG_HOST');
        }
        //地址栏访问的域名
        if (self::get_env('HTTP_ALI_SWIFT_STAT_HOST')) {
            return self::get_env('HTTP_ALI_SWIFT_STAT_HOST');
        }
        //CDN域名，如全局域名就是all.domain

        //反向代理
        if (self::get_env('PROXY-REVER-HOST')) {
            return self::get_env('PROXY-REVER-HOST');
        }

        $host = self::get_env('HTTP_X_FORWARDED_HOST');
        return $host ? $host : self::get_env('HTTP_HOST');
    }

    public static function get_language()
    {
        return self::get_env('HTTP_ACCEPT_LANGUAGE');
    }

    public static function get_charset()
    {
        return $_SERVER['HTTP_ACCEPT_CHARSET'];
    }

    public static function get_clientip()
    {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
            if (strpos($ip, ',')) {
                $ips = explode(',', $ip);
                $ip = trim($ips[0]);
            }
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } elseif (isset($_SERVER['HTTP_VIA']) && isset($_SERVER['HTTP_ALI_CDN_REAL_IP'])) //阿里云CDN
        {
            $ip = $_SERVER['HTTP_ALI_CDN_REAL_IP'];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) //腾讯云CDN
        {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
        return isset($ip) && preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : 'unknown';
    }

    public static function get_env($key)
    {
        return isset($_SERVER[$key]) ? $_SERVER[$key] : (isset($_ENV[$key]) ? $_ENV[$key] : false);
    }

    public static function clean()
    {

    }

    public static function is_ssl()
    {
        return (strtolower(self::get_env('HTTPS')) === 'on' || strtolower(self::get_env('HTTP_SSL_HTTPS')) === 'on' || self::get_env('HTTP_X_FORWARDED_PROTO') == 'https');
    }

    public static function is_XmlHttpRequest()
    {
        return (self::get_env('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest');
    }

    public static function is_post()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public static function is_get()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    public static function is_ie()
    {
        return strpos(self::get_env('HTTP_USER_AGENT'), 'MSIE') ? true : false;
    }

    public static function is_spider()
    {
        static $is_spider;
        if (!is_null($is_spider)) {
            return $is_spider;
        }

        $browsers = 'msie|netscape|opera|konqueror|mozilla';
        $spiders = 'bot|spider|google|isaac|surveybot|baiduspider|yahoo|sohu-search|yisou|3721|qihoo|daqi|ia_archiver|p.arthur|fast-webcrawler|java|microsoft-atl-native|turnitinbot|webgather|sleipnir|msn';
        if (preg_match("/($browsers)/i", $_SERVER['HTTP_USER_AGENT'])) {
            $is_spider = false;
        } elseif (preg_match("/($spiders)/i", $_SERVER['HTTP_USER_AGENT'])) {
            $is_spider = true;
        }
        return $is_spider;
    }

    public static function is_mobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile',
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    public static function is_wechat()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $is_weixin = strpos($agent, 'micromessenger') ? true : false;
        if ($is_weixin) {
            return true;
        } else {
            return false;
        }
    }

    public static function set_viewer($viewer = null)
    {
        self::$_request_viewer = $viewer;
    }

    public static function get_viewer()
    {
        if (self::$_request_viewer) {
            return self::$_request_viewer;
        }
        if (self::is_wechat()) {
            return 'wechat';
        }
        if (self::is_mobile()) {
            return 'mobile';
        }
        return 'web';
    }

}
