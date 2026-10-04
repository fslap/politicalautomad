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

	private ?CharMarkdown $parser = null;

	public function __construct() {
		parent::__construct(__DIR__, 'split');
	}

	public function context(array $config): array {
		if ($this->parser === null) {
			$this->parser = new CharMarkdown();
		}

		$images = $this->splitImages((string) $config['section_image']);
		$ctx = array(
			'section_id' => $config['section_id'],
			'title' => $config['title'],
			'desktop_image' => $images['desktop'],
			'mobile_image' => $images['mobile'] !== $images['desktop'] ? $images['mobile'] : '',
			'split_hover_border' => strpos($this->getClasses($config), 'split-hover-border') !== false,
			'classes' => $this->getClasses($config),
			'has_inner_container' => $this->hasInnerContainer($config)
		);

		foreach (array('left', 'right') as $side) {
			$type = (string) $config["{$side}_type"];
			$content = (string) $config["{$side}_content"];
			$image = (string) $config["{$side}_image"];

			// Older data stores the image URL in the content field.
			if ($type === 'image' && $image === '') {
				$image = $content;
			}

			$map = is_array($config["{$side}_map_config"]) ? $config["{$side}_map_config"] : array();
			$center = $map['center'] ?? (isset($map['center_lat'], $map['center_lng']) ? $map['center_lat'] . ',' . $map['center_lng'] : '51.0,10.0');

			$ctx["{$side}_is_markdown"] = $type === 'markdown';
			$ctx["{$side}_is_image"] = $type === 'image';
			$ctx["{$side}_is_leaflet"] = $type === 'leaflet';
			$ctx["{$side}_html_content"] = $type === 'markdown' ? $this->parser->render($content) : '';
			$ctx["{$side}_image"] = $image;
			$ctx["{$side}_map_height"] = preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) ($map['height'] ?? '320px'));
			$ctx["{$side}_map_json"] = $this->json(array(
				'center' => \Automad\Party\LeafletMap\LeafletMap::parseCenter(is_array($center) ? join(',', $center) : (string) $center),
				'zoom' => (int) ($map['zoom'] ?? 6),
				'tileUrl' => $map['tile_url'] ?? 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
				'markers' => \Automad\Party\LeafletMap\LeafletMap::normalizeMarkers(is_array($map['markers'] ?? null) ? $map['markers'] : array()),
				'geojson' => is_array($map['geojson'] ?? null) ? $map['geojson'] : null
			));
		}

		return $ctx;
	}

	protected function definition(): array {
		return array(
			'title' => 'Split (zweispaltig)',
			'icon' => 'layout-split',
			'description' => 'Zwei Spalten, jeweils mit Markdown, Bild oder Karte.',
			'fields' => array(
				self::sectionIdField(),
				self::titleField(),
				array('name' => 'section_image', 'type' => 'text', 'label' => 'Section-Bild (desktop[,mobil])'),
				array('name' => 'left_type', 'type' => 'select', 'label' => 'Links: Typ', 'default' => 'markdown', 'options' => array('markdown' => 'Markdown', 'image' => 'Bild', 'leaflet' => 'Karte')),
				array('name' => 'left_content', 'type' => 'markdown', 'label' => 'Links: Inhalt (Markdown)'),
				array('name' => 'left_image', 'type' => 'image', 'label' => 'Links: Bild'),
				array('name' => 'left_map_config', 'type' => 'json', 'label' => 'Links: Karten-Konfiguration', 'default' => array('height' => '320px', 'center' => '51.0,10.0', 'zoom' => 6, 'geojson' => null)),
				array('name' => 'right_type', 'type' => 'select', 'label' => 'Rechts: Typ', 'default' => 'markdown', 'options' => array('markdown' => 'Markdown', 'image' => 'Bild', 'leaflet' => 'Karte')),
				array('name' => 'right_content', 'type' => 'markdown', 'label' => 'Rechts: Inhalt (Markdown)'),
				array('name' => 'right_image', 'type' => 'image', 'label' => 'Rechts: Bild'),
				array('name' => 'right_map_config', 'type' => 'json', 'label' => 'Rechts: Karten-Konfiguration', 'default' => array('height' => '320px', 'center' => '51.0,10.0', 'zoom' => 6, 'geojson' => null)),
				self::classesField('inner-container')
			)
		);
	}
}
