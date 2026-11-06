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
 * Apply a firmware patch.
 *
 * @author Florian Leon Steenbuck
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class FirmwareApply extends AbstractCommand {
	public function __construct() {
		$this->ArgumentCollection = new ArgumentCollection(array(
			new Argument('file', 'The patch archive (file name inside of the firmware directory or a path). Defaults to the newest patch.')
		));
	}

	public function description(): string {
		return 'Apply a firmware patch (tar.gz created with "php bin/dist.php --patch"). Content and settings are kept.';
	}

	public function example(): string {
		return 'php automad/console firmware:apply --file politicalautomad-firmware-2.0.0.tar.gz';
	}

	public function name(): string {
		return 'firmware:apply';
	}

	public function run(): int {
		$file = $this->ArgumentCollection->value('file');

		if (!$file) {
			$file = Firmware::patches()[0]['file'] ?? '';
		}

		if (!$file) {
			echo Console::clr('error', 'No patch found in ' . Firmware::dir()) . PHP_EOL;

			return 1;
		}

		try {
			$result = Firmware::apply($file, function (string $message): void {
				echo Console::clr('text', $message) . PHP_EOL;
			});
		} catch (\Throwable $e) {
			echo Console::clr('error', $e->getMessage()) . PHP_EOL;

			return 1;
		}

		echo Console::clr('heading', 'Firmware ' . ($result['version'] ?: $file) . ' installed. Backup: ' . $result['backup']) . PHP_EOL;

		return 0;
	}
}
