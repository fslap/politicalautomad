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

namespace Automad\Party\SkylineBreaker;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component SkylineBreaker (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class SkylineBreaker extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'skylinebreaker');
	}

	public function context(array $config): array {
		$seedText = trim((string) $config['seed']);

		if ($seedText === '' || $seedText === 'auto') {
			$seedText = AM_REQUEST ?: 'fsl';
		}

		$seed = is_numeric($seedText) ? (int) $seedText : crc32($seedText);
		$rng = new \Random\Randomizer(new \Random\Engine\Mt19937($seed));
		$count = is_numeric($config['count']) && (int) $config['count'] > 0 ? min(200, (int) $config['count']) : $rng->getInt(24, 48);
		$buildings = array();

		for ($i = 0; $i < $count; $i++) {
			$h = $rng->getInt(25, 95);

			$buildings[] = array(
				'x' => $rng->getInt(0, 100),
				'w' => $rng->getInt(2, 7),
				'h' => $h,
				'y' => 100 - $h,
				'depth' => $rng->getInt(1, 3),
				'twinkle' => $rng->getInt(0, 100) < 55
			);
		}

		// Draw far buildings first.
		usort($buildings, fn ($a, $b) => $b['depth'] <=> $a['depth']);

		return array(
			'height' => max(40, (int) $config['height']),
			'seed' => $seed,
			'buildings' => $buildings,
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Skyline-Trenner',
			'icon' => 'buildings',
			'description' => 'Zufällig generierte, nächtliche Skyline als Seitentrenner.',
			'fields' => array(
				array('name' => 'height', 'type' => 'number', 'label' => 'Höhe (px)', 'default' => 320),
				array('name' => 'seed', 'type' => 'text', 'label' => 'Seed', 'default' => 'auto', 'help' => '"auto" erzeugt pro Seite eine eigene Skyline, jeder andere Text eine feste.'),
				array('name' => 'count', 'type' => 'number', 'label' => 'Anzahl Gebäude (leer = zufällig)'),
				self::classesField('')
			)
		);
	}
}
