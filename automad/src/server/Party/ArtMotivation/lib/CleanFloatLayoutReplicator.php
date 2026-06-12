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

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Replicates a floating layout for the purpose of making relative layouted art available through just boxes 
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 *
 */
class CleanFloatLayoutReplicator {
    private $canvas_width;
    private $canvas_height;
    private $canvas;
    private $placed_images = [];
    private $margin_right = 10;
    private $bigger_images = false; // Option for bigger image sizing
    
    public function __construct($width = 1600, $height = 1000, $bigger_images = false) {
        $this->canvas_width = $width;
        $this->canvas_height = $height;
        $this->bigger_images = $bigger_images;
        
        if (!extension_loaded('gd')) {
            throw new Exception('GD extension is required');
        }
        
        $this->initializeCanvas();
    }
    
    private function initializeCanvas() {
        $this->canvas = imagecreatetruecolor($this->canvas_width, $this->canvas_height);
        
        // Make transparent background
        imagealphablending($this->canvas, false);
        imagesavealpha($this->canvas, true);
        $transparent = imagecolorallocatealpha($this->canvas, 0, 0, 0, 127);
        imagefill($this->canvas, 0, 0, $transparent);
        imagealphablending($this->canvas, true);
    }
    
    public function layoutImages($images, $css_classes) {
        if (count($images) !== count($css_classes)) {
            throw new Exception('Images and CSS classes arrays must have the same length');
        }
        
        $this->placed_images = [];
        
        // First pass: Place all images without drawing them yet (positioning only)
        for ($i = 0; $i < count($images); $i++) {
            $image_path = trim($images[$i]);
            $css_class = trim($css_classes[$i]);
            
            if (file_exists($image_path)) {
                $this->placeImageClean($image_path, $css_class, $i);
            }
        }
        
        // Second pass: Center the entire block and draw all images
        $this->centerAndDrawAllImages();
        
        return $this->canvas;
    }
    
    private function placeImageClean($image_path, $css_class, $index) {
        // Load image
        $source_image = $this->loadImage($image_path);
        if (!$source_image) return;
        
        // Calculate CSS dimensions
        $css_props = $this->parseCSSClass($css_class);
        $dimensions = $this->calculateDimensions($source_image, $css_props);
        
        // Find position based on simple logic
        $position = $this->findCleanPosition($dimensions, $css_props, $index);
        
        // Store for centering
        $this->placed_images[] = [
            'image' => $source_image,
            'x' => $position['x'],
            'y' => $position['y'],
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'source_width' => imagesx($source_image),
            'source_height' => imagesy($source_image)
        ];
    }
    
    private function findCleanPosition($dimensions, $css_props, $index) {
        // First image: start first column at origin with offset
        if ($index == 0) {
            $offset_y = ($css_props['margin_top_percent'] / 100) * $this->canvas_height;
            return ['x' => 0, 'y' => $offset_y];
        }
        
        // For subsequent images: find the best column to place them in
        return $this->findBestColumnPosition($dimensions, $css_props, $index);
    }
    
    private function findBestColumnPosition($dimensions, $css_props, $index) {
        $offset_y = ($css_props['margin_top_percent'] / 100) * $this->canvas_height;
        
        // For second image: place next to first image with its offset  
        if ($index == 1) {
            $first = $this->placed_images[0];
            $next_x = $first['x'] + $first['width'] + $this->margin_right;
            return ['x' => $next_x, 'y' => $offset_y];
        }
        
        // For third image: check if it fits under second image, otherwise next to it
        if ($index == 2) {
            $second = $this->placed_images[1];
            $under_y = $second['y'] + $second['height'] + 10;
            $available_height = $this->canvas_height - $under_y;
            
            // If enough space under second image, place there
            if ($available_height >= $dimensions['height'] + 50) {
                return ['x' => $second['x'], 'y' => $under_y];
            } else {
                // Otherwise place next to second image
                $next_x = $second['x'] + $second['width'] + $this->margin_right;
                return ['x' => $next_x, 'y' => $offset_y];
            }
        }
        
        // For additional images, continue the pattern
        $last = $this->placed_images[$index - 1];
        $under_y = $last['y'] + $last['height'] + 10;
        $available_height = $this->canvas_height - $under_y;
        
        if ($available_height >= $dimensions['height'] + 50) {
            return ['x' => $last['x'], 'y' => $under_y];
        } else {
            $next_x = $last['x'] + $last['width'] + $this->margin_right;
            return ['x' => $next_x, 'y' => $offset_y];
        }
    }
    
    private function getColumns() {
        $columns = [];
        
        foreach ($this->placed_images as $img) {
            $col_x = $img['x'];
            if (!isset($columns[$col_x])) {
                $columns[$col_x] = [
                    'bottom_y' => 0,
                    'total_width' => 0
                ];
            }
            
            // Update column info based on this image
            $bottom_y = $img['y'] + $img['height'];
            if ($bottom_y > $columns[$col_x]['bottom_y']) {
                $columns[$col_x]['bottom_y'] = $bottom_y;
            }
            
            // Track the total width of this column (widest element defines column width)
            if ($img['width'] > $columns[$col_x]['total_width']) {
                $columns[$col_x]['total_width'] = $img['width'];
            }
        }
        
        return $columns;
    }
    
    private function calculateNewColumnX() {
        if (empty($this->placed_images)) {
            return 0;
        }
        
        $columns = $this->getColumns();
        $rightmost_x = 0;
        
        // Find the rightmost position by checking each column's complete width
        foreach ($columns as $col_x => $column_info) {
            $column_right = $col_x + $column_info['total_width'];
            if ($column_right > $rightmost_x) {
                $rightmost_x = $column_right;
            }
        }
        
        return $rightmost_x + $this->margin_right;
    }
    
    private function parseCSSClass($css_class) {
        $classes = explode('.', $css_class);
        
        $properties = [
            'width_percent' => 25, // Back to smaller sizing that worked
            'height_percent' => 35, // Back to smaller sizing that worked
            'sizing_mode' => 'width_based',
            'margin_top_percent' => 0
        ];
        
        foreach ($classes as $class) {
            switch ($class) {
                case 'wide':
                    $properties['width_percent'] = $this->bigger_images ? 43 : 35; // 43% for bigger option
                    $properties['sizing_mode'] = 'width_based';
                    break;
                case 'tricorn':
                    $properties['height_percent'] = 45; // Back to 45%
                    $properties['sizing_mode'] = 'height_based';
                    break;
                case 'half':
                    $properties['height_percent'] = $this->bigger_images ? 75 : 65; // 75% for bigger option
                    $properties['sizing_mode'] = 'height_based';
                    break;
                case 'vertical':
                    if (in_array('half', $classes)) {
                        $properties['height_percent'] = 20; // Back to 20%
                    } elseif (in_array('piece', $classes)) {
                        $properties['height_percent'] = $this->bigger_images ? 23 : 18; // 23% for bigger option
                    }
                    $properties['sizing_mode'] = 'height_based';
                    break;
                case 'piece':
                    if (!in_array('vertical', $classes)) {
                        $properties['height_percent'] = 25; // Back to 25%
                        $properties['sizing_mode'] = 'height_based';
                    }
                    break;
                case 'float-island':
                    $properties['width_percent'] = 25; // Back to 25% that prevented overlaps
                    $properties['sizing_mode'] = 'width_based';
                    break;
                default:
                    // Handle offset classes (offset5, offset10, etc.)
                    if (preg_match('/offset(\d+)/', $class, $matches)) {
                        $properties['margin_top_percent'] = (int)$matches[1];
                    }
                    // Handle margin classes  
                    if (preg_match('/margin(\d+)/', $class, $matches)) {
                        $properties['margin_top_percent'] = (int)$matches[1];
                    }
                    break;
            }
        }
        
        return $properties;
    }
    
    private function calculateDimensions($source_image, $css_properties) {
        $source_width = imagesx($source_image);
        $source_height = imagesy($source_image);
        $aspect_ratio = $source_width / $source_height;
        
        if ($css_properties['sizing_mode'] === 'height_based') {
            $target_height = ($css_properties['height_percent'] / 100) * $this->canvas_height;
            $target_width = $target_height * $aspect_ratio;
        } else {
            $target_width = ($css_properties['width_percent'] / 100) * $this->canvas_width;
            $target_height = $target_width / $aspect_ratio;
        }
        
        // Ensure dimensions fit within canvas bounds - strict alignment with viewbox
        $target_width = min($target_width, $this->canvas_width * 0.85);
        $target_height = min($target_height, $this->canvas_height * 0.85);
        
        return [
            'width' => (int)$target_width,
            'height' => (int)$target_height
        ];
    }
    
    private function centerAndDrawAllImages() {
        if (empty($this->placed_images)) return;
        
        // Find bounds of the entire image block (all images together)
        $min_x = PHP_INT_MAX;
        $max_x = 0;
        $min_y = PHP_INT_MAX;
        $max_y = 0;
        
        foreach ($this->placed_images as $img) {
            $min_x = min($min_x, $img['x']);
            $max_x = max($max_x, $img['x'] + $img['width']);
            $min_y = min($min_y, $img['y']);
            $max_y = max($max_y, $img['y'] + $img['height']);
        }
        
        // Calculate center offset for the entire block
        $block_width = $max_x - $min_x;
        $block_height = $max_y - $min_y;
        $center_offset_x = ($this->canvas_width - $block_width) / 2 - $min_x;
        $center_offset_y = ($this->canvas_height - $block_height) / 2 - $min_y;
        
        // Draw all images with the block center offset applied
        foreach ($this->placed_images as $img) {
            $final_x = $img['x'] + $center_offset_x;
            $final_y = $img['y'] + $center_offset_y;
            
            // Ensure position is within bounds
            $final_x = max(0, min($this->canvas_width - $img['width'], $final_x));
            $final_y = max(0, min($this->canvas_height - $img['height'], $final_y));
            
            imagecopyresampled(
                $this->canvas, $img['image'],
                $final_x, $final_y, 0, 0,
                $img['width'], $img['height'],
                $img['source_width'], $img['source_height']
            );
            
            // Clean up
            imagedestroy($img['image']);
        }
    }
    
    private function loadImage($image_path) {
        $image_info = getimagesize($image_path);
        if (!$image_info) return false;
        
        switch ($image_info['mime']) {
            case 'image/jpeg':
                return imagecreatefromjpeg($image_path);
            case 'image/png':
                $img = imagecreatefrompng($image_path);
                imagealphablending($img, true);
                imagesavealpha($img, true);
                return $img;
            case 'image/gif':
                return imagecreatefromgif($image_path);
            case 'image/webp':
                return imagecreatefromwebp($image_path);
            default:
                return false;
        }
    }
    
    public function getCanvas() {
        return $this->canvas;
    }
    
    public function __destruct() {
        if ($this->canvas) {
            imagedestroy($this->canvas);
        }
    }
}