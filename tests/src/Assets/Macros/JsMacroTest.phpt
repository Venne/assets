<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace VenneTests\Assets\Macros;

use Tester\Assert;
use Tester\TestCase;
use Venne\Assets\Macros\JsMacro;

require __DIR__ . '/../../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JsMacrosTest extends TestCase
{

	public function testExpand()
	{
		$compiler = new \Nette\Latte\Compiler;
		JsMacro::install($compiler);

		Assert::same( '<?php $_control[\'js\']->render(\'/test\', array(\'config\' => array (
))); ?>',  $compiler->expandMacro('js', 'test')->openingCode );
	}

}

$testCache = new JsMacrosTest;
$testCache->run();
