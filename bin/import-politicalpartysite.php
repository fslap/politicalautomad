<?php
/*
 * Convert the content of politicalpartysite (index.artox, or config/sections.php and
 * config/data/*.php as fallback) into Automad page data with party blocks.
 *
 * The result is a data folder override with the same structure as the Automad installation:
 *
 *   <out>/pages/data      the home page with all sections as party blocks
 *   <out>/pages/index     the (empty) order of sub pages
 *   <out>/shared/data     the global data with the site name
 *   <out/MEDIA.txt        the files that are referenced by the content
 *
 * Secrets of the original configuration (SMTP credentials, Turnstile secret) are never copied.
 *
 * Usage:
 *
 *   php bin/import-politicalpartysite.php /path/to/politicalpartysite /path/to/output [/path/to/current/shared/data]
 */

if (PHP_SAPI !== 'cli') {
	exit('This script must be run from the command line.');
}

if ($argc < 3) {
	fwrite(STDERR, "Usage: php bin/import-politicalpartysite.php <politicalpartysite> <output> [shared/data]\n");
	exit(1);
}

[$script, $sourceDir, $outDir] = $argv;
$sharedSource = $argv[3] ?? dirname(__DIR__) . '/shared/data';
$sourceDir = rtrim($sourceDir, '/');
$outDir = rtrim($outDir, '/');

if (!is_file("$sourceDir/index.artox") && !is_file("$sourceDir/config/sections.php")) {
	fwrite(STDERR, "Neither $sourceDir/index.artox nor $sourceDir/config/sections.php found.\n");
	exit(1);
}

/**
 * Keys of the original configuration that are never copied.
 */
const SECRET_KEYS = array('smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'turnstile_secret', 'mail_verify_cert', 'from_email', 'form_handler', 'form_template');

/**
 * Keys that only describe the original section and are not needed.
 */
const DROP_KEYS = array('section_type', 'layer_count', 'animation_type', 'scene_type', 'overflow');

$media = array();

/**
 * Generate a block id.
 *
 * @return string
 */
function blockId(): string {
	return substr(str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode(random_bytes(24))), 0, 32);
}

/**
 * Remember a referenced file.
 *
 * @param mixed $value
 * @param array $media
 */
function collectMedia(mixed $value, array &$media): void {
	if (is_array($value)) {
		foreach ($value as $item) {
			collectMedia($item, $media);
		}

		return;
	}

	if (!is_string($value)) {
		return;
	}

	foreach (explode(',', $value) as $part) {
		$part = trim((string) preg_replace('/\?.*$/', '', trim($part)));

		if (preg_match('/^[\w.\-]+\.(jpe?g|png|gif|webp|svg|pdf|mp3|mp4)$/i', $part)) {
			$media[$part] = true;
		}
	}
}

/**
 * Split a "desktop,mobile" image value into two fields.
 *
 * @param array $data
 * @param string $key
 * @return array
 */
function splitImageField(array $data, string $key): array {
	if (!isset($data[$key]) || !is_string($data[$key])) {
		return $data;
	}

	$images = array_values(array_filter(array_map('trim', explode(',', $data[$key]))));
	$data[$key] = $images[0] ?? '';

	if (isset($images[1]) && $images[1] !== $images[0]) {
		$data["{$key}_mobile"] = $images[1];
	}

	return $data;
}

/**
 * Convert a map configuration of a split pane.
 *
 * @param array $map
 * @return array
 */
function convertMapConfig(array $map): array {
	$height = (string) ($map['height'] ?? '320px');

	return array(
		'center' => (string) ($map['center'] ?? (isset($map['center_lat'], $map['center_lng']) ? $map['center_lat'] . ',' . $map['center_lng'] : '51.0,10.0')),
		'zoom' => (int) ($map['zoom'] ?? 6),
		'height' => preg_match('/^\d+(px|vh|rem|em|%)$/', $height) ? $height : '520px',
		'markers' => is_array($map['markers'] ?? null) ? $map['markers'] : array(),
		'geojson' => is_array($map['geojson'] ?? null) ? $map['geojson'] : null
	);
}

/**
 * Convert the configuration of a section into the data of a party block.
 *
 * @param string $component
 * @param array $config
 * @return array
 */
function convertSection(string $component, array $config): array {
	foreach (array_merge(SECRET_KEYS, DROP_KEYS) as $key) {
		unset($config[$key]);
	}

	switch ($component) {
		case 'Markdown':
		case 'Quote':
		case 'Split':
			$config = splitImageField($config, 'section_image');

			break;
		case 'SecurityConcept':
			$config = splitImageField($config, 'main_image');

			break;
		case 'Form':
			// The original form posted to an external handler, Automad sends the mails itself.
			$config['mail_to'] = '';
			$config['form_action'] = '';

			break;
		case 'ArtMotivation':
			unset($config['section_image'], $config['gallery_type'], $config['float_style']);
			$config['generate_mobile'] = false;

			break;
	}

	if ($component === 'Split') {
		$panes = array();

		foreach (array('left', 'right') as $side) {
			$type = (string) ($config["{$side}_type"] ?? 'markdown');
			$content = (string) ($config["{$side}_content"] ?? '');

			$panes[] = array(
				'title' => '',
				'type' => $type,
				'width' => 'auto',
				'content' => $type === 'markdown' ? $content : '',
				'image' => $type === 'image' ? $content : '',
				'map_config' => convertMapConfig(is_array($config["{$side}_map_config"] ?? null) ? $config["{$side}_map_config"] : array()),
				'classes' => ''
			);

			unset($config["{$side}_type"], $config["{$side}_content"], $config["{$side}_map_config"]);
		}

		$config['panes'] = $panes;
	}

	return $config;
}

/**
 * Read all sections. index.artox is the complete source of the original site: the generated
 * config/sections.php reuses file names (e.g. ticket.php) for different sections. The parser of
 * the original artox-to-timber-converter.php is used in memory in order to read the content exactly
 * like the original site did.
 *
 * @param string $sourceDir
 * @return array<int, array{component: string, config: array, source: string}>
 */
function readSections(string $sourceDir): array {
	$artox = "$sourceDir/index.artox";
	$converterFile = "$sourceDir/artox-to-timber-converter.php";

	if (is_file($artox) && is_file($converterFile)) {
		// Only load the class, the converter runs its own CLI code when it is included directly.
		if (!class_exists('ArtoxToTimberConverter', false)) {
			$code = (string) file_get_contents($converterFile);
			$cli = strpos($code, "if (php_sapi_name() === 'cli')");
			$classFile = tempnam(sys_get_temp_dir(), 'artox') . '.php';

			file_put_contents($classFile, $cli !== false ? substr($code, 0, $cli) : $code);
			require_once $classFile;
			unlink($classFile);
		}

		$converter = new ArtoxToTimberConverter();
		$call = function (string $method, mixed ...$args) use ($converter): mixed {
			$reflection = new ReflectionMethod($converter, $method);

			return $reflection->invoke($converter, ...$args);
		};

		$call('parseArtoxFormat', (string) file_get_contents($artox));

		$property = new ReflectionProperty($converter, 'sections');
		$sections = array();

		foreach ($property->getValue($converter) as $index => $section) {
			$sections[] = array(
				'component' => (string) $call('getComponentClass', $section['type']),
				'config' => (array) $call('convertSectionToConfig', $section),
				'source' => "index.artox #$index " . $section['type']
			);
		}

		return $sections;
	}

	$sections = array();

	foreach (require "$sourceDir/config/sections.php" as $index => $section) {
		$file = "$sourceDir/config/data/" . ($section['config'] ?? '');

		if (is_file($file)) {
			$sections[] = array(
				'component' => (string) ($section['component'] ?? ''),
				'config' => require $file,
				'source' => 'config/data/' . basename($file)
			);
		}
	}

	return $sections;
}

$blocks = array();
$report = array();

foreach (readSections($sourceDir) as $index => $section) {
	$component = $section['component'];
	$config = $section['config'];

	if ($component === '' || !is_file(dirname(__DIR__) . "/automad/src/server/Party/$component/$component.php")) {
		$report[] = "skipped #$index ($component): no party component";

		continue;
	}

	$toEmail = (string) ($config['to_email'] ?? '');
	$data = convertSection($component, $config);

	if ($component === 'Form' && $toEmail !== '') {
		$data['mail_to'] = $toEmail;
	}

	collectMedia($data, $media);

	$blocks[] = array(
		'id' => blockId(),
		'type' => 'party' . $component,
		'data' => $data,
		'tunes' => array('layout' => array('stretched' => true))
	);

	$report[] = sprintf('#%02d %-16s %s', $index, $component, $section['source']);
}

$now = date('c');
$page = array(
	'draft' => array(),
	'published' => array(
		'title' => 'Freie Soziale-Libertäre Alternative Partei',
		'template' => 'page_full_width_left',
		'checkboxHideTitle' => '1',
		'checkboxCompactLayout' => '1',
		'checkboxHidePreviousAndNextPageNavigation' => '1',
		'private' => false,
		'+main' => array('blocks' => $blocks),
		':created' => $now,
		':lastModified' => $now,
		':lastPublished' => $now
	)
);

$shared = is_readable($sharedSource) ? (array) json_decode((string) file_get_contents($sharedSource), true) : array();
$shared['draft'] = array();
$shared['published'] = array_merge($shared['published'] ?? array(), array(
	'sitename' => 'Freie Soziale-Libertäre Alternative Partei',
	':lastModified' => $now,
	':lastPublished' => $now
));

@mkdir("$outDir/pages", 0755, true);
@mkdir("$outDir/shared", 0755, true);

$flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

file_put_contents("$outDir/pages/data", json_encode($page, $flags));
file_put_contents("$outDir/pages/index", json_encode(array(), $flags));
file_put_contents("$outDir/shared/data", json_encode($shared, $flags));

ksort($media);
file_put_contents(
	"$outDir/MEDIA.txt",
	"Diese Dateien werden von den Inhalten referenziert und gehören in den Ordner pages/ (Dateien der Startseite).\n"
	. "Im Dashboard: Startseite > Files > hochladen.\n\n"
	. join("\n", array_keys($media)) . "\n"
);

echo join(PHP_EOL, $report), PHP_EOL;
echo count($blocks) . ' sections converted, ' . count($media) . ' referenced files (see MEDIA.txt).' . PHP_EOL;
