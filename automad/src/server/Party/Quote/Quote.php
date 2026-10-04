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

namespace Automad\Party\Quote;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component Quote (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class Quote extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'quote');
	}

	public function context(array $config): array {
		$images = $this->splitImages((string) $config['section_image']);

		return array(
			'section_id' => $config['section_id'],
			'title' => $config['title'],
			'desktop_image' => $images['desktop'],
			'mobile_image' => $images['mobile'] !== $images['desktop'] ? $images['mobile'] : '',
			'quote_text' => $config['quote_text'],
			'quote_author' => $config['quote_author'],
			'classes' => $this->getClasses($config),
			'has_inner_container' => $this->hasInnerContainer($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Zitat-Section',
			'icon' => 'chat-quote',
			'description' => 'Großes Zitat mit Autor, Titel und optionalem Bild.',
			'fields' => array(
				self::sectionIdField(),
				self::titleField(),
				array('name' => 'section_image', 'type' => 'text', 'label' => 'Section-Bild (desktop[,mobil])', 'help' => 'Desktop-Bild, optional mit Komma getrennt ein Mobil-Bild: desktop.jpg,mobile.jpg'),
				array('name' => 'quote_text', 'type' => 'textarea', 'label' => 'Zitat', 'default' => 'Zitat'),
				array('name' => 'quote_author', 'type' => 'text', 'label' => 'Autor'),
				self::classesField('inner-container')
			)
		);
	}
}
