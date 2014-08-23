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

class PackagesExtension extends \Nette\DI\CompilerExtension
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

use Nette\Configurator;
use Nette\Utils\Strings;
use Tester\Assert;
use Venne\Assets\DI\AssetsExtension;
use Venne\Packages\DI\PackagesExtension;
use Nette\DI\Compiler;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AssetsExtensionTest extends \Tester\TestCase
{

	/**
	 * @param bool $loadPackagesExtension
	 * @return \SystemContainer|Nette\DI\Container
	 */
	public function createContainer($loadPackagesExtension = false)
	{
		$configurator = new Configurator();
		$configurator->setTempDirectory(TEMP_DIR);
		$configurator->addParameters(array('wwwDir' => __DIR__, 'container' => array('class' => 'SystemContainer_' . md5(Strings::random()))));

		if ($loadPackagesExtension) {
			$configurator->onCompile[] = function (Configurator $configurator, Compiler $compiler) {
				$compiler->addExtension('packages', new PackagesExtension());
			};
		}

		$configurator->onCompile[] = function (Configurator $configurator, Compiler $compiler) {
			$compiler->addExtension('assets', new AssetsExtension());
		};

		return $configurator->createContainer();
	}

	public function testRegisterTypes()
	{
		$container = $this->createContainer();

		Assert::type('Venne\Assets\ICssLoaderFactory', $container->getService('assets.cssLoaderFactory'));
		Assert::type('Venne\Assets\IJavaScriptLoaderFactory', $container->getService('assets.jsLoaderFactory'));

		/** @var Nette\Latte\Engine $latteEngine */
		$latteEngine = $container->getByType('Nette\Bridges\ApplicationLatte\ILatteFactory')->create();
		$latteEngine->compile(__DIR__ . '/foo.latte');

		try {
			Assert::type('Latte\MacroNode', $latteEngine->getCompiler()->expandMacro('js', 'foo.js'));
			Assert::type('Latte\MacroNode', $latteEngine->getCompiler()->expandMacro('css', 'foo.css'));
		} catch (\Latte\CompileException $e) {
			Assert::fail($e->getMessage());
		}

		Assert::same('<?php $_control[\'js\']->render(\'' . __DIR__ . '/@test/foo.js\', array(\'config\' => array (
))); ?>', $latteEngine->getCompiler()->expandMacro('js', '@test/foo.js')->openingCode);
	}

	public function testRegisterWithPackageExtension()
	{
		$container = $this->createContainer(true);

		/** @var Nette\Latte\Engine $latteEngine */
		$latteEngine = $container->getByType('Nette\Bridges\ApplicationLatte\ILatteFactory')->create();
		$latteEngine->compile(__DIR__ . '/foo.latte');

		Assert::same('<?php $_control[\'js\']->render(\'' . __DIR__ . '/%@test.foo/foo.js%\', array(\'config\' => array (
))); ?>', $latteEngine->getCompiler()->expandMacro('js', '@test.foo/foo.js')->openingCode);
	}

}

$testCache = new AssetsExtensionTest;
$testCache->run();
