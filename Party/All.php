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

/**
 * "All" registry and loader for political party components.
 * 
 * Includes automad available stump, artwork and license text in all.
 * Use to discover/register all self-contained components in Party/.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class All
{
    /**
     * List of available component classes (key = short name, value = FQCN)
     */
    public static array $components = [
        'ArtMotivation' => \Automad\Party\ArtMotivation\ArtMotivation::class,
        'ManifestoBlock' => \Automad\Party\ManifestoBlock\ManifestoBlock::class,
        'SupportBlock' => \Automad\Party\SupportBlock\SupportBlock::class,
        'EventBlock' => \Automad\Party\EventBlock\EventBlock::class,
        'TeamBlock' => \Automad\Party\TeamBlock\TeamBlock::class,
        'DonateBlock' => \Automad\Party\DonateBlock\DonateBlock::class,
        'StatementBlock' => \Automad\Party\StatementBlock\StatementBlock::class,
        'Split' => \Automad\Party\Split\Split::class,
        'Gallery' => \Automad\Party\Gallery\Gallery::class,
        'LeafletMap' => \Automad\Party\LeafletMap\LeafletMap::class,
        'Form' => \Automad\Party\Form\Form::class,
        'Quote' => \Automad\Party\Quote\Quote::class,
        'Documents' => \Automad\Party\Documents\Documents::class,
        // All remaining components from politicalpartysite/Components/ now have prepared folders + stub classes ready:
        // CaseLeafletRegion, Image, Markdown, PartyHeader, SecurityConcept,
        // SkylineBreaker, VerticalSlider, ViewboxHover, WaterAnimation, LandscapeScene,
        // RegionScroll, ScrollElement, ScrollReference, MusicPlayer + all Wp* components
    ];

    /**
     * Get all registered components
     */
    public static function getAll(): array
    {
        return self::$components;
    }

    /**
     * Instantiate a component by name with passed data array
     *
     * @param string $name
     * @param array $data
     * @return AbstractMultiBlock|null
     */
    public static function make(string $name, array $data = []): ?AbstractMultiBlock
    {
        if (!isset(self::$components[$name])) {
            return null;
        }
        $class = self::$components[$name];
        return new $class($data);
    }

    /**
     * Render a component directly (convenience)
     *
     * @param string $name
     * @param array $data
     * @return string
     */
    public static function render(string $name, array $data = []): string
    {
        $instance = self::make($name, $data);
        if ($instance) {
            return $instance->render($data);
        }
        return '<!-- Component not found: ' . htmlspecialchars($name) . ' -->';
    }
}