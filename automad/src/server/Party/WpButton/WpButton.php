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

namespace Automad\Party\WpButton;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component WpButton (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class WpButton extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'wpbutton');
	}

	public function context(array $config): array {
		$buttons = array_values(array_filter(
			$config['buttons'],
			fn ($button) => is_array($button) && trim((string) ($button['label'] ?? '')) !== ''
		));

		return array(
			'buttons' => $buttons,
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'WP Buttons',
			'icon' => 'hand-index',
			'description' => 'Eine Gruppe von Buttons (wp-block-buttons).',
			'fields' => array(
				array(
					'name' => 'buttons',
					'type' => 'list',
					'label' => 'Buttons',
					'itemTitle' => 'label',
					'default' => array(array('label' => 'Mitmachen', 'href' => '#', 'classes' => 'wp-element-button', 'target' => '')),
					'fields' => array(
						array('name' => 'label', 'type' => 'text', 'label' => 'Beschriftung'),
						array('name' => 'href', 'type' => 'url', 'label' => 'Link'),
						array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => 'wp-element-button'),
						array('name' => 'target', 'type' => 'select', 'label' => 'Ziel', 'options' => array('' => 'Gleiches Fenster', '_blank' => 'Neues Fenster'))
					)
				),
				self::classesField('wp-block-buttons')
			)
		);
	}
}
