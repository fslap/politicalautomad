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
 * Party component Case Leaflet Region (politicalpartysite → Automad Party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 *
 */
class CaseLeafletRegion extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'caseleafletregion');
	}


private function normalizeMarkers(array $markers): array
    {
        $out = [];
        foreach ($markers as $m) {
            if (!is_array($m)) {
                continue;
            }
            $lat = isset($m['lat']) ? (float)$m['lat'] : null;
            $lng = isset($m['lng']) ? (float)$m['lng'] : null;
            if ($lat === null || $lng === null) {
                continue;
            }
            $out[] = [
                'id' => (string)($m['id'] ?? $m['marker_id'] ?? ''),
                'title' => (string)($m['title'] ?? $m['name'] ?? ''),
                'description' => (string)($m['description'] ?? ''),
                'lat' => $lat,
                'lng' => $lng,
                'region' => (string)($m['region'] ?? ''),
                'zoom' => isset($m['zoom']) ? (int)$m['zoom'] : null
            ];
        }
        return $out;
    }

    private function buildRegions(array $markers): array
    {
        $groups = [];
        foreach ($markers as $m) {
            $region = trim((string)($m['region'] ?? ''));
            if ($region === '') {
                continue;
            }
            if (!isset($groups[$region])) {
                $groups[$region] = ['name' => $region, 'markers' => []];
            }
            if (!empty($m['id'])) {
                $groups[$region]['markers'][] = $m['id'];
            }
        }
        return array_values($groups);
    }

    private function normalizeGroups(array $groups): array
    {
        $out = [];
        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }
            $markerIds = [];
            if (isset($group['marker_ids']) && is_array($group['marker_ids'])) {
                $markerIds = array_values(array_filter(array_map('strval', $group['marker_ids'])));
            }
            $out[] = [
                'id' => (string)($group['id'] ?? ''),
                'name' => (string)($group['name'] ?? ''),
                'marker_ids' => $markerIds
            ];
        }
        return $out;
    }

    private function withLinkMeta(array $links, string $statusIconBase): array
    {
        $statusMap = [
            'open' => $statusIconBase . '/status-open.svg',
            'pending' => $statusIconBase . '/status-pending.svg',
            'success' => $statusIconBase . '/status-success.svg',
            'denied' => $statusIconBase . '/status-denied.svg'
        ];

        $out = [];
        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }
            $type = (string)($link['type'] ?? 'external');
            $url = (string)($link['url'] ?? '');
            $label = (string)($link['label'] ?? $link['title'] ?? $url);
            $status = (string)($link['status'] ?? '');
            $favicon = '';
            if ($type === 'external' && $url !== '') {
                $host = parse_url($url, PHP_URL_HOST);
                if ($host) {
                    $favicon = 'https://www.google.com/s2/favicons?domain=' . $host . '&sz=32';
                }
            }

            $out[] = [
                'type' => $type,
                'url' => $url,
                'label' => $label,
                'status' => $status,
                'status_icon' => $statusMap[$status] ?? '',
                'favicon' => $favicon
            ];
        }

        return $out;
    }

    private function normalizeItems(array $items, string $statusIconBase): array
    {
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $links = isset($item['links']) && is_array($item['links']) ? $item['links'] : [];
            $out[] = [
                'marker_id' => (string)($item['marker_id'] ?? $item['id'] ?? ''),
                'title' => (string)($item['title'] ?? ''),
                'description' => (string)($item['description'] ?? ''),
                'region' => (string)($item['region'] ?? ''),
                'links' => $this->withLinkMeta($links, $statusIconBase)
            ];
        }
        return $out;
    }
	public function context(array $config): array
	{

        $markers = $this->normalizeMarkers($config['markers'] ?? []);
        $statusIconBase = $config['status_icon_base'] ?? '/public/icons/fragdenstaat';
        $items = $this->normalizeItems($config['items'] ?? [], $statusIconBase);
        $groups = $this->normalizeGroups($config['groups'] ?? []);
        $regions = $groups ? $groups : $this->buildRegions($markers);

        $center = (string)($config['map_center'] ?? '52.520008,13.404954');
        $zoom = (string)($config['map_zoom'] ?? '10');

        $geojson = (string)($config['geojson_content'] ?? '');

        return [
            'section_id' => $config['section_id'] ?? 'cases-map',
            'title' => $config['title'] ?? '',
            'map_center' => $center,
            'map_zoom' => $zoom,
            'map_height' => $config['map_height'] ?? '520px',
            'tile_url' => $config['tile_url'] ?? 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'geojson_content' => $geojson,
            'markers' => $markers,
            'items' => $items,
            'regions' => $regions,
            'groups' => $groups,
            'default_region' => $config['default_region'] ?? '',
            'region_priority' => ($config['region_priority'] ?? true) ? true : false,
            'markers_json' => json_encode($markers, JSON_UNESCAPED_SLASHES),
            'items_json' => json_encode($items, JSON_UNESCAPED_SLASHES),
            'groups_json' => json_encode($regions, JSON_UNESCAPED_SLASHES),
            'geojson_json' => $geojson ?: 'null',
            'classes' => $this->getClasses($config),
            'has_inner_container' => $this->hasInnerContainer($config)
        ];
	}
}

