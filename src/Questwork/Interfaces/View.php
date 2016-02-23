<?php
namespace Questwork\Interfaces;

interface View
{
	public function vars($key, $value);

	public function path($path);

	public function extension($extension);

	public function render();
}