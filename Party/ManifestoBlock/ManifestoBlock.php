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
namespace Automad\Party\ManifestoBlock;

defined('AUTOMAD') or die('Direct access not permitted!');

use Automad\Party\AbstractMultiBlock;

/**
 * Manifesto / Political Program multi-block component.
 * 
 * Self-contained port following v2- branch pattern.
 * Array data only, mustache-first via parent, templates without external imports.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class ManifestoBlock extends AbstractMultiBlock
{
    protected string $templatePath = __DIR__;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Render using ONLY passed $passedData array (no $this data refs)
     */
    public function render(array $passedData = []): string
    {
        if (!$this->validateData($passedData)) {
            $passedData = $this->getDefaultData();
        }

        return $this->processTemplate($this->getTemplate('default'), $passedData);
    }

    private function getDefaultData(): array
    {
        return [
            'title' => 'Our Manifesto',
            'intro' => 'Core principles and concrete steps for change.',
            'sections' => [
                [
                    'title' => 'Climate & Future',
                    'content' => 'Bold action on environment and sustainability.',
                    'points' => ['Net zero by 2035', 'Green jobs program', 'Protect biodiversity']
                ],
                [
                    'title' => 'Social Justice',
                    'content' => 'Equality, housing, education for all.',
                    'points' => ['Affordable housing', 'Free education', 'Universal healthcare access']
                ]
            ],
            'cta' => 'Read the full program'
        ];
    }
}