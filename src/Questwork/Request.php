<?php
namespace Questwork;

class Request implements Interfaces\Request
{
    public function headers($key = NULL)
    {
        return is_null($key) ? getallheaders() : getallheaders()[$key];
    }

    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function isMethod($method)
    {
        return ($_SERVER['REQUEST_METHOD'] == $method);
    }

    public function isGet()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET');
    }

    public function isPost()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'POST');
    }

    public function isXhr()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    public function isCli() {
        return (PHP_SAPI == 'cli');
    }

    public function argument($key = NULL) {
        return is_null($key) ? $_SERVER['argv'] : $_SERVER['argv'][$key];
    }

    public function referer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL;
    }

    public function uri()
    {
        return [
            'scheme' => $this->scheme(),
            'host' => $this->host(),
            'port' => $this->port(),
            'path' => $this->path(),
            'query' => $this->query(),
        ];
    }

    public function scheme()
    {
        if ($this->isCli()) {
            return NULL;
        }
        return (isset($_SERVER['HTTPS'])) ? 'https' : 'http';
    }

    public function host()
    {
        return $this->isCli() ? NULL : $_SERVER['HTTP_HOST'];
    }

    public function port()
    {
        return $_SERVER['SERVER_PORT'];
    }

    public function path()
    {
        if ($this->isCli()) {
            return substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
        }
        list($path) = explode('?', $_SERVER['REQUEST_URI']);
        return $path;
    }

    public function query()
    {
        if ($this->isCli()) {
            list($path, $query) = explode('?', $this->argument(1));
            return $query;
        }
        return $_SERVER['QUERY_STRING'];
    }

    public function ip() {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function basePath()
    {
        if ($this->isCli()) {
            list($path) = explode('?', $this->argument(1));
            return '/' . $path;
        }
        $path = explode('/', $this->path());
        array_splice($path, 0, substr_count($_SERVER['SCRIPT_NAME'], '/'));
        return '/' . implode('/', $path);
    }

    public function baseUrl()
    {
        $script = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
        return $this->scheme() . '://' . $this->host() . $script;
    }

    public function get($key = NULL, $value = NULL)
    {
        if ($this->isCli()) {
            foreach ($this->argument() as $arg) {
                if (strpos($arg, '=') !== FALSE) {
                    list($k, $v) = explode('=', $arg);
                    $_GET[$k] = $v;
                }
            }
        }
        if (!is_null($value)) {
            $_GET[$key] = $value;
            return $this;
        }
        return is_null($key) ? $_GET : (isset($_GET[$key]) ? $_GET[$key] : NULL);
    }

    public function post($key = NULL, $value = NULL)
    {
        if (!is_null($value)) {
            $_POST[$key] = $value;
            return $this;
        }
        return is_null($key) ? $_POST : (isset($_POST[$key]) ? $_POST[$key] : NULL);
    }

    public function session($key = NULL, $value = NULL)
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!is_null($value)) {
            $_SESSION[$key] = $value;
            return $this;
        }
        return is_null($key) ? $_SESSION : (isset($_SESSION[$key]) ? $_SESSION[$key] : NULL);
    }

    public function removeSession($key)
    {
        unset($_SESSION[$key]);
        return $this;
    }

    public function cookie($key = NULL, $value = NULL, $expire = 0, $path = '/')
    {
        if (!is_null($value)) {
            setcookie($name, $value, $expire, $path);
            return $this;
        }
        return is_null($key) ? $_COOKIE : (isset($_COOKIE[$key]) ? $_COOKIE[$key] : NULL);
    }

    public function removeCookie($name)
    {
        unset($_COOKIE[$name]);
        return $this;
    }

    public function files($key = NULL)
    {
        $files = [];
        foreach ($_FILES as $name => $file) {
            if (count($file) == count($file, TRUE)) {
                $files[$name] = $file;
            } else {
                $files[$name] = [];
                foreach ($file as $k => $array) {
                    foreach ($array as $count => $value) {
                        if (!isset($files[$name][$count])) {
                            $files[$name][$count] = [];
                        }
                        $files[$name][$count][$k] = $value;
                    }
                }
            }
        }
        return is_null($key) ? $files : (isset($files[$key]) ? $files[$key] : NULL);
    }

    public function input()
    {
        return file_get_contents('php://input');
    }

    public function output()
    {
        return file_get_contents('php://output');
    }

}
