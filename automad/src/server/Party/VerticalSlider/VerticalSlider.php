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
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component Vertical Slider (politicalpartysite → Automad Party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 *
 */
class VerticalSlider extends AbstractDynamicTemplateBlock {
	use ComponentConfig;


	private array $registry;

	public function __construct() {
		parent::__construct(__DIR__, 'verticalslider');
		$this->registry = require __DIR__ . '/config/sections.php';
	}

	private function normalizeType(string $type): string {
		$t = strtoupper($type);
		if ($t === 'MARKDOWN_SECTION') return 'markdown';
		if ($t === 'QUOTE_SECTION') return 'quote';
		if ($t === 'PARTY_HEADER') return 'party-header';
		if ($t === 'DOCUMENT_SECTION') return 'documents';
		if ($t === 'LEAFLET_MAP') return 'leaflet';
		if ($t === 'SPLIT_SECTION') return 'split';
		if ($t === 'SECURITY_CONCEPT') return 'security-concept';
		if ($t === 'MUSIC_PLAYER') return 'music-player';
		if ($t === 'VIEWBOX_HOVER') return 'viewbox-hover';
		if ($t === 'WATER_ANIMATION') return 'water-animation';
		if ($t === 'LANDSCAPE_SCENE') return 'landscape-scene';
		if ($t === 'SCROLL_REFERENCE') return 'scroll-reference';
		if ($t === 'SCROLL_ELEMENT') return 'scroll-element';
		if (strpos($t, 'WP_') === 0) {
			return strtolower(str_replace('_', '-', $t));
		}
		return strtolower(str_replace('_', '-', $t));
	}

	private function findSectionConfig(string $type): ?array {
		foreach ($this->registry as $entry) {
			if (($entry['type'] ?? '') === $type) {
				return $entry;
			}
		}
		return null;
	}

	public function context(array $config): array {
		$items = [];
		foreach (($config['items'] ?? []) as $item) {
			if (!is_array($item)) continue;
			$raw_type = $item['type'] ?? ($item['section_type'] ?? '');
			if ($raw_type === '') continue;
			$type = $this->normalizeType((string)$raw_type);

			$entry = $this->findSectionConfig($type);
			if (!$entry) continue;

			$componentName = $entry['component'] ?? '';
			if ($componentName === '' || !isset(All::$components[$componentName])) continue;

			$itemConfig = $item;
			unset($itemConfig['type'], $itemConfig['section_type']);

			$class = All::$components[$componentName];
			$component = new $class();
			$data = $component->context($itemConfig);

			$flag = 'is_' . str_replace('-', '_', $type);
			$items[] = array_merge($data, [
				'type' => $type,
				'template_twig' => $entry['template_twig'] ?? ($entry['template'] ?? ''),
				'template_mustache' => $entry['template_mustache'] ?? '',
				'engine' => $entry['engine'] ?? 'auto',
				$flag => true
			]);
		}

		return [
			'section_id' => $config['section_id'] ?? '',
			'title' => $config['title'] ?? '',
			'items' => $items,
			'classes' => $this->getClasses($config),
			'has_inner_container' => $this->hasInnerContainer($config)
		];
	}

}

