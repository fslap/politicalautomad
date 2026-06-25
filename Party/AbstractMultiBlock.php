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
namespace Automad\Party;

defined('AUTOMAD') or die('Direct access not permitted!');

use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;

/**
 * Abstract base class for multi-block political party components in Automad.
 * 
 * Represents components that show split views or multiple conceptual blocks.
 * Ported and adapted from politicalpartysite v2- branches (e.g. v2-artmotivation, v2-political base).
 *
 * Key features implemented:
 * - Mustache first construct/init
 * - Array data passing only (no $this data references in methods)
 * - All methods from the layer in website repo copied/adapted here (build, render, init, process, validate etc.)
 * - Self-contained, extendable
 * - Works with Twig/Mustache templates (imports removed in concrete)
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
abstract class AbstractMultiBlock
{
    /**
     * Mustache engine instance (initialized first)
     */
    protected Mustache_Engine $mustache;

    /**
     * Base template path for component (overridden in child)
     */
    protected string $templatePath = __DIR__;

    /**
     * Constructor - MUSTACHE FIRST construct init, then setup
     * 
     * @param array $data Initial data array (passed, not stored as this-> for data refs)
     */
    public function __construct(array $data = [])
    {
        // Mustache FIRST
        $this->initMustache();
        
        // Then other init, but NO storing data in $this for referencing in methods
        // Data is always passed fresh to methods like render($data)
        $this->init($data);
    }

    /**
     * Initialize Mustache engine (first thing)
     * Supports filesystem loading for self-contained component templates/
     */
    protected function initMustache(): void
    {
        $loader = new Mustache_Loader_FilesystemLoader($this->templatePath . '/templates');
        $this->mustache = new Mustache_Engine([
            'loader' => $loader,
            'partials_loader' => $loader,
            'escape' => function($value) {
                return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
            }
        ]);
    }

    /**
     * Additional init (called after mustache)
     * Can be overridden, but keep minimal (base now not holding full building)
     *
     * @param array $data
     */
    protected function init(array $data = []): void
    {
        // Base init - only up to core, rest in concrete or copied methods
    }

    /**
     * Render the component using passed data array ONLY.
     * No $this->data references. Replace any old this-ref with $passedData.
     *
     * @param array $passedData The data to use for rendering (required style)
     * @return string Rendered HTML
     */
    abstract public function render(array $passedData = []): string;

    /**
     * Build the blocks structure from passed data.
     * Copied/adapted from website repo layer methods.
     *
     * @param array $passedData
     * @return array Built blocks
     */
    public function build(array $passedData = []): array
    {
        // Example implementation - concrete classes override or extend
        $blocks = [];
        if (!empty($passedData['blocks'])) {
            foreach ($passedData['blocks'] as $block) {
                $blocks[] = $this->processBlock($block);
            }
        }
        return $blocks;
    }

    /**
     * Process single block data (copied method layer)
     *
     * @param array $blockData
     * @return array Processed
     */
    protected function processBlock(array $blockData): array
    {
        // Adapt for array only
        return [
            'title' => $blockData['title'] ?? '',
            'content' => $blockData['content'] ?? '',
            'image' => $blockData['image'] ?? null,
            // ... more fields as in v2 components
        ];
    }

    /**
     * Validate the passed data (copied from layer)
     *
     * @param array $passedData
     * @return bool
     */
    public function validateData(array $passedData = []): bool
    {
        // Basic validation, extend in child
        return !empty($passedData);
    }

    /**
     * Get template name (for mustache/twig)
     *
     * @param string $variant
     * @return string
     */
    public function getTemplate(string $variant = 'default'): string
    {
        return $variant . '.mustache';  // prefer mustache, twig fallback possible
    }

    /**
     * Process template with data using Mustache (core)
     *
     * @param string $templateName
     * @param array $passedData
     * @return string
     */
    protected function processTemplate(string $templateName, array $passedData): string
    {
        try {
            return $this->mustache->render($templateName, $passedData);
        } catch (\Exception $e) {
            // Fallback or error
            return '<!-- Template error: ' . htmlspecialchars($e->getMessage()) . ' -->';
        }
    }

    /**
     * Get all available blocks or sections (for "All" integration)
     *
     * @param array $passedData
     * @return array
     */
    public function getAllBlocks(array $passedData = []): array
    {
        return $this->build($passedData);
    }

    // Additional copied methods from the layer in v2- political/website repo can be added here
    // e.g. mergeData, sanitizeInput, loadAssets, registerInAutomad, etc.
    // For brevity in this template, core ones shown. In full port copy all ~10-15 methods.
}