<?php
/*
 * Build or deploy a ready-to-use version of politicalautomad with PHP and Composer only.
 *
 * Node is NOT required, the compiled assets are part of the repository (automad/dist).
 * After changing client code (automad/src/client) run "npm run build" on a development
 * machine and commit automad/dist.
 *
 * Usage:
 *
 *   php bin/dist.php
 *       Creates an installable zip archive and the web installer install.php in ./build.
 *       Upload both files to the web space and open https://your-domain/install.php
 *
 *   php bin/dist.php --target=/path/to/webspace
 *       Installs or updates the site directly in the given directory.
 *       Existing content and settings (config, pages, shared) are never overwritten.
 *
 * Options:
 *
 *   --target=DIR        install/update into DIR instead of creating a zip
 *   --out=DIR           output directory of the zip (default: ./build)
 *   --composer=CMD      composer command (default: "composer", falls back to composer.phar)
 *   --skip-composer     use the existing vendor directories of the source instead of running composer
 *   --help              show this help
 */

if (PHP_SAPI !== 'cli') {
	exit('This script must be run from the command line.');
}

const REQUIRED_PHP = '8.2.0';

/**
 * The files and directories that are part of a dist package.
 */
const DIST_ITEMS = array(
	'automad',
	'cache',
	'config',
	'lib',
	'packages',
	'pages',
	'shared',
	'vendor',
	'.htaccess',
	'composer.json',
	'composer.lock',
	'index.php',
	'LICENSE.md',
	'LICENSE_PARTY_PURPOSE.md',
	'MULTI_LICENSE_PARTY_PURPOSE.md'
);

/**
 * Paths (relative to the source root) that are never copied.
 */
const EXCLUDE = array(
	'automad/src/client',
	'automad/tests',
	'automad/.wizard-completed',
	'config/accounts.php',
	'node_modules'
);

/**
 * Directories that contain user data and are only created on a fresh installation.
 */
const USER_DATA = array('config', 'pages', 'shared', 'cache');

/**
 * Directories that are replaced as a whole on updates.
 */
const REPLACE = array('automad', 'lib', 'vendor');

$options = getopt('', array('target:', 'out:', 'composer:', 'skip-composer', 'help'));

if (isset($options['help'])) {
	preg_match('/\/\*(.*?)\*\//s', (string) file_get_contents(__FILE__), $matches);
	echo preg_replace('/^ ?\* ?/m', '', $matches[1] ?? '') . PHP_EOL;
	exit(0);
}

$source = dirname(__DIR__);

/**
 * Print a message.
 *
 * @param string $message
 */
function out(string $message): void {
	echo $message . PHP_EOL;
}

/**
 * Print a section title.
 *
 * @param string $title
 */
function section(string $title): void {
	out(str_repeat('-', 75));
	out($title);
}

/**
 * Exit with an error.
 *
 * @param string $message
 */
function fail(string $message): void {
	fwrite(STDERR, PHP_EOL . 'ERROR: ' . $message . PHP_EOL);
	exit(1);
}

/**
 * Normalize a path to forward slashes.
 *
 * @param string $path
 * @return string
 */
function normalize(string $path): string {
	return str_replace('\\', '/', $path);
}

/**
 * Test whether a relative path is excluded.
 *
 * @param string $relative
 * @return bool
 */
function isExcluded(string $relative): bool {
	$relative = normalize($relative);

	foreach (EXCLUDE as $exclude) {
		if ($relative === $exclude || str_starts_with($relative, $exclude . '/')) {
			return true;
		}
	}

	// Version control directories and files, e.g. inside of vendor packages.
	return (bool) preg_match('#(^|/)(\.git|\.svn|\.hg)(/|$)|(^|/)\.gitignore$#', $relative);
}

/**
 * Copy a file or a directory recursively.
 *
 * @param string $from
 * @param string $to
 * @param string $relative the path relative to the source root used for exclusions
 * @param bool $overwrite
 */
function copyPath(string $from, string $to, string $relative = '', bool $overwrite = true): void {
	if ($relative !== '' && isExcluded($relative)) {
		return;
	}

	if (is_link($from)) {
		$from = (string) realpath($from);
	}

	if (is_dir($from)) {
		if (!is_dir($to) && !mkdir($to, 0755, true)) {
			fail("Can't create directory $to");
		}

		foreach (scandir($from) ?: array() as $item) {
			if ($item === '.' || $item === '..') {
				continue;
			}

			copyPath("$from/$item", "$to/$item", ltrim("$relative/$item", '/'), $overwrite);
		}

		return;
	}

	if (!$overwrite && file_exists($to)) {
		return;
	}

	if (!is_dir(dirname($to))) {
		mkdir(dirname($to), 0755, true);
	}

	if (!copy($from, $to)) {
		fail("Can't copy $from to $to");
	}
}

/**
 * Remove a file or directory recursively.
 *
 * @param string $path
 */
function removePath(string $path): void {
	if (is_link($path) || is_file($path)) {
		unlink($path);

		return;
	}

	if (!is_dir($path)) {
		return;
	}

	foreach (scandir($path) ?: array() as $item) {
		if ($item !== '.' && $item !== '..') {
			removePath("$path/$item");
		}
	}

	rmdir($path);
}

/**
 * Run a shell command in a directory and stream its output.
 *
 * @param string $command
 * @param string $cwd
 * @return int the exit code
 */
function run(string $command, string $cwd): int {
	if (!function_exists('proc_open')) {
		fail('proc_open() is disabled. Run composer manually and use --skip-composer.');
	}

	out("> $command");

	$process = proc_open($command, array(STDIN, STDOUT, STDERR), $pipes, $cwd);

	if (!is_resource($process)) {
		return 1;
	}

	return proc_close($process);
}

/**
 * Find the composer command.
 *
 * @param string|null $custom
 * @param string $source
 * @return string
 */
function findComposer(?string $custom, string $source): string {
	if ($custom) {
		return $custom;
	}

	foreach (array(getcwd() . '/composer.phar', "$source/composer.phar") as $phar) {
		if (is_file($phar)) {
			return escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($phar);
		}
	}

	return 'composer';
}

/**
 * Return the version information of the source.
 *
 * @param string $source
 * @return string
 */
function version(string $source): string {
	$composer = json_decode((string) @file_get_contents("$source/package.json"), true);
	$version = $composer['version'] ?? 'dev';
	$commit = '';
	$branch = '';

	if (is_dir("$source/.git") && function_exists('shell_exec')) {
		$commit = trim((string) @shell_exec('git -C ' . escapeshellarg($source) . ' rev-parse --short HEAD 2>/dev/null'));
		$branch = trim((string) @shell_exec('git -C ' . escapeshellarg($source) . ' rev-parse --abbrev-ref HEAD 2>/dev/null'));
	}

	return trim("$version $commit" . ($branch ? " ($branch)" : ''));
}

/**
 * Add a directory to a zip archive recursively.
 *
 * @param ZipArchive $zip
 * @param string $dir
 * @param string $prefix
 */
function zipDir(ZipArchive $zip, string $dir, string $prefix = ''): void {
	foreach (scandir($dir) ?: array() as $item) {
		if ($item === '.' || $item === '..') {
			continue;
		}

		$path = "$dir/$item";
		$name = ltrim("$prefix/$item", '/');

		if (is_dir($path)) {
			$zip->addEmptyDir($name);
			zipDir($zip, $path, $name);
		} else {
			$zip->addFile($path, $name);
		}
	}
}

// ---------------------------------------------------------------------------
// Checks

section('Checking requirements ...');

if (version_compare(PHP_VERSION, REQUIRED_PHP, '<')) {
	fail('PHP ' . REQUIRED_PHP . ' or newer is required, this is PHP ' . PHP_VERSION . '. Use e.g. "php8.3 bin/dist.php".');
}

out('PHP ' . PHP_VERSION);

foreach (array('curl', 'gd', 'mbstring', 'json', 'zip') as $extension) {
	if (!extension_loaded($extension)) {
		out("Warning: the PHP extension \"$extension\" is not loaded.");
	}
}

foreach (array('automad/dist/build/admin/index.js', 'automad/dist/build/party/index.css', 'automad/dist/build/blocks/index.js') as $asset) {
	if (!is_file("$source/$asset")) {
		fail("The compiled asset $asset is missing. Run \"npm run build\" on a development machine and commit automad/dist.");
	}
}

$target = $options['target'] ?? null;
$skipComposer = isset($options['skip-composer']);

if (!$target && !class_exists(ZipArchive::class)) {
	fail('The PHP extension "zip" is required to create an archive. Use --target to install directly instead.');
}

// ---------------------------------------------------------------------------
// Staging

$version = version($source);
$staging = "$source/build/.staging";

section("Preparing $version ...");

removePath($staging);
mkdir($staging, 0755, true);

foreach (DIST_ITEMS as $item) {
	if (!file_exists("$source/$item")) {
		continue;
	}

	// Dependencies are installed by composer below.
	if (!$skipComposer && in_array($item, array('vendor', 'packages'), true)) {
		continue;
	}

	if (!$skipComposer && $item === 'lib') {
		foreach (array('composer.json', 'composer.lock', '.htaccess') as $file) {
			if (is_file("$source/lib/$file")) {
				copyPath("$source/lib/$file", "$staging/lib/$file", "lib/$file");
			}
		}

		continue;
	}

	copyPath("$source/$item", "$staging/$item", $item);
}

// ---------------------------------------------------------------------------
// Dependencies

if (!$skipComposer) {
	section('Installing PHP dependencies ...');

	$composer = findComposer($options['composer'] ?? null, $source);
	$flags = '--no-dev --no-interaction --no-progress --optimize-autoloader --prefer-dist';

	putenv('COMPOSER_ALLOW_SUPERUSER=1');

	if (run("$composer install $flags", $staging) !== 0) {
		fail('composer install failed in the base directory.');
	}

	if (run("$composer install $flags", "$staging/lib") !== 0) {
		fail('composer install failed in the lib directory.');
	}

	// The package installer plugin moves the theme to packages, do that manually when plugins are disabled.
	if (!is_dir("$staging/packages/automad/standard-lite") && is_dir("$staging/vendor/automad/standard-lite")) {
		copyPath("$staging/vendor/automad/standard-lite", "$staging/packages/automad/standard-lite");
	}

	// Remove version control directories of source installs.
	foreach (array('vendor', 'lib/vendor', 'packages') as $dir) {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator("$staging/$dir", FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);

		$vcs = array();

		foreach ($iterator as $file) {
			if ($file->isDir() && in_array($file->getFilename(), array('.git', '.svn', '.hg'), true)) {
				$vcs[] = $file->getPathname();
			}
		}

		array_map('removePath', $vcs);
	}
}

foreach (array('lib/vendor/autoload.php', 'vendor/autoload.php', 'lib/vendor/mustache/mustache', 'lib/vendor/twig/twig') as $required) {
	if (!file_exists("$staging/$required")) {
		fail("$required is missing after installing the dependencies.");
	}
}

// Disable the dashboard updater since it would replace this fork with the upstream version.
touch("$staging/.no-updates");
file_put_contents("$staging/VERSION", $version . PHP_EOL);

// ---------------------------------------------------------------------------
// Zip

if (!$target) {
	section('Creating archive ...');

	$outDir = rtrim(normalize($options['out'] ?? "$source/build"), '/');

	if (!is_dir($outDir)) {
		mkdir($outDir, 0755, true);
	}

	$name = 'politicalautomad-' . preg_replace('/[^a-z0-9.\-]+/i', '-', trim($version)) . '.zip';
	$name = preg_replace(array('/-+/', '/-\.zip$/'), array('-', '.zip'), (string) $name);
	$file = "$outDir/$name";

	@unlink($file);

	$zip = new ZipArchive();

	if ($zip->open($file, ZipArchive::CREATE) !== true) {
		fail("Can't create $file");
	}

	zipDir($zip, $staging);
	$zip->close();
	removePath($staging);

	section('Done');
	out("Created $file (" . round(filesize($file) / 1024 / 1024, 1) . ' MB)');

	// The web installer extracts the archive on servers without shell access.
	copy(__DIR__ . '/install.php', "$outDir/install.php");
	out("Created $outDir/install.php");
	out('Upload both files to the web space and open https://your-domain/install.php');
	exit(0);
}

// ---------------------------------------------------------------------------
// Install or update

$target = rtrim(normalize($target), '/');

if (!is_dir($target) && !mkdir($target, 0755, true)) {
	fail("Can't create target directory $target");
}

$target = normalize((string) realpath($target));

if ($target === normalize((string) realpath($source))) {
	fail('The target must not be the source directory.');
}

$isUpdate = is_file("$target/index.php") && is_dir("$target/automad");

section(($isUpdate ? 'Updating' : 'Installing') . " $target ...");

foreach (scandir($staging) ?: array() as $item) {
	if ($item === '.' || $item === '..') {
		continue;
	}

	$from = "$staging/$item";
	$to = "$target/$item";

	if (in_array($item, USER_DATA, true)) {
		// Only add missing files, never overwrite content and settings.
		copyPath($from, $to, '', false);
		out("  kept   $item" . ($isUpdate ? ' (only missing files added)' : ''));

		continue;
	}

	if (in_array($item, REPLACE, true) && is_dir($to)) {
		$wizard = "$to/.wizard-completed";
		$keepWizard = $item === 'automad' && is_file($wizard);

		// Replace the directory as a whole in order to remove stale files.
		$old = "$to.old-" . time();
		$new = "$to.new-" . time();

		copyPath($from, $new);

		if ($keepWizard) {
			copy($wizard, "$new/.wizard-completed");
		}

		rename($to, $old);
		rename($new, $to);
		removePath($old);
		out("  update $item");

		continue;
	}

	if ($item === '.htaccess' && is_file($to)) {
		// Keep custom settings like SetEnv lines of an existing .htaccess.
		$custom = array_filter(
			preg_split('/\R/', (string) file_get_contents($to)) ?: array(),
			fn ($line) => preg_match('/^\s*SetEnv\s+PARTY_/i', $line)
		);

		copyPath($from, $to);

		if ($custom) {
			file_put_contents($to, PHP_EOL . join(PHP_EOL, $custom) . PHP_EOL, FILE_APPEND);
		}

		out('  update .htaccess' . ($custom ? ' (SetEnv lines kept)' : ''));

		continue;
	}

	// packages may contain other themes, only overwrite the files of the dist.
	copyPath($from, $to);
	out("  update $item");
}

removePath($staging);

// Clear the page cache after an update.
if ($isUpdate && is_file("$target/automad/console")) {
	run(escapeshellarg(PHP_BINARY) . ' automad/console cache:clear', $target);
}

section('Done');

if (!$isUpdate) {
	out('Open /dashboard in the browser to create the first user account.');
	out('Automad will offer the file accounts.php for download - upload it to ' . "$target/config/");
} else {
	out('Content, users and settings have been kept.');
}
