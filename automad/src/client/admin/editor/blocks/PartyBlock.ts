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

import {
	App,
	Bindings,
	create,
	createField,
	createGenericModal,
	createSelect,
	CSS,
	debounce,
	EventName,
	FieldTag,
	getPageURL,
	PartyBlockController,
	query,
	requestAPI,
	resolveFileUrl,
	uniqueId,
} from '@/admin/core';
import { BaseFieldComponent } from '@/admin/components/Fields/BaseField';
import { BaseBlock } from './BaseBlock';
import {
	createPartyMapEditor,
	type PartyMapBinding,
	type PartyMapEditor,
} from './PartyMapEditor';
import type { KeyValueMap } from '@/admin/types';

/**
 * A field definition as it is provided by the PHP class of a party component.
 */
export interface PartyField {
	name: string;
	type:
		| 'text'
		| 'textarea'
		| 'markdown'
		| 'html'
		| 'number'
		| 'select'
		| 'toggle'
		| 'image'
		| 'url'
		| 'color'
		| 'strings'
		| 'json'
		| 'list'
		| 'map';
	label?: string;
	default?: any;
	placeholder?: string;
	help?: string;
	options?: KeyValueMap;
	fields?: PartyField[];
	itemTitle?: string;
	bind?: PartyMapBinding;
	object?: string;
	optionsFrom?: { field: string; value: string; label?: string };
}

/**
 * A hook that updates a part of an open form when data is changed by another field.
 */
interface FormHook {
	element: HTMLElement;
	target?: KeyValueMap;
	name?: string;
	onFormChange?: boolean;
	refresh: () => void;
}

/**
 * The definition of a party block as it is sent by the app bootstrap controller.
 */
export interface PartyBlockDefinition {
	type: string;
	component: string;
	title: string;
	icon: string;
	description: string;
	stretchable: boolean;
	fields: PartyField[];
	defaults: KeyValueMap;
}

/**
 * Map simple field types to Automad field components.
 * Note that this is a function in order to avoid accessing the field tags
 * before the core module is fully initialized.
 *
 * @param type
 * @return the field tag
 */
const fieldTag = (type: PartyField['type']): FieldTag => {
	const tags: Partial<Record<PartyField['type'], FieldTag>> = {
		text: FieldTag.input,
		textarea: FieldTag.textarea,
		markdown: FieldTag.markdown,
		html: FieldTag.code,
		number: FieldTag.number,
		toggle: FieldTag.toggle,
		image: FieldTag.image,
		url: FieldTag.url,
		color: FieldTag.color,
		strings: FieldTag.textarea,
		json: FieldTag.code,
	};

	return tags[type] ?? FieldTag.input;
};

/**
 * Deep clone plain data.
 *
 * @param value
 * @return the cloned value
 */
const clone = <T>(value: T): T => {
	if (value === undefined) {
		return value;
	}

	return JSON.parse(JSON.stringify(value));
};

/**
 * Return the default value of a field.
 *
 * @param field
 * @return the default value
 */
const defaultValue = (field: PartyField): any => {
	if (field.default !== undefined && field.default !== null) {
		return clone(field.default);
	}

	switch (field.type) {
		case 'list':
		case 'strings':
			return [];
		case 'toggle':
			return false;
		case 'json':
			return null;
		default:
			return '';
	}
};

/**
 * Create the default data for a list of fields.
 *
 * @param fields
 * @return the default data
 */
const defaultData = (fields: PartyField[]): KeyValueMap => {
	return fields.reduce((data: KeyValueMap, field) => {
		if (field.type === 'map') {
			return data;
		}

		data[field.name] = defaultValue(field);

		return data;
	}, {});
};

/**
 * Convert a stored value into the string that is used by the form field.
 *
 * @param field
 * @param value
 * @return the field value
 */
const toFieldValue = (field: PartyField, value: any): any => {
	switch (field.type) {
		case 'strings':
			return Array.isArray(value) ? value.join('\n') : `${value ?? ''}`;
		case 'json':
			if (value === null || value === undefined || value === '') {
				return '';
			}

			return typeof value === 'string'
				? value
				: JSON.stringify(value, null, 2);
		case 'toggle':
			return !!value;
		default:
			return value ?? '';
	}
};

/**
 * Convert a form field value back into the stored value.
 *
 * @param field
 * @param value
 * @return the stored value
 */
const fromFieldValue = (field: PartyField, value: any): any => {
	switch (field.type) {
		case 'strings':
			return `${value ?? ''}`
				.split(/\r?\n/)
				.map((line) => line.trim())
				.filter((line) => line.length > 0);
		case 'json':
			if (`${value ?? ''}`.trim() === '') {
				return null;
			}

			try {
				return JSON.parse(value);
			} catch {
				// Keep the raw string while typing, the server ignores invalid JSON.
				return value;
			}
		case 'number':
			return value === '' || isNaN(Number(value)) ? '' : Number(value);
		case 'toggle':
			return !!value;
		default:
			return value;
	}
};

/**
 * Get a short text representation of a value used in summaries.
 *
 * @param value
 * @return the text
 */
const summarize = (value: any): string => {
	if (typeof value !== 'string') {
		return '';
	}

	const div = document.createElement('div');

	div.innerHTML = value;

	const text = (div.textContent || '').replace(/\s+/g, ' ').trim();

	return text.length > 120 ? `${text.slice(0, 117)}…` : text;
};

/**
 * Create a party block tool class for a given definition.
 *
 * @param definition
 * @return the block tool class
 */
export const createPartyBlockTool = (definition: PartyBlockDefinition) => {
	return class extends PartyBlock {
		static get toolbox() {
			return {
				title: definition.title,
				icon: `<i class="bi bi-${definition.icon}"></i>`,
			};
		}

		static get sanitize() {
			// Keep all HTML inside of all fields. Escaping is done by the template engine.
			return definition.fields.reduce((sanitize: KeyValueMap, field) => {
				sanitize[field.name] = true;

				return sanitize;
			}, {});
		}

		protected get definition(): PartyBlockDefinition {
			return definition;
		}
	};
};

/**
 * The generic party block that renders a summary and a live preview of a party component
 * and provides a modal form that is generated from the component's field definitions.
 */
export abstract class PartyBlock extends BaseBlock<KeyValueMap> {
	/**
	 * The definition of the party component.
	 */
	protected abstract get definition(): PartyBlockDefinition;

	/**
	 * The summary container.
	 */
	private summary: HTMLElement;

	/**
	 * The preview container.
	 */
	private preview: HTMLElement;

	/**
	 * The preview state.
	 */
	private previewEnabled = false;

	/**
	 * Prepare the data that is passed to the constructor.
	 * New blocks are initialized with the defaults of the component.
	 *
	 * @param data
	 * @return the prepared data
	 */
	protected prepareData(data: KeyValueMap): KeyValueMap {
		const fields = this.definition.fields;
		const prepared = clone(data || {});

		if (Object.keys(prepared).length === 0) {
			return defaultData(fields);
		}

		fields.forEach((field) => {
			if (field.type !== 'map' && prepared[field.name] === undefined) {
				prepared[field.name] = defaultValue(field);
			}
		});

		return prepared;
	}

	/**
	 * Render the block.
	 *
	 * @return the rendered element
	 */
	render(): HTMLElement {
		const { title, icon, description } = this.definition;
		const card = create(
			'div',
			[CSS.editorBlockParty],
			{ 'data-party-type': this.definition.type },
			this.wrapper
		);

		const header = create('div', [CSS.editorBlockPartyHeader], {}, card);
		const label = create('span', [CSS.editorBlockPartyTitle], {}, header);

		create('i', ['bi', `bi-${icon}`], {}, label);
		create('span', [], {}, label).textContent = title;

		const actions = create(
			'span',
			[CSS.editorBlockPartyActions],
			{},
			header
		);
		const editButton = create(
			'button',
			[CSS.button, CSS.buttonIcon],
			{ type: 'button', title: App.text('edit') },
			actions,
			'<i class="bi bi-pencil"></i>'
		);

		const previewButton = create(
			'button',
			[CSS.button, CSS.buttonIcon],
			{ type: 'button', title: 'Vorschau' },
			actions,
			'<i class="bi bi-eye"></i>'
		);

		if (description) {
			create(
				'div',
				[CSS.editorBlockPartyDescription],
				{},
				card
			).textContent = description;
		}

		this.summary = create('div', [CSS.editorBlockPartySummary], {}, card);
		this.preview = create('div', [CSS.editorBlockPartyPreview], {}, card);

		this.renderSummary();

		if (!this.readOnly) {
			this.listen(editButton, 'click', (event: Event) => {
				event.stopPropagation();
				this.openForm();
			});

			this.listen(this.summary, 'click', () => {
				this.openForm();
			});
		} else {
			editButton.setAttribute('disabled', '');
		}

		this.listen(previewButton, 'click', (event: Event) => {
			event.stopPropagation();
			this.previewEnabled = !this.previewEnabled;
			previewButton.classList.toggle(CSS.active, this.previewEnabled);
			this.renderPreview();
		});

		if (this.previewEnabled) {
			previewButton.classList.add(CSS.active);
			this.renderPreview();
		}

		return this.wrapper;
	}

	/**
	 * Return the block data.
	 *
	 * @return the saved data
	 */
	getData(): KeyValueMap {
		return clone(this.data);
	}

	/**
	 * Save all data including empty values in order to allow for clearing fields
	 * that have a non-empty default value.
	 *
	 * @return the data
	 */
	save(): KeyValueMap {
		return this.getData();
	}

	/**
	 * Notify the editor about a change.
	 */
	private changed = debounce(() => {
		this.renderSummary();
		this.blockAPI.dispatchChange();

		if (this.previewEnabled) {
			this.renderPreview();
		}
	}, 200);

	/**
	 * Render a short summary of the block content.
	 */
	private renderSummary(): void {
		this.summary.innerHTML = '';

		const list = create('dl', [], {}, this.summary);
		let count = 0;

		this.definition.fields.forEach((field) => {
			const value = this.data[field.name];
			let text = '';

			if (field.name === 'classes' || field.type === 'map') {
				return;
			}

			switch (field.type) {
				case 'list':
					text = Array.isArray(value)
						? `${value.length} ${value.length === 1 ? 'Eintrag' : 'Einträge'}`
						: '';
					break;
				case 'strings':
					text = Array.isArray(value) ? value.join(' · ') : '';
					break;
				case 'toggle':
					text = value ? '✓' : '';
					break;
				case 'json':
					text = value ? 'JSON' : '';
					break;
				case 'select':
					text = field.options?.[value] ?? `${value ?? ''}`;
					break;
				case 'image':
					if (value) {
						const dd = this.summaryRow(list, field);

						create(
							'img',
							[],
							{ src: resolveFileUrl(`${value}`), alt: '' },
							dd
						);

						count++;
					}

					return;
				default:
					text = summarize(`${value ?? ''}`);
			}

			if (!text || count >= 6) {
				return;
			}

			this.summaryRow(list, field).textContent = text;
			count++;
		});

		if (count === 0) {
			create('p', [CSS.textMuted], {}, this.summary).textContent =
				'Klicken, um die Inhalte zu bearbeiten …';
		}
	}

	/**
	 * Create a summary row.
	 *
	 * @param list
	 * @param field
	 * @return the value element
	 */
	private summaryRow(list: HTMLElement, field: PartyField): HTMLElement {
		create('dt', [], {}, list).textContent = field.label || field.name;

		return create('dd', [], {}, list);
	}

	/**
	 * Render a live preview of the block by requesting the rendered HTML from the server.
	 */
	private async renderPreview(): Promise<void> {
		if (!this.previewEnabled) {
			this.preview.innerHTML = '';

			return;
		}

		const { data } = await requestAPI(PartyBlockController.preview, {
			type: this.definition.type,
			data: JSON.stringify(this.getData()),
			url: getPageURL() || '/',
		});

		if (!this.previewEnabled || !data) {
			return;
		}

		const iframe = create(
			'iframe',
			[],
			{ title: `${this.definition.title} – Vorschau`, loading: 'lazy' },
			null
		) as HTMLIFrameElement;

		iframe.srcdoc = `<!DOCTYPE html>
			<html lang="de">
				<head>
					<meta charset="utf-8">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<base href="${location.origin}${App.baseURL}/" target="_blank">
					${data.assets}
					<style>body { margin: 0; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; } a { pointer-events: none; }</style>
				</head>
				<body>${data.html}</body>
			</html>`;

		const resize = () => {
			try {
				const height =
					iframe.contentDocument.documentElement.scrollHeight;

				iframe.style.height = `${Math.min(Math.max(height, 80), 900)}px`;
			} catch {}
		};

		iframe.addEventListener('load', () => {
			resize();
			setTimeout(resize, 500);
			setTimeout(resize, 1500);
		});

		this.preview.innerHTML = '';
		this.preview.appendChild(iframe);
	}

	/**
	 * Open the modal form.
	 */
	private openForm(): void {
		const { modal, body } = createGenericModal(this.definition.title);

		query(`.${CSS.modalDialog}`, modal).classList.add(CSS.modalDialogLarge);

		const form = create('div', [CSS.editorBlockPartyForm], {}, body);

		this.formHooks = [];
		this.formCleanup = [];

		this.renderFields(this.definition.fields, this.data, form, () =>
			this.runFormHooks()
		);

		Bindings.connectElements(body);

		const cleanup = () => {
			this.formCleanup.forEach((callback) => callback());
			this.formCleanup = [];
			this.formHooks = [];
		};

		modal.listen(modal, EventName.modalClose, (event: Event) => {
			// Ignore events of nested modals like the image picker.
			if (event.target === modal) {
				cleanup();
			}
		});

		setTimeout(() => {
			modal.open();
		});
	}

	/**
	 * Hooks that update parts of the open form.
	 */
	private formHooks: FormHook[] = [];

	/**
	 * Callbacks that are called when the form is closed.
	 */
	private formCleanup: Array<() => void> = [];

	/**
	 * Register a form hook.
	 *
	 * @param hook
	 */
	private addFormHook(hook: FormHook): void {
		this.formHooks.push(hook);
	}

	/**
	 * Refresh all fields of a data object that have been changed by another field.
	 *
	 * @param target
	 * @param names
	 */
	private refreshValues(target: KeyValueMap, names: string[]): void {
		this.formHooks.forEach((hook) => {
			if (
				hook.target === target &&
				names.includes(hook.name) &&
				hook.element.isConnected
			) {
				hook.refresh();
			}
		});
	}

	/**
	 * Run all hooks that depend on any change in the form.
	 */
	private runFormHooks(): void {
		this.formHooks.forEach((hook) => {
			if (hook.onFormChange && hook.element.isConnected) {
				hook.refresh();
			}
		});
	}

	/**
	 * Render a set of fields that are bound to a data object.
	 *
	 * @param fields
	 * @param target
	 * @param container
	 * @param onChange
	 */
	private renderFields(
		fields: PartyField[],
		target: KeyValueMap,
		container: HTMLElement,
		onChange: () => void = () => {}
	): void {
		fields.forEach((field) => {
			if (field.type === 'list') {
				this.renderList(field, target, container, onChange);

				return;
			}

			if (field.type === 'select') {
				this.renderSelect(field, target, container, onChange);

				return;
			}

			if (field.type === 'map') {
				this.renderMap(field, target, container);

				return;
			}

			const tag = fieldTag(field.type);
			const wrapper = create(
				'div',
				[CSS.editorBlockPartyField],
				{},
				container
			);
			const attributes: KeyValueMap = {};

			if (field.placeholder) {
				attributes.placeholder = field.placeholder;
			}

			const component = createField(
				tag,
				wrapper,
				{
					key: uniqueId(),
					name: `party_${field.type === 'html' ? 'html' : 'field'}_${uniqueId()}`,
					label: field.label || field.name,
					value: toFieldValue(field, target[field.name]),
					placeholder: field.placeholder || '',
				},
				[],
				attributes
			) as BaseFieldComponent;

			if (field.help) {
				create('small', [CSS.textMuted], {}, wrapper).textContent =
					field.help;
			}

			const update = debounce(() => {
				target[field.name] = fromFieldValue(field, component.query());
				onChange();
				this.changed();
			}, 100);

			this.listen(wrapper, 'input change', update);

			this.addFormHook({
				element: wrapper,
				target,
				name: field.name,
				refresh: () => {
					const value = toFieldValue(field, target[field.name]);

					if (component.query() !== value) {
						component.mutate(value);
					}
				},
			});
		});
	}

	/**
	 * Render the interactive map editor.
	 *
	 * @param field
	 * @param target
	 * @param container
	 */
	private renderMap(
		field: PartyField,
		target: KeyValueMap,
		container: HTMLElement
	): void {
		const wrapper = create(
			'div',
			[
				CSS.field,
				CSS.editorBlockPartyField,
				CSS.editorBlockPartyMapField,
			],
			{},
			container
		);

		create(
			'label',
			[CSS.fieldLabel],
			{},
			create('div', [], {}, wrapper)
		).textContent = field.label || field.name;

		if (field.help) {
			create('small', [CSS.textMuted], {}, wrapper).textContent =
				field.help;
		}

		const host = create('div', [], {}, wrapper);

		// With an "object" the bound values are stored inside of a JSON field.
		const object = (): KeyValueMap => {
			if (!field.object) {
				return target;
			}

			const value = target[field.object];

			if (!value || typeof value !== 'object' || Array.isArray(value)) {
				target[field.object] = {};
			}

			return target[field.object];
		};

		const data = new Proxy({} as KeyValueMap, {
			get: (_, key: string) => object()[key],
			set: (_, key: string, value) => {
				object()[key] = value;

				return true;
			},
		});

		let editor: PartyMapEditor = null;
		let destroyed = false;

		createPartyMapEditor(host, data, field.bind || {}, (changed) => {
			this.refreshValues(object(), changed);

			if (field.object) {
				this.refreshValues(target, [field.object]);
			}

			this.runFormHooks();
			this.changed();
		})
			.then((instance) => {
				if (destroyed) {
					instance.destroy();

					return;
				}

				editor = instance;
			})
			.catch((error) => {
				host.textContent = `Die Karte konnte nicht geladen werden: ${error}`;
			});

		this.addFormHook({
			element: wrapper,
			onFormChange: true,
			refresh: () => editor?.reload(),
		});

		this.formCleanup.push(() => {
			destroyed = true;
			editor?.destroy();
		});
	}

	/**
	 * Render a select field.
	 *
	 * @param field
	 * @param target
	 * @param container
	 * @param onChange
	 */
	private renderSelect(
		field: PartyField,
		target: KeyValueMap,
		container: HTMLElement,
		onChange: () => void
	): void {
		const id = uniqueId();
		const wrapper = create(
			'div',
			[CSS.field, CSS.editorBlockPartyField],
			{},
			container
		);
		const labelWrapper = create('div', [], {}, wrapper);

		create(
			'label',
			[CSS.fieldLabel],
			{ for: id },
			labelWrapper
		).textContent = field.label || field.name;

		const options = () => {
			const fixed = Object.keys(field.options || {}).map((value) => ({
				value,
				text: field.options[value],
			}));

			const source = field.optionsFrom;

			if (!source || !Array.isArray(this.data[source.field])) {
				return fixed;
			}

			const derived = (this.data[source.field] as KeyValueMap[])
				.filter((entry) => entry && entry[source.value])
				.map((entry) => {
					const value = `${entry[source.value]}`;
					const label = source.label
						? summarize(`${entry[source.label] ?? ''}`)
						: '';

					return {
						value,
						text: label ? `${label} (${value})` : value,
					};
				});

			const current = `${target[field.name] ?? ''}`;

			// Keep the current value even if it doesn't exist anymore.
			if (
				current &&
				![...fixed, ...derived].some(
					(option) => option.value === current
				)
			) {
				derived.push({ value: current, text: `${current} (fehlt)` });
			}

			return [...fixed, ...derived];
		};

		const select = createSelect(
			options(),
			`${target[field.name] ?? ''}`,
			wrapper,
			null,
			id,
			'<i class="bi bi-ui-radios"></i> '
		);

		if (field.help) {
			create('small', [CSS.textMuted], {}, wrapper).textContent =
				field.help;
		}

		this.listen(select, 'change', () => {
			target[field.name] = select.value;
			onChange();
			this.changed();
		});

		if (field.optionsFrom) {
			let last = JSON.stringify(options());

			this.addFormHook({
				element: wrapper,
				onFormChange: true,
				refresh: () => {
					const next = options();
					const json = JSON.stringify(next);

					if (json !== last) {
						last = json;
						select.options = next;
						select.value = `${target[field.name] ?? ''}`;
					}
				},
			});
		}
	}

	/**
	 * Render a repeatable list of items.
	 *
	 * @param field
	 * @param target
	 * @param container
	 * @param onChange
	 */
	private renderList(
		field: PartyField,
		target: KeyValueMap,
		container: HTMLElement,
		onChange: () => void
	): void {
		const subFields = field.fields || [];
		const wrapper = create(
			'div',
			[CSS.editorBlockPartyList],
			{},
			container
		);
		const header = create(
			'div',
			[CSS.editorBlockPartyListHeader],
			{},
			wrapper
		);

		create('label', [CSS.fieldLabel], {}, header).textContent =
			field.label || field.name;

		const addButton = create(
			'button',
			[CSS.button],
			{ type: 'button' },
			header,
			'<i class="bi bi-plus-lg"></i><span>Eintrag hinzufügen</span>'
		);

		const items = create(
			'div',
			[CSS.editorBlockPartyListItems],
			{},
			wrapper
		);

		if (!Array.isArray(target[field.name])) {
			target[field.name] = [];
		}

		const list: KeyValueMap[] = target[field.name];

		const itemTitle = (item: any, index: number): string => {
			const key = field.itemTitle || subFields[0]?.name;
			const title = summarize(`${(item && item[key]) ?? ''}`);

			return `${index + 1}. ${title || 'Eintrag'}`;
		};

		const renderItems = (openIndex: number = -1) => {
			items.innerHTML = '';

			list.forEach((item, index) => {
				if (typeof item !== 'object' || item === null) {
					// Convert scalar items of older data into objects.
					item = { [subFields[0]?.name ?? 'value']: item };
					list[index] = item;
				}

				const details = create(
					'details',
					[CSS.editorBlockPartyListItem],
					index === openIndex ? { open: '' } : {},
					items
				);

				const summary = create('summary', [], {}, details);
				const title = create('span', [], {}, summary);

				title.textContent = itemTitle(item, index);

				const actions = create(
					'span',
					[CSS.editorBlockPartyActions],
					{},
					summary
				);
				const button = (
					icon: string,
					text: string,
					action: () => void
				) => {
					const element = create(
						'button',
						[CSS.button, CSS.buttonIcon],
						{ type: 'button', title: text },
						actions,
						`<i class="bi bi-${icon}"></i>`
					);

					this.listen(element, 'click', (event: Event) => {
						event.preventDefault();
						event.stopPropagation();
						action();
						onChange();
						this.changed();
					});
				};

				button('arrow-up', 'Nach oben', () => {
					if (index > 0) {
						list.splice(index - 1, 0, list.splice(index, 1)[0]);
						renderItems(index - 1);
					}
				});

				button('arrow-down', 'Nach unten', () => {
					if (index < list.length - 1) {
						list.splice(index + 1, 0, list.splice(index, 1)[0]);
						renderItems(index + 1);
					}
				});

				button('copy', 'Duplizieren', () => {
					list.splice(index + 1, 0, clone(item));
					renderItems(index + 1);
				});

				button('trash3', 'Entfernen', () => {
					list.splice(index, 1);
					renderItems();
				});

				const body = create(
					'div',
					[CSS.editorBlockPartyListBody],
					{},
					details
				);

				subFields.forEach((subField) => {
					if (item[subField.name] === undefined) {
						item[subField.name] = defaultValue(subField);
					}
				});

				this.renderFields(subFields, item, body, () => {
					title.textContent = itemTitle(item, index);
					onChange();
				});
			});

			Bindings.connectElements(items);
		};

		this.listen(addButton, 'click', (event: Event) => {
			event.preventDefault();
			list.push(defaultData(subFields));
			renderItems(list.length - 1);
			onChange();
			this.changed();
		});

		renderItems();

		this.addFormHook({
			element: wrapper,
			target,
			name: field.name,
			refresh: () => renderItems(),
		});

		if (field.help) {
			create('small', [CSS.textMuted], {}, wrapper).textContent =
				field.help;
		}
	}
}
