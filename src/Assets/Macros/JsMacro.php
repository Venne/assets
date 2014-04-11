<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Assets\Macros;

use Nette\Latte\CompileException;
use Nette\Latte\Compiler;
use Nette\Latte\PhpWriter;
use Venne\Packages\PathResolver;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JsMacro extends \Nette\Latte\Macros\MacroSet
{

	/** @var string */
	private $wwwDir;

	/** @var PathResolver */
	private $pathResolver;


	/**
	 * @param string $wwwDir
	 */
	public function setWwwDir($wwwDir)
	{
		$this->wwwDir = $wwwDir;
	}


	/**
	 * @param PathResolver $pathResolver
	 */
	public function setPathResolver(PathResolver $pathResolver)
	{
		$this->pathResolver = $pathResolver;
	}


	public function filter(\Nette\Latte\MacroNode $node, PhpWriter $writer)
	{
		$files = array();
		$pos = 0;
		while ($file = $node->tokenizer->fetchWord()) {

			if (strpos($file, '=>') !== FALSE) {
				$node->tokenizer->position = $pos;
				break;
			}

			$files[] = $this->wwwDir . '/' . $this->pathResolver->expandResource($file);
			$pos = $node->tokenizer->position;
		}

		if (!count($files)) {
			throw new CompileException("Missing file name in {js}");
		}

		eval('$args = ' . $writer->formatArray() . ';');
		return ("\$_control['js']->render('" . join('\', \'', $files) . "', array('config' => " . var_export($args, TRUE) . "));");
	}


	public static function install(Compiler $compiler, PathResolver $pathResolver = NULL, $wwwDir = NULL)
	{
		$me = new static($compiler);
		$me->setWwwDir($wwwDir);
		$me->setPathResolver($pathResolver);
		$me->addMacro('js', array($me, 'filter'));
	}
}

