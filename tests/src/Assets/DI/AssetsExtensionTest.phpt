<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Packages\DI;

require __DIR__ . '/../../bootstrap.php';

use Nette\DI\CompilerExtension;

class PackagesExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$container->addDefinition('pathResolver')
			->setClass('Venne\\Packages\\PathResolver');

	}
}

namespace Venne\Packages;

class PathResolver
{

	public function expandResource($data)
	{
		return '%' . $data . '%';
	}

}


namespace VenneTests\Assets\DI;

use Nette;
use Tester\Assert;
use Tester\TestCase;
use Venne\Assets\DI\AssetsExtension;
use Venne\Packages\DI\PackagesExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AssetsExtensionTest extends TestCase
{

	/**
	 * @param bool $loadPackagesExtension
	 * @return \SystemContainer|Nette\DI\Container
	 */
	public function createContainer($loadPackagesExtension = FALSE)
	{
		$configurator = new Nette\Configurator();
		$configurator->setTempDirectory(TEMP_DIR);
		$configurator->addParameters(array('wwwDir' => __DIR__, 'container' => array('class' => 'SystemContainer_' . md5(Nette\Utils\Strings::random()))));

		if ($loadPackagesExtension) {
			$configurator->onCompile[] = function ($configurator, Nette\DI\Compiler $compiler) {
				$compiler->addExtension('packages', new PackagesExtension);
			};
		}

		$configurator->onCompile[] = function ($configurator, Nette\DI\Compiler $compiler) {
			$compiler->addExtension('assets', new AssetsExtension);
		};

		return $configurator->createContainer();
	}


	public function testRegisterTypes()
	{
		$container = $this->createContainer();

		Assert::type('Venne\Assets\ICssLoaderFactory', $container->getService('assets.cssLoaderFactory'));
		Assert::type('Venne\Assets\IJavaScriptLoaderFactory', $container->getService('assets.jsLoaderFactory'));

		/** @var Nette\Latte\Engine $latteEngine */
		$latteEngine = $container->createService('nette.latte');

		try {
			Assert::type('Nette\Latte\MacroNode', $latteEngine->compiler->expandMacro('js', 'foo.js'));
			Assert::type('Nette\Latte\MacroNode', $latteEngine->compiler->expandMacro('css', 'foo.css'));
		} catch (Nette\Latte\CompileException $e) {
			Assert::fail($e->getMessage());
		}

		Assert::same('<?php $_control[\'js\']->render(\'' . __DIR__ . '/@test/foo.js\', array(\'config\' => array (
))); ?>', $latteEngine->compiler->expandMacro('js', '@test/foo.js')->openingCode);
	}


	public function testRegisterWithPackageExtension()
	{
		$container = $this->createContainer(TRUE);

		/** @var Nette\Latte\Engine $latteEngine */
		$latteEngine = $container->createService('nette.latte');

		Assert::same('<?php $_control[\'js\']->render(\'' . __DIR__ . '/%@test.foo/foo.js%\', array(\'config\' => array (
))); ?>', $latteEngine->compiler->expandMacro('js', '@test.foo/foo.js')->openingCode);
	}

}

$testCache = new AssetsExtensionTest;
$testCache->run();
