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

export interface PartyMarker {
	id?: string;
	title?: string;
	description?: string;
	lat: number;
	lng: number;
	region?: string;
	zoom?: number | null;
}

export interface PartyMapConfig {
	center: [number, number];
	zoom: number;
	tileUrl: string;
	markers: PartyMarker[];
	geojson: any;
}

/**
 * Lazy load Leaflet.
 *
 * @return the Leaflet namespace
 */
export const loadLeaflet = async (): Promise<typeof Leaflet> => {
	return (await import('@/vendor/leaflet')).default;
};

/**
 * Parse a JSON config from a data attribute.
 *
 * @param element
 * @param attribute
 * @return the parsed config
 */
export const parseConfig = <T>(element: HTMLElement, attribute: string): T => {
	try {
		return JSON.parse(element.getAttribute(attribute) || '{}');
	} catch {
		return {} as T;
	}
};

/**
 * Create a marker icon that doesn't require any image files.
 *
 * @param L
 * @return the icon
 */
export const markerIcon = (L: typeof Leaflet): Leaflet.DivIcon => {
	return L.divIcon({
		className: '',
		html: '<div class="party-map-marker"></div>',
		iconSize: [22, 22],
		iconAnchor: [11, 26],
		popupAnchor: [0, -24],
	});
};

/**
 * Create the popup content of a marker.
 *
 * @param title
 * @param description
 * @param titleClass
 * @param descriptionClass
 * @return the popup element
 */
export const popupContent = (
	title: string,
	description: string,
	titleClass: string = 'party-map-popup__title',
	descriptionClass: string = 'party-map-popup__desc'
): HTMLElement => {
	const wrapper = document.createElement('div');
	const titleElement = document.createElement('strong');

	titleElement.className = titleClass;
	titleElement.textContent = title || '';
	wrapper.appendChild(titleElement);

	if (description) {
		const descriptionElement = document.createElement('div');

		descriptionElement.className = descriptionClass;
		descriptionElement.textContent = description;
		wrapper.appendChild(descriptionElement);
	}

	return wrapper;
};

/**
 * Create a map with a tile layer.
 *
 * @param L
 * @param element
 * @param config
 * @return the map
 */
export const createMap = (
	L: typeof Leaflet,
	element: HTMLElement,
	config: Partial<PartyMapConfig>
): Leaflet.Map => {
	const map = L.map(element, { scrollWheelZoom: false }).setView(
		config.center ?? [52.520008, 13.404954],
		config.zoom || 10
	);

	L.tileLayer(
		config.tileUrl || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
		{
			maxZoom: 19,
			attribution: '&copy; OpenStreetMap contributors',
		}
	).addTo(map);

	// Maps that are initialized while being hidden (e.g. in sliders) need to be resized once visible.
	if ('IntersectionObserver' in window) {
		new IntersectionObserver((entries) => {
			if (entries.some((entry) => entry.isIntersecting)) {
				map.invalidateSize();
			}
		}).observe(element);
	}

	return map;
};

/**
 * Create the GeoJSON layer options. Points with a radius property are rendered as circles
 * (that is how the map editor stores circles), other points as small circle markers.
 *
 * @param L
 * @return the options
 */
export const geoJsonOptions = (L: typeof Leaflet): Leaflet.GeoJSONOptions => ({
	pointToLayer: (feature, latLng) => {
		const radius = Number(feature?.properties?.radius);

		return radius
			? L.circle(latLng, { radius })
			: L.circleMarker(latLng, { radius: 6 });
	},
	onEachFeature: (feature, layer) => {
		if (feature.properties?.name) {
			layer.bindPopup(popupContent(feature.properties.name, ''));
		}
	},
});

/**
 * Initialize a simple map.
 *
 * @param element
 */
export const initMap = async (element: HTMLElement): Promise<void> => {
	const config = parseConfig<PartyMapConfig>(element, 'data-party-map');
	const L = await loadLeaflet();
	const map = createMap(L, element, config);
	const icon = markerIcon(L);

	(config.markers || []).forEach((marker) => {
		const layer = L.marker([marker.lat, marker.lng], { icon }).addTo(map);

		if (marker.title || marker.description) {
			layer.bindPopup(popupContent(marker.title, marker.description));
		}
	});

	if (config.geojson && config.geojson.type) {
		L.geoJSON(config.geojson, geoJsonOptions(L)).addTo(map);
	}
};
