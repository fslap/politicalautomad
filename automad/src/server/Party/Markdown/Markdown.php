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

namespace Automad\Party\Markdown;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

require_once __DIR__ . '/lib/Parsedown.php';

/**
 * Party component Markdown (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class Markdown extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	private ?\Parsedown $parser = null;

	public function __construct() {
		parent::__construct(__DIR__, 'markdown');
	}

	public function context(array $config): array {
		if ($this->parser === null) {
			$this->parser = new \Parsedown();
			$this->parser->setSafeMode(true);
		}

		$images = $this->splitImages((string) $config['section_image']);

		return array(
			'section_id' => $config['section_id'],
			'title' => $config['title'],
			'desktop_image' => $images['desktop'],
			'mobile_image' => $images['mobile'] !== $images['desktop'] ? $images['mobile'] : '',
			'html_content' => $this->parser->text((string) $config['markdown_content']),
			'classes' => $this->getClasses($config),
			'has_inner_container' => $this->hasInnerContainer($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Markdown-Section',
			'icon' => 'markdown',
			'description' => 'Section mit Titel, optionalem Bild und Markdown-Inhalt.',
			'fields' => array(
				self::sectionIdField(),
				self::titleField(),
				array('name' => 'section_image', 'type' => 'text', 'label' => 'Section-Bild (desktop[,mobil])', 'help' => 'Desktop-Bild, optional mit Komma getrennt ein Mobil-Bild: desktop.jpg,mobile.jpg'),
				array('name' => 'markdown_content', 'type' => 'markdown', 'label' => 'Markdown-Inhalt', 'default' => ''),
				self::classesField('inner-container')
			)
		);
	}
}
