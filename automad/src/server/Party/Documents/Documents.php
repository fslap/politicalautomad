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

namespace Automad\Party\Documents;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component Documents (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class Documents extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'documents');
	}

	public function context(array $config): array {
		$classes = $this->getClasses($config);

		return array(
			'title' => $config['title'],
			'section_id' => $config['section_id'],
			'documents' => $config['documents'],
			'classes' => $classes,
			'slider_class' => strpos($classes, 'vertical-slider') !== false ? 'vertical-slider' : '',
			'has_inner_container' => $this->hasInnerContainer($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Dokumente',
			'icon' => 'file-earmark-pdf',
			'description' => 'Horizontale (oder vertikale) Liste von Dokumenten mit Vorschaubild.',
			'fields' => array(
				self::titleField('Dokumente'),
				self::sectionIdField(),
				array(
					'name' => 'documents',
					'type' => 'list',
					'label' => 'Dokumente',
					'itemTitle' => 'title',
					'default' => array(array('title' => 'Satzung', 'file' => '', 'preview' => '', 'description' => '')),
					'fields' => array(
						array('name' => 'title', 'type' => 'text', 'label' => 'Titel'),
						array('name' => 'file', 'type' => 'url', 'label' => 'Datei / Link'),
						array('name' => 'preview', 'type' => 'image', 'label' => 'Vorschaubild'),
						array('name' => 'description', 'type' => 'text', 'label' => 'Kurzbeschreibung')
					)
				),
				self::classesField('inner-container')
			)
		);
	}
}
