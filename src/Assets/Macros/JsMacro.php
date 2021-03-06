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

use Latte;
use Latte\CompileException;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\PhpWriter;
use Venne\Packages\PathResolver;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JsMacro extends Latte\Macros\MacroSet
{

	/** @var string */
	private $wwwDir;

	/** @var \Venne\Packages\PathResolver */
	private $pathResolver;

	/**
	 * @param string $wwwDir
	 */
	public function setWwwDir($wwwDir)
	{
		$this->wwwDir = $wwwDir;
	}

	public function setPathResolver(PathResolver $pathResolver = null)
	{
		$this->pathResolver = $pathResolver;
	}

	public function filter(MacroNode $node, PhpWriter $writer)
	{
		$files = array();
		while ($file = $node->tokenizer->fetchWord()) {
			$files[] = $this->wwwDir . '/' . ($this->pathResolver ? $this->pathResolver->expandResource($file) : $file);
		}

		if (!count($files)) {
			throw new CompileException('Missing file name in {js}');
		}

		eval('$args = ' . $writer->formatArray() . ';');

		return ('$_control[\'js\']->render(\'' . join('\', \'', $files) . '\', array(\'config\' => ' . var_export($args, true) . '));');
	}

	/**
	 * @param \Latte\Compiler $compiler
	 * @param string|null $wwwDir
	 * @param \Venne\Packages\PathResolver|null $pathResolver
	 */
	public static function install(Compiler $compiler, $wwwDir = null, PathResolver $pathResolver = null)
	{
		$me = new static($compiler);
		$me->setWwwDir($wwwDir);
		$me->setPathResolver($pathResolver);
		$me->addMacro('js', array($me, 'filter'));
	}

}
