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
namespace Automad\Party\EventBlock;

defined('AUTOMAD') or die('Direct access not permitted!');

use Automad\Party\AbstractMultiBlock;

/**
 * Events / Calendar block component for political party sites.
 * Self-contained following v2- branch rules exactly.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class EventBlock extends AbstractMultiBlock
{
    protected string $templatePath = __DIR__;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

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
            'title' => 'Upcoming Events',
            'events' => [
                [
                    'date' => '2026-07-12',
                    'time' => '18:00',
                    'title' => 'Town Hall Meeting',
                    'location' => 'Community Center, Main Square',
                    'description' => 'Open discussion about local policies.'
                ],
                [
                    'date' => '2026-07-20',
                    'time' => '14:00',
                    'title' => 'Climate Action Workshop',
                    'location' => 'Green Park Pavilion',
                    'description' => 'Hands-on workshop for sustainable living.'
                ]
            ]
        ];
    }
}