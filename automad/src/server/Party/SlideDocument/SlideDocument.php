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

namespace Automad\Party\SlideDocument;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\DocumentCard\DocumentCard;
use Automad\Party\InfoStack\InfoStack;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\Traits\SlideKit;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Folie mit Dokumentkarte, Info-Stapel und Hover-Deko (Skizze: Folie 2).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class SlideDocument extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	use SlideKit;

	public function __construct() {
		parent::__construct(__DIR__, 'slidedocument');
	}

	public function context(array $config): array {
		return array_merge($this->slideContext($config, 'document'), array(
			'doc_html' => (new DocumentCard())->renderInner(self::unprefix($config, 'doc_')),
			'info_html' => (new InfoStack())->renderInner(self::unprefix($config, 'info_'))
		));
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			...self::slideFields('Dokument', 'party-slide--paper', ''),
			...self::prefixFields(DocumentCard::elementFields(), 'doc_', 'Dokument'),
			...self::prefixFields(InfoStack::elementFields(), 'info_', 'Info')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Folie: Dokument',
			'icon' => 'file-earmark-richtext',
			'description' => 'Folie mit Dokumentkarte, Info-Stapel und Hover-Deko (Skizze: Folie 2).',
			'fields' => self::elementFields()
		);
	}
}
