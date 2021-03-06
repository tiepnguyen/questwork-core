<?php
namespace Questwork;

class Response implements Interfaces\Response
{
	protected $status;

	protected $headers;

	protected $body;

	public function __construct()
	{
		$this->status = http_response_code();
		$this->body = '';
		$this->headers = ['Content-Type' => 'text/html'];
		$this->header($this->headers);
	}

	public function __toString()
	{
		return $this->write();
	}

	public function status($code = NULL)
	{
		if (is_null($code)) {
			return http_response_code();
		} else {
			$this->status = $code;
			http_response_code($code);
		}
		return $this;
	}

	public function header($key = NULL, $value = NULL)
	{
		if (is_numeric($key)) {
			$this->status($key);
		} else if (is_string($key)) {
			if (is_string($value)) {
				$this->headers[$key] = $value;
				header($key . ':' . $value);
			} else {
				return $this->headers[$key];
			}
		} else if (is_array($key)) {
			foreach ($key as $name => $value) {
				$this->header($name, $value);
			}
		} else if (is_null($key)) {
			return $this->headers;
		}
		if (is_array($value)) {
			$this->header($value);
		}
		return $this;
	}

	public function body($content = NULL)
	{
		if (is_null($content)) {
			return $this->body;
		} else {
			$this->body = $content;
		}
		return $this;
	}

	public function redirect($url, $timer = 0, $message = NULL)
	{
		if (is_null($message) && !is_numeric($timer)) {
			$message = $timer;
			$timer = 0;
		}
		if (!is_numeric($timer)) {
			header('Location:' . $url);
		} else {
			header('Refresh:' . $timer . ';url=' . $url);
		}
		$this->end($message);
	}

	public function refresh($timer = 0, $message = NULL)
	{
		if (is_null($message) && !is_numeric($timer)) {
			$message = $timer;
			$timer = 0;
		}
		header('Refresh:' . $timer);
		$this->end($message);
	}

	public function end($message = NULL)
	{
		exit($message);
	}

	public function write($body = NULL)
	{
		if (!is_null($body)) {
			$this->body = $body;
		}
		if (is_array($this->body)) {
			$this->body = json_encode($this->header('Content-Type', 'application/json')->body, JSON_PRETTY_PRINT);
		}
		echo $this->body;
		return $this;
	}
}