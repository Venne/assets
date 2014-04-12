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
use Venne\Assets\Macros\CssMacro;

require __DIR__ . '/../../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CssMacrosTest extends TestCase
{

	public function testExpand()
	{
		$compiler = new \Nette\Latte\Compiler;
		CssMacro::install($compiler);

		Assert::same('<?php $_control[\'css\']->render(\'/test\', array(\'config\' => array (
))); ?>', $compiler->expandMacro('css', 'test')->openingCode);

		Assert::same('<?php $_control[\'css\']->render(\'/test\', array(\'config\' => array (
  \'media\' => \'textmedia\',
))); ?>', $compiler->expandMacro('css', 'test media=>textmedia')->openingCode);

		Assert::same('<?php $_control[\'css\']->render(\'/test\', array(\'config\' => array (
  \'media\' => \'textmedia\',
  \'type\' => \'texttype\',
  \'title\' => \'texttitle\',
  \'alternate\' => \'alternate\',
))); ?>', $compiler->expandMacro('css', 'test media=>textmedia, type=>texttype, title=>texttitle, alternate=>alternate')->openingCode);
	}

}

$testCache = new CssMacrosTest;
$testCache->run();
