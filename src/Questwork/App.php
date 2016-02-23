<?php
namespace Questwork;

class App extends Base implements Interfaces\App
{
	protected $request;

	protected $response;

    protected static $actions;

	public function __construct(Interfaces\Request $request, Interfaces\Response $response)
	{
        $this->request = $request;
        $this->response = $response;
        self::$config = [];
        self::$baseUrl = $request->baseUrl();
	}

	public function execute($routes = [], $rootPath = NULL)
	{
		$params = $this->parseRoute($routes);
		self::$rootPath = defined('APP_PATH_ROOT') ? APP_PATH_ROOT : ($rootPath ?: dirname($_SERVER['SCRIPT_FILENAME']));
		$controller = $params['controller'];
		$path = defined('APP_PATH_CLASS') ? APP_PATH_CLASS : self::$rootPath . '/classes';
		if ($file = $this->fileExistsCi($path . '/' . $controller . '.php')) {
            $class = '\\' . substr($file, strlen($path) + 1, -4);
        } else if ($file = $this->fileExistsCi($path . str_repeat('/' . $controller, 2) . '.php')) {
            $class = substr($file, strlen($path) + 1, -4);
            $class = str_repeat('\\' . substr($class, 0, strlen($class) / 2), 2);
        } else {
            trigger_error('Route not found: ' . $controller);
            throw new Exception('Route not found', 404);
        }
        require $file;
        $controller = new $class($this->request, $this->response, $params);
        return $this;
	}

	public function config($key = NULL, $value = NULL)
    {
        if (is_array($key)) {
            self::$config = array_merge(self::$config, $key);
        } else if (is_string($key)) {
            if (is_null($value)) {
                return isset(self::$config[$key]) ? self::$config[$key] : NULL;
            } else {
                self::$config[$key] = $value;
            }
        }
        return $this;
    }

    static function rootPath()
    {
        return self::$rootPath;
    }

    static function baseUrl()
    {
        return self::$baseUrl;
    }

    public function errorHandler($handler)
    {
        set_error_handler($handler);
        return $this;
    }

    public function loadHooks($path)
    {
        if (is_string($path)) {
            $path = glob($path . '/*.php');
        }
        foreach ($path as $plugin) {
            require_once($plugin);
        }
        return $this;
    }

    static function addAction($key, $function, $priority = 0)
    {
        if (is_null(self::$actions[$key])) {
            self::$actions[$key] = [];
        }
        array_push(self::$actions[$key], ['priority' => $priority, 'action' => $function]);
        usort(self::$actions[$key], function($a, $b) {
            return $b['priority'] - $a['priority'] ?: 1;
        });
    }

    static function runAction($key, $context)
    {
        if (!is_null(self::$actions[$key])) {
            foreach (self::$actions[$key] as $function) {
                call_user_func($function['action'], $context);
            }
        }
    }

	protected function parseRoute($routes = [])
    {
        $paths = $this->request->basePath();
        $matched = FALSE;
        foreach ($routes as $count => $route) {
            $rule = str_replace(')', ')?', $route[0]);
            $pattern = preg_replace('/<.*?>/', '(?P$0[\w\-\+\._%]+)', $rule);
            preg_match('#^' . $pattern . '/?$#', $paths, $match);
            $data = [];
            if (!empty($match)) {
                $matched = $count;
                foreach ($match as $key => $value) {
                    if (is_string($key)) {
                        $data[$key] = $value;
                    }
                }
                break;
            }
        }

        if ($matched !== FALSE) {
            $defaults = $routes[$matched][1];
            $params = array_merge($defaults, $data);
            return $params;
        }
    }

    protected function fileExistsSingle($file)
    {
        if (file_exists($file) === TRUE) {
            return $file;
        }
        $lowerfile = strtolower($file);
        foreach (glob(dirname($file) . '/*') as $file) {
            if (strtolower($file) === $lowerfile) {
                return $file;
            }
        }
        return FALSE;
    }

    protected function fileExistsCi($filePath)
    {
        if (file_exists($filePath) === TRUE) {
            return $filePath;
        }
        $dirs = explode('/', $filePath);
        $len = count($dirs);
        $dir = '/';
        foreach ($dirs as $i => $part) {
            $dirpath = $this->fileExistsSingle($dir . $part);
            if ($dirpath === FALSE) {
                return FALSE;
            }
            $dir = $dirpath;
            $dir .= (($i > 0) && ($i < $len - 1)) ? '/' : '';
        }
        return $dir;
    }

}