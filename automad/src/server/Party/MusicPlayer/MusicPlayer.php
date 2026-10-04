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

namespace Automad\Party\MusicPlayer;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component MusicPlayer (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class MusicPlayer extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'musicplayer');
	}

	public function context(array $config): array {
		$sectionId = $config['section_id'] ?: 'music_player_' . substr(md5($this->json($config)), 0, 6);

		return array(
			'title' => $config['title'],
			'section_id' => $sectionId,
			'player_width' => $config['player_width'],
			'player_height' => $config['player_height'],
			'iframe_border_radius' => $config['iframe_border_radius'],
			'default_source' => $config['default_source'],
			'platforms' => $config['platforms'],
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Musik-Player',
			'icon' => 'music-note-beamed',
			'description' => 'Eingebetteter Player (iframe) mit Umschaltung zwischen Plattformen.',
			'fields' => array(
				self::titleField('Musik'),
				self::sectionIdField('music_player'),
				array('name' => 'default_source', 'type' => 'url', 'label' => 'Standard-Quelle (Embed-URL)'),
				array('name' => 'player_width', 'type' => 'text', 'label' => 'Breite', 'default' => '100%'),
				array('name' => 'player_height', 'type' => 'text', 'label' => 'Höhe', 'default' => '352'),
				array('name' => 'iframe_border_radius', 'type' => 'text', 'label' => 'Eckenradius', 'default' => '12px'),
				array(
					'name' => 'platforms',
					'type' => 'list',
					'label' => 'Plattformen',
					'itemTitle' => 'text',
					'fields' => array(
						array('name' => 'text', 'type' => 'text', 'label' => 'Name'),
						array('name' => 'url', 'type' => 'url', 'label' => 'Embed-URL'),
						array('name' => 'class', 'type' => 'select', 'label' => 'Stil', 'default' => 'wp-element-button', 'options' => array('wp-element-button' => 'Button', 'spotify' => 'Spotify', 'tidal' => 'Tidal', 'youtube' => 'YouTube', 'soundcloud' => 'SoundCloud'))
					)
				),
				self::classesField('inner-container')
			)
		);
	}
}
