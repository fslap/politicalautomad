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
namespace Automad\Party\Markdown;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

require_once __DIR__ . '/lib/Parsedown.php';

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Represents a split view of concept to be shown
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 *
 */
class Concept extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
    private \Parsedown $parser;

    public function __construct() {
        parent::__construct(__DIR__, 'markdown');
        $this->parser = new \Parsedown();
        $this->parser->setSafeMode(true);
        $this->parser->setMarkupEscaped(true);
    }

    public function context(array $config): array
    {
        $markdown_content = $config['markdown_content'] ?? '';
        $html_content = $this->parser->text($markdown_content);
        
        $section_image = $config['section_image'] ?? '';
        $images = explode(',', $section_image);
        
        return [
            'section_id' => $config['section_id'] ?? '',
            'title' => $config['title'] ?? '',
            'desktop_image' => !empty($images[0]) ? trim($images[0]) : '',
            'mobile_image' => isset($images[1]) ? trim($images[1]) : (!empty($images[0]) ? trim($images[0]) : ''),
            'html_content' => $html_content,
            'classes' => $this->getClasses(),
            'has_inner_container' => $this->hasInnerContainer()
        ];
    }
}