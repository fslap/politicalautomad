<?php
/*
 * politicalautomad web installer
 *
 * Upload this file together with the politicalautomad-*.zip archive (created by bin/dist.php)
 * into the directory your domain points to and open https://your-domain/install.php.
 *
 * The archive is extracted on the server in small steps to avoid timeouts, the requirements
 * are checked and the installer removes itself and the archive when finished.
 *
 * For safety reasons the installer refuses to run when a user account already exists.
 * Existing content (config, pages, shared) is never overwritten.
 */

declare(strict_types=1);

const STEP_SIZE = 300;
const REQUIRED_PHP = '8.2.0';
const KEEP_EXISTING = array('config/', 'pages/', 'shared/', 'cache/');

@set_time_limit(120);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

$dir = __DIR__;
$self = basename(__FILE__);

/**
 * Escape a string for HTML.
 *
 * @param string $value
 * @return string
 */
function e(string $value): string {
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Render the page and exit.
 *
 * @param string $title
 * @param string $body
 * @param string|null $refresh URL to continue with automatically
 */
function page(string $title, string $body, ?string $refresh = null): void {
	$meta = $refresh ? '<meta http-equiv="refresh" content="0;url=' . e($refresh) . '">' : '';

	echo <<<HTML
		<!DOCTYPE html>
		<html lang="de">
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title>{$title}</title>
			{$meta}
			<style>
				body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; background: #f5f5f4; color: #1c1d20; margin: 0; }
				main { max-width: 46rem; margin: 3rem auto; padding: 2rem; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
				h1 { margin-top: 0; font-size: 1.5rem; }
				table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
				td { padding: .4rem .5rem; border-bottom: 1px solid #eee; vertical-align: top; }
				.ok { color: #2e7d32; font-weight: 600; } .warn { color: #b26a00; font-weight: 600; } .err { color: #c62828; font-weight: 600; }
				.btn { display: inline-block; padding: .7rem 1.3rem; background: #bd2f3e; color: #fff; border-radius: 6px; text-decoration: none; font-weight: 600; border: 0; cursor: pointer; font-size: 1rem; }
				.bar { height: 10px; background: #eee; border-radius: 5px; overflow: hidden; } .bar > div { height: 100%; background: #bd2f3e; }
				code { background: #f1f1f1; padding: .1rem .3rem; border-radius: 3px; }
			</style>
		</head>
		<body><main><h1>{$title}</h1>{$body}</main></body>
		</html>
		HTML;
	exit;
}

/**
 * Find the dist archive next to the installer.
 *
 * @param string $dir
 * @return string|null
 */
function findArchive(string $dir): ?string {
	$files = glob($dir . '/politicalautomad-*.zip') ?: array();
	usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

	return $files[0] ?? null;
}

/**
 * Remove a directory recursively.
 *
 * @param string $path
 */
function removeDir(string $path): void {
	if (!is_dir($path)) {
		return;
	}

	foreach (scandir($path) ?: array() as $item) {
		if ($item === '.' || $item === '..') {
			continue;
		}

		$full = "$path/$item";
		is_dir($full) && !is_link($full) ? removeDir($full) : @unlink($full);
	}

	@rmdir($path);
}

$installed = is_file("$dir/config/accounts.php");
$action = $_GET['action'] ?? '';

// ---------------------------------------------------------------------------
// Finish

if ($action === 'finish') {
	$archive = findArchive($dir);

	if ($archive) {
		@unlink($archive);
	}

	@unlink("$dir/.install-progress");

	$selfRemoved = @unlink(__FILE__);
	$dashboard = 'dashboard';

	page(
		'Installation abgeschlossen',
		'<p class="ok">politicalautomad ist installiert.</p>'
		. ($selfRemoved ? '' : '<p class="warn">Bitte lösche <code>' . e($self) . '</code> manuell per FTP.</p>')
		. '<p>Nächste Schritte:</p><ol>'
		. '<li>Öffne das Dashboard und lege den ersten Benutzer an.</li>'
		. '<li>Automad bietet dir die Datei <code>accounts.php</code> zum Download an &ndash; lade sie per FTP in den Ordner <code>config/</code> hoch.</li>'
		. '<li>Melde dich an und richte im Assistenten die E-Mail-Einstellungen ein.</li></ol>'
		. '<p><a class="btn" href="' . $dashboard . '">Zum Dashboard</a> &nbsp; <a href="./">Zur Website</a></p>'
	);
}

if ($installed) {
	page(
		'Bereits installiert',
		'<p class="warn">In diesem Ordner gibt es bereits Benutzerkonten (<code>config/accounts.php</code>). '
		. 'Der Web-Installer läuft aus Sicherheitsgründen nur bei Neuinstallationen.</p>'
		. '<p>Updates bitte per SSH mit <code>php bin/dist.php --target=…</code> einspielen und <code>' . e($self) . '</code> löschen.</p>'
	);
}

// ---------------------------------------------------------------------------
// Checks

$archive = findArchive($dir);
$checks = array();
$fatal = false;

$phpOk = version_compare(PHP_VERSION, REQUIRED_PHP, '>=');
$checks[] = array('PHP-Version ' . PHP_VERSION, $phpOk ? 'ok' : 'err', $phpOk ? '' : 'Mindestens PHP 8.2 nötig &ndash; bei lima-city unter Domains &rarr; PHP-Version umstellen.');
$fatal = $fatal || !$phpOk;

foreach (array('zip' => true, 'json' => true, 'mbstring' => true, 'curl' => false, 'gd' => false) as $extension => $required) {
	$loaded = extension_loaded($extension);
	$checks[] = array("PHP-Erweiterung $extension", $loaded ? 'ok' : ($required ? 'err' : 'warn'), $loaded ? '' : ($required ? 'Wird benötigt.' : 'Empfohlen.'));
	$fatal = $fatal || ($required && !$loaded);
}

$writable = is_writable($dir);
$checks[] = array('Schreibrechte im Ordner', $writable ? 'ok' : 'err', $writable ? '' : 'PHP darf hier keine Dateien anlegen.');
$fatal = $fatal || !$writable;

$checks[] = array('Archiv', $archive ? 'ok' : 'err', $archive ? e(basename($archive)) . ' (' . round(filesize($archive) / 1048576, 1) . ' MB)' : 'Keine politicalautomad-*.zip neben dem Installer gefunden.');
$fatal = $fatal || !$archive;

if (function_exists('apache_get_modules')) {
	$rewrite = in_array('mod_rewrite', apache_get_modules(), true);
	$checks[] = array('Apache mod_rewrite', $rewrite ? 'ok' : 'warn', $rewrite ? '' : 'Für schöne URLs empfohlen.');
}

$blocking = array();

foreach (array('index.html', 'index.htm', 'default.html', 'default.htm') as $file) {
	if (is_file("$dir/$file")) {
		$blocking[] = $file;
	}
}

if ($blocking) {
	$checks[] = array('Platzhalter-Startseite', 'warn', e(join(', ', $blocking)) . ' hat Vorrang vor index.php und wird beim Installieren umbenannt.');
}

// ---------------------------------------------------------------------------
// Extract in steps

if ($action === 'extract' && !$fatal) {
	$zip = new ZipArchive();

	if ($zip->open($archive) !== true) {
		page('Fehler', '<p class="err">Das Archiv konnte nicht geöffnet werden. Ist der Upload vollständig?</p>');
	}

	$total = $zip->numFiles;
	$start = max(0, (int) ($_GET['offset'] ?? 0));
	$end = min($total, $start + STEP_SIZE);

	if ($start === 0) {
		foreach ($blocking as $file) {
			@rename("$dir/$file", "$dir/$file.bak");
		}
	}

	for ($i = $start; $i < $end; $i++) {
		$name = (string) $zip->getNameIndex($i);

		// Never extract paths outside of the installation directory.
		if ($name === '' || str_contains($name, '..') || str_starts_with($name, '/') || str_contains($name, '\\')) {
			continue;
		}

		$target = "$dir/$name";

		if (str_ends_with($name, '/')) {
			if (!is_dir($target)) {
				@mkdir($target, 0755, true);
			}

			continue;
		}

		$keep = false;

		foreach (KEEP_EXISTING as $prefix) {
			if (str_starts_with($name, $prefix) && file_exists($target)) {
				$keep = true;
			}
		}

		if ($keep) {
			continue;
		}

		if (!is_dir(dirname($target))) {
			@mkdir(dirname($target), 0755, true);
		}

		$content = $zip->getFromIndex($i);

		if ($content === false || @file_put_contents($target, $content) === false) {
			$zip->close();
			page('Fehler', '<p class="err">Die Datei <code>' . e($name) . '</code> konnte nicht geschrieben werden.</p><p><a class="btn" href="?action=extract&amp;offset=' . $i . '">Erneut versuchen</a></p>');
		}
	}

	$zip->close();
	file_put_contents("$dir/.install-progress", (string) $end);

	if ($end < $total) {
		$percent = (int) floor($end / $total * 100);
		$next = "?action=extract&offset=$end";

		page(
			'Installiere &hellip;',
			"<p>$end von $total Dateien entpackt ($percent %).</p><div class=\"bar\"><div style=\"width: $percent%\"></div></div>"
			. '<p><small>Die Seite lädt automatisch weiter. Falls nicht: <a href="' . e($next) . '">weiter</a>.</small></p>',
			$next
		);
	}

	// Verify the result.
	$missing = array();

	foreach (array('index.php', '.htaccess', 'automad/src/server/App.php', 'automad/dist/build/admin/index.js', 'automad/dist/build/party/index.css', 'lib/vendor/autoload.php', 'vendor/autoload.php', 'packages/automad/standard-lite') as $file) {
		if (!file_exists("$dir/$file")) {
			$missing[] = $file;
		}
	}

	if ($missing) {
		page('Unvollständig', '<p class="err">Folgende Dateien fehlen nach dem Entpacken:</p><ul><li><code>' . join('</code></li><li><code>', array_map('e', $missing)) . '</code></li></ul><p>Bitte das Archiv erneut hochladen und den Installer neu starten.</p><p><a class="btn" href="?action=extract&amp;offset=0">Neu starten</a></p>');
	}

	page(
		'Dateien entpackt',
		"<p class=\"ok\">Alle $total Dateien wurden erfolgreich entpackt.</p>"
		. '<p>Im letzten Schritt werden das Archiv und der Installer gelöscht.</p>'
		. '<p><a class="btn" href="?action=finish">Installation abschließen</a></p>'
	);
}

// ---------------------------------------------------------------------------
// Overview

$rows = '';

foreach ($checks as [$label, $state, $info]) {
	$icon = array('ok' => '✔', 'warn' => '!', 'err' => '✖')[$state];
	$rows .= "<tr><td class=\"$state\">$icon</td><td>$label</td><td>$info</td></tr>";
}

$resume = (int) @file_get_contents("$dir/.install-progress");
$button = $fatal
	? '<p class="err">Bitte zuerst die rot markierten Punkte beheben und die Seite neu laden.</p>'
	: '<p><a class="btn" href="?action=extract&amp;offset=0">Jetzt installieren</a>'
		. ($resume > 0 ? ' &nbsp; <a href="?action=extract&amp;offset=' . $resume . '">Abgebrochene Installation fortsetzen</a>' : '')
		. '</p>';

page(
	'politicalautomad installieren',
	'<p>Installationsordner: <code>' . e($dir) . '</code></p>'
	. "<table>$rows</table>"
	. $button
);
