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
namespace Automad\Party\Form;

defined('AUTOMAD') or die('Direct access not permitted!');

use Automad\Party\AbstractMultiBlock;

/**
 * Form component (contact, volunteer signup, feedback, etc.).
 * Self-contained and ready for political party usage.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class Form extends AbstractMultiBlock
{
    protected string $templatePath = __DIR__;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function render(array $passedData = []): string
    {
        if (!$this->validateData($passedData)) {
            $passedData = [
                'title' => 'Get in Touch',
                'action' => '#',
                'fields' => [
                    ['type' => 'text', 'name' => 'name', 'label' => 'Name', 'required' => true],
                    ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'required' => true],
                    ['type' => 'textarea', 'name' => 'message', 'label' => 'Message', 'required' => true]
                ],
                'submit' => 'Send Message'
            ];
        }
        return $this->processTemplate($this->getTemplate('default'), $passedData);
    }
}