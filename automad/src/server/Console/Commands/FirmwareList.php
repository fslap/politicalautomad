<?php
/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

namespace Automad\Console\Commands;

use Automad\Console\Argument;
use Automad\Console\ArgumentCollection;
use Automad\Console\Console;
use Automad\Party\Firmware;

defined('AUTOMAD_CONSOLE') or die('Console only!' . PHP_EOL);

/**
 * List the available firmware patches and backups.
 *
 * @author Florian Leon Steenbuck
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class FirmwareList extends AbstractCommand {
	public function description(): string {
		return 'List the firmware patches in the firmware directory and the available backups.';
	}

	public function example(): string {
		return 'php automad/console firmware:list';
	}

	public function name(): string {
		return 'firmware:list';
	}

	public function run(): int {
		echo Console::clr('heading', 'Installed version: ' . (Firmware::currentVersion() ?: 'unknown')) . PHP_EOL . PHP_EOL;
		echo Console::clr('text', 'Patches in ' . Firmware::dir() . ':') . PHP_EOL;

		foreach (Firmware::patches() as $patch) {
			echo '  ' . $patch['file'] . '  ' . ($patch['version'] ?: '-') . '  ' . round($patch['size'] / 1048576, 1) . ' MB' . ($patch['error'] ? '  ERROR: ' . $patch['error'] : '') . PHP_EOL;
		}

		echo PHP_EOL . Console::clr('text', 'Backups:') . PHP_EOL;

		foreach (Firmware::backups() as $backup) {
			echo "  $backup" . PHP_EOL;
		}

		return 0;
	}
}
