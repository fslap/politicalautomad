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
namespace Automad\Party\ArtMotivation;

defined('AUTOMAD') or die('Direct access not permitted!');

use Automad\Party\AbstractMultiBlock;

/**
 * Represents a split view of concept to be shown
 *
 * Artistic / Political Motivation component.
 * Self-contained port from v2-artmotivation branch style.
 * - Array data passing (no this-ref data)
 * - Mustache first in parent
 * - Twig/Mustache templates self-contained (imports removed)
 * - Only specific assets
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class ArtMotivation extends AbstractMultiBlock
{
    protected string $templatePath = __DIR__;

    /**
     * Constructor delegates to parent (mustache first there)
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Render split view using ONLY passed data array.
     * Replace any old $this data refs with $passedData.
     *
     * Expected $passedData keys (example):
     * - title, subtitle
     * - left: [title, content, image, link]
     * - right: [title, content, image, link]
     * - cta, theme
     */
    public function render(array $passedData = []): string
    {
        if (!$this->validateData($passedData)) {
            $passedData = $this->getDefaultData();
        }

        $template = $this->getTemplate('default');
        
        // Use mustache process with passedData directly
        $html = $this->processTemplate($template, $passedData);

        // Optionally wrap or add assets link (self contained)
        if (!empty($passedData['include_assets'])) {
            $html = $this->addAssets() . $html;
        }

        return $html;
    }

    /**
     * Default data structure (for demo, not data driven from afd/military)
     */
    private function getDefaultData(): array
    {
        return [
            'title' => 'Our Motivation',
            'subtitle' => 'Split view concept for political art & motivation',
            'left' => [
                'title' => 'Vision',
                'content' => 'Building a better future together...',
                'image' => '/shared/images/vision.jpg',
                'link' => '#vision'
            ],
            'right' => [
                'title' => 'Action',
                'content' => 'Real steps for real change.',
                'image' => '/shared/images/action.jpg',
                'link' => '#action'
            ],
            'cta' => 'Join the movement',
            'theme' => 'default'
        ];
    }

    /**
     * Add component specific CSS (no theme base copied)
     */
    private function addAssets(): string
    {
        return '<link rel="stylesheet" href="/Party/ArtMotivation/assets/artmotivation.css">';
    }

    // Other methods inherited from AbstractMultiBlock (build, processBlock etc.)
    // Can override processBlock etc for specific split view logic if needed.
}