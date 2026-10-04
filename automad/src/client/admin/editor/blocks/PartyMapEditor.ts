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
import { create, CSS, html, listen } from '@/admin/core';
import type { KeyValueMap, Listener } from '@/admin/types';

/**
 * The names of the fields that are edited by the map editor.
 */
export interface PartyMapBinding {
	markers?: string;
	geojson?: string;
	center?: string;
	zoom?: string;
	tileUrl?: string;
	height?: string;
}

/**
 * The map editor controller.
 */
export interface PartyMapEditor {
	reload: () => void;
	destroy: () => void;
	invalidate: () => void;
}

type Tool = 'marker' | 'polyline' | 'polygon' | 'rectangle' | 'circle';

const DEFAULT_TILES = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

/**
 * Parse a "lat,lng" center value.
 *
 * @param value
 * @return the center
 */
const parseCenter = (value: any): [number, number] => {
	const parts = Array.isArray(value) ? value : `${value ?? ''}`.split(',');
	const lat = parseFloat(parts[0]);
	const lng = parseFloat(parts[1]);

	return isNaN(lat) || isNaN(lng) ? [51.0, 10.0] : [lat, lng];
};

/**
 * Create a short unique marker id.
 *
 * @return the id
 */
const markerId = (): string => `m-${Math.random().toString(36).slice(2, 8)}`;

/**
 * Set German texts for the drawing tools.
 *
 * @param L
 */
const localize = (L: typeof Leaflet): void => {
	const draw = (L as any).drawLocal;

	if (!draw || draw._partyLocalized) {
		return;
	}

	const handlers = draw.draw.handlers;

	handlers.marker.tooltip.start = 'Klicken, um den Marker zu setzen.';
	handlers.circlemarker.tooltip.start = 'Klicken, um den Punkt zu setzen.';
	handlers.circle.tooltip.start =
		'Klicken und ziehen, um einen Kreis zu zeichnen.';
	handlers.circle.radius = 'Radius';
	handlers.rectangle.tooltip.start =
		'Klicken und ziehen, um ein Rechteck zu zeichnen.';
	handlers.simpleshape.tooltip.end =
		'Maus loslassen, um die Form abzuschließen.';
	handlers.polyline.tooltip.start = 'Klicken, um die Linie zu beginnen.';
	handlers.polyline.tooltip.cont = 'Klicken, um die Linie fortzusetzen.';
	handlers.polyline.tooltip.end =
		'Letzten Punkt erneut anklicken, um die Linie zu beenden.';
	handlers.polyline.error =
		'<strong>Fehler:</strong> Linien dürfen sich nicht kreuzen.';
	handlers.polygon.tooltip.start = 'Klicken, um die Fläche zu beginnen.';
	handlers.polygon.tooltip.cont = 'Klicken, um die Fläche fortzusetzen.';
	handlers.polygon.tooltip.end =
		'Ersten Punkt anklicken, um die Fläche zu schließen.';

	draw.edit.handlers.edit.tooltip.text =
		'Punkte ziehen, um die Formen zu verändern.';
	draw.edit.handlers.edit.tooltip.subtext = 'Mit „Fertig“ übernehmen.';
	draw._partyLocalized = true;
};

/**
 * Create an interactive map editor that is a port of the Leaflet editor of politicalpartysite.
 * Markers are stored in the markers list (keeping all additional marker data like title or region),
 * all other shapes are stored as GeoJSON. Circles are stored as points with a radius property.
 *
 * @param container
 * @param data - the object that contains the bound fields
 * @param bind - the names of the bound fields
 * @param onSync - called with the names of the changed fields
 * @return the editor controller
 */
export const createPartyMapEditor = async (
	container: HTMLElement,
	data: KeyValueMap,
	bind: PartyMapBinding,
	onSync: (changed: string[]) => void
): Promise<PartyMapEditor> => {
	const L = (await import('@/vendor/leafletDraw')).default;
	const listeners: Listener[] = [];

	localize(L);

	const toolbar = create(
		'div',
		[CSS.editorBlockPartyMapToolbar],
		{},
		container,
		html`
			<select data-tool>
				<option value="marker">Marker</option>
				<option value="polyline">Linie</option>
				<option value="polygon">Fläche</option>
				<option value="rectangle">Rechteck</option>
				<option value="circle">Kreis</option>
			</select>
			<button type="button" class="${CSS.button}" data-act="draw">
				<i class="bi bi-pencil"></i><span>Zeichnen</span>
			</button>
			<button type="button" class="${CSS.button}" data-act="edit">
				<i class="bi bi-bounding-box-circles"></i
				><span>Formen bearbeiten</span>
			</button>
			<button type="button" class="${CSS.button}" data-act="delete">
				<i class="bi bi-eraser"></i><span>Löschen</span>
			</button>
			<button type="button" class="${CSS.button}" data-act="done">
				<i class="bi bi-check2"></i><span>Fertig</span>
			</button>
			<button type="button" class="${CSS.button}" data-act="fit">
				<i class="bi bi-arrows-fullscreen"></i><span>Alles zeigen</span>
			</button>
			<button type="button" class="${CSS.button}" data-act="clear">
				<i class="bi bi-trash3"></i><span>Alles entfernen</span>
			</button>
		`
	);

	const status = create(
		'div',
		[CSS.editorBlockPartyMapStatus],
		{},
		container
	);
	const initialHeight =
		parseInt(`${(bind.height && data[bind.height]) || ''}`) || 400;
	const mapElement = create(
		'div',
		[CSS.editorBlockPartyMap],
		{ style: `height: ${Math.min(Math.max(initialHeight, 250), 800)}px;` },
		container
	);

	const map = L.map(mapElement).setView(
		parseCenter(bind.center ? data[bind.center] : null),
		Number((bind.zoom && data[bind.zoom]) || 6)
	);

	let tiles = L.tileLayer(
		(bind.tileUrl && data[bind.tileUrl]) || DEFAULT_TILES,
		{ maxZoom: 19, attribution: '&copy; OpenStreetMap' }
	).addTo(map);

	const icon = L.divIcon({
		className: '',
		html: '<div class="party-map-marker"></div>',
		iconSize: [22, 22],
		iconAnchor: [11, 26],
	});

	const shapes = new L.FeatureGroup().addTo(map);
	const markers = new L.FeatureGroup().addTo(map);

	let activeDraw: any = null;
	let activeEdit: any = null;
	let deleteMode = false;
	let signature = '';
	let ready = false;

	const setStatus = (text: string): void => {
		status.textContent = text;
	};

	const defaultStatus = (): void => {
		const markerCount = markers.getLayers().length;
		const shapeCount = shapes.getLayers().length;

		setStatus(
			`${markerCount} Marker, ${shapeCount} Formen · Marker lassen sich verschieben, die Kartenansicht wird als Startansicht gespeichert.`
		);
	};

	const currentSignature = (): string =>
		JSON.stringify([
			bind.markers ? data[bind.markers] : null,
			bind.geojson ? data[bind.geojson] : null,
		]);

	const markerList = (): KeyValueMap[] => {
		if (!bind.markers) {
			return [];
		}

		if (!Array.isArray(data[bind.markers])) {
			data[bind.markers] = [];
		}

		return data[bind.markers];
	};

	const syncMarkers = (): void => {
		if (!bind.markers) {
			return;
		}

		const list = markerList();
		const existing = new Map<string, KeyValueMap>();

		list.forEach((marker) => {
			if (marker?.id) {
				existing.set(`${marker.id}`, marker);
			}
		});

		const next: KeyValueMap[] = [];

		markers.eachLayer((layer: any) => {
			const latLng = layer.getLatLng();
			const marker = existing.get(layer._partyMarkerId) ?? {
				id: layer._partyMarkerId,
				title: '',
				description: '',
			};

			marker.lat = +latLng.lat.toFixed(6);
			marker.lng = +latLng.lng.toFixed(6);
			next.push(marker);
		});

		// Mutate in place in order to keep references of the form lists valid.
		list.splice(0, list.length, ...next);
	};

	const syncShapes = (): void => {
		if (!bind.geojson) {
			return;
		}

		const features: any[] = [];

		shapes.eachLayer((layer: any) => {
			const feature = layer.toGeoJSON();

			if (layer instanceof L.Circle) {
				feature.properties = {
					...feature.properties,
					radius: Math.round(layer.getRadius()),
				};
			}

			features.push(feature);
		});

		data[bind.geojson] = features.length
			? { type: 'FeatureCollection', features }
			: null;
	};

	const sync = (changed: string[]): void => {
		if (changed.includes('markers')) {
			syncMarkers();
		}

		if (changed.includes('geojson')) {
			syncShapes();
		}

		signature = currentSignature();
		defaultStatus();
		onSync(
			changed.map((key) => (bind as KeyValueMap)[key]).filter(Boolean)
		);
	};

	const addMarker = (
		lat: number,
		lng: number,
		id: string,
		title: string
	): void => {
		const marker: any = L.marker([lat, lng], {
			icon,
			draggable: true,
			title,
		});

		marker._partyMarkerId = id;

		if (title) {
			marker.bindTooltip(title);
		}

		marker.on('dragend', () => sync(['markers']));
		marker.addTo(markers);
	};

	const addFeature = (feature: any): void => {
		const geometry = feature?.geometry;

		if (!geometry) {
			return;
		}

		if (geometry.type === 'Point') {
			const [lng, lat] = geometry.coordinates;
			const radius = Number(feature.properties?.radius);
			const layer: any = radius
				? L.circle([lat, lng], { radius })
				: L.circleMarker([lat, lng], { radius: 6 });

			layer.feature = {
				type: 'Feature',
				properties: { ...(feature.properties || {}) },
			};
			shapes.addLayer(layer);

			return;
		}

		L.geoJSON(feature).eachLayer((layer) => shapes.addLayer(layer));
	};

	const reload = (): void => {
		if (currentSignature() === signature) {
			return;
		}

		markers.clearLayers();
		shapes.clearLayers();

		let addedIds = false;

		markerList().forEach((marker) => {
			const lat = parseFloat(marker?.lat);
			const lng = parseFloat(marker?.lng);

			if (isNaN(lat) || isNaN(lng)) {
				return;
			}

			if (!marker.id) {
				marker.id = markerId();
				addedIds = true;
			}

			addMarker(lat, lng, `${marker.id}`, `${marker.title || ''}`);
		});

		const geojson = bind.geojson ? data[bind.geojson] : null;
		const features =
			geojson?.type === 'FeatureCollection'
				? geojson.features || []
				: geojson?.type === 'Feature'
					? [geojson]
					: [];

		features.forEach(addFeature);

		signature = currentSignature();
		defaultStatus();

		if (addedIds) {
			onSync([bind.markers]);
		}
	};

	const disableAll = (): void => {
		try {
			activeDraw?.disable();
		} catch {}

		try {
			activeEdit?.save();
			activeEdit?.disable();
		} catch {}

		activeDraw = null;
		activeEdit = null;
		deleteMode = false;
		mapElement.classList.remove(CSS.editorBlockPartyMapDelete);
		defaultStatus();
	};

	const fit = (): void => {
		const group = L.featureGroup([
			...markers.getLayers(),
			...shapes.getLayers(),
		]);

		if (group.getLayers().length) {
			map.fitBounds(group.getBounds(), {
				padding: [24, 24],
				maxZoom: 14,
			});
		}
	};

	map.on(L.Draw.Event.CREATED, (event: any) => {
		if (event.layerType === 'marker') {
			const latLng = event.layer.getLatLng();

			addMarker(latLng.lat, latLng.lng, markerId(), '');
			disableAll();
			sync(['markers']);

			return;
		}

		shapes.addLayer(event.layer);
		disableAll();
		sync(['geojson']);
	});

	map.on(L.Draw.Event.EDITED, () => sync(['geojson']));

	const onDelete = (event: any): void => {
		if (!deleteMode) {
			return;
		}

		const layer = event.propagatedFrom ?? event.layer;
		const isMarker = markers.hasLayer(layer);

		(isMarker ? markers : shapes).removeLayer(layer);
		sync([isMarker ? 'markers' : 'geojson']);
		setStatus(
			'Löschmodus: Marker oder Form anklicken, um sie zu entfernen. „Fertig“ beendet den Modus.'
		);
	};

	markers.on('click', onDelete);
	shapes.on('click', onDelete);

	map.on('moveend', () => {
		if (!ready) {
			return;
		}

		const center = map.getCenter();
		const changed: string[] = [];

		if (bind.center) {
			data[bind.center] =
				`${center.lat.toFixed(6)},${center.lng.toFixed(6)}`;
			changed.push(bind.center);
		}

		if (bind.zoom) {
			data[bind.zoom] = map.getZoom();
			changed.push(bind.zoom);
		}

		if (changed.length) {
			onSync(changed);
		}
	});

	listeners.push(
		listen(toolbar, 'click', (event: Event) => {
			const button = (event.target as HTMLElement).closest(
				'[data-act]'
			) as HTMLElement;

			if (!button) {
				return;
			}

			event.preventDefault();

			const action = button.dataset.act;

			disableAll();

			switch (action) {
				case 'draw': {
					const tool = (
						toolbar.querySelector(
							'[data-tool]'
						) as HTMLSelectElement
					).value as Tool;

					if (
						(tool === 'marker' && !bind.markers) ||
						(tool !== 'marker' && !bind.geojson)
					) {
						return;
					}

					const tools: Record<Tool, () => any> = {
						marker: () => new L.Draw.Marker(map as any, { icon }),
						polyline: () => new L.Draw.Polyline(map as any, {}),
						polygon: () =>
							new L.Draw.Polygon(map as any, {
								allowIntersection: false,
							}),
						rectangle: () => new L.Draw.Rectangle(map as any, {}),
						circle: () => new L.Draw.Circle(map as any, {}),
					};

					activeDraw = tools[tool]();
					activeDraw.enable();
					break;
				}
				case 'edit':
					if (!shapes.getLayers().length) {
						setStatus('Es gibt noch keine Formen zum Bearbeiten.');

						return;
					}

					activeEdit = new (L as any).EditToolbar.Edit(map, {
						featureGroup: shapes,
						selectedPathOptions: {
							dashArray: '10, 10',
							fill: true,
							fillColor: '#fe57a1',
							fillOpacity: 0.1,
							maintainColor: false,
						},
					});

					activeEdit.enable();
					setStatus(
						'Bearbeiten: Punkte der Formen ziehen. „Fertig“ übernimmt die Änderungen.'
					);
					break;
				case 'delete':
					deleteMode = true;
					mapElement.classList.add(CSS.editorBlockPartyMapDelete);
					setStatus(
						'Löschmodus: Marker oder Form anklicken, um sie zu entfernen. „Fertig“ beendet den Modus.'
					);
					break;
				case 'fit':
					fit();
					break;
				case 'clear':
					if (
						!window.confirm(
							'Alle Marker und Formen von der Karte entfernen?'
						)
					) {
						return;
					}

					markers.clearLayers();
					shapes.clearLayers();
					sync(['markers', 'geojson']);
					break;
			}
		})
	);

	// Store the height when the map is resized by the user.
	let lastHeight = mapElement.offsetHeight;
	const resizeObserver = new ResizeObserver(() => {
		map.invalidateSize();

		const height = mapElement.offsetHeight;

		if (
			ready &&
			bind.height &&
			height > 0 &&
			Math.abs(height - lastHeight) > 4
		) {
			lastHeight = height;
			data[bind.height] = `${height}px`;
			onSync([bind.height]);
		}
	});

	resizeObserver.observe(mapElement);

	reload();

	// Ignore the initial view changes that are caused by opening the modal.
	setTimeout(() => {
		map.invalidateSize();
		lastHeight = mapElement.offsetHeight;
		ready = true;
	}, 600);

	return {
		reload: () => {
			const url = (bind.tileUrl && data[bind.tileUrl]) || DEFAULT_TILES;

			if ((tiles as any)._url !== url) {
				tiles.remove();
				tiles = L.tileLayer(url, {
					maxZoom: 19,
					attribution: '&copy; OpenStreetMap',
				}).addTo(map);
			}

			reload();
		},
		invalidate: () => map.invalidateSize(),
		destroy: () => {
			disableAll();
			resizeObserver.disconnect();
			listeners.forEach((listener) => listener.remove());
			map.remove();
		},
	};
};
