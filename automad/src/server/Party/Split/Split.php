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

namespace Automad\Party\Split;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\Traits\YouTube;
use Lib\CharMarkdown;

defined('AUTOMAD') or die('Direct access not permitted!');

require_once __DIR__ . '/lib/CharMarkdown.php';

/**
 * Party component Split (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class Split extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	use YouTube;

	/**
	 * The pane widths.
	 */
	const WIDTHS = array('auto' => 'Automatisch (gleich breit)', '25' => '1/4', '33.333' => '1/3', '40' => '2/5', '50' => '1/2', '60' => '3/5', '66.666' => '2/3', '75' => '3/4', '100' => 'Volle Breite');

	private ?CharMarkdown $parser = null;

	public function __construct() {
		parent::__construct(__DIR__, 'split');
	}

	public function context(array $config): array {
		if ($this->parser === null) {
			$this->parser = new CharMarkdown();
		}

		$images = $this->splitImages((string) $config['section_image'], (string) ($config['section_image_mobile'] ?? ''));
		$panes = self::legacyPanes($config) ?? $config['panes'];
		$panes = array_values(array_filter($panes, 'is_array'));
		$count = count($panes);
		$out = array();

		foreach ($panes as $index => $pane) {
			$out[] = $this->paneContext($pane, $index, $count);
		}

		$gap = array('none' => '0', 'small' => '1rem', 'medium' => '2rem', 'large' => '4rem')[$config['gap']] ?? '2rem';

		return array(
			'section_id' => $config['section_id'],
			'title' => $config['title'],
			'desktop_image' => $images['desktop'],
			'mobile_image' => $images['mobile'] !== $images['desktop'] ? $images['mobile'] : '',
			'panes' => $out,
			'container_classes' => join(' ', array_filter(array(
				'split-container--flex',
				"split-container--count-$count",
				'split-container--align-' . (in_array($config['align'], array('start', 'center', 'end', 'stretch'), true) ? $config['align'] : 'start'),
				'split-container--stack-' . (in_array($config['stack'], array('1000', '700', 'never'), true) ? $config['stack'] : '1000'),
				$config['hover_border'] ? 'split-hover-border' : ''
			))),
			'container_style' => "--split-gap: $gap;",
			'classes' => $this->getClasses($config),
			'has_inner_container' => $this->hasInnerContainer($config)
		);
	}

	protected function definition(): array {
		$mapConfig = array('height' => '320px', 'center' => '51.0,10.0', 'zoom' => 6, 'geojson' => null);

		return array(
			'title' => 'Split (mehrspaltig)',
			'icon' => 'layout-split',
			'description' => 'Beliebig viele Spalten nebeneinander – jede mit eigenem Inhalt (Markdown, Bild, Karte, HTML oder YouTube) und eigener Breite.',
			'fields' => array(
				self::sectionIdField(),
				self::titleField(),
				...self::imagePairFields('section_image', 'Section-Bild'),
				array(
					'name' => 'panes',
					'type' => 'list',
					'label' => 'Spalten',
					'addLabel' => 'Spalte hinzufügen',
					'itemTitle' => 'title',
					'default' => array(
						array('title' => '', 'type' => 'markdown', 'content' => 'Linke Spalte …', 'width' => 'auto'),
						array('title' => '', 'type' => 'markdown', 'content' => 'Rechte Spalte …', 'width' => 'auto')
					),
					'fields' => array(
						array('name' => 'title', 'type' => 'text', 'label' => 'Überschrift der Spalte (optional)'),
						array('name' => 'type', 'type' => 'select', 'label' => 'Inhaltstyp', 'default' => 'markdown', 'options' => array('markdown' => 'Markdown', 'image' => 'Bild', 'leaflet' => 'Karte', 'video' => 'YouTube-Video', 'html' => 'HTML')),
						array('name' => 'width', 'type' => 'select', 'label' => 'Breite', 'default' => 'auto', 'options' => self::WIDTHS),
						array('name' => 'content', 'type' => 'markdown', 'label' => 'Markdown (Typ Markdown)'),
						array('name' => 'image', 'type' => 'image', 'label' => 'Bild (Typ Bild)'),
						array('name' => 'image_alt', 'type' => 'text', 'label' => 'Alternativtext des Bildes'),
						array('name' => 'video', 'type' => 'url', 'label' => 'YouTube-Link (Typ YouTube-Video)'),
						array('name' => 'html', 'type' => 'html', 'label' => 'HTML (Typ HTML)'),
						array(
							'name' => 'map_editor',
							'type' => 'map',
							'label' => 'Karten-Editor (Typ Karte)',
							'object' => 'map_config',
							'bind' => array('markers' => 'markers', 'geojson' => 'geojson', 'center' => 'center', 'zoom' => 'zoom', 'height' => 'height')
						),
						array('name' => 'map_config', 'type' => 'json', 'label' => 'Karten-Konfiguration (Typ Karte)', 'default' => $mapConfig),
						array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen der Spalte', 'placeholder' => 'bgwhite')
					)
				),
				array('name' => 'gap', 'type' => 'select', 'label' => 'Abstand zwischen den Spalten', 'default' => 'medium', 'options' => array('none' => 'Keiner', 'small' => 'Klein', 'medium' => 'Mittel', 'large' => 'Groß')),
				array('name' => 'align', 'type' => 'select', 'label' => 'Vertikale Ausrichtung', 'default' => 'start', 'options' => array('start' => 'Oben', 'center' => 'Mitte', 'end' => 'Unten', 'stretch' => 'Gleich hoch')),
				array('name' => 'stack', 'type' => 'select', 'label' => 'Untereinander anzeigen ab', 'default' => '1000', 'options' => array('1000' => 'Tablet (unter 1000 px)', '700' => 'Handy (unter 700 px)', 'never' => 'Nie')),
				array('name' => 'hover_border', 'type' => 'toggle', 'label' => 'Rahmen beim Überfahren (split-hover-border)'),
				self::classesField('inner-container')
			)
		);
	}

	/**
	 * Convert data of the older two column version (left_* and right_* fields) into panes.
	 *
	 * @param array $config
	 * @return array|null
	 */
	private static function legacyPanes(array $config): ?array {
		$legacy = false;

		foreach (array('left', 'right') as $side) {
			if (!empty($config["{$side}_content"]) || !empty($config["{$side}_image"]) || !empty($config["{$side}_type"])) {
				$legacy = true;
			}
		}

		// New data always contains panes, older data never does.
		if (!$legacy || array_key_exists('panes_version', $config) || self::panesChanged($config['panes'] ?? array())) {
			return null;
		}

		$panes = array();

		foreach (array('left', 'right') as $side) {
			$type = (string) ($config["{$side}_type"] ?? 'markdown');
			$content = (string) ($config["{$side}_content"] ?? '');
			$image = (string) ($config["{$side}_image"] ?? '');

			$panes[] = array(
				'type' => $type,
				'width' => 'auto',
				'content' => $type === 'markdown' ? $content : '',
				'image' => $image !== '' ? $image : ($type === 'image' ? $content : ''),
				'map_config' => is_array($config["{$side}_map_config"] ?? null) ? $config["{$side}_map_config"] : array()
			);
		}

		return $panes;
	}

	/**
	 * Build the template context of a pane.
	 *
	 * @param array $pane
	 * @param int $index
	 * @param int $count
	 * @return array
	 */
	private function paneContext(array $pane, int $index, int $count): array {
		$type = (string) ($pane['type'] ?? 'markdown');
		$width = (string) ($pane['width'] ?? 'auto');
		$map = is_array($pane['map_config'] ?? null) ? $pane['map_config'] : array();
		$center = $map['center'] ?? (isset($map['center_lat'], $map['center_lng']) ? $map['center_lat'] . ',' . $map['center_lng'] : '51.0,10.0');
		$position = $count === 1 ? 'split-left' : ($index === 0 ? 'split-left' : ($index === $count - 1 ? 'split-right' : 'split-middle'));

		return array(
			'title' => (string) ($pane['title'] ?? ''),
			'position' => $position,
			'classes' => trim((string) ($pane['classes'] ?? '')),
			'style' => isset(self::WIDTHS[$width]) && $width !== 'auto' ? "--split-width: $width%;" : '',
			'is_auto' => !isset(self::WIDTHS[$width]) || $width === 'auto',
			'is_markdown' => $type === 'markdown',
			'is_image' => $type === 'image',
			'is_leaflet' => $type === 'leaflet',
			'is_video' => $type === 'video',
			'is_html' => $type === 'html',
			'html_content' => $type === 'markdown' ? $this->parser->render((string) ($pane['content'] ?? '')) : '',
			'html' => $type === 'html' ? (string) ($pane['html'] ?? '') : '',
			'image' => (string) ($pane['image'] ?? ''),
			'image_alt' => (string) ($pane['image_alt'] ?? ''),
			'video_embed' => $type === 'video' ? self::youtubeEmbedUrl((string) ($pane['video'] ?? '')) : '',
			'map_height' => preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) ($map['height'] ?? '320px')),
			'map_json' => $type === 'leaflet' ? $this->json(array(
				'center' => \Automad\Party\LeafletMap\LeafletMap::parseCenter(is_array($center) ? join(',', $center) : (string) $center),
				'zoom' => (int) ($map['zoom'] ?? 6),
				'tileUrl' => $map['tile_url'] ?? 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
				'markers' => \Automad\Party\LeafletMap\LeafletMap::normalizeMarkers(is_array($map['markers'] ?? null) ? $map['markers'] : array()),
				'geojson' => is_array($map['geojson'] ?? null) ? $map['geojson'] : null
			)) : ''
		);
	}

	/**
	 * Test whether panes differ from the defaults.
	 *
	 * @param array $panes
	 * @return bool
	 */
	private static function panesChanged(array $panes): bool {
		foreach ($panes as $pane) {
			if (!is_array($pane)) {
				continue;
			}

			$content = trim((string) ($pane['content'] ?? ''));

			if (!in_array($content, array('', 'Linke Spalte …', 'Rechte Spalte …'), true) || !empty($pane['image']) || !empty($pane['html']) || !empty($pane['video']) || ($pane['type'] ?? 'markdown') !== 'markdown') {
				return true;
			}
		}

		return false;
	}
}
