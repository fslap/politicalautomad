/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

/**
 * Let images look at the pointer: sets --look-x and --look-y (-1 … 1) relative to the
 * center of the element while the pointer moves over the page.
 *
 * @param element
 */
export const initLook = (element: HTMLElement): void => {
	if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	let frame = 0;
	let x = 0;
	let y = 0;

	const update = (): void => {
		frame = 0;

		const rect = element.getBoundingClientRect();

		if (rect.bottom < 0 || rect.top > window.innerHeight) {
			return;
		}

		const cx = rect.left + rect.width / 2;
		const cy = rect.top + rect.height / 2;
		const clamp = (value: number): number =>
			Math.max(-1, Math.min(1, value));

		element.style.setProperty(
			'--look-x',
			clamp((x - cx) / (window.innerWidth / 2)).toFixed(3)
		);
		element.style.setProperty(
			'--look-y',
			clamp((y - cy) / (window.innerHeight / 2)).toFixed(3)
		);
	};

	window.addEventListener(
		'pointermove',
		(event) => {
			x = event.clientX;
			y = event.clientY;

			if (!frame) {
				frame = window.requestAnimationFrame(update);
			}
		},
		{ passive: true }
	);
};
