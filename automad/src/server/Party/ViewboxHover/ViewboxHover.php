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

namespace Automad\Party\ViewboxHover;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component ViewboxHover (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class ViewboxHover extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'viewboxhover');
	}

	public function context(array $config): array {
		$sectionId = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $config['section_id']) ?: 'viewbox';
		$images = array_values(array_filter($config['images'], fn ($image) => is_array($image) && !empty($image['file'])));
		$count = count($images);

		foreach ($images as $index => &$image) {
			$x = is_numeric($image['x_percent'] ?? null) ? (float) $image['x_percent'] : 0;
			$y = is_numeric($image['y_percent'] ?? null) ? (float) $image['y_percent'] : 0;
			$custom = $x != 0 || $y != 0;

			if ($custom) {
				$style = "position: absolute; top: {$y}%; left: {$x}%;";
			} elseif ($index === 0) {
				$style = 'position: relative;';
			} else {
				$offset = $index * 15;
				$style = "position: absolute; top: {$offset}px; left: {$offset}px;";
			}

			$image['index'] = $index;
			$image['input_id'] = "viewbox-$sectionId-$index";
			$image['checked'] = $index === 0;
			$image['style'] = $style . ' z-index: ' . ($count - $index) . ';';
			$image['lines'] = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) ($image['description'] ?? '')) ?: array())));
		}

		return array(
			'section_id' => $sectionId,
			'title' => $config['title'],
			'description' => $config['description'],
			'info_position' => $config['info_position'] === 'left' ? 'left' : 'right',
			'images' => $images,
			'classes' => $this->getClasses($config),
			'has_inner_container' => $this->hasInnerContainer($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Viewbox-Stapel',
			'icon' => 'stack',
			'description' => 'Gestapelte Bilder – ein Klick zeigt die zugehörige Info.',
			'fields' => array(
				self::sectionIdField('viewbox'),
				self::titleField(),
				array('name' => 'description', 'type' => 'textarea', 'label' => 'Beschreibung'),
				array('name' => 'info_position', 'type' => 'select', 'label' => 'Position der Info', 'default' => 'right', 'options' => array('left' => 'Links', 'right' => 'Rechts')),
				array(
					'name' => 'images',
					'type' => 'list',
					'label' => 'Bilder',
					'itemTitle' => 'title',
					'fields' => array(
						array('name' => 'file', 'type' => 'image', 'label' => 'Bild'),
						array('name' => 'title', 'type' => 'text', 'label' => 'Titel'),
						array('name' => 'description', 'type' => 'textarea', 'label' => 'Beschreibung'),
						array('name' => 'x_percent', 'type' => 'number', 'label' => 'Position X (%)'),
						array('name' => 'y_percent', 'type' => 'number', 'label' => 'Position Y (%)')
					)
				),
				self::classesField('inner-container')
			)
		);
	}
}
