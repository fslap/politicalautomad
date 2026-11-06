/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

/**
 * Side menu with text: a click on a heading shows its text.
 *
 * @param element
 */
export const initSideMenu = (element: HTMLElement): void => {
	const buttons = Array.from(
		element.querySelectorAll<HTMLButtonElement>('[data-smt-index]')
	);
	const entries = Array.from(
		element.querySelectorAll<HTMLElement>('[data-smt-entry]')
	);
	const text = element.querySelector<HTMLElement>('.side-menu-text__text');

	buttons.forEach((button) => {
		button.addEventListener('click', () => {
			const index = button.dataset.smtIndex;

			buttons.forEach((other) => {
				if (other === button) {
					other.setAttribute('aria-current', 'true');
				} else {
					other.removeAttribute('aria-current');
				}
			});

			entries.forEach((entry) => {
				entry.hidden = entry.dataset.smtEntry !== index;
			});

			if (text) {
				text.scrollTop = 0;
			}
		});
	});
};
