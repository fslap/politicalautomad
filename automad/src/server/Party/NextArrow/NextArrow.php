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

namespace Automad\Party\NextArrow;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\Traits\SlideKit;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Doppelstrich-Pfeil zum nächsten oder vorherigen Abschnitt, optional mit Text (Skizze: Navigation).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class NextArrow extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	use SlideKit;

	public function __construct() {
		parent::__construct(__DIR__, 'nextarrow');
	}

	public function context(array $config): array {
		$direction = $config['direction'] === 'up' ? 'up' : 'down';

		return array(
			'chevrons' => self::chevrons($direction),
			'label' => $config['label'],
			'target' => trim((string) $config['target']),
			'direction' => $direction,
			'classes' => trim('next-arrow next-arrow--' . (in_array($config['align'], array('left', 'right'), true) ? $config['align'] : 'center') . ' ' . $this->getClasses($config))
		);
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			array('name' => 'direction', 'type' => 'select', 'label' => 'Richtung', 'default' => 'down', 'options' => array('down' => 'Weiter (nach unten)', 'up' => 'Zurück (nach oben)')),
			array('name' => 'label', 'type' => 'text', 'label' => 'Text', 'default' => ''),
			array('name' => 'target', 'type' => 'text', 'label' => 'Ziel (#anker, leer = nächster bzw. vorheriger Abschnitt)', 'default' => ''),
			array('name' => 'align', 'type' => 'select', 'label' => 'Ausrichtung', 'default' => 'center', 'options' => array('left' => 'Links', 'center' => 'Mitte', 'right' => 'Rechts')),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Weiter-Pfeil',
			'icon' => 'chevron-double-down',
			'description' => 'Doppelstrich-Pfeil zum nächsten oder vorherigen Abschnitt, optional mit Text (Skizze: Navigation).',
			'fields' => self::elementFields()
		);
	}
}
