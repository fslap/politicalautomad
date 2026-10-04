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
 * \#########|   \\\|      _\    :#;:  :#;:#;:  #;:#;:#;:#;:
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

namespace Automad\Party\Traits;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Helpers ported from politicalpartysite Components/Component.php.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
trait ComponentConfig {
	/**
	 * The common field for additional CSS classes that are used inside the component template.
	 *
	 * @param string $default
	 * @return array
	 */
	protected static function classesField(string $default = ''): array {
		return array(
			'name' => 'classes',
			'type' => 'text',
			'label' => 'CSS-Klassen',
			'default' => $default,
			'placeholder' => 'inner-container bgorange',
			'help' => 'Hilfsklassen: inner-container, widthcontainer, bgorange, bgblue, bgpink, bgextrared, bgcoolgreen, bggold, bgpurple, bgwhite, overlay-up, overlay-down'
		);
	}
	protected function getClasses(array $config): string {
		return trim((string) ($config['classes'] ?? ''));
	}

	protected function hasInnerContainer(array $config): bool {
		$classes = $this->getClasses($config);

		return strpos($classes, 'inner-container') !== false ||
			strpos($classes, 'bg') === 0;
	}

	/**
	 * The common field for the anchor id of a section.
	 *
	 * @param string $default
	 * @return array
	 */
	protected static function sectionIdField(string $default = ''): array {
		return array(
			'name' => 'section_id',
			'type' => 'text',
			'label' => 'Section-ID (Anker)',
			'default' => $default,
			'placeholder' => 'z.B. themen'
		);
	}

	/**
	 * The common title field.
	 *
	 * @param string $default
	 * @return array
	 */
	protected static function titleField(string $default = ''): array {
		return array(
			'name' => 'title',
			'type' => 'text',
			'label' => 'Titel',
			'default' => $default
		);
	}
}
