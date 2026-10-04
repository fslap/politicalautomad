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

namespace Automad\Party\SecurityConcept;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component SecurityConcept (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class SecurityConcept extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'securityconcept');
	}

	public function context(array $config): array {
		$images = $this->splitImages((string) $config['main_image']);

		return array(
			'section_id' => $config['section_id'],
			'title' => $config['title'],
			'subtitle' => $config['subtitle'],
			'subtitle_emphasis' => $config['subtitle_emphasis'],
			'desktop_image' => $images['desktop'],
			'mobile_image' => $images['mobile'],
			'feature_prefix' => $config['feature_prefix'],
			'features' => $config['features'],
			'classes' => $this->getClasses($config),
			'has_inner_container' => $this->hasInnerContainer($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Konzept / Feature-Liste',
			'icon' => 'shield-check',
			'description' => 'Halbe-Halbe-Section mit Bild, Titel, Untertiteln und Aufzählung.',
			'fields' => array(
				self::sectionIdField(),
				self::titleField('Sicherheit'),
				array('name' => 'subtitle', 'type' => 'text', 'label' => 'Untertitel'),
				array('name' => 'subtitle_emphasis', 'type' => 'text', 'label' => 'Untertitel (kursiv)'),
				array('name' => 'main_image', 'type' => 'text', 'label' => 'Bild (desktop[,mobil])', 'help' => 'Desktop-Bild, optional mit Komma getrennt ein Mobil-Bild: desktop.jpg,mobile.jpg'),
				array('name' => 'feature_prefix', 'type' => 'text', 'label' => 'Aufzählungszeichen', 'default' => '-'),
				array('name' => 'features', 'type' => 'strings', 'label' => 'Punkte (einer pro Zeile)', 'default' => array('Punkt 1', 'Punkt 2')),
				self::classesField('inner-container')
			)
		);
	}
}
