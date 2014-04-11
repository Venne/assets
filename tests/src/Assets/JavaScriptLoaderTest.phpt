<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace VenneTests\Assets;

use Nette\Http\Request;
use Nette\Http\UrlScript;
use Tester\Assert;
use Tester\TestCase;
use WebLoader\Compiler;
use WebLoader\FileCollection;
use WebLoader\Nette\JavaScriptLoader;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JavaScriptLoaderTest extends TestCase
{

	public function testRender()
	{
		$httpRequest = new Request(new UrlScript('/foo/bar'));
		$files = new FileCollection(TEMP_DIR . '/js');
		$files->addFiles(array(
			__DIR__ . '/js/foo.js',
			__DIR__ . '/js/bar.js',
			__DIR__ . '/js/a/b.js',
		));

		mkdir(TEMP_DIR . '/temp');
		$compiler = Compiler::createJsCompiler($files, TEMP_DIR . '/temp');
		$control = new JavaScriptLoader($compiler, '/webtmp', $httpRequest);

		$fn = function () use ($control) {
			ob_start();
			call_user_func_array(array($control, 'render'), func_get_args());
			return ob_get_clean();
		};

		Assert::match('<script type="text/javascript" src="/webtmp/jsloader-%h%.js?%d%"></script>', $fn());
	}

}

$testCache = new JavaScriptLoaderTest;
$testCache->run();
