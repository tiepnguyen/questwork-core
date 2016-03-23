<?php
namespace Questwork;

class View extends Base implements Interfaces\View
{
	protected $path;

	protected $name;

	protected $extension;

    protected $vars;

    protected $file;

    protected $rendered;

    protected static $error;

	public function __construct($name = NULL, $vars = [])
	{
		$this->name = $name;
		$this->vars = $vars;
		$this->path = defined('APP_PATH_VIEW') ? APP_PATH_VIEW : 'views';
        $this->extension = defined('APP_VIEW_EXT') ? APP_VIEW_EXT : '.html';
	}

	public function __set($var, $value)
    {
        $this->vars[$var] = $value;
    }

    public function __get($var)
    {
        return $this->vars[$var];
    }

    public function __toString()
    {
        try {
    		return $this->render();
    	} catch (Exception $error) {
    		return $error->getMessage();
    	}
    }

    public function vars($key = NULL, $value = NULL)
    {
        if (is_array($key)) {
            $this->vars = array_merge($this->vars, $key);
        } else if (is_string($key)) {
            if (is_null($value)) {
                return isset($this->vars[$key]) ? $this->vars[$key] : NULL;
            } else {
                $this->vars[$key] = $value;
            }
        }
        else {
            return $this->vars;
        }
        return $this;
    }

    public function path($path = NULL)
    {
        if (is_null($path)) {
            return $this->path;
        } else {
            $this->path = $path;
        }
        return $this;
    }

    public function extension($extension = NULL)
    {
        if (is_null($extension)) {
            return $this->extension;
        } else {
            $this->extension = $extension;
        }
        return $this;
    }

    public function render()
    {
    	if (!file_exists($this->file = $this->path . '/' . $this->name . $this->extension)) {
            trigger_error('Missing template: ' . $this->file);
            throw new Exception('Missing template: ' . $this->file);
        }
        if (!empty($this->vars)) {
            foreach ($this->vars as $key => $var) {
                if (is_object($var) && is_subclass_of($var, __CLASS__)) {
                    $this->vars[$key] = $var->render();
                }
            }
            extract($this->vars);
        }
        $this->rendered = '';
        $render = @eval('$this->rendered = "' . str_replace('"', '\"', file_get_contents($this->file)) . '";');

        self::$error = is_null(self::$error) && !is_null($error = error_get_last()) && $error['type'] == E_PARSE;
        if (self::$error == TRUE) {
            $message = 'View parsing error: ' . $this->file . ' on line ' . $error['line'];
            trigger_error($message);
            throw new Exception($message);
        }
        return $this->rendered;
    }
}