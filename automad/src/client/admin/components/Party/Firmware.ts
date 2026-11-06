/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

import {
	create,
	CSS,
	FirmwareController,
	html,
	notifyError,
	notifySuccess,
	requestApi,
} from '@/admin/core';
import { BaseComponent } from '../Base';

interface FirmwarePatch {
	file: string;
	size: number;
	modified: number;
	version: string;
	created: string;
	error: string;
}

interface FirmwareData {
	version: string;
	directory: string;
	patches: FirmwarePatch[];
	backups: string[];
}

/**
 * The firmware patch settings. Patches (tar.gz files created with "php bin/dist.php --patch")
 * are copied to the firmware directory of the installation and applied here.
 * Content and settings are never touched by a patch.
 *
 * @extends BaseComponent
 */
export class FirmwareComponent extends BaseComponent {
	static TAG_NAME = 'am-party-firmware';

	/**
	 * The callback function used when an element is created in the DOM.
	 */
	connectedCallback(): void {
		this.classList.add(CSS.flex, CSS.flexColumn, CSS.flexGap);
		this.load();
	}

	/**
	 * Load and render the firmware state.
	 */
	private async load(): Promise<void> {
		this.innerHTML = '<am-spinner></am-spinner>';

		const { data, error } = await requestApi(FirmwareController.list);

		if (error || !data) {
			this.innerHTML = '';
			create('p', [CSS.textMuted], {}, this).textContent = error || 'Fehler beim Laden.';

			return;
		}

		this.render(data as FirmwareData);
	}

	/**
	 * Render the firmware state.
	 *
	 * @param data
	 */
	private render(data: FirmwareData): void {
		this.innerHTML = html`
			<div class="${CSS.card}">
				<div class="${CSS.cardBody}">
					<p>Installierte Version: <strong>$${data.version || 'unbekannt'}</strong></p>
					<p class="${CSS.textMuted}">
						Firmware-Patches ersetzen nur den Programmcode (automad, lib, vendor, packages).
						Inhalte, Benutzer und Einstellungen (config, pages, shared) bleiben unverändert.
						Vor jedem Patch wird automatisch ein Backup angelegt.
					</p>
					<p class="${CSS.textMuted}">
						Patch erstellen: <code>php bin/dist.php --patch</code><br />
						Die <code>.tar.gz</code>-Datei per FTP oder Shell in diesen Ordner kopieren:<br />
						<code>$${data.directory}</code><br />
						Per Shell geht auch: <code>php automad/console firmware:apply</code>
					</p>
				</div>
			</div>
		`;

		const patches = create('div', [CSS.flex, CSS.flexColumn, CSS.flexGap], {}, this);

		if (!data.patches.length) {
			create('p', [CSS.textMuted], {}, patches).textContent =
				'Keine Patches im Firmware-Ordner gefunden.';
		}

		data.patches.forEach((patch) => {
			const row = create(
				'div',
				[CSS.card],
				{},
				patches,
				html`
					<div class="${CSS.cardBody} ${CSS.flex} ${CSS.flexBetween} ${CSS.flexGap}">
						<div>
							<strong>$${patch.version || patch.file}</strong><br />
							<small class="${CSS.textMuted}">
								$${patch.file} · $${(patch.size / 1048576).toFixed(1)} MB
								${patch.created ? `· ${new Date(patch.created).toLocaleString()}` : ''}
							</small>
							${patch.error ? html`<br /><small>$${patch.error}</small>` : ''}
						</div>
						<button class="${CSS.button} ${CSS.buttonPrimary}">Einspielen</button>
					</div>
				`
			);

			this.listen(row.querySelector('button'), 'click', () => this.apply(patch));
		});

		if (data.backups.length) {
			const backups = create('div', [CSS.card], {}, this);
			const body = create('div', [CSS.cardBody, CSS.flex, CSS.flexBetween, CSS.flexGap], {}, backups);

			create('span', [], {}, body).textContent = `Letztes Backup: ${data.backups[0]}`;

			const button = create('button', [CSS.button], {}, body, 'Zurücksetzen');

			this.listen(button, 'click', () => this.rollback(data.backups[0]));
		}
	}

	/**
	 * Apply a patch.
	 *
	 * @param patch
	 */
	private async apply(patch: FirmwarePatch): Promise<void> {
		if (!window.confirm(`Firmware ${patch.version || patch.file} jetzt einspielen?`)) {
			return;
		}

		this.innerHTML = '<am-spinner></am-spinner>';

		const { success, error } = await requestApi(FirmwareController.apply, { file: patch.file });

		if (error) {
			notifyError(error);
			this.load();

			return;
		}

		notifySuccess(success);

		// The dashboard code has been replaced, reload in order to use the new version.
		setTimeout(() => window.location.reload(), 1500);
	}

	/**
	 * Restore a backup.
	 *
	 * @param backup
	 */
	private async rollback(backup: string): Promise<void> {
		if (!window.confirm(`Backup ${backup} wiederherstellen?`)) {
			return;
		}

		const { success, error } = await requestApi(FirmwareController.rollback, { backup });

		if (error) {
			notifyError(error);
			this.load();

			return;
		}

		notifySuccess(success);
		setTimeout(() => window.location.reload(), 1500);
	}
}

customElements.define(FirmwareComponent.TAG_NAME, FirmwareComponent);
