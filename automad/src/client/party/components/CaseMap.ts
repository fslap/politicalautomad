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

import type * as Leaflet from 'leaflet';
import {
	createMap,
	loadLeaflet,
	markerIcon,
	parseConfig,
	popupContent,
	type PartyMapConfig,
	type PartyMarker,
} from './Map';

interface CaseLink {
	type: string;
	url: string;
	label: string;
	status: string;
	status_icon: string;
	favicon: string;
}

interface CaseItem {
	marker_id: string;
	title: string;
	description: string;
	region: string;
	links: CaseLink[];
}

interface CaseGroup {
	id?: string;
	name: string;
	marker_ids: string[];
}

interface CaseMapConfig extends PartyMapConfig {
	items: CaseItem[];
	groups: CaseGroup[];
	regionPriority: boolean;
	defaultRegion: string;
}

/**
 * Initialize the cases map with a searchable and filterable list of cases.
 * This is a port of the inline script of the politicalpartysite template.
 *
 * @param root
 */
export const initCaseMap = async (root: HTMLElement): Promise<void> => {
	const config = parseConfig<CaseMapConfig>(root, 'data-party-case-map');
	const mapElement = root.querySelector<HTMLElement>(
		'.case-leaflet-region__map'
	);
	const listElement = root.querySelector<HTMLElement>(
		'.case-leaflet-region__list'
	);
	const searchElement = root.querySelector<HTMLInputElement>(
		'.case-leaflet-region__search'
	);
	const filterElement = root.querySelector<HTMLSelectElement>(
		'.case-leaflet-region__filter'
	);

	if (!mapElement || !listElement || !searchElement || !filterElement) {
		return;
	}

	const L = await loadLeaflet();
	const map = createMap(L, mapElement, config);
	const icon = markerIcon(L);
	const markerLayer = L.layerGroup().addTo(map);
	const markers: PartyMarker[] = config.markers || [];
	const items: CaseItem[] = config.items || [];
	const groups: CaseGroup[] = config.groups || [];
	let markerById: Record<string, Leaflet.Marker> = {};

	const findGroup = (name: string): CaseGroup =>
		groups.find((group) => group.name === name) ??
		(name ? { name, marker_ids: [] } : null);

	const inGroup = (
		markerId: string,
		region: string,
		group: CaseGroup
	): boolean => {
		if (!group) {
			return true;
		}

		if (group.marker_ids?.length) {
			return !!markerId && group.marker_ids.includes(markerId);
		}

		return !region || region === group.name;
	};

	const fitRegion = (group: CaseGroup): void => {
		const bounds: Leaflet.LatLngExpression[] = markers
			.filter(
				(marker) =>
					group &&
					inGroup(marker.id, marker.region || '__none__', group)
			)
			.map((marker) => [marker.lat, marker.lng]);

		if (bounds.length) {
			map.fitBounds(L.latLngBounds(bounds), { padding: [24, 24] });
		}
	};

	const selectItem = (item: CaseItem, scrollIntoView: boolean): void => {
		listElement
			.querySelectorAll('.case-leaflet-region__item')
			.forEach((element) => {
				element.classList.toggle(
					'is-active',
					(element as HTMLElement).dataset.markerId === item.marker_id
				);

				if (
					scrollIntoView &&
					(element as HTMLElement).dataset.markerId === item.marker_id
				) {
					element.scrollIntoView({ block: 'nearest' });
				}
			});

		const marker = markerById[item.marker_id];

		if (!marker) {
			return;
		}

		if (config.regionPriority && item.region) {
			fitRegion(findGroup(item.region));
		} else {
			const data = markers.find((entry) => entry.id === item.marker_id);

			map.setView(marker.getLatLng(), data?.zoom || config.zoom || 10);
		}

		marker.openPopup();
	};

	const addMarkers = (group: CaseGroup): void => {
		markerLayer.clearLayers();
		markerById = {};

		markers.forEach((data) => {
			if (!inGroup(data.id, data.region, group)) {
				return;
			}

			const marker = L.marker([data.lat, data.lng], { icon }).addTo(
				markerLayer
			);

			marker.bindPopup(
				popupContent(
					data.title,
					data.description,
					'case-leaflet-region__popup-title',
					'case-leaflet-region__popup-desc'
				)
			);

			marker.on('click', () => {
				const item = items.find((entry) => entry.marker_id === data.id);

				if (item) {
					selectItem(item, true);
				}
			});

			if (data.id) {
				markerById[data.id] = marker;
			}
		});

		if (config.geojson && config.geojson.type) {
			L.geoJSON(config.geojson, {
				onEachFeature: (feature, layer) => {
					if (feature.properties?.name) {
						layer.bindPopup(
							popupContent(feature.properties.name, '')
						);
					}
				},
			}).addTo(markerLayer);
		}
	};

	const matchesSearch = (item: CaseItem, query: string): boolean => {
		if (!query) {
			return true;
		}

		const links = (item.links || [])
			.map((link) => `${link.label} ${link.url}`)
			.join(' ');

		return `${item.title} ${item.description} ${links}`
			.toLowerCase()
			.includes(query);
	};

	const renderLink = (link: CaseLink): HTMLElement => {
		const anchor = document.createElement('a');

		anchor.className = 'case-leaflet-region__link';
		anchor.href = link.url || '#';
		anchor.target = '_blank';
		anchor.rel = 'noopener';

		const iconSource =
			link.type === 'fragdenstaat'
				? link.status_icon
				: link.type === 'external'
					? link.favicon
					: '';

		if (iconSource) {
			const image = document.createElement('img');

			image.className =
				link.type === 'fragdenstaat'
					? 'case-leaflet-region__status'
					: 'case-leaflet-region__favicon';
			image.src = iconSource;
			image.alt =
				link.type === 'fragdenstaat' ? link.status || 'status' : '';
			anchor.appendChild(image);
		}

		const label = document.createElement('span');

		label.textContent = link.label || link.url || '';
		anchor.appendChild(label);

		return anchor;
	};

	const renderList = (): void => {
		const query = searchElement.value.trim().toLowerCase();
		const group = findGroup(filterElement.value);

		listElement.innerHTML = '';

		items.forEach((item) => {
			if (
				!inGroup(item.marker_id, item.region, group) ||
				!matchesSearch(item, query)
			) {
				return;
			}

			const card = document.createElement('div');
			const title = document.createElement('div');

			card.className = 'case-leaflet-region__item';
			card.dataset.markerId = item.marker_id || '';
			title.className = 'case-leaflet-region__item-title';
			title.textContent = item.title || '';
			card.appendChild(title);

			if (item.description) {
				const description = document.createElement('div');

				description.className = 'case-leaflet-region__item-desc';
				description.textContent = item.description;
				card.appendChild(description);
			}

			if (item.links?.length) {
				const links = document.createElement('div');

				links.className = 'case-leaflet-region__links';
				item.links.forEach((link) =>
					links.appendChild(renderLink(link))
				);
				card.appendChild(links);
			}

			card.addEventListener('click', () => selectItem(item, false));
			listElement.appendChild(card);
		});
	};

	filterElement.value = config.defaultRegion || '';
	addMarkers(findGroup(filterElement.value));
	renderList();

	searchElement.addEventListener('input', renderList);
	filterElement.addEventListener('change', () => {
		const group = findGroup(filterElement.value);

		addMarkers(group);
		renderList();

		if (config.regionPriority && group) {
			fitRegion(group);
		}
	});
};
