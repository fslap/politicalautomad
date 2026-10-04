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

namespace Automad\Party\LeafletMap;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component LeafletMap (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class LeafletMap extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'leafletmap');
	}

	public function context(array $config): array {
		$sectionId = $config['section_id'] ?: 'map';

		return array(
			'section_id' => $sectionId,
			'title' => $config['title'],
			'map_height' => preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['map_height']),
			'map_json' => $this->json(array(
				'center' => self::parseCenter((string) $config['map_center']),
				'zoom' => (int) $config['map_zoom'],
				'tileUrl' => $config['tile_url'],
				'markers' => self::normalizeMarkers($config['markers']),
				'geojson' => is_array($config['geojson_content']) ? $config['geojson_content'] : null
			)),
			'classes' => $this->getClasses($config),
			'has_inner_container' => $this->hasInnerContainer($config)
		);
	}

	/**
	 * Filter markers with valid coordinates.
	 *
	 * @param array $markers
	 * @return array
	 */
	public static function normalizeMarkers(array $markers): array {
		$out = array();

		foreach ($markers as $marker) {
			if (!is_array($marker) || !is_numeric($marker['lat'] ?? null) || !is_numeric($marker['lng'] ?? null)) {
				continue;
			}

			$out[] = array(
				'id' => (string) ($marker['id'] ?? ''),
				'title' => (string) ($marker['title'] ?? ''),
				'description' => (string) ($marker['description'] ?? ''),
				'lat' => (float) $marker['lat'],
				'lng' => (float) $marker['lng'],
				'region' => (string) ($marker['region'] ?? ''),
				'zoom' => is_numeric($marker['zoom'] ?? null) ? (int) $marker['zoom'] : null
			);
		}

		return $out;
	}

	/**
	 * Parse a "lat,lng" string.
	 *
	 * @param string $center
	 * @return array{0: float, 1: float}
	 */
	public static function parseCenter(string $center): array {
		$parts = array_map('trim', explode(',', $center));

		if (count($parts) < 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
			return array(52.520008, 13.404954);
		}

		return array((float) $parts[0], (float) $parts[1]);
	}

	protected function definition(): array {
		return array(
			'title' => 'Karte (Leaflet)',
			'icon' => 'map',
			'description' => 'OpenStreetMap-Karte mit Markern und optionalem GeoJSON.',
			'fields' => array(
				self::sectionIdField('map'),
				self::titleField(),
				array('name' => 'map_center', 'type' => 'text', 'label' => 'Kartenmitte (lat,lng)', 'default' => '52.520008,13.404954'),
				array('name' => 'map_zoom', 'type' => 'number', 'label' => 'Zoom', 'default' => 10),
				array('name' => 'map_height', 'type' => 'text', 'label' => 'Kartenhöhe', 'default' => '400px'),
				array('name' => 'tile_url', 'type' => 'text', 'label' => 'Tile-URL', 'default' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
				array(
					'name' => 'markers',
					'type' => 'list',
					'label' => 'Marker',
					'itemTitle' => 'title',
					'fields' => array(
						array('name' => 'title', 'type' => 'text', 'label' => 'Titel'),
						array('name' => 'description', 'type' => 'textarea', 'label' => 'Beschreibung'),
						array('name' => 'lat', 'type' => 'number', 'label' => 'Breitengrad (lat)'),
						array('name' => 'lng', 'type' => 'number', 'label' => 'Längengrad (lng)')
					)
				),
				array('name' => 'geojson_content', 'type' => 'json', 'label' => 'GeoJSON (FeatureCollection)', 'default' => null),
				self::classesField('')
			)
		);
	}
}
