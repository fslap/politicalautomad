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

namespace Automad\Party\EventBlock;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component EventBlock (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class EventBlock extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'eventblock');
	}

	public function context(array $config): array {
		return array(
			'section_id' => $config['section_id'],
			'title' => $config['title'],
			'events' => array_values(array_filter($config['events'], 'is_array')),
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Termine',
			'icon' => 'calendar-event',
			'description' => 'Liste kommender Veranstaltungen.',
			'fields' => array(
				self::sectionIdField('events'),
				self::titleField('Kommende Termine'),
				array(
					'name' => 'events',
					'type' => 'list',
					'label' => 'Termine',
					'itemTitle' => 'title',
					'fields' => array(
						array('name' => 'title', 'type' => 'text', 'label' => 'Titel'),
						array('name' => 'date', 'type' => 'text', 'label' => 'Datum', 'placeholder' => '24.10.2026'),
						array('name' => 'time', 'type' => 'text', 'label' => 'Uhrzeit', 'placeholder' => '19:00'),
						array('name' => 'location', 'type' => 'text', 'label' => 'Ort'),
						array('name' => 'description', 'type' => 'textarea', 'label' => 'Beschreibung'),
						array('name' => 'link', 'type' => 'url', 'label' => 'Link (Anmeldung)'),
						array('name' => 'link_text', 'type' => 'text', 'label' => 'Linktext', 'default' => 'Anmelden →')
					)
				),
				self::classesField('')
			)
		);
	}
}
