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

		return array(
			'section_id' => $config['section_id'],
			'title' => $config['title'],
			'artworks' => $artworks,
			'classes' => $this->getClasses($config),
			'mobile_art_image' => $config['generate_mobile'] ? $this->buildMobileArtImage($artworks, (string) $config['section_id']) : ''
		);
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
			'description' => 'Collage aus Kunstwerken im Float-Layout. Für Mobilgeräte wird automatisch ein zusammengesetztes Bild erzeugt.',
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
						array('name' => 'classes', 'type' => 'text', 'label' => 'Layout-Klassen', 'default' => 'half', 'help' => 'z.B. half, third, quarter, piece, tricorn, full, thumb, wide, narrow, float-island – optional mit vertical und offset5 … offset50'),
						array('name' => 'description', 'type' => 'text', 'label' => 'Beschreibung (Alt-Text)')
					)
				),
				array('name' => 'generate_mobile', 'type' => 'toggle', 'label' => 'Mobiles Collage-Bild erzeugen', 'default' => true),
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
