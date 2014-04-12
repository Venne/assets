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
use Venne\Assets\CssLoader;
use WebLoader\Compiler;
use WebLoader\FileCollection;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CssLoaderTest extends TestCase
{

	public function testRender()
	{
		$httpRequest = new Request(new UrlScript('/foo/bar'));
		$files = new FileCollection(TEMP_DIR . '/css');
		$files->addFiles(array(
			__DIR__ . '/css/foo.css',
			__DIR__ . '/css/bar.css',
			__DIR__ . '/css/a/b.css',
		));

		mkdir(TEMP_DIR . '/temp');
		$compiler = Compiler::createCssCompiler($files, TEMP_DIR . '/temp');
		$control = new CssLoader($compiler, '/webtmp', $httpRequest);

		$fn = function () use ($control) {
			ob_start();
			call_user_func_array(array($control, 'render'), func_get_args());
			return ob_get_clean();
		};

		Assert::match('<link rel="stylesheet" type="text/css" href="/webtmp/cssloader-%h%.css?%d%">', $fn());
		Assert::match('<link rel="stylesheet" type="text/css" href="/webtmp/cssloader-%h%-foo.css?%d%">', $fn('css/foo.css'));
		Assert::match('<link rel="stylesheet" type="text/css" media="mediatext" href="/webtmp/cssloader-%h%.css?%d%">', $fn(array('config' => array('media' => 'mediatext'))));
		Assert::match('<link rel="stylesheet" type="typetext" href="/webtmp/cssloader-%h%.css?%d%">', $fn(array('config' => array('type' => 'typetext'))));
		Assert::match('<link rel="stylesheet" type="text/css" title="titletext" href="/webtmp/cssloader-%h%.css?%d%">', $fn(array('config' => array('title' => 'titletext'))));
		Assert::match('<link rel="stylesheet alternate" type="text/css" href="/webtmp/cssloader-%h%.css?%d%">', $fn(array('config' => array('alternate' => 'alternate'))));
		Assert::match('<link rel="stylesheet alternate" type="typetext" media="mediatext" title="titletext" href="/webtmp/cssloader-%h%.css?%d%">', $fn(array('config' => array(
			'alternate' => 'alternate',
			'title' => 'titletext',
			'type' => 'typetext',
			'media' => 'mediatext'
		))));

		Assert::match('<link rel="stylesheet alternate" type="typetext" media="mediatext" title="titletext" href="/webtmp/cssloader-%h%-foo.css?%d%">', $fn('css/foo.css', array('config' => array(
			'alternate' => 'alternate',
			'title' => 'titletext',
			'type' => 'typetext',
			'media' => 'mediatext'
		))));
	}

}

$testCache = new CssLoaderTest;
$testCache->run();
