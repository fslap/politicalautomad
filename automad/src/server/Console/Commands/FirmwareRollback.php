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
 * Restore the code from a firmware backup.
 *
 * @author Florian Leon Steenbuck
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class FirmwareRollback extends AbstractCommand {
	public function __construct() {
		$this->ArgumentCollection = new ArgumentCollection(array(
			new Argument('backup', 'The backup name (see firmware:list). Defaults to the latest backup.')
		));
	}

	public function description(): string {
		return 'Restore the code of the installation from a firmware backup.';
	}

	public function example(): string {
		return 'php automad/console firmware:rollback';
	}

	public function name(): string {
		return 'firmware:rollback';
	}

	public function run(): int {
		try {
			$name = Firmware::rollback($this->ArgumentCollection->value('backup') ?: null, function (string $message): void {
				echo Console::clr('text', $message) . PHP_EOL;
			});
		} catch (\Throwable $e) {
			echo Console::clr('error', $e->getMessage()) . PHP_EOL;

			return 1;
		}

		echo Console::clr('heading', "Backup $name restored.") . PHP_EOL;

		return 0;
	}
}
