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

namespace Automad\Party\SlideListForm;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Form\Form;
use Automad\Party\ListCard\ListCard;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\Traits\SlideKit;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Folie mit Listenkarte und Formular, die Ecke zeigt beim Hover mehr (Skizze: Satzung / Mitglied werden).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class SlideListForm extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	use SlideKit;

	public function __construct() {
		parent::__construct(__DIR__, 'slidelistform');
	}

	public function context(array $config): array {
		$form = self::unprefix($config, 'form_');
		$form['classes'] = 'party-form--card';

		return array_merge($this->slideContext($config, 'listform'), array(
			'list_html' => (new ListCard())->renderInner(self::unprefix($config, 'list_')),
			'form_html' => (new Form())->renderInner($form)
		));
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			...self::slideFields('Mitglied werden', 'party-slide--paper', 'bottom-right'),
			...self::prefixFields(ListCard::elementFields(), 'list_', 'Liste'),
			...self::prefixFields(self::blockFields(new Form(), array('classes')), 'form_', 'Formular')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Folie: Satzung und Formular',
			'icon' => 'ui-checks',
			'description' => 'Folie mit Listenkarte und Formular, die Ecke zeigt beim Hover mehr (Skizze: Satzung / Mitglied werden).',
			'fields' => self::elementFields()
		);
	}
}
