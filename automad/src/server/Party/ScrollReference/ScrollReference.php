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
namespace Automad\Party\ScrollReference;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Represents a split view of concept to be shown
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 *
 */
class ScrollReference extends AbstractMultiBlock {
    public __construct() {
        parent::__construct(__DIR__, 'scrollreference');
    }

    public function context(array $config): array
    {
        $items = [];

        if (isset($config['items']) && is_array($config['items'])) {
            $items = $config['items'];
        }

        if (empty($items) && !empty($config['json']) && file_exists($config['json'])) {
            $decoded = json_decode(file_get_contents($config['json']), true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        if (isset($items['items']) && is_array($items['items'])) {
            $items = $items['items'];
        }

        if (isset($config['match']) && $config['match'] !== '') {
            $match = $config['match'];
            $items = array_values(array_filter(
                $items,
                function ($item, $idx) use ($match) {
                    if (is_numeric($match) && (int)$match === (int)$idx) {
                        return true;
                    }

                    if (isset($item['id']) && (string)$item['id'] === (string)$match) {
                        return true;
                    }

                    if (isset($item['subject']) && stripos((string)$item['subject'], (string)$match) !== false) {
                        return true;
                    }

                    return false;
                },
                ARRAY_FILTER_USE_BOTH
            ));
        }

        return [
            'items' => $items,
            'horizontal' => true,
            'classes' => $this->getClasses(),
            'has_inner_container' => $this->hasInnerContainer()
        ];
    }
}