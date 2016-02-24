<?php
namespace Questwork\Interfaces;

interface Response
{
	public function status($code);

	public function header($key, $value);

	public function body($content);

	public function redirect($url, $timer, $message);

	public function end($message);
}