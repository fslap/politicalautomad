<?php
/*
 * International Political Party Zone for all Partys also Locally
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

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Firmware patches replace the code of an installation (automad, lib, vendor, packages and
 * the root files) with the content of a tar archive that is created by "php bin/dist.php --patch".
 * Content and settings (config, pages, shared, cache) are never part of a patch and are never touched.
 *
 * Patch archives are placed in the "firmware" directory of the installation. Before a patch is
 * applied, all replaced items are moved to "firmware/backups/<timestamp>" and can be restored.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
final class Firmware {
	/**
	 * The directory where patches and backups are stored, relative to the base directory.
	 */
	const DIR = '/firmware';

	/**
	 * Items that can be replaced by a patch. Everything else in an archive is rejected.
	 */
	const ITEMS = array(
		'automad',
		'lib',
		'vendor',
		'packages',
		'index.php',
		'.htaccess',
		'.no-updates',
		'composer.json',
		'composer.lock',
		'VERSION',
		'LICENSE.md',
		'LICENSE_PARTY_PURPOSE.md',
		'MULTI_LICENSE_PARTY_PURPOSE.md',
		self::MANIFEST
	);

	/**
	 * Files inside of replaced directories that belong to the installation and are kept.
	 */
	const KEEP = array(
		'automad/.setup-completed',
		'automad/.wizard-completed'
	);

	/**
	 * The name of the manifest file inside of a patch.
	 */
	const MANIFEST = 'firmware.json';

	/**
	 * The number of backups that are kept.
	 */
	const MAX_BACKUPS = 3;

	/**
	 * Apply a patch archive.
	 *
	 * @param string $name the file name of a patch inside of the firmware directory or an absolute path
	 * @param callable|null $log a callback that receives log messages
	 * @return array{version: string, backup: string}
	 */
	public static function apply(string $name, ?callable $log = null): array {
		$log = $log ?? function (string $message): void {
		};

		$file = self::resolve($name);

		try {
			$manifest = self::manifest($file);
		} catch (\Throwable $e) {
			$manifest = array();
		}
		$base = self::base();
		$tmp = self::dir() . '/.tmp-' . date('YmdHis');
		$backup = self::dir() . '/backups/' . date('Y-m-d_H-i-s');

		self::removePath($tmp);
		self::mkdir($tmp);

		try {
			$log('Entpacke ' . basename($file) . ' …');
			self::extract($file, $tmp);
			$root = self::findRoot($tmp);

			foreach (self::listTop($root) as $item) {
				if (!in_array($item, self::ITEMS, true)) {
					throw new \RuntimeException("Unerlaubter Eintrag im Patch: $item");
				}
			}

			if (!is_file("$root/automad/src/server/App.php") || !is_file("$root/index.php")) {
				throw new \RuntimeException('Der Patch ist unvollständig (automad/src/server/App.php oder index.php fehlt).');
			}

			self::mkdir($backup);

			foreach (self::listTop($root) as $item) {
				if ($item === self::MANIFEST) {
					continue;
				}

				$from = "$root/$item";
				$to = "$base/$item";

				foreach (self::KEEP as $keep) {
					if (str_starts_with($keep, "$item/") && file_exists("$base/$keep")) {
						self::mkdir(dirname("$root/$keep"));
						copy("$base/$keep", "$root/$keep");
					}
				}

				if (file_exists($to)) {
					if (!rename($to, "$backup/$item")) {
						throw new \RuntimeException("$item konnte nicht gesichert werden.");
					}
				}

				if (!rename($from, $to)) {
					// Restore the backup of this item before giving up.
					if (file_exists("$backup/$item")) {
						rename("$backup/$item", $to);
					}

					throw new \RuntimeException("$item konnte nicht ersetzt werden.");
				}

				$log("Ersetzt: $item");
			}

			file_put_contents("$backup/" . self::MANIFEST, json_encode(array(
				'replacedBy' => $manifest['version'] ?? basename($file),
				'previousVersion' => self::currentVersion(),
				'created' => date('c')
			), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		} finally {
			self::removePath($tmp);
		}

		self::pruneBackups();
		self::clearCache();

		$log('Fertig. Backup: ' . basename($backup));

		return array('version' => (string) ($manifest['version'] ?? ''), 'backup' => basename($backup));
	}

	/**
	 * Return the backups, newest first.
	 *
	 * @return string[]
	 */
	public static function backups(): array {
		$dirs = glob(self::dir() . '/backups/*', GLOB_ONLYDIR) ?: array();
		rsort($dirs);

		return array_map('basename', $dirs);
	}

	/**
	 * The version of the installation.
	 *
	 * @return string
	 */
	public static function currentVersion(): string {
		$file = self::base() . '/VERSION';

		return is_readable($file) ? trim((string) file_get_contents($file)) : '';
	}

	/**
	 * The firmware directory. It is created and protected on first use.
	 *
	 * @return string
	 */
	public static function dir(): string {
		$dir = self::base() . self::DIR;

		if (!is_dir($dir)) {
			self::mkdir($dir);
		}

		if (!is_file("$dir/.htaccess")) {
			file_put_contents("$dir/.htaccess", "Require all denied\nDeny from all\n");
		}

		return $dir;
	}

	/**
	 * List the available patches.
	 *
	 * @return array<int, array{file: string, size: int, modified: int, version: string, created: string}>
	 */
	public static function patches(): array {
		$files = array_merge(
			glob(self::dir() . '/*.tar.gz') ?: array(),
			glob(self::dir() . '/*.tgz') ?: array(),
			glob(self::dir() . '/*.tar') ?: array()
		);

		usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

		return array_map(function (string $file): array {
			try {
				$manifest = self::manifest($file);
			} catch (\Throwable $e) {
				$manifest = array('error' => $e->getMessage());
			}

			return array(
				'file' => basename($file),
				'size' => (int) filesize($file),
				'modified' => (int) filemtime($file),
				'version' => (string) ($manifest['version'] ?? ''),
				'created' => (string) ($manifest['created'] ?? ''),
				'error' => (string) ($manifest['error'] ?? '')
			);
		}, $files);
	}

	/**
	 * Restore a backup.
	 *
	 * @param string|null $name the backup name, defaults to the latest backup
	 * @param callable|null $log
	 * @return string the restored backup
	 */
	public static function rollback(?string $name = null, ?callable $log = null): string {
		$log = $log ?? function (string $message): void {
		};

		$backups = self::backups();
		$name = $name ?: ($backups[0] ?? '');

		if (!$name || !in_array($name, $backups, true)) {
			throw new \RuntimeException('Kein Backup gefunden.');
		}

		$dir = self::dir() . "/backups/$name";
		$base = self::base();
		$trash = self::dir() . '/.rollback-' . date('YmdHis');

		self::mkdir($trash);

		foreach (self::listTop($dir) as $item) {
			if ($item === self::MANIFEST || !in_array($item, self::ITEMS, true)) {
				continue;
			}

			foreach (self::KEEP as $keep) {
				if (str_starts_with($keep, "$item/") && file_exists("$base/$keep")) {
					self::mkdir(dirname("$dir/$keep"));
					copy("$base/$keep", "$dir/$keep");
				}
			}

			if (file_exists("$base/$item")) {
				rename("$base/$item", "$trash/$item");
			}

			rename("$dir/$item", "$base/$item");
			$log("Wiederhergestellt: $item");
		}

		self::removePath($trash);
		self::removePath($dir);
		self::clearCache();

		return $name;
	}

	/**
	 * The base directory of the installation.
	 *
	 * @return string
	 */
	private static function base(): string {
		return rtrim(AM_BASE_DIR, '/');
	}

	/**
	 * Clear the page cache.
	 */
	private static function clearCache(): void {
		try {
			\Automad\Core\Cache::clear();
		} catch (\Throwable $e) {
		}
	}

	/**
	 * Extract an archive with PharData or the tar command as fallback.
	 *
	 * @param string $file
	 * @param string $target
	 */
	private static function extract(string $file, string $target): void {
		if (class_exists(\PharData::class)) {
			try {
				$archive = self::openArchive($file);
				self::validateEntries($archive);
				$archive->extractTo($target, null, true);

				return;
			} catch (\RuntimeException $e) {
				if (str_starts_with($e->getMessage(), 'Unerlaubt')) {
					throw $e;
				}
			} catch (\Throwable $e) {
			}
		}

		if (!function_exists('exec')) {
			throw new \RuntimeException('Das Archiv konnte nicht entpackt werden (weder PharData noch tar verfügbar).');
		}

		$flags = preg_match('/\.(tar\.gz|tgz)$/i', $file) ? '-xzf' : '-xf';
		exec('tar ' . $flags . ' ' . escapeshellarg($file) . ' -C ' . escapeshellarg($target) . ' 2>&1', $output, $code);

		if ($code !== 0) {
			throw new \RuntimeException('tar ist fehlgeschlagen: ' . join(' ', $output));
		}

		// Check the extracted files for symlinks and paths outside of the target.
		$real = (string) realpath($target);
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($target, \FilesystemIterator::SKIP_DOTS));

		foreach ($iterator as $entry) {
			if ($entry->isLink() || !str_starts_with((string) realpath($entry->getPathname()), $real)) {
				throw new \RuntimeException('Unerlaubter Eintrag im Patch: ' . $entry->getFilename());
			}
		}
	}

	/**
	 * Find the root directory of an extracted patch (archives may contain a single top level directory).
	 *
	 * @param string $dir
	 * @return string
	 */
	private static function findRoot(string $dir): string {
		$items = self::listTop($dir);

		if (count($items) === 1 && is_dir("$dir/{$items[0]}") && !in_array($items[0], self::ITEMS, true)) {
			return "$dir/{$items[0]}";
		}

		return $dir;
	}

	/**
	 * List the top level items of a directory.
	 *
	 * @param string $dir
	 * @return string[]
	 */
	private static function listTop(string $dir): array {
		return array_values(array_filter(scandir($dir) ?: array(), fn ($item) => $item !== '.' && $item !== '..'));
	}

	/**
	 * Read the manifest of a patch.
	 *
	 * @param string $file
	 * @return array
	 */
	private static function manifest(string $file): array {
		if (!class_exists(\PharData::class)) {
			return array();
		}

		$archive = self::openArchive($file);

		foreach (array(self::MANIFEST, './' . self::MANIFEST) as $path) {
			if (isset($archive[$path])) {
				return (array) json_decode((string) file_get_contents($archive[$path]->getPathname()), true);
			}
		}

		return array();
	}

	/**
	 * Create a directory.
	 *
	 * @param string $dir
	 */
	private static function mkdir(string $dir): void {
		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
			throw new \RuntimeException("Das Verzeichnis $dir konnte nicht angelegt werden.");
		}
	}

	/**
	 * Open an archive with PharData. Phar can't handle file names with several dots
	 * like "politicalautomad-firmware-2.0.0-beta.68.tar.gz", therefore a link with a
	 * simple name is used.
	 *
	 * @param string $file
	 * @return \PharData
	 */
	private static function openArchive(string $file): \PharData {
		$extension = preg_match('/\.(tar\.gz|tgz)$/i', $file) ? 'tar.gz' : 'tar';
		$link = sys_get_temp_dir() . '/am-firmware-' . substr(sha1($file . filemtime($file)), 0, 12) . '.' . $extension;

		if (!file_exists($link) && !@symlink($file, $link)) {
			copy($file, $link);
		}

		return new \PharData($link);
	}

	/**
	 * Remove old backups.
	 */
	private static function pruneBackups(): void {
		foreach (array_slice(self::backups(), self::MAX_BACKUPS) as $old) {
			self::removePath(self::dir() . "/backups/$old");
		}
	}

	/**
	 * Remove a file or directory recursively.
	 *
	 * @param string $path
	 */
	private static function removePath(string $path): void {
		if (is_link($path) || is_file($path)) {
			unlink($path);

			return;
		}

		if (!is_dir($path)) {
			return;
		}

		foreach (self::listTop($path) as $item) {
			self::removePath("$path/$item");
		}

		rmdir($path);
	}

	/**
	 * Resolve a patch name to a file inside of the firmware directory.
	 *
	 * @param string $name
	 * @return string
	 */
	private static function resolve(string $name): string {
		$file = str_contains($name, '/') ? $name : self::dir() . '/' . $name;
		$real = realpath($file);

		if (!$real || !is_file($real) || !preg_match('/\.(tar|tar\.gz|tgz)$/i', $real)) {
			throw new \RuntimeException("Patch nicht gefunden: $name");
		}

		return $real;
	}

	/**
	 * Reject archives with entries outside of the allowed items.
	 *
	 * @param \PharData $archive
	 */
	private static function validateEntries(\PharData $archive): void {
		$prefix = 'phar://' . $archive->getPath() . '/';
		$iterator = new \RecursiveIteratorIterator($archive);

		foreach ($iterator as $entry) {
			$relative = substr((string) $entry->getPathname(), strlen($prefix));

			if (str_contains($relative, '..') || str_starts_with($relative, '/')) {
				throw new \RuntimeException("Unerlaubter Eintrag im Patch: $relative");
			}
		}
	}
}
