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

use Nette\DI\CompilerExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AssetsExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		// macros
		$container->getDefinition('nette.latte')
			->addSetup('Venne\Assets\Macros\CssMacro::install(?->compiler, ?, ?)', array('@self', '@Venne\\Packages\\PathResolver', $this->getContainerBuilder()->expand('%wwwDir%')))
			->addSetup('Venne\Assets\Macros\JsMacro::install(?->compiler, ?, ?)', array('@self', '@Venne\\Packages\\PathResolver', $this->getContainerBuilder()->expand('%wwwDir%')));


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
			->setAutowired(FALSE);

		$container->addDefinition($this->prefix('jsCompiler'))
			->setClass('WebLoader\Compiler')
			->setFactory('WebLoader\Compiler::createJsCompiler', array($this->prefix('@jsFileCollection'), $this->containerBuilder->expand('%wwwDir%/cache')))
			->addSetup('setCheckLastModified', array($this->containerBuilder->expand($this->containerBuilder->expand('%debugMode%'))))
			->addSetup('setJoinFiles', array(!$container->parameters['debugMode']))
			->setAutowired(FALSE);


		// loaders
		$container->addDefinition($this->prefix('cssLoaderFactory'))
			->setClass('Venne\Assets\CssLoader', array($this->prefix('@cssCompiler'), '/cache'))
			->setImplement('Venne\Assets\ICssLoaderFactory');

		$container->addDefinition($this->prefix('jsLoaderFactory'))
			->setClass('Venne\Assets\JavaScriptLoader', array($this->prefix('@jsCompiler'), '/cache'))
			->setImplement('Venne\Assets\IJavaScriptLoaderFactory')
			->setAutowired(FALSE);


		// filters
		$container->addDefinition($this->prefix('cssUrlsFilter'))
			->setClass('WebLoader\Filter\CssUrlsFilter', array($this->containerBuilder->expand('%wwwDir%')))
			->addSetup('$service = new WebLoader\Filter\CssUrlsFilter(?, $this->parameters[\'basePath\'])', array($this->containerBuilder->expand('%wwwDir%')));
	}

}