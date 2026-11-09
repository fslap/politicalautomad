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
 * \#########|   \\|      _\    :#;:  :#;:#;:  #;:#;:#;:#;:
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

namespace Automad\Party\Traits;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Shared parts of the slides (sketch "First Page" … "Last Page"): fields of elements that
 * are reused with a prefix, the slide frame with menu, arrows, the hover corner and the
 * hover decoration.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
trait SlideKit {
	/**
	 * Corners of the hover corner and the decoration.
	 */
	public static array $corners = array(
		'' => 'Keine',
		'top-right' => 'Oben rechts',
		'bottom-right' => 'Unten rechts',
		'bottom-left' => 'Unten links',
		'top-left' => 'Oben links'
	);
	/**
	 * Background classes of slides.
	 */
	public static array $slideBackgrounds = array(
		'' => 'Weiß',
		'party-slide--paper' => 'Papier (hell)',
		'bgblue' => 'Blau',
		'bgorange' => 'Terrakotta',
		'bgpurple' => 'Aubergine',
		'bgcoolgreen' => 'Grün',
		'bggold' => 'Ocker',
		'bgred' => 'Rot',
		'darklast' => 'Dunkel',
		'bgmilitary' => 'Tarnmuster (Militär)'
	);

	/**
	 * The fields of another party block (its protected definition).
	 *
	 * @param object $block
	 * @param string[] $skip names of fields that are not reused
	 * @return array
	 */
	public static function blockFields(object $block, array $skip = array()): array {
		$method = new \ReflectionMethod($block, 'definition');
		$definition = $method->invoke($block);

		return array_values(array_filter(
			$definition['fields'] ?? array(),
			fn ($field) => !in_array($field['name'] ?? '', $skip, true)
		));
	}

	/**
	 * The small boat with a sail of the sketch.
	 *
	 * @return string
	 */
	public static function boatSvg(): string {
		return '<svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg"><path d="M31 6 L31 40" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>'
			. '<path d="M33 9 L52 34 L33 34 Z" fill="currentColor" opacity="0.85"/>'
			. '<path d="M8 42 Q32 50 56 42 L50 54 Q32 58 14 54 Z" fill="currentColor"/></svg>';
	}

	/**
	 * A double chevron (the arrows of the sketch with two strokes).
	 *
	 * @param string $direction up or down
	 * @return string
	 */
	public static function chevrons(string $direction = 'down'): string {
		$d = $direction === 'up'
			? 'M4 13 L16 5 L28 13 M4 23 L16 15 L28 23'
			: 'M4 5 L16 13 L28 5 M4 15 L16 23 L28 15';

		return '<svg class="party-chevrons" viewBox="0 0 32 28" aria-hidden="true"><path d="' . $d . '" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}

	/**
	 * The fields of a hover corner.
	 *
	 * @param string $prefix
	 * @param string $group
	 * @param string $corner
	 * @return array
	 */
	public static function cornerFields(string $prefix, string $group, string $corner = 'top-right'): array {
		return array(
			array('name' => $prefix . 'corner', 'type' => 'select', 'label' => "$group: Schraffierte Ecke", 'default' => $corner, 'options' => self::$corners),
			array('name' => $prefix . 'corner_label', 'type' => 'text', 'label' => "$group: Text in der Ecke (erscheint beim Hover)", 'default' => ''),
			array('name' => $prefix . 'corner_link', 'type' => 'url', 'label' => "$group: Link der Ecke", 'default' => '')
		);
	}

	/**
	 * HTML of a hover corner.
	 *
	 * @param string $corner
	 * @param string $label
	 * @param string $link
	 * @return string
	 */
	public static function cornerHtml(string $corner, string $label, string $link): string {
		if (!isset(self::$corners[$corner]) || $corner === '') {
			return '';
		}

		$text = $label !== '' ? '<span class="party-corner__label">' . htmlspecialchars($label) . '</span>' : '';
		$tag = $link !== '' ? 'a href="' . htmlspecialchars($link) . '"' : 'span';
		$close = $link !== '' ? 'a' : 'span';

		return "<$tag class=\"party-corner party-corner--$corner\" aria-hidden=\"" . ($label === '' ? 'true' : 'false') . '">'
			. '<span class="party-corner__hatch"></span>' . $text . "</$close>";
	}

	/**
	 * The fields of a hover decoration (a small image in a corner).
	 *
	 * @param string $prefix
	 * @param string $group
	 * @return array
	 */
	public static function decorFields(string $prefix, string $group): array {
		return array(
			array('name' => $prefix . 'decor_image', 'type' => 'image', 'label' => "$group: Deko-Bild (Hover)", 'default' => ''),
			array('name' => $prefix . 'decor_position', 'type' => 'select', 'label' => "$group: Position Deko-Bild", 'default' => 'bottom-right', 'options' => array_slice(self::$corners, 1, null, true)),
			array('name' => $prefix . 'decor_animation', 'type' => 'select', 'label' => "$group: Animation Deko-Bild", 'default' => 'tilt', 'options' => array(
				'tilt' => 'Kippen',
				'move' => 'Bewegen',
				'float' => 'Schweben',
				'wobble' => 'Wackeln'
			))
		);
	}

	/**
	 * HTML of a hover decoration. Without image a small boat is drawn.
	 *
	 * @param string $image
	 * @param string $position
	 * @param string $animation
	 * @return string
	 */
	public static function decorHtml(string $image, string $position, string $animation): string {
		$position = isset(self::$corners[$position]) && $position !== '' ? $position : 'bottom-right';
		$animation = in_array($animation, array('tilt', 'move', 'float', 'wobble'), true) ? $animation : 'tilt';
		$content = $image !== ''
			? '<img src="' . htmlspecialchars($image) . '" alt="" decoding="async">'
			: self::boatSvg();

		return "<span class=\"party-decor party-decor--$position party-decor--$animation\" aria-hidden=\"true\">$content</span>";
	}

	/**
	 * Prefix the names (and labels) of element fields in order to reuse them in a slide.
	 *
	 * @param array $fields
	 * @param string $prefix
	 * @param string $group
	 * @return array
	 */
	public static function prefixFields(array $fields, string $prefix, string $group): array {
		return array_map(function (array $field) use ($prefix, $group) {
			$field = self::prefixReferences($field, $prefix);
			$field['name'] = $prefix . $field['name'];
			$field['label'] = $group . ': ' . ($field['label'] ?? $field['name']);

			return $field;
		}, $fields);
	}

	/**
	 * Get the values of prefixed fields without the prefix.
	 *
	 * @param array $config
	 * @param string $prefix
	 * @return array
	 */
	public static function unprefix(array $config, string $prefix): array {
		$out = array();

		foreach ($config as $key => $value) {
			if (str_starts_with((string) $key, $prefix)) {
				$out[substr((string) $key, strlen($prefix))] = $value;
			}
		}

		return $out;
	}

	/**
	 * Change the defaults of element fields for a slide.
	 *
	 * @param array $fields
	 * @param array $defaults name => default
	 * @return array
	 */
	public static function withDefaults(array $fields, array $defaults): array {
		return array_map(function (array $field) use ($defaults) {
			if (array_key_exists($field['name'] ?? '', $defaults)) {
				$field['default'] = $defaults[$field['name']];
			}

			return $field;
		}, $fields);
	}

	/**
	 * The context of the slide frame.
	 *
	 * @param array $config
	 * @param string $layout
	 * @return array
	 */
	protected function slideContext(array $config, string $layout): array {
		$background = (string) ($config['slide_background'] ?? '');
		$background = isset(self::$slideBackgrounds[$background]) ? $background : '';
		$ratio = ($config['slide_ratio'] ?? '16-9') === 'auto' ? 'auto' : '16-9';
		$title = trim((string) ($config['slide_title'] ?? ''));
		$nav = !isset($config['slide_nav']) || (bool) $config['slide_nav'];
		$sectionId = trim((string) ($config['section_id'] ?? ''));

		$top = $sectionId !== '' ? '<a id="' . htmlspecialchars($sectionId) . '"></a>' : '';

		if ($nav) {
			$top .= '<div class="party-slide__nav party-slide__nav--top">'
				. '<button type="button" class="party-slide__menu" data-slide-menu aria-label="Folien-Übersicht" aria-expanded="false">'
				. '<span></span><span></span><span></span></button>'
				. '<button type="button" class="party-slide__arrow party-slide__arrow--up" data-slide-prev aria-label="Vorherige Folie">' . self::chevrons('up') . '</button>'
				. '</div>';
		}

		$bottom = $nav
			? '<button type="button" class="party-slide__arrow party-slide__arrow--down" data-slide-next aria-label="Nächste Folie">' . self::chevrons('down') . '</button>'
			: '';

		$bottom .= self::cornerHtml((string) ($config['slide_corner'] ?? ''), (string) ($config['slide_corner_label'] ?? ''), (string) ($config['slide_corner_link'] ?? ''));

		if (!empty($config['slide_decor_image']) || $layout === 'document') {
			$bottom .= self::decorHtml((string) ($config['slide_decor_image'] ?? ''), (string) ($config['slide_decor_position'] ?? ''), (string) ($config['slide_decor_animation'] ?? ''));
		}

		return array(
			'slide_classes' => trim("party-slide party-slide--$layout party-slide--ratio-$ratio $background " . ($config['classes'] ?? '')),
			'slide_title' => $title,
			'slide_top' => $top,
			'slide_bottom' => $bottom
		);
	}

	/**
	 * The fields of the slide frame.
	 *
	 * @param string $title
	 * @param string $background
	 * @param string $corner
	 * @return array
	 */
	protected static function slideFields(string $title, string $background = '', string $corner = 'top-right'): array {
		return array(
			array('name' => 'section_id', 'type' => 'text', 'label' => 'Folie: Section-ID (Anker)', 'default' => ''),
			array('name' => 'slide_title', 'type' => 'text', 'label' => 'Folie: Name im Folien-Menü', 'default' => $title),
			array('name' => 'slide_background', 'type' => 'select', 'label' => 'Folie: Hintergrund', 'default' => $background, 'options' => self::$slideBackgrounds),
			array('name' => 'slide_ratio', 'type' => 'select', 'label' => 'Folie: Format', 'default' => '16-9', 'options' => array('16-9' => '16:9 (Bildschirmfüllend)', 'auto' => 'Automatische Höhe')),
			array('name' => 'slide_nav', 'type' => 'toggle', 'label' => 'Folie: Menü und Pfeile anzeigen', 'default' => true),
			...self::cornerFields('slide_', 'Folie', $corner),
			...self::decorFields('slide_', 'Folie'),
			array('name' => 'classes', 'type' => 'text', 'label' => 'Folie: CSS-Klassen', 'default' => '')
		);
	}

	/**
	 * Prefix references to other top level fields (map bindings, options of selects),
	 * also inside of nested list fields.
	 *
	 * @param array $field
	 * @param string $prefix
	 * @return array
	 */
	private static function prefixReferences(array $field, string $prefix): array {
		if (isset($field['bind']) && is_array($field['bind'])) {
			$field['bind'] = array_map(fn ($name) => $prefix . $name, $field['bind']);
		}

		if (isset($field['object']) && is_string($field['object'])) {
			$field['object'] = $prefix . $field['object'];
		}

		if (isset($field['optionsFrom']['field'])) {
			$field['optionsFrom']['field'] = $prefix . $field['optionsFrom']['field'];
		}

		if (isset($field['fields']) && is_array($field['fields'])) {
			$field['fields'] = array_map(fn ($child) => self::prefixReferences($child, $prefix), $field['fields']);
		}

		return $field;
	}
}
