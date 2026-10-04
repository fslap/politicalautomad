<?php
/*
 * International Political Party Zone for all Partys also Locally
 *
 *                      #;:#;:#;:
 *                       #;:#;:            #;:#;:#;
 *            %&                         #;: #;:;:#;:#;:#;:#
 *    %$%    %$%           #;:    #;:     #;: #;:;:#;:#;:
 *    ____________                ;:      #;:  #;: #;:#;:#;:#;
 *   /§§§§§§§§§§§|                                    ;:#;:#;:
 *   |###########/  / &/      #;:#;:#;:#;:#;:        #;:#;: #;: #;
 *   /%%%%%%%%%%/   |$$$/        #;:#;:#;:#;:#;#;:#;:;:#;:#;:
 *  /######### /   |$$$/          #;:#;:#;:#;:#;:#;:#;:#;:#;:#;:
 * /%%%%%%%%%%/   \CCC/           #;:#;:#;:#;:#;:#;:#;:#;:
 * \#########|   \\|      _\    :#;:  :#;:#;:  #;:#;:#;:#;:
 *  \________/   /[[]]\  \_/   :#\:#\:#\:#\:#;:
 *   \====__/    \xxxx/              #\:#\:#;:#;:
 *   |""""""\    |yyy/                #;: #;:#;:#;: |#;:\
 *   |~~~~~~/ #   |c|                 ;:#;:\;:#;:   |#;:;:
 *   +~~~~~/  |   \t/                  #\:#;\       |##;/
 *    +~~~/   |#
 *     +~/\       \*+ ~
 *                      #
 *
 *
 *
 * https://north.sbdp.ro https://mid.sbdp.ro/ https://pacif.sbdp.ro/ https://low.sbdp.ro/
 * (c) Florian Leon Steenbuck
 *
 * Copyright (c) 2026 by Florian Leon Steenbuck
 * https://kil.ls https://fslap.de
 *
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

namespace Automad\Party;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Core\Blocks;
use Automad\DynamicBlockRegister;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Bootstrap party dynamic blocks into the core Blocks resolver (v2-political).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
final class BlockBootstrap {
	public static function createRegister(): DynamicBlockRegister {
		$register = new DynamicBlockRegister();
		self::register($register);

		return $register;
	}

	/**
	 * Return the editor definitions of all registered party blocks.
	 *
	 * @return array<int, array>
	 */
	public static function definitions(): array {
		$definitions = array();

		foreach (All::$components as $class) {
			$block = new $class();

			if ($block instanceof AbstractDynamicTemplateBlock) {
				$definitions[] = $block->editorDefinition();
			}
		}

		return $definitions;
	}

	public static function init(): DynamicBlockRegister {
		$register = self::createRegister();
		Blocks::setDynamicRegister($register);

		return $register;
	}
	public static function register(DynamicBlockRegister $register): void {
		foreach (All::$components as $class) {
			$register->register(new $class());
		}
	}
}
