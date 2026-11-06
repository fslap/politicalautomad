/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

/**
 * Markdown viewer: switch between the full text, the summary and the short version and
 * page through the chapters.
 *
 * @param viewer
 */
export const initMarkdownViewer = (viewer: HTMLElement): void => {
	const chapters = Array.from(
		viewer.querySelectorAll<HTMLElement>('[data-md-chapter]')
	);
	const position = viewer.querySelector<HTMLElement>('[data-md-position]');
	let current = 0;

	const setMode = (mode: string): void => {
		viewer.dataset.mode = mode;
		viewer
			.querySelectorAll<HTMLElement>('[data-md-mode]')
			.forEach((button) => {
				button.setAttribute(
					'aria-pressed',
					button.dataset.mdMode === mode ? 'true' : 'false'
				);
			});
	};

	const show = (index: number): void => {
		current = (index + chapters.length) % chapters.length;
		chapters.forEach((chapter, i) => {
			chapter.hidden = i !== current;
		});

		if (position) {
			position.textContent = `${current + 1} / ${chapters.length}`;
		}
	};

	viewer.querySelectorAll<HTMLElement>('[data-md-mode]').forEach((button) => {
		button.addEventListener('click', () => setMode(button.dataset.mdMode));
	});

	viewer
		.querySelector('[data-md-prev]')
		?.addEventListener('click', () => show(current - 1));
	viewer
		.querySelector('[data-md-next]')
		?.addEventListener('click', () => show(current + 1));

	setMode(viewer.dataset.mode || 'summary');

	if (chapters.length) {
		show(0);
	}
};
