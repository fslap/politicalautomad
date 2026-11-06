<?php
/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

/*
 * Convert the artox content of politicalpartysite into Automad page data with party blocks.
 *
 * The source is either an artox file (index.artox, site_data.txt) or the index.php of the
 * original site with the inline artox string ($siteData = '...';). Multi-line values (markdown,
 * GeoJSON of maps) are read completely.
 *
 * The result is a data folder override with the same structure as the Automad installation:
 *
 *   <out>/pages/data      the home page with all sections as party blocks
 *   <out>/pages/index     the (empty) order of sub pages
 *   <out>/shared/data     the global data with the site name
 *   <out>/MEDIA.txt       the files that are referenced by the content
 *
 * Secrets of the original configuration (SMTP credentials, Turnstile secret) are never copied.
 *
 * Usage:
 *
 *   php bin/import-politicalpartysite.php <index.php|index.artox|dir> <output> [--music=<dir>] [--shared=<shared/data>] [--example]
 *
 *   --music   directory with the track lists of the music players (economy.json, matching.json),
 *             e.g. fslap.de/cascade/inc of the original site
 *   --shared  current shared/data of the installation (the site name is merged into it)
 *   --example add an example YouTube video with time code comments
 */

if (PHP_SAPI !== 'cli') {
	exit('This script must be run from the command line.');
}

$args = array();
$options = array();

foreach (array_slice($argv, 1) as $arg) {
	if (preg_match('/^--(music|shared)=(.*)$/', $arg, $match)) {
		$options[$match[1]] = $match[2];
	} elseif ($arg === '--example') {
		$options['example'] = true;
	} else {
		$args[] = $arg;
	}
}

if (count($args) < 2) {
	fwrite(STDERR, "Usage: php bin/import-politicalpartysite.php <index.php|index.artox|dir> <output> [--music=<dir>] [--shared=<shared/data>]\n");
	exit(1);
}

[$source, $outDir] = $args;
$outDir = rtrim($outDir, '/');
$musicDir = rtrim($options['music'] ?? '', '/');
$sharedSource = $options['shared'] ?? dirname(__DIR__) . '/shared/data';

if (is_dir($source)) {
	foreach (array('index.artox', 'site_data.txt', 'index.php') as $file) {
		if (is_file("$source/$file")) {
			$source = "$source/$file";

			break;
		}
	}
}

if (!is_file($source)) {
	fwrite(STDERR, "No artox source found: $source\n");
	exit(1);
}

/**
 * Keys of the original configuration that are never copied.
 */
const SECRET_KEYS = array('smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_secure', 'turnstile_secret', 'mail_verify_cert', 'from_email', 'form_handler', 'form_template');

/**
 * Artox section types and the matching party components.
 */
const COMPONENTS = array(
	'PARTY_HEADER' => 'PartyHeader',
	'REGION_SCROLL' => 'RegionScroll',
	'CONTACT_CLOSER' => 'ContactCloser',
	'WATER_ANIMATION' => 'WaterAnimation',
	'SECURITY_CONCEPT' => 'SecurityConcept',
	'DOCUMENT_SECTION' => 'Documents',
	'ART_MOTIVATION' => 'ArtMotivation',
	'FORM_SECTION' => 'Form',
	'SPLIT_SECTION' => 'Split',
	'MARKDOWN_SECTION' => 'Markdown',
	'QUOTE_SECTION' => 'Quote',
	'LANDSCAPE_SCENE' => 'LandscapeScene',
	'MUSIC_ANCHOR' => 'MusicAnchor',
	'MUSIC_PLAYER' => 'MusicPlayer',
	'PLAIN_HTML' => 'ContactCloser'
);

// ---------------------------------------------------------------------------
// Parser

/**
 * Read the artox text of a source file. The index.php of the original site contains it
 * as single quoted PHP string.
 *
 * @param string $file
 * @return string
 */
function readArtox(string $file): string {
	$code = str_replace("\r\n", "\n", (string) file_get_contents($file));

	if (!preg_match('/\$siteData\s*=\s*\'/', $code, $match, PREG_OFFSET_CAPTURE)) {
		return $code;
	}

	// Read the single quoted PHP string up to the first unescaped quote. The string is too
	// large for a regular expression (backtrack limit).
	$start = $match[0][1] + strlen($match[0][0]);
	$length = strlen($code);
	$text = '';

	for ($i = $start; $i < $length; $i++) {
		$char = $code[$i];

		if ($char === '\\' && $i + 1 < $length && ($code[$i + 1] === '\\' || $code[$i + 1] === "'")) {
			$text .= $code[++$i];

			continue;
		}

		if ($char === "'") {
			break;
		}

		$text .= $char;
	}

	return $text;
}

/**
 * Split the artox text into sections. Commented lines (//) are ignored.
 *
 * @param string $text
 * @return array<int, array{type: string, classes: string, meta: string[], content: string[]}>
 */
function parseSections(string $text): array {
	$sections = array();
	$current = null;
	$part = 'none';

	foreach (explode("\n", $text) as $line) {
		if (str_starts_with(ltrim($line), '//')) {
			continue;
		}

		if ($current === null) {
			if (preg_match('/^([A-Z][A-Z0-9_]*)(?:\[(.*)\])?\s*$/', trim($line), $match)) {
				$current = array('type' => $match[1], 'classes' => trim($match[2] ?? ''), 'meta' => array(), 'content' => array());
				$part = 'meta';
			}

			continue;
		}

		if (trim($line) === '~END_META~') {
			$part = 'content';

			continue;
		}

		if (trim($line) === '~END_CONTENT~') {
			$sections[] = $current;
			$current = null;
			$part = 'none';

			continue;
		}

		$current[$part][] = $line;
	}

	return $sections;
}

/**
 * Parse key=value lines. A line that doesn't start with a known key pattern continues
 * the value of the previous key (markdown, GeoJSON).
 *
 * @param string[] $lines
 * @param string $keyPattern
 * @return array<int, array{0: string, 1: string}>
 */
function parsePairs(array $lines, string $keyPattern = '[a-z][a-z0-9_]*'): array {
	$pairs = array();

	foreach ($lines as $line) {
		if (preg_match('/^(' . $keyPattern . ')=(.*)$/', $line, $match)) {
			$value = $match[2];

			// A key that was written twice (e.g. "right_content=right_content=").
			while (str_starts_with($value, $match[1] . '=')) {
				$value = substr($value, strlen($match[1]) + 1);
			}

			$pairs[] = array($match[1], $value);

			continue;
		}

		if ($pairs) {
			$pairs[count($pairs) - 1][1] .= "\n" . $line;
		}
	}

	return array_map(fn ($pair) => array($pair[0], trim($pair[1])), $pairs);
}

/**
 * Convert key=value pairs into an associative array (the last value of a key wins).
 *
 * @param array $pairs
 * @return array<string, string>
 */
function pairsToMap(array $pairs): array {
	$map = array();

	foreach ($pairs as [$key, $value]) {
		$map[$key] = $value;
	}

	return $map;
}

/**
 * Split content lines into records that are separated by blank lines.
 * Lines starting with # are commented out.
 *
 * @param string[] $lines
 * @return array<int, array<string, string>>
 */
function parseRecords(array $lines): array {
	$records = array();
	$record = array();

	foreach (array_merge($lines, array('')) as $line) {
		if (trim($line) === '') {
			if ($record) {
				$records[] = $record;
			}

			$record = array();

			continue;
		}

		if (str_starts_with(ltrim($line), '#')) {
			continue;
		}

		if (preg_match('/^([a-z][a-z0-9_]*)=(.*)$/', $line, $match)) {
			$record[$match[1]] = trim($match[2]);
		}
	}

	return $records;
}

/**
 * Split a comma separated value into its parts.
 *
 * @param string $value
 * @param int $count
 * @return string[]
 */
function csv(string $value, int $count): array {
	$parts = array_map('trim', explode(',', $value, $count));

	return array_pad($parts, $count, '');
}

/**
 * Remove query strings like "?v1" from file names.
 *
 * @param string $file
 * @return string
 */
function cleanFile(string $file): string {
	return trim((string) preg_replace('/\?.*$/', '', $file));
}

// ---------------------------------------------------------------------------
// Conversion

/**
 * Split a "desktop,mobile" image value into the image and its mobile variant.
 *
 * @param array $data
 * @param string $key
 * @param string $value
 * @return array
 */
function imagePair(array $data, string $key, string $value): array {
	[$desktop, $mobile] = csv($value, 2);
	$data[$key] = cleanFile($desktop);

	if ($mobile !== '' && $mobile !== $desktop) {
		$data["{$key}_mobile"] = cleanFile($mobile);
	}

	return $data;
}

/**
 * Convert svg layers (water animation, landscape scene).
 *
 * @param string[] $lines
 * @param bool $animation whether the third value is an animation class (otherwise a description)
 * @return array
 */
function convertLayers(array $lines, bool $animation): array {
	$layers = array();

	foreach (parsePairs($lines, 'layer|svg') as [$key, $value]) {
		if ($key === 'layer') {
			[, $position] = csv($value, 2);
			$layers[] = array('position' => $position ?: 'svgoffsetleft', 'svgs' => array());

			continue;
		}

		if (!$layers) {
			$layers[] = array('position' => 'svgoffsetleft', 'svgs' => array());
		}

		[$file, $position, $third] = csv($value, 3);
		$layers[count($layers) - 1]['svgs'][] = array(
			'file' => cleanFile($file),
			'position' => $position ?: 'pos1',
			// water1 … water8 are animations, other values (landscape) are descriptions.
			'animation' => $third,
			// The boat of the original floats above the waves, it is moved onto them.
			'shift' => $animation && cleanFile($file) === 'sailing.svg' ? 17 : 0
		);
	}

	return $layers;
}

/**
 * Convert the map keys of a split pane ("right_map_center" etc.).
 *
 * @param array $values
 * @param string $prefix
 * @param string $content GeoJSON
 * @param array $report
 * @return array
 */
function convertMap(array $values, string $prefix, string $content, array &$report): array {
	$height = (string) ($values["{$prefix}map_height"] ?? '520px');
	$geojson = null;

	if (trim($content) !== '') {
		$geojson = json_decode($content, true);

		if (!is_array($geojson)) {
			$report[] = '    ! GeoJSON konnte nicht gelesen werden: ' . json_last_error_msg();
			$geojson = null;
		}
	}

	$map = array(
		'center' => (string) ($values["{$prefix}map_center"] ?? '51.0,10.0'),
		'zoom' => (int) ($values["{$prefix}map_zoom"] ?? 6),
		// "full" fills the height of the pane next to the map.
		'height' => preg_match('/^(full|\d+(\.\d+)?(px|vh|rem|em|%))$/', $height) ? $height : '520px',
		'markers' => array(),
		'geojson' => $geojson
	);

	if (!empty($values["{$prefix}map_max_zoom"])) {
		$map['max_zoom'] = (int) $values["{$prefix}map_max_zoom"];
	}

	if (!empty($values["{$prefix}tile_url"])) {
		$map['tile_url'] = $values["{$prefix}tile_url"];
	}

	return $map;
}

/**
 * Read the track lists of the music players.
 *
 * @param string $musicDir
 * @param string $source e.g. "cascade/music/youtube/economy"
 * @return array<int, array{title: string, url: string}>
 */
function tracks(string $musicDir, string $source): array {
	if ($musicDir === '' || !preg_match('#music/([a-z0-9_-]+)/([a-z0-9_-]+)/?$#i', $source, $match)) {
		return array();
	}

	[, $platform, $list] = $match;
	$file = "$musicDir/$list.json";

	if (!is_readable($file)) {
		return array();
	}

	$data = json_decode((string) file_get_contents($file), true);
	$tracks = array();

	foreach ((array) ($data[$platform] ?? array()) as $key => $track) {
		if (is_array($track) && !empty($track['url'])) {
			$tracks[] = array('title' => (string) ($track['title'] ?? $key), 'url' => (string) $track['url']);
		}
	}

	return $tracks;
}

/**
 * Convert a section into the data of a party block.
 *
 * @param array $section
 * @param string $musicDir
 * @param array $report
 * @return array
 */
function convertSection(array $section, string $musicDir, array &$report): array {
	$type = $section['type'];
	$meta = pairsToMap(parsePairs($section['meta']));
	$content = $section['content'];

	foreach (SECRET_KEYS as $key) {
		unset($meta[$key]);
	}

	$data = array('classes' => $section['classes']);

	foreach (array('section_id', 'title') as $key) {
		if (isset($meta[$key])) {
			$data[$key] = $meta[$key];
		}
	}

	switch ($type) {
		case 'PARTY_HEADER':
			foreach (array('party_name', 'warning_text', 'main_link', 'main_link_text', 'intro_text') as $key) {
				$data[$key] = $meta[$key] ?? '';
			}

			break;

		case 'REGION_SCROLL':
			foreach (array('navigation_target', 'navigation_text', 'scroll_width') as $key) {
				if (isset($meta[$key])) {
					$data[$key] = $meta[$key];
				}
			}

			$data['regions'] = array_map(fn ($record) => array(
				'region_id' => $record['region_id'] ?? '',
				'region_name' => $record['region_name'] ?? '',
				'region_description' => $record['region_description'] ?? ''
			), parseRecords($content));

			break;

		case 'CONTACT_CLOSER':
			break;

		case 'PLAIN_HTML':
			$data['raw_content'] = trim(join("\n", $content));

			break;

		case 'WATER_ANIMATION':
			$data['layers'] = convertLayers($content, true);

			break;

		case 'LANDSCAPE_SCENE':
			$data['layers'] = convertLayers($content, false);

			break;

		case 'SECURITY_CONCEPT':
			foreach (array('subtitle', 'subtitle_emphasis', 'feature_prefix') as $key) {
				$data[$key] = $meta[$key] ?? '';
			}

			if (isset($meta['main_image'])) {
				$data = imagePair($data, 'main_image', $meta['main_image']);
			}

			$data['features'] = array_values(array_map(
				fn ($pair) => $pair[1],
				array_filter(parsePairs($content), fn ($pair) => $pair[0] === 'feature')
			));

			break;

		case 'DOCUMENT_SECTION':
			$data['documents'] = array_map(fn ($record) => array(
				'title' => $record['document_title'] ?? '',
				'file' => cleanFile($record['document_file'] ?? ''),
				'preview' => cleanFile($record['document_preview'] ?? ''),
				'description' => $record['document_description'] ?? ''
			), parseRecords($content));

			break;

		case 'ART_MOTIVATION':
			$data['artworks'] = array();

			foreach (parsePairs($content) as [$key, $value]) {
				if ($key !== 'artwork') {
					continue;
				}

				// image,classes,description,mobile image (the mobile image is not needed, the canvas is relative)
				[$image, $classes, $description] = csv($value, 4);
				$data['artworks'][] = array(
					'image' => cleanFile($image),
					'description' => $description,
					'size' => '',
					'vertical' => false,
					'offset' => '0',
					'classes' => $classes
				);
			}

			$data['generate_mobile'] = false;

			break;

		case 'FORM_SECTION':
			$data['form_method'] = $meta['form_method'] ?? 'post';
			// The original form posted to an external handler, Automad sends the mails itself.
			$data['form_action'] = '';
			$data['mail_to'] = $meta['to_email'] ?? '';
			$data['turnstile_site_key'] = $meta['turnstile_site_key'] ?? '';
			$data['fields'] = array();

			foreach (parsePairs($content) as [$key, $value]) {
				if ($key !== 'field') {
					continue;
				}

				[$name, $fieldType, $label, $required, $options] = csv($value, 5);
				$data['fields'][] = array(
					'label' => $label,
					'name' => $name,
					'type' => $fieldType ?: 'text',
					'required' => $required === 'required',
					'options' => $options !== '' ? array_map('trim', explode('|', $options)) : array()
				);
			}

			break;

		case 'SPLIT_SECTION':
			$values = pairsToMap(parsePairs($content, '(?:left|right)_[a-z0-9_]+'));
			$data['panes'] = array();

			foreach (array('left', 'right') as $side) {
				$paneType = $values["{$side}_type"] ?? '';
				$paneContent = $values["{$side}_content"] ?? '';

				if ($paneType === '' && $paneContent === '') {
					continue;
				}

				$pane = array(
					'title' => '',
					'type' => in_array($paneType, array('markdown', 'image', 'leaflet', 'video', 'html'), true) ? $paneType : 'markdown',
					'width' => 'auto',
					'content' => '',
					'image' => '',
					'image_alt' => '',
					'video' => '',
					'html' => '',
					'classes' => ''
				);

				switch ($pane['type']) {
					case 'leaflet':
						$pane['map_config'] = convertMap($values, "{$side}_", $paneContent, $report);

						break;
					case 'image':
						$pane['image'] = cleanFile($paneContent);

						break;
					case 'video':
						$pane['video'] = $paneContent;

						break;
					case 'html':
						$pane['html'] = $paneContent;

						break;
					default:
						$pane['content'] = $paneContent;
				}

				$data['panes'][] = $pane;
			}

			break;

		case 'MARKDOWN_SECTION':
			$data['markdown_content'] = trim(join("\n", $content));

			if (isset($meta['section_image'])) {
				$data = imagePair($data, 'section_image', $meta['section_image']);
			}

			break;

		case 'QUOTE_SECTION':
			$values = pairsToMap(parsePairs($content));
			$data['quote_text'] = $values['quote_text'] ?? ($meta['quote_text'] ?? '');
			$data['quote_author'] = $values['quote_author'] ?? ($meta['quote_author'] ?? '');

			if (isset($meta['section_image'])) {
				$data = imagePair($data, 'section_image', $meta['section_image']);
			}

			break;

		case 'MUSIC_ANCHOR':
			$data['anchor_id'] = $meta['anchor_id'] ?? 'music';

			break;

		case 'MUSIC_PLAYER':
			foreach (array('player_width', 'player_height', 'iframe_border_radius') as $key) {
				if (isset($meta[$key])) {
					$data[$key] = $meta[$key];
				}
			}

			$defaultSource = $meta['default_source'] ?? '';
			$data['default_source'] = '';
			$data['platforms'] = array();

			foreach (parsePairs($content) as [$key, $value]) {
				if ($key !== 'music_platform') {
					continue;
				}

				[$platformSource, $class, $text] = csv($value, 3);
				$tracks = tracks($musicDir, $platformSource);

				if (!$tracks) {
					$report[] = "    ! Keine Trackliste für $platformSource (--music angeben)";
				}

				$data['platforms'][] = array(
					'text' => $text ?: ucfirst($class),
					'url' => '',
					'class' => $class ?: 'wp-element-button',
					'tracks' => $tracks
				);

				if ($platformSource === $defaultSource && $tracks) {
					$data['default_source'] = $tracks[0]['url'];
				}
			}

			break;
	}

	return $data;
}

/**
 * Remember referenced files.
 *
 * @param mixed $value
 * @param array $media
 */
function collectMedia(mixed $value, array &$media): void {
	if (is_array($value)) {
		foreach ($value as $key => $item) {
			if ($key !== 'geojson') {
				collectMedia($item, $media);
			}
		}

		return;
	}

	if (is_string($value) && preg_match('/^[\w.\-]+\.(jpe?g|png|gif|webp|svg|pdf|mp3|mp4)$/i', $value)) {
		$media[$value] = true;
	}

	// Images inside of markdown.
	if (is_string($value) && preg_match_all('/!\[[^\]]*\]\(([^)\s]+)\)/', $value, $matches)) {
		foreach ($matches[1] as $path) {
			$media[$path] = true;
		}
	}
}

// ---------------------------------------------------------------------------
// Run

$blocks = array();
$report = array();
$media = array();

foreach (parseSections(readArtox($source)) as $index => $section) {
	$component = COMPONENTS[$section['type']] ?? '';

	if ($component === '' || !is_file(dirname(__DIR__) . "/automad/src/server/Party/$component/$component.php")) {
		$report[] = sprintf('#%02d %-18s übersprungen (keine Party-Komponente)', $index, $section['type']);

		continue;
	}

	$sectionReport = array();
	$data = convertSection($section, $musicDir, $sectionReport);
	collectMedia($data, $media);

	$blocks[] = array(
		'id' => substr(str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode(random_bytes(24))), 0, 32),
		'type' => 'party' . $component,
		'data' => $data,
		'tunes' => array('layout' => array('stretched' => true))
	);

	$details = array();

	foreach (array('regions', 'documents', 'artworks', 'fields', 'features', 'panes', 'layers', 'platforms') as $key) {
		if (isset($data[$key])) {
			$details[] = count($data[$key]) . " $key";
		}
	}

	foreach ($data['panes'] ?? array() as $pane) {
		$details[] = $pane['type'] === 'leaflet'
			? 'Karte ' . count($pane['map_config']['geojson']['features'] ?? array()) . ' Formen'
			: $pane['type'] . ' ' . mb_strlen($pane['content'] ?: $pane['image']) . ' Zeichen';
	}

	if (isset($data['markdown_content'])) {
		$details[] = mb_strlen($data['markdown_content']) . ' Zeichen';
	}

	foreach ($data['platforms'] ?? array() as $platform) {
		$details[] = $platform['text'] . ' ' . count($platform['tracks']) . ' Tracks';
	}

	$report[] = sprintf('#%02d %-16s %-22s %s', $index, $component, $data['section_id'] ?? '', join(', ', $details));
	array_push($report, ...$sectionReport);
}

/**
 * Create a block.
 *
 * @param string $component
 * @param array $data
 * @return array
 */
function block(string $component, array $data): array {
	return array(
		'id' => substr(str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode(random_bytes(24))), 0, 32),
		'type' => 'party' . $component,
		'data' => $data,
		'tunes' => array('layout' => array('stretched' => true))
	);
}

/**
 * Insert a block after the first block that matches the callback (or at the end).
 *
 * @param array $blocks
 * @param array $block
 * @param callable $after
 * @return array
 */
function insertAfter(array $blocks, array $block, callable $after): array {
	foreach ($blocks as $index => $existing) {
		if ($after($existing)) {
			array_splice($blocks, $index + 1, 0, array($block));

			return $blocks;
		}
	}

	$blocks[] = $block;

	return $blocks;
}

/**
 * Names of the locations of the military map (center of the circles, see index.artox).
 */
const MILITARY_LOCATIONS = array(
	array(53.94, 12.64, 'Vorpommern (bei Demmin)', 'Nord'),
	array(53.05, 11.17, 'Wendland (Lüchow-Dannenberg)', 'Nord'),
	array(52.12, 10.62, 'Braunschweiger Land (bei Wolfenbüttel)', 'Mitte'),
	array(50.88, 8.39, 'Siegerland / Dillenburg', 'Mitte'),
	array(48.95, 11.25, 'Altmühltal (bei Eichstätt)', 'Süd')
);

/**
 * Build a case map (CaseLeafletRegion) from the circles of the military map: one marker
 * per location in the center of its circle, grouped into regions.
 *
 * @param array $split the data of the military split section
 * @return array|null
 */
function militaryCaseMap(array $split): ?array {
	$map = null;

	foreach ($split['panes'] ?? array() as $pane) {
		if ($pane['type'] === 'leaflet') {
			$map = $pane['map_config'];
		}
	}

	$features = $map['geojson']['features'] ?? array();

	if (!$features) {
		return null;
	}

	$markers = array();
	$items = array();
	$groups = array();

	foreach ($features as $index => &$feature) {
		$ring = $feature['geometry']['coordinates'][0] ?? array();

		if (count($ring) < 4) {
			continue;
		}

		$points = array_slice($ring, 0, -1);
		$lng = array_sum(array_column($points, 0)) / count($points);
		$lat = array_sum(array_column($points, 1)) / count($points);
		$radius = max(array_map(fn ($point) => abs($point[1] - $lat), $points)) * 111;

		$name = 'Standort ' . ($index + 1);
		$region = 'Weitere';

		foreach (MILITARY_LOCATIONS as [$knownLat, $knownLng, $knownName, $knownRegion]) {
			if (abs($knownLat - $lat) < 0.3 && abs($knownLng - $lng) < 0.3) {
				$name = $knownName;
				$region = $knownRegion;
			}
		}

		$id = 'standort-' . ($index + 1);
		$description = sprintf('Vorgeschlagener Standort für Heer- und Feldtruppen, Einzugsbereich rund %d km.', round($radius / 5) * 5);

		$feature['properties'] = array('name' => $name, 'region' => $region);
		$markers[] = array('id' => $id, 'title' => $name, 'description' => $description, 'lat' => round($lat, 5), 'lng' => round($lng, 5), 'region' => $region, 'zoom' => 8);
		$items[] = array('marker_id' => $id, 'title' => $name, 'description' => $description, 'region' => $region, 'links' => array());
		$groups[$region][] = $id;
	}

	unset($feature);

	$order = array_flip(array('Nord', 'Mitte', 'Süd', 'Weitere'));
	uksort($groups, fn ($a, $b) => ($order[$a] ?? 9) <=> ($order[$b] ?? 9));

	return array(
		'section_id' => 'military_cases',
		'title' => $split['title'] ?? 'Standorte Heer-/Feldtruppen',
		'map_center' => $map['center'] ?? '51.692,9.89',
		'map_zoom' => (int) ($map['zoom'] ?? 6),
		'map_height' => '560px',
		'tile_url' => $map['tile_url'] ?? 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
		'region_priority' => true,
		'default_region' => '',
		'markers' => $markers,
		'items' => $items,
		'groups' => array_map(fn ($name, $ids) => array('name' => $name, 'marker_ids' => $ids), array_keys($groups), $groups),
		'geojson_content' => array('type' => 'FeatureCollection', 'features' => $features),
		'status_icon_base' => '',
		'classes' => 'domcontainer relative widthcontainer'
	);
}

// The military map of politicalpartysite (nightly) as case map below the water animation.
foreach ($blocks as $block) {
	if ($block['type'] === 'partySplit' && ($block['data']['section_id'] ?? '') === 'military_map') {
		$caseMap = militaryCaseMap($block['data']);

		if ($caseMap) {
			$blocks = insertAfter($blocks, block('CaseLeafletRegion', $caseMap), fn ($existing) => $existing['type'] === 'partyWaterAnimation');
			$report[] = '+   CaseLeafletRegion military_cases       ' . count($caseMap['markers']) . ' Standorte in ' . count($caseMap['groups']) . ' Regionen (unter der Wasser-Animation)';
		}

		break;
	}
}

// An example of a YouTube video with time code comments (only with --example).
$blocks = empty($options['example']) ? $blocks : insertAfter($blocks, block('VideoCommentary', array(
	'section_id' => 'video',
	'title' => 'Video: FSLAP',
	'video' => 'https://www.youtube.com/watch?v=YC23GCc79_U',
	'intro' => 'Beispiel für ein YouTube-Video mit Kommentaren zu Zeitmarken. Die Kommentare und das Video lassen sich im Dashboard ändern.',
	'comments' => array(
		array('time' => '0:00', 'title' => 'Start', 'category' => '', 'text' => 'Beispiel-Kommentar: Hier beginnt das Video.'),
		array('time' => '0:30', 'title' => 'Erste Strophe', 'category' => '', 'text' => 'Beispiel-Kommentar: Ein Klick auf die Zeitmarke springt im Video an diese Stelle.'),
		array('time' => '1:00', 'title' => 'Refrain', 'category' => '', 'text' => 'Beispiel-Kommentar: Während das Video läuft, wird der passende Kommentar hervorgehoben.')
	),
	'comments_text' => '',
	'video_position' => 'left',
	'follow' => true,
	'pause_on_leave' => true,
	'classes' => 'domcontainer relative widthcontainer'
)), fn ($existing) => ($existing['data']['section_id'] ?? '') === 'principles');
if (!empty($options['example'])) {
	$report[] = '+   VideoCommentary  video                  Beispiel-YouTube-Video (nach "Unsere Grundwerte")';
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
	. "Im Dashboard: Startseite > Files > hochladen. Pfade mit / am Anfang beziehen sich auf die Wurzel der Seite.\n\n"
	. join("\n", array_keys($media)) . "\n"
);

echo join(PHP_EOL, $report), PHP_EOL;
echo count($blocks) . ' Sections übernommen, ' . count($media) . ' referenzierte Dateien (siehe MEDIA.txt).' . PHP_EOL;
