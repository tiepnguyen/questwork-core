<?php
namespace Questwork\Interfaces;

interface App
{
	public function execute($routes, $rootPath);

	public function errorHandler($handler);

	public function config($key, $value);

	public static function rootPath();

	public static function baseUrl();
}