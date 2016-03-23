<?php
namespace Questwork;

class App extends Base implements Interfaces\App
{
	protected $request;

	protected $response;

    protected static $actions;

    public static $env;

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
		$controller = '\\' . implode('', array_map('ucfirst', explode('-', $params['controller'])));
        if (!class_exists($controller) && !class_exists($controller = str_repeat($controller, 2))) {
            trigger_error('Route not found: ' . $controller);
            throw new Exception('Route not found', 404);
        }
        $controller = new $controller($this->request, $this->response, $params);
        // $controller();
        $controller->printResponse();
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

    static function runAction($key, $context = NULL, $args = [])
    {
        if (!is_null(self::$actions[$key])) {
            foreach (self::$actions[$key] as $function) {
                call_user_func($function['action'], $context, $args);
            }
        }
    }

    static function rootPath()
    {
        return self::$rootPath;
    }

    static function baseUrl()
    {
        return self::$baseUrl;
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
}