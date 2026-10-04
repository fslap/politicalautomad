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

namespace Automad\Party\CaseLeafletRegion;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component CaseLeafletRegion (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class CaseLeafletRegion extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'caseleafletregion');
	}

	public function context(array $config): array {
		$sectionId = $config['section_id'] ?: 'cases-map';
		$markers = \Automad\Party\LeafletMap\LeafletMap::normalizeMarkers($config['markers']);
		$iconBase = rtrim((string) $config['status_icon_base'], '/');
		$items = $this->normalizeItems($config['items'], $iconBase);
		$groups = $this->normalizeGroups($config['groups']);
		$regions = $groups ?: $this->buildRegions($markers, $items);

		return array(
			'section_id' => $sectionId,
			'title' => $config['title'],
			'map_height' => preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['map_height']),
			'regions' => $regions,
			'map_json' => $this->json(array(
				'center' => \Automad\Party\LeafletMap\LeafletMap::parseCenter((string) $config['map_center']),
				'zoom' => (int) $config['map_zoom'],
				'tileUrl' => $config['tile_url'],
				'markers' => $markers,
				'items' => $items,
				'groups' => $regions,
				'geojson' => is_array($config['geojson_content']) ? $config['geojson_content'] : null,
				'regionPriority' => (bool) $config['region_priority'],
				'defaultRegion' => $config['default_region']
			)),
			'classes' => $this->getClasses($config),
			'has_inner_container' => $this->hasInnerContainer($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Fälle-Karte mit Regionen',
			'icon' => 'pin-map',
			'description' => 'Karte mit durchsuchbarer Fallliste, Regionen-Filter und FragDenStaat-Status.',
			'fields' => array(
				self::sectionIdField('cases-map'),
				self::titleField('Fälle'),
				array('name' => 'map_center', 'type' => 'text', 'label' => 'Kartenmitte (lat,lng)', 'default' => '52.520008,13.404954'),
				array('name' => 'map_zoom', 'type' => 'number', 'label' => 'Zoom', 'default' => 6),
				array('name' => 'map_height', 'type' => 'text', 'label' => 'Kartenhöhe', 'default' => '520px'),
				array('name' => 'tile_url', 'type' => 'text', 'label' => 'Tile-URL', 'default' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
				array('name' => 'region_priority', 'type' => 'toggle', 'label' => 'Bei Auswahl auf Region zoomen', 'default' => true),
				array('name' => 'default_region', 'type' => 'text', 'label' => 'Vorausgewählte Region'),
				array(
					'name' => 'markers',
					'type' => 'list',
					'label' => 'Marker',
					'itemTitle' => 'title',
					'fields' => array(
						array('name' => 'id', 'type' => 'text', 'label' => 'Marker-ID'),
						array('name' => 'title', 'type' => 'text', 'label' => 'Titel'),
						array('name' => 'description', 'type' => 'textarea', 'label' => 'Beschreibung'),
						array('name' => 'lat', 'type' => 'number', 'label' => 'Breitengrad (lat)'),
						array('name' => 'lng', 'type' => 'number', 'label' => 'Längengrad (lng)'),
						array('name' => 'region', 'type' => 'text', 'label' => 'Region'),
						array('name' => 'zoom', 'type' => 'number', 'label' => 'Zoom (optional)')
					)
				),
				array(
					'name' => 'items',
					'type' => 'list',
					'label' => 'Fälle',
					'itemTitle' => 'title',
					'fields' => array(
						array('name' => 'marker_id', 'type' => 'text', 'label' => 'Marker-ID'),
						array('name' => 'title', 'type' => 'text', 'label' => 'Titel'),
						array('name' => 'description', 'type' => 'textarea', 'label' => 'Beschreibung'),
						array('name' => 'region', 'type' => 'text', 'label' => 'Region'),
						array(
							'name' => 'links',
							'type' => 'list',
							'label' => 'Links',
							'itemTitle' => 'label',
							'fields' => array(
								array('name' => 'label', 'type' => 'text', 'label' => 'Beschriftung'),
								array('name' => 'url', 'type' => 'url', 'label' => 'URL'),
								array('name' => 'type', 'type' => 'select', 'label' => 'Typ', 'default' => 'external', 'options' => array('external' => 'Externer Link', 'fragdenstaat' => 'FragDenStaat-Anfrage')),
								array('name' => 'status', 'type' => 'select', 'label' => 'Status', 'options' => array('' => '—', 'open' => 'Offen', 'pending' => 'In Bearbeitung', 'success' => 'Erfolgreich', 'denied' => 'Abgelehnt'))
							)
						)
					)
				),
				array(
					'name' => 'groups',
					'type' => 'list',
					'label' => 'Gruppen (optional)',
					'itemTitle' => 'name',
					'fields' => array(
						array('name' => 'name', 'type' => 'text', 'label' => 'Name'),
						array('name' => 'marker_ids', 'type' => 'strings', 'label' => 'Marker-IDs (eine pro Zeile)')
					)
				),
				array('name' => 'geojson_content', 'type' => 'json', 'label' => 'GeoJSON (FeatureCollection)', 'default' => null),
				array('name' => 'status_icon_base', 'type' => 'text', 'label' => 'Pfad der Status-Icons', 'placeholder' => 'Standard: mitgelieferte Icons'),
				self::classesField('')
			)
		);
	}

	private function buildRegions(array $markers, array $items): array {
		$groups = array();

		foreach (array_merge($markers, $items) as $entry) {
			$region = trim((string) ($entry['region'] ?? ''));

			if ($region !== '' && !isset($groups[$region])) {
				$groups[$region] = array('id' => '', 'name' => $region, 'marker_ids' => array());
			}
		}

		return array_values($groups);
	}

	private function normalizeGroups(array $groups): array {
		$out = array();

		foreach ($groups as $group) {
			if (!is_array($group) || trim((string) ($group['name'] ?? '')) === '') {
				continue;
			}

			$out[] = array(
				'id' => (string) ($group['id'] ?? ''),
				'name' => (string) $group['name'],
				'marker_ids' => array_values(array_filter(array_map('strval', (array) ($group['marker_ids'] ?? array()))))
			);
		}

		return $out;
	}

	private function normalizeItems(array $items, string $iconBase): array {
		$out = array();

		foreach ($items as $item) {
			if (!is_array($item)) {
				continue;
			}

			$out[] = array(
				'marker_id' => (string) ($item['marker_id'] ?? ($item['id'] ?? '')),
				'title' => (string) ($item['title'] ?? ''),
				'description' => (string) ($item['description'] ?? ''),
				'region' => (string) ($item['region'] ?? ''),
				'links' => $this->withLinkMeta(is_array($item['links'] ?? null) ? $item['links'] : array(), $iconBase)
			);
		}

		return $out;
	}

	/**
	 * Return the URL of a status icon. Without a custom base path, the bundled icons are inlined.
	 *
	 * @param string $status
	 * @param string $iconBase
	 * @return string
	 */
	private function statusIcon(string $status, string $iconBase): string {
		if (!in_array($status, array('open', 'pending', 'success', 'denied'), true)) {
			return '';
		}

		if ($iconBase !== '') {
			return "$iconBase/status-$status.svg";
		}

		$file = __DIR__ . "/icons/status-$status.svg";

		return is_readable($file) ? 'data:image/svg+xml;base64,' . base64_encode((string) file_get_contents($file)) : '';
	}

	private function withLinkMeta(array $links, string $iconBase): array {
		$out = array();

		foreach ($links as $link) {
			if (!is_array($link)) {
				continue;
			}

			$type = (string) ($link['type'] ?? 'external');
			$url = (string) ($link['url'] ?? '');
			$status = (string) ($link['status'] ?? '');
			$favicon = '';

			if ($type === 'external' && $url !== '') {
				$host = parse_url($url, PHP_URL_HOST);

				if ($host) {
					$favicon = 'https://www.google.com/s2/favicons?domain=' . rawurlencode($host) . '&sz=32';
				}
			}

			$out[] = array(
				'type' => $type,
				'url' => $url,
				'label' => (string) ($link['label'] ?? ($link['title'] ?? $url)),
				'status' => $status,
				'status_icon' => $this->statusIcon($status, $iconBase),
				'favicon' => $favicon
			);
		}

		return $out;
	}
}
