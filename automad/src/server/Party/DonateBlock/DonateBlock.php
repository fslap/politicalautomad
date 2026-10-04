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

namespace Automad\Party\DonateBlock;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component DonateBlock (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class DonateBlock extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'donateblock');
	}

	public function context(array $config): array {
		$amounts = array_values(array_filter(array_map(fn ($amount) => trim((string) $amount), $config['amounts']), 'strlen'));

		return array(
			'section_id' => $config['section_id'],
			'title' => $config['title'],
			'message' => $config['message'],
			'amounts' => array_map(fn ($amount) => array('value' => $amount, 'currency' => $config['currency']), $amounts),
			'button_text' => $config['button_text'],
			'donate_url' => $config['donate_url'],
			'note' => $config['note'],
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Spenden',
			'icon' => 'cash-coin',
			'description' => 'Spendenaufruf mit Betrags-Buttons.',
			'fields' => array(
				self::sectionIdField('donate'),
				self::titleField('Unterstütze unsere Bewegung'),
				array('name' => 'message', 'type' => 'textarea', 'label' => 'Text', 'default' => 'Jeder Beitrag stärkt unsere Stimme.'),
				array('name' => 'amounts', 'type' => 'strings', 'label' => 'Beträge (einer pro Zeile)', 'default' => array('10', '25', '50', '100')),
				array('name' => 'currency', 'type' => 'text', 'label' => 'Währungszeichen', 'default' => '€'),
				array('name' => 'button_text', 'type' => 'text', 'label' => 'Button-Text', 'default' => 'Jetzt spenden'),
				array('name' => 'donate_url', 'type' => 'url', 'label' => 'Spenden-Link', 'default' => '#donate-form', 'help' => 'Der gewählte Betrag wird als Parameter "amount" angehängt.'),
				array('name' => 'note', 'type' => 'text', 'label' => 'Hinweis', 'default' => 'Sicher & verschlüsselt spenden'),
				self::classesField('bgorange')
			)
		);
	}
}
