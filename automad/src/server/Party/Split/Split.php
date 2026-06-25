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
namespace Automad\Party\Split;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component Split (politicalpartysite → Automad Party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 *
 */
class Split extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	private \Lib\CharMarkdown $parser;

	public function __construct() {
		parent::__construct(__DIR__, 'split');
		require_once __DIR__ . '/lib/CharMarkdown.php';
		$this->parser = new \Lib\CharMarkdown();

	}

	public function context(array $config): array
	{

        $section_image = $config['section_image'] ?? '';
        $images = explode(',', $section_image);
        
        // Parse markdown content
        $left_html = '';
        $right_html = '';
        
        if (($config['left_type'] ?? 'markdown') === 'markdown') {
            $left_html = $this->parsedown->render($config['left_content'] ?? '');
        }
        
        if (($config['right_type'] ?? 'markdown') === 'markdown') {
            $right_html = $this->parsedown->render($config['right_content'] ?? '');
        }

        $left_type = $config['left_type'] ?? 'markdown';
        $right_type = $config['right_type'] ?? 'markdown';

        $left_map_id = 'split_map_left_' . uniqid();
        $right_map_id = 'split_map_right_' . uniqid();

        $left_map_config = $config['left_map_config'] ?? [];
        $right_map_config = $config['right_map_config'] ?? [];
        $left_map_config['map_id'] = $left_map_id;
        $right_map_config['map_id'] = $right_map_id;

        return [
            'section_id' => $config['section_id'] ?? '',
            'title' => $config['title'] ?? '',
            'desktop_image' => !empty($images[0]) ? trim($images[0]) : '',
            'mobile_image' => isset($images[1]) ? trim($images[1]) : (!empty($images[0]) ? trim($images[0]) : ''),
            'left_content' => $config['left_content'] ?? '',
            'right_content' => $config['right_content'] ?? '',
            'left_type' => $left_type,
            'right_type' => $right_type,
            'left_html_content' => $left_html,
            'right_html_content' => $right_html,
            'left_map_config' => $left_map_config,
            'right_map_config' => $right_map_config,
            'left_map_id' => $left_map_id,
            'right_map_id' => $right_map_id,
            'left_is_markdown' => $left_type === 'markdown',
            'left_is_image' => $left_type === 'image',
            'left_is_leaflet' => $left_type === 'leaflet',
            'right_is_markdown' => $right_type === 'markdown',
            'right_is_image' => $right_type === 'image',
            'right_is_leaflet' => $right_type === 'leaflet',
            'split_hover_border' => strpos($this->getClasses($config), 'split-hover-border') !== false,
            'classes' => $this->getClasses($config),
            'has_inner_container' => $this->hasInnerContainer($config)
        ];
	}
}

