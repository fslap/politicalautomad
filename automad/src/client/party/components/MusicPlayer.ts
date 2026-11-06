/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

import { loadWithConsent } from './Consent';

/**
 * Show the track list of the selected platform of a music player. Tracks are loaded
 * through the GDPR lander (nothing is requested from a provider before the consent).
 *
 * @param element
 */
export const initMusicPlayer = (element: HTMLElement): void => {
	const iframe = element.querySelector<HTMLIFrameElement>('.music-iframe');
	const buttons = Array.from(
		element.querySelectorAll<HTMLAnchorElement>('[data-music-platform]')
	);
	const lists = Array.from(
		element.querySelectorAll<HTMLElement>('[data-music-tracks]')
	);

	const load = (link: HTMLAnchorElement): void => {
		if (iframe && link.getAttribute('href')) {
			loadWithConsent(iframe, link.href, link.textContent.trim());
		}
	};

	buttons.forEach((button) => {
		button.addEventListener('click', (event) => {
			event.preventDefault();

			const index = button.dataset.musicPlatform;

			buttons.forEach((other) => {
				other.classList.toggle('is-active', other === button);
			});

			lists.forEach((list) => {
				list.hidden = list.dataset.musicTracks !== index;
			});

			load(button);
		});
	});

	element
		.querySelectorAll<HTMLAnchorElement>('[data-music-tracks] a')
		.forEach((link) => {
			link.addEventListener('click', (event) => {
				event.preventDefault();

				link.closest('ol')
					?.querySelectorAll('a')
					.forEach((other) => {
						other.classList.toggle('is-active', other === link);
					});

				load(link);
			});
		});
};
