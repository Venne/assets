<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Assets;

use Nette\Http\Request as NetteHttpRequest;
use WebLoader\Compiler;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JavaScriptLoader extends \WebLoader\Nette\JavaScriptLoader
{

	/** @var string */
	private $relativeTempPath;

	/** @var \Nette\Http\Request */
	private $httpRequest;

	/**
	 * @param \WebLoader\Compiler $compiler
	 * @param string $relativeTempPath
	 * @param \Nette\Http\Request $httpRequest
	 */
	public function __construct(Compiler $compiler, $relativeTempPath, NetteHttpRequest $httpRequest)
	{
		parent::__construct($compiler, '');

		$this->relativeTempPath = $relativeTempPath;
		$this->httpRequest = $httpRequest;
	}

	public function render()
	{
		$baseUrl = rtrim($this->httpRequest->getUrl()->getBaseUrl(), '/');
		$basePath = preg_replace('#https?://[^/]+#A', '', $baseUrl);

		$this->setTempPath($basePath . $this->relativeTempPath);

		$args = array();
		if (func_num_args() > 0) {
			foreach (func_get_args() as $arg) {
				if (!is_array($arg) || !isset($arg['config'])) {
					$args[] = $arg;
				}
			}
		}

		call_user_func_array(array($this, 'parent::render'), $args);
	}
}
