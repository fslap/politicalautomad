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

namespace Automad\Party\MarkdownViewer;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Kapitel mit Lang-, Zusammenfassungs- und Kurzfassung, blättern mit Pfeilen (Skizze: Last Page).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class MarkdownViewer extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'markdownviewer');
	}

	public function context(array $config): array {
		$md = fn ($text) => trim((string) $text) === '' ? '' : \Automad\Core\Str::markdown((string) $text);
		$chapters = array();

		foreach (is_array($config['chapters']) ? $config['chapters'] : array() as $chapter) {
			if (!is_array($chapter)) {
				continue;
			}

			$full = $md($chapter['full'] ?? '');
			$summary = $md($chapter['summary'] ?? '') ?: $full;
			$short = $md($chapter['short'] ?? '') ?: $summary;

			if ($full === '' && $summary === '') {
				continue;
			}

			$chapters[] = array(
				'index' => count($chapters),
				'title' => (string) ($chapter['title'] ?? ''),
				'full' => $full ?: $summary,
				'summary' => $summary,
				'short' => $short,
				'first' => empty($chapters)
			);
		}

		$mode = in_array($config['mode'], array('full', 'summary', 'short'), true) ? $config['mode'] : 'summary';

		return array(
			'title' => $config['title'],
			'chapters' => $chapters,
			'count' => count($chapters),
			'multiple' => count($chapters) > 1,
			'mode' => $mode,
			'classes' => $this->getClasses($config)
		);
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			array('name' => 'title', 'type' => 'text', 'label' => 'Titel', 'default' => ''),
			array(
				'name' => 'chapters',
				'type' => 'list',
				'label' => 'Kapitel',
				'addLabel' => 'Kapitel hinzufügen',
				'itemTitle' => 'title',
				'default' => array(),
				'fields' => array(
					array('name' => 'title', 'type' => 'text', 'label' => 'Titel'),
					array('name' => 'full', 'type' => 'markdown', 'label' => 'Eröffnen (ganzer Text)'),
					array('name' => 'summary', 'type' => 'markdown', 'label' => 'Zusammengefasst'),
					array('name' => 'short', 'type' => 'markdown', 'label' => 'Gekürzt')
				)
			),
			array('name' => 'mode', 'type' => 'select', 'label' => 'Ansicht beim Laden', 'default' => 'summary', 'options' => array('full' => 'Eröffnen', 'summary' => 'Zusammengefasst', 'short' => 'Gekürzt')),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Markdown-Viewer',
			'icon' => 'book',
			'description' => 'Kapitel mit Lang-, Zusammenfassungs- und Kurzfassung, blättern mit Pfeilen (Skizze: Last Page).',
			'fields' => self::elementFields()
		);
	}
}
