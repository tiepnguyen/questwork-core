<?php
namespace Questwork;

class Controller extends Base implements Interfaces\Controller
{
	protected $request;

	protected $response;

	public function __construct(Interfaces\Request $request, Interfaces\Response $response, $args = [])
	{
		$this->request = $request;
		$this->response = $response;
	}

	public function __destruct()
	{
		echo $this->response;
	}
}