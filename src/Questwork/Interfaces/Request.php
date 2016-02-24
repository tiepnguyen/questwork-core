<?php
namespace Questwork\Interfaces;

interface Request
{
	public function headers($key);

	public function method();

	public function isGet();

	public function isPost();

	public function isXhr();

	public function referer();

	public function scheme();

	public function host();

	public function port();

	public function path();

	public function ip();

	public function basePath();

	public function baseUrl();

	public function query();

	public function get($key);

	public function post($key);

	public function files($key);
}