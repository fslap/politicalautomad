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
namespace Automad\Party\Form;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component Form (politicalpartysite → Automad Party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 *
 */
class Form extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'form');
	}

	public function context(array $config): array
	{

        $fields = $config['fields'] ?? [];
        $fields = array_map(function ($field) {
            $type = $field['type'] ?? 'text';
            $field['is_textarea'] = ($type === 'textarea');
            $field['is_select'] = ($type === 'select');
            $field['is_input'] = (!$field['is_textarea'] && !$field['is_select']);
            $field['required_attr'] = !empty($field['required']) ? 'required' : '';
            return $field;
        }, $fields);

        return [
            'section_id' => $config['section_id'] ?? '',
            'title' => $config['title'] ?? '',
            'description' => $config['description'] ?? '',
            'form_method' => $config['form_method'] ?? 'post',
            'form_action' => $config['form_action'] ?? '',
            'turnstile_theme' => $config['turnstile_theme'] ?? 'light',
            'turnstile_site_key' => $config['turnstile_site_key'] ?? '',
            'fields' => $fields,
            'classes' => $this->getClasses($config),
            'has_inner_container' => $this->hasInnerContainer($config)
        ];
	}
}

