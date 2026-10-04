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

namespace Automad\Party\VerticalSlider;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Core\Blocks;
use Automad\Party\All;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component VerticalSlider (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class VerticalSlider extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	/**
	 * Legacy politicalpartysite section types.
	 */
	const LEGACY_TYPES = array(
		'MARKDOWN_SECTION' => 'Markdown',
		'QUOTE_SECTION' => 'Quote',
		'PARTY_HEADER' => 'PartyHeader',
		'DOCUMENT_SECTION' => 'Documents',
		'LEAFLET_MAP' => 'LeafletMap',
		'SPLIT_SECTION' => 'Split',
		'SECURITY_CONCEPT' => 'SecurityConcept',
		'MUSIC_PLAYER' => 'MusicPlayer',
		'VIEWBOX_HOVER' => 'ViewboxHover',
		'WATER_ANIMATION' => 'WaterAnimation',
		'LANDSCAPE_SCENE' => 'LandscapeScene',
		'SCROLL_REFERENCE' => 'ScrollReference',
		'SCROLL_ELEMENT' => 'ScrollElement',
		'FORM_SECTION' => 'Form',
		'REGION_SCROLL' => 'RegionScroll',
		'ART_MOTIVATION' => 'ArtMotivation'
	);

	public function __construct() {
		parent::__construct(__DIR__, 'verticalslider');
	}

	public function context(array $config): array {
		$register = Blocks::getDynamicRegister();
		$slides = array();

		foreach ($config['items'] as $item) {
			if (!is_array($item) || $register === null) {
				continue;
			}

			$type = (string) ($item['component'] ?? '');
			$data = is_array($item['data'] ?? null) ? $item['data'] : array();

			// Support politicalpartysite items like {"type": "MARKDOWN_SECTION", "title": "…"}.
			if (!empty($item['type'] ?? $item['section_type'] ?? '')) {
				$legacy = strtoupper(str_replace('-', '_', (string) ($item['type'] ?? $item['section_type'])));
				$name = self::LEGACY_TYPES[$legacy] ?? str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', $legacy))));
				$type = AbstractDynamicTemplateBlock::TYPE_PREFIX . $name;
				$data = $item;
				unset($data['type'], $data['section_type']);
			}

			if ($type === $this->type() || !$register->type($type)) {
				continue;
			}

			$block = $register->object($type);

			if ($block instanceof AbstractDynamicTemplateBlock) {
				$slides[] = array('html' => $block->renderInner($data), 'slug' => $block->slug());
			}
		}

		return array(
			'section_id' => $config['section_id'],
			'title' => $config['title'],
			'height' => preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['height']),
			'items' => $slides,
			'classes' => $this->getClasses($config),
			'has_inner_container' => $this->hasInnerContainer($config)
		);
	}

	protected function definition(): array {
		$options = array();

		foreach (All::$components as $name => $class) {
			if ($name !== 'VerticalSlider') {
				$options[AbstractDynamicTemplateBlock::TYPE_PREFIX . $name] = $name;
			}
		}

		return array(
			'title' => 'Vertikaler Slider',
			'icon' => 'layout-three-columns',
			'description' => 'Vertikal einrastende Folge von Party-Components.',
			'fields' => array(
				self::sectionIdField(),
				self::titleField(),
				array('name' => 'height', 'type' => 'text', 'label' => 'Höhe des Sliders', 'default' => '80vh'),
				array(
					'name' => 'items',
					'type' => 'list',
					'label' => 'Slides',
					'itemTitle' => 'component',
					'fields' => array(
						array('name' => 'component', 'type' => 'select', 'label' => 'Component', 'default' => 'partyMarkdown', 'options' => $options),
						array('name' => 'data', 'type' => 'json', 'label' => 'Daten (JSON)', 'default' => array('title' => 'Slide', 'markdown_content' => 'Text …'), 'help' => 'Felder der gewählten Component, z.B. {"title": "…"}')
					)
				),
				self::classesField('')
			)
		);
	}
}
