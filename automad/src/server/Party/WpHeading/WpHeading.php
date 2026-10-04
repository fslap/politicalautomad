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

namespace Automad\Party\WpHeading;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component WpHeading (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class WpHeading extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'wpheading');
	}

	public function context(array $config): array {
		$level = strtolower((string) $config['level']);

		if (!in_array($level, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'), true)) {
			$level = 'h2';
		}

		$classes = $this->getClasses($config);
		$classAttr = $classes ? ' class="' . htmlspecialchars($classes, ENT_QUOTES) . '"' : '';

		return array(
			'level' => $level,
			'text' => $config['text'],
			'classes' => $classes,
			'html' => "<$level$classAttr>" . htmlspecialchars((string) $config['text'], ENT_QUOTES, 'UTF-8') . "</$level>"
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'WP Überschrift',
			'icon' => 'type-h1',
			'description' => 'Überschrift im WordPress-Block-Stil (wp-block-heading).',
			'fields' => array(
				array('name' => 'level', 'type' => 'select', 'label' => 'Ebene', 'default' => 'h2', 'options' => array('h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6')),
				array('name' => 'text', 'type' => 'text', 'label' => 'Text', 'default' => 'Überschrift'),
				self::classesField('wp-block-heading')
			)
		);
	}
}
