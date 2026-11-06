<?php
/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

namespace Automad\Controllers\Api;

use Automad\Api\Response;
use Automad\Core\Request;
use Automad\Party\Firmware;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * The firmware controller lists and applies firmware patches in the dashboard.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class FirmwareController {
	/**
	 * Apply a patch from the firmware directory.
	 *
	 * @return Response the response object
	 */
	public static function apply(): Response {
		$Response = new Response();
		$file = basename((string) Request::post('file'));
		$log = array();

		try {
			$result = Firmware::apply($file, function (string $message) use (&$log): void {
				$log[] = $message;
			});
		} catch (\Throwable $e) {
			return $Response->setError($e->getMessage())->setData(array('log' => $log));
		}

		return $Response
			->setSuccess('Firmware ' . ($result['version'] ?: $file) . ' wurde eingespielt. Backup: ' . $result['backup'])
			->setData(array('log' => $log, 'reload' => true));
	}

	/**
	 * Return the installed version, the available patches and backups.
	 *
	 * @return Response the response object
	 */
	public static function list(): Response {
		$Response = new Response();

		try {
			return $Response->setData(array(
				'version' => Firmware::currentVersion(),
				'directory' => Firmware::dir(),
				'patches' => Firmware::patches(),
				'backups' => Firmware::backups()
			));
		} catch (\Throwable $e) {
			return $Response->setError($e->getMessage());
		}
	}

	/**
	 * Restore a backup.
	 *
	 * @return Response the response object
	 */
	public static function rollback(): Response {
		$Response = new Response();

		try {
			$name = Firmware::rollback(basename((string) Request::post('backup')) ?: null);
		} catch (\Throwable $e) {
			return $Response->setError($e->getMessage());
		}

		return $Response->setSuccess("Backup $name wurde wiederhergestellt.")->setData(array('reload' => true));
	}
}
