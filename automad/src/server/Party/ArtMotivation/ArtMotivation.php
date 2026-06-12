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

/**
 * Represents a split view of concept to be shown
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 *
 */
class ArtMotivation extends AbstractMultiBlock {
    public __construct() {
        parent::__construct(__DIR__, 'artmotivation');
    }

    public function context(array $config): array
    {
        $section_id = $config['section_id'] ?? '';
        $artworks = $config['artworks'] ?? [];

        return [
            'section_id' => $config['section_id'] ?? '',
            'title' => $config['title'] ?? '',
            'artworks' => $artworks,
            'classes' => $this->getClasses(),
            'mobile_art_image' => $this->buildMobileArtImage($artworks, $section_id)
        ];
    }

    private function buildMobileArtImage(array $artworks, string $section_id): string
    {
        if (empty($artworks)) {
            return '';
        }

        $root = realpath(__DIR__ . '/..');
        if ($root === false) {
            return '';
        }

        $slug = preg_replace('/[^a-z0-9\\-]+/i', '-', $section_id ?: 'art');
        $slug = trim($slug, '-');
        if ($slug === '') {
            $slug = 'art';
        }

        $output_relative = 'public/assets/generated/art-' . $slug . '.png';
        $output_path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $output_relative);

        $images = [];
        $classes = [];
        $latest_mtime = 0;

        foreach ($artworks as $artwork) {
            $image_ref = $artwork['image'] ?? '';
            if ($image_ref === '') {
                continue;
            }

            $image_ref = preg_replace('/\\?.*/', '', $image_ref);
            $image_ref = ltrim($image_ref, '/');
            $image_path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $image_ref);

            if (!is_file($image_path)) {
                continue;
            }

            $images[] = $image_path;
            $classes[] = $this->normalizeArtClass($artwork['classes'] ?? '');

            $mtime = filemtime($image_path);
            if ($mtime !== false) {
                $latest_mtime = max($latest_mtime, $mtime);
            }
        }

        if (empty($images)) {
            return '';
        }

        if (is_file($output_path)) {
            $out_mtime = filemtime($output_path);
            if ($out_mtime !== false && $out_mtime >= $latest_mtime) {
                return $output_relative;
            }
        }

        $output_dir = dirname($output_path);
        if (!is_dir($output_dir)) {
            mkdir($output_dir, 0777, true);
        }
        
        try {
            $replicator = new CleanFloatLayoutReplicator();
            $canvas = $replicator->layoutImages($images, $classes);
            imagepng($canvas, $output_path);
        } catch (\Throwable $e) {
            return '';
        }

        return $output_relative;
    }

    private function normalizeArtClass(string $classList): string
    {
        $classList = trim(preg_replace('/\\s+/', '.', $classList));
        return trim($classList, '.');
    }
}