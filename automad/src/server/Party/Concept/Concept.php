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
namespace Automad\Party\Concept;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Represents a split view of concept to be shown
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 *
 */
class Concept extends AbstractMultiBlock {
    public __construct() {
        parent::__construct(__DIR__, 'concept');
    }

    public function context(array $config): array
    {
        $main_image = $config['main_image'] ?? '';
        $images = explode(',', $main_image);
        
        $data = [
            'section_id' => $config['section_id'] ?? '',
            'desktop_image' => trim($images[0]),
            'mobile_image' => isset($images[1]) ? trim($images[1]) : trim($images[0]),
            'title' => $config['title'] ?? '',
            'subtitle' => $config['subtitle'] ?? '',
            'subtitle_emphasis' => $config['subtitle_emphasis'] ?? '',
            'feature_prefix' => $config['feature_prefix'] ?? '✓',
            'features' => $config['features'] ?? [],
            'classes' => $this->getClasses()
        ];

        try {
            list($width, $height, $type, $attr) = getimagesize($data['desktop_image']);
            $refsrc = $data['desktop_image'];
            $data['desktop_image'] = [];
            $data['desktop_image']['width'] = /*0;*/$width;
            $data['desktop_image']['height'] = /*0;*/$height;
            $data['desktop_image']['title'] = $data['title'] . ' - Desktop';
            $data['desktop_image']['desktop_image'] = $refsrc;
        } catch (\Exception $_) {
            // stub
        }

        try {
            list($width, $height, $type, $attr) = getimagesize($data['mobile_image']);
            $refsrc = $data['mobile_image'];
            $data['mobile_image'] = [];
            $data['mobile_image']['width'] = /*0;//*/ $width;
            $data['mobile_image']['height'] = /*0;//*/$height;
            $data['mobile_image']['title'] = $data['title'] . ' - Mobile';
            $data['mobile_image']['mobile_image'] = $refsrc;
        } catch (\Exception $_) {
            // stub
        }

        return $data;
    }
}