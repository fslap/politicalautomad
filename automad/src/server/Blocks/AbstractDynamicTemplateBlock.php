<?php
namespace Automad\Blocks;

use Automad\Core\Automad;
use Automad\Models\ComponentCollection;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * The abstract base block.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 *
 */
abstract class AbstractDynamicTemplateBlock extends AbstractBlock {
    private string $name = '';

    public __construct(
        private string $path,
        false|string $name = false,
        private string $engine = /* Mustache_Engine::class */ 'Mustache_Engine',
        private string|array $source = []
    ) {
        if ($name === false) {
            $name = $this::class;
        }
        $this->name = $name;
    }

    abstract public function context(array $config): array;

	/**
	 * Render a paragraph block.
	 *
	 * @param BlockData $block
	 * @param Automad $Automad
	 * @return string the rendered HTML
	 */
	public function render(array $block, Automad $Automad): string {
        if ((!is_array($this->source)) && class_exists($this->source)) {

        }
    }

	/**
	 * Search and replace inside a block.
	 *
	 * @param BlockData $block
	 * @param ?ComponentCollection $ComponentCollection
	 * @param string $searchRegex
	 * @param string $replace
	 * @param bool $replaceInPublishedComponent
	 * @return BlockData
	 */
	abstract public function replace(
		array $block,
		?ComponentCollection $ComponentCollection,
		string $searchRegex,
		string $replace,
		bool $replaceInPublishedComponent
	): array;

	/**
	 * Return a searchable string representation of a block.
	 *
	 * @param BlockData $block
	 * @param ?ComponentCollection $ComponentCollection
	 * @return string
	 */
	abstract public function toString(array $block, ?ComponentCollection $ComponentCollection): string;
}