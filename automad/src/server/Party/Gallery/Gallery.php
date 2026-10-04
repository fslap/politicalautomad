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

namespace Automad\Party\Gallery;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component Gallery (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class Gallery extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'gallery');
	}

	public function context(array $config): array {
		$images = array();

		foreach ($config['images'] as $image) {
			// Support plain string lists from older data.
			if (is_string($image)) {
				$image = array('src' => $image, 'alt' => '', 'caption' => '');
			}

			if (!is_array($image) || empty($image['src'])) {
				continue;
			}

			$images[] = array(
				'src' => $image['src'],
				'alt' => $image['alt'] ?? '',
				'caption' => $image['caption'] ?? ''
			);
		}

		return array(
			'section_id' => $config['section_id'],
			'title' => $config['title'],
			'images' => $images,
			'columns' => max(1, min(6, (int) $config['columns'])),
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Bildergalerie',
			'icon' => 'images',
			'description' => 'Raster aus Bildern mit optionalen Bildunterschriften.',
			'fields' => array(
				self::sectionIdField(),
				self::titleField(),
				array(
					'name' => 'images',
					'type' => 'list',
					'label' => 'Bilder',
					'itemTitle' => 'caption',
					'fields' => array(
						array('name' => 'src', 'type' => 'image', 'label' => 'Bild'),
						array('name' => 'alt', 'type' => 'text', 'label' => 'Alternativtext'),
						array('name' => 'caption', 'type' => 'text', 'label' => 'Bildunterschrift')
					)
				),
				array('name' => 'columns', 'type' => 'number', 'label' => 'Spalten', 'default' => 3),
				self::classesField('')
			)
		);
	}
}
