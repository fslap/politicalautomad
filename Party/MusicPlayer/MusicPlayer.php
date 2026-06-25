<?php
/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck 2026 - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md
 */
namespace Automad\Party\MusicPlayer;

defined('AUTOMAD') or die('Direct access not permitted!');

use Automad\Party\AbstractMultiBlock;

class MusicPlayer extends AbstractMultiBlock {
    protected string $templatePath = __DIR__;
    public function __construct(array $data = []) { parent::__construct($data); }
    public function render(array $passedData = []): string {
        if (!$this->validateData($passedData)) $passedData = [];
        return $this->processTemplate($this->getTemplate('default'), $passedData);
    }
}
