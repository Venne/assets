<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Assets\DI;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AssetsExtension extends \Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		// macros
		$args = array('@self', $this->getContainerBuilder()->expand('%wwwDir%'));
		if (count($this->compiler->getExtensions('Venne\Packages\DI\PackagesExtension'))) {
			$args[] = '@Venne\\Packages\\PathResolver';
		} else {
			$args[] = null;
		}

		$container->getDefinition('nette.latteFactory')
			->addSetup('?->onCompile[] = function($engine) { Venne\Assets\Macros\CssMacro::install($engine->getCompiler(), ?, ?); }', $args)
			->addSetup('?->onCompile[] = function($engine) { Venne\Assets\Macros\JsMacro::install($engine->getCompiler(), ?, ?); }', $args);

		// collections
		$container->addDefinition($this->prefix('cssFileCollection'))
			->setClass('Venne\Assets\CssFileCollection');

		$container->addDefinition($this->prefix('jsFileCollection'))
			->setClass('Venne\Assets\JsFileCollection');

		// compilers
		$container->addDefinition($this->prefix('cssCompiler'))
			->setClass('WebLoader\Compiler')
			->setFactory('WebLoader\Compiler::createCssCompiler', array($this->prefix('@cssFileCollection'), $this->containerBuilder->expand('%wwwDir%/cache')))
			->addSetup('$service->addFileFilter(?)', array($this->prefix('@cssUrlsFilter')))
			->addSetup('setCheckLastModified', array($this->containerBuilder->expand('%debugMode%')))
			->addSetup('setJoinFiles', array(!$container->parameters['debugMode']))
			->setAutowired(false);

		$container->addDefinition($this->prefix('jsCompiler'))
			->setClass('WebLoader\Compiler')
			->setFactory('WebLoader\Compiler::createJsCompiler', array($this->prefix('@jsFileCollection'), $this->containerBuilder->expand('%wwwDir%/cache')))
			->addSetup('setCheckLastModified', array($this->containerBuilder->expand($this->containerBuilder->expand('%debugMode%'))))
			->addSetup('setJoinFiles', array(!$container->parameters['debugMode']))
			->setAutowired(false);

		// loaders
		$container->addDefinition($this->prefix('cssLoaderFactory'))
			->setClass('Venne\Assets\CssLoader', array($this->prefix('@cssCompiler'), '/cache'))
			->setImplement('Venne\Assets\CssLoaderFactory');

		$container->addDefinition($this->prefix('jsLoaderFactory'))
			->setClass('Venne\Assets\JavaScriptLoader', array($this->prefix('@jsCompiler'), '/cache'))
			->setImplement('Venne\Assets\JavaScriptLoaderFactory');

		// filters
		$container->addDefinition($this->prefix('cssUrlsFilter'))
			->setClass('WebLoader\Filter\CssUrlsFilter', array($this->containerBuilder->expand('%wwwDir%')))
			->addSetup('$service = new WebLoader\Filter\CssUrlsFilter(?, $this->parameters[\'basePath\'])', array($this->containerBuilder->expand('%wwwDir%')));
	}

}
