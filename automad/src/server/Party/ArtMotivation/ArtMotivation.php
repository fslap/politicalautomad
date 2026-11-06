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

namespace Automad\Party\ArtMotivation;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Core\Automad;
use Automad\Core\Resolve;
use Automad\Party\Traits\ComponentConfig;
use CleanFloatLayoutReplicator;

defined('AUTOMAD') or die('Direct access not permitted!');

require_once __DIR__ . '/lib/CleanFloatLayoutReplicator.php';

/**
 * Party component ArtMotivation (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class ArtMotivation extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	/**
	 * The path of the page that is currently rendered.
	 */
	private string $pagePath = '/';

	public function __construct() {
		parent::__construct(__DIR__, 'artmotivation');
	}

	public function context(array $config): array {
		$artworks = array_values(array_filter($config['artworks'], fn ($artwork) => is_array($artwork) && !empty($artwork['image'])));

		foreach ($artworks as &$artwork) {
			$artwork['classes'] = self::artworkClasses($artwork);
		}

		unset($artwork);

		$align = in_array($config['align'], array('start', 'center', 'end'), true) ? $config['align'] : 'center';
		$ratio = preg_match('/^\d+(\.\d+)?\s*\/\s*\d+(\.\d+)?$/', trim((string) $config['ratio'])) ? trim((string) $config['ratio']) : '16 / 10';
		[$ratioW, $ratioH] = array_map('floatval', explode('/', $ratio));

		return array(
			'section_id' => $config['section_id'],
			'title' => $config['title'],
			'artworks' => $artworks,
			'columns' => $this->layoutColumns($artworks, 1600, 1600 / ($ratioW / max(0.01, $ratioH)), (bool) $config['bigger']),
			'style' => "aspect-ratio: $ratio;",
			'align' => $align,
			'bigger' => (bool) $config['bigger'],
			'classes' => $this->getClasses($config),
			// The relative CSS canvas scales on all screens, a generated image is only used when enabled.
			'mobile_art_image' => $config['generate_mobile'] ? $this->buildMobileArtImage($artworks, (string) $config['section_id']) : ''
		);
	}

	/**
	 * Distribute the artworks into columns exactly like the art builder (CleanFloatLayoutReplicator)
	 * and return relative sizes: widths in % of the canvas width (cqw) and heights and offsets in %
	 * of the canvas height (cqh).
	 *
	 * @param array $artworks
	 * @param float $width the canvas width of the builder
	 * @param float $height the canvas height of the builder
	 * @param bool $bigger the "bigger images" option of the builder
	 * @return array
	 */
	private function layoutColumns(array $artworks, float $width, float $height, bool $bigger): array {
		$margin = 10;
		$columns = array();
		$last = null;

		foreach (array_values($artworks) as $index => $artwork) {
			$props = self::sizeProperties(explode(' ', $artwork['classes']), $bigger);
			$aspect = $this->aspectRatio((string) $artwork['image']);

			if ($props['mode'] === 'height') {
				$h = $props['height'] / 100 * $height;
				$w = $h * $aspect;
			} else {
				$w = $props['width'] / 100 * $width;
				$h = $w / $aspect;
			}

			// The builder caps both dimensions at 85 % of the canvas, the aspect ratio is kept here.
			$scale = min(1, ($width * 0.85) / $w, ($height * 0.85) / $h);
			$w *= $scale;
			$h *= $scale;

			$offset = $props['offset'] / 100 * $height;
			$stack = false;

			// The first two images always start a column, all others are stacked under the
			// previous one in case there are at least 50 px left.
			if ($index >= 2 && $last !== null) {
				$underY = $last['y'] + $last['h'] + $margin;
				$stack = ($height - $underY) >= $h + 50;
			}

			$y = $stack ? $last['y'] + $last['h'] + $margin : $offset;

			if (!$stack) {
				$columns[] = array('items' => array());
			}

			$columns[count($columns) - 1]['items'][] = array(
				'image' => $artwork['image'],
				'description' => $artwork['description'] ?? '',
				'classes' => $artwork['classes'],
				'style' => sprintf(
					'width: %scqw; height: %scqh; margin-top: %scqh;',
					round($w / $width * 100, 3),
					round($h / $height * 100, 3),
					round(($stack ? $margin : $offset) / $height * 100, 3)
				)
			);

			$last = array('y' => $y, 'h' => $h);
		}

		return $columns;
	}

	/**
	 * The aspect ratio (width / height) of an image. Remote or missing images default to 3:4.
	 *
	 * @param string $image
	 * @return float
	 */
	private function aspectRatio(string $image): float {
		$path = preg_replace('/\?.*/', '', $image) ?? $image;

		if (!preg_match('#^(https?:)?//#', $path)) {
			$file = Resolve::filePath($this->pagePath, $path);
			$size = is_file($file) ? @getimagesize($file) : false;

			if ($size && $size[0] > 0 && $size[1] > 0) {
				return $size[0] / $size[1];
			}

			// SVG files have no pixel size, try the viewBox.
			if (is_file($file) && preg_match('/viewBox="[\d.\-]+\s+[\d.\-]+\s+([\d.]+)\s+([\d.]+)"/', (string) file_get_contents($file, false, null, 0, 4096), $match) && (float) $match[2] > 0) {
				return (float) $match[1] / (float) $match[2];
			}
		}

		return 0.75;
	}

	/**
	 * The size properties of the art builder for a set of classes.
	 *
	 * @param array $classes
	 * @param bool $bigger
	 * @return array{mode: string, width: float, height: float, offset: int}
	 */
	private static function sizeProperties(array $classes, bool $bigger): array {
		$props = array('mode' => 'width', 'width' => 25, 'height' => 35, 'offset' => 0);
		$vertical = in_array('vertical', $classes, true);

		foreach ($classes as $class) {
			switch ($class) {
				case 'wide':
					$props = array_merge($props, array('mode' => 'width', 'width' => $bigger ? 43 : 35));
					break;
				case 'float-island':
					$props = array_merge($props, array('mode' => 'width', 'width' => 25));
					break;
				case 'narrow':
					$props = array_merge($props, array('mode' => 'width', 'width' => 15));
					break;
				case 'tricorn':
					$props = array_merge($props, array('mode' => 'height', 'height' => 45));
					break;
				case 'half':
					$props = array_merge($props, array('mode' => 'height', 'height' => $vertical ? 20 : ($bigger ? 75 : 65)));
					break;
				case 'piece':
					$props = array_merge($props, array('mode' => 'height', 'height' => $vertical ? ($bigger ? 23 : 18) : 25));
					break;
				case 'full':
					$props = array_merge($props, array('mode' => 'height', 'height' => 85));
					break;
				case 'third':
					$props = array_merge($props, array('mode' => 'height', 'height' => 33));
					break;
				case 'quarter':
					$props = array_merge($props, array('mode' => 'height', 'height' => 25));
					break;
				case 'thumb':
					$props = array_merge($props, array('mode' => 'height', 'height' => 15));
					break;
				default:
					if (preg_match('/^(offset|margin)(\d+)$/', $class, $match)) {
						$props['offset'] = (int) $match[2];
					}
			}
		}

		return $props;
	}

	/**
	 * The artwork sizes of the art builder (CleanFloatLayoutReplicator). Sizes are relative to the
	 * canvas height or the canvas width, see automad/src/client/party/styles/art.less.
	 */
	const SIZES = array(
		'' => 'Aus den Klassen übernehmen (Standard: 25 % der Breite)',
		'half' => 'Half – 65 % der Höhe (Hochformat: 20 %)',
		'tricorn' => 'Tricorn – 45 % der Höhe',
		'piece' => 'Piece – 25 % der Höhe (Hochformat: 18 %)',
		'wide' => 'Wide – 35 % der Breite',
		'float-island' => 'Float-Island – 25 % der Breite',
		'default' => 'Standard – 25 % der Breite',
		'full' => 'Full – 85 % der Höhe',
		'third' => 'Third – 33 % der Höhe',
		'quarter' => 'Quarter – 25 % der Höhe',
		'thumb' => 'Thumb – 15 % der Höhe',
		'narrow' => 'Narrow – 15 % der Breite'
	);

	/**
	 * Build the classes of an artwork from the size, orientation and offset fields.
	 * Classes of older data (e.g. "half vertical offset10") are kept.
	 *
	 * @param array $artwork
	 * @return string
	 */
	private static function artworkClasses(array $artwork): string {
		$classes = preg_split('/\s+/', trim((string) ($artwork['classes'] ?? ''))) ?: array();
		$size = (string) ($artwork['size'] ?? '');

		if ($size !== '' && $size !== 'default' && isset(self::SIZES[$size])) {
			// The size field wins over sizes in the classes field.
			$classes = array_diff($classes, array_keys(self::SIZES));

			$classes[] = $size;
		}

		if (!empty($artwork['vertical'])) {
			$classes[] = 'vertical';
		}

		$offset = (int) ($artwork['offset'] ?? 0);

		if ($offset > 0) {
			$classes = array_filter($classes, fn ($class) => !preg_match('/^(offset|margin)\d+$/', $class));
			$classes[] = 'offset' . min(50, (int) (round($offset / 5) * 5));
		}

		return trim(join(' ', array_unique(array_filter($classes))));
	}

	public function render(array $block, Automad $Automad): string {
		$Page = $Automad->Context->get();
		$this->pagePath = $Page->path;

		return parent::render($block, $Automad);
	}

	protected function definition(): array {
		return array(
			'title' => 'Kunst / Motivation',
			'icon' => 'palette',
			'description' => 'Collage aus Kunstwerken nach dem Art Builder: relative Leinwand, Spalten werden automatisch gefüllt.',
			'fields' => array(
				self::sectionIdField('art'),
				self::titleField(),
				array(
					'name' => 'artworks',
					'type' => 'list',
					'label' => 'Kunstwerke',
					'itemTitle' => 'description',
					'fields' => array(
						array('name' => 'image', 'type' => 'image', 'label' => 'Bild'),
						array('name' => 'description', 'type' => 'text', 'label' => 'Beschreibung (Alt-Text)'),
						array('name' => 'size', 'type' => 'select', 'label' => 'Größe (relativ zur Leinwand)', 'default' => '', 'options' => self::SIZES),
						array('name' => 'vertical', 'type' => 'toggle', 'label' => 'Hochformat-Variante (vertical)'),
						array('name' => 'offset', 'type' => 'select', 'label' => 'Versatz nach unten (% der Leinwandhöhe)', 'default' => '0', 'options' => array('0' => 'Kein Versatz', '5' => '5 %', '10' => '10 %', '15' => '15 %', '20' => '20 %', '25' => '25 %', '30' => '30 %', '35' => '35 %', '40' => '40 %', '45' => '45 %', '50' => '50 %')),
						array('name' => 'classes', 'type' => 'text', 'label' => 'Zusätzliche Klassen (optional)', 'default' => '')
					)
				),
				array('name' => 'ratio', 'type' => 'text', 'label' => 'Seitenverhältnis der Leinwand', 'default' => '16 / 10', 'help' => 'Wie im Art Builder (1600 × 1000). Alle Größen sind relativ zu dieser Fläche und skalieren mit.'),
				array('name' => 'align', 'type' => 'select', 'label' => 'Vertikale Ausrichtung der Spalten', 'default' => 'center', 'options' => array('start' => 'Oben', 'center' => 'Mitte', 'end' => 'Unten')),
				array('name' => 'bigger', 'type' => 'toggle', 'label' => 'Größere Bilder (bigger images)', 'default' => false),
				array('name' => 'generate_mobile', 'type' => 'toggle', 'label' => 'Zusätzlich ein Collage-Bild für Mobilgeräte erzeugen', 'default' => false),
				self::classesField('')
			)
		);
	}

	/**
	 * Compose all artworks into a single image for small screens.
	 *
	 * @param array $artworks
	 * @param string $sectionId
	 * @return string the URL of the generated image
	 */
	private function buildMobileArtImage(array $artworks, string $sectionId): string {
		if (empty($artworks) || !function_exists('imagecreatetruecolor')) {
			return '';
		}

		$images = array();
		$classes = array();
		$hash = array();

		foreach ($artworks as $artwork) {
			$file = Resolve::filePath($this->pagePath, (string) preg_replace('/\?.*/', '', (string) $artwork['image']));
			$real = realpath($file);

			if (!$real || !is_file($real) || strpos($real, (string) realpath(AM_BASE_DIR)) !== 0) {
				continue;
			}

			$images[] = $real;
			$classes[] = trim((string) preg_replace('/\s+/', '.', trim((string) ($artwork['classes'] ?? ''))), '.');
			$hash[] = $real . ':' . filemtime($real) . ':' . end($classes);
		}

		if (empty($images)) {
			return '';
		}

		$slug = trim((string) preg_replace('/[^a-z0-9\-]+/i', '-', $sectionId ?: 'art'), '-') ?: 'art';
		$relative = AM_DIR_CACHE . '/party/art-' . $slug . '-' . substr(sha1(join('|', $hash)), 0, 12) . '.png';
		$output = AM_BASE_DIR . $relative;

		if (is_file($output)) {
			return $relative;
		}

		if (!is_dir(dirname($output))) {
			mkdir(dirname($output), 0777, true);
		}

		try {
			$replicator = new CleanFloatLayoutReplicator();
			$canvas = $replicator->layoutImages($images, $classes);
			imagepng($canvas, $output);
		} catch (\Throwable $e) {
			return '';
		}

		return $relative;
	}
}
