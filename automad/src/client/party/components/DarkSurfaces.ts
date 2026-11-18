/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

const ATTRIBUTE = 'data-party-surface';

/**
 * The relative luminance of a computed CSS color, null for transparent colors.
 *
 * @param color
 * @return the luminance (0–1)
 */
const luminance = (color: string): number | null => {
	const values = (color.match(/[\d.]+/g) || []).map(Number);

	if (values.length < 3) {
		return null;
	}

	const srgb = color.startsWith('color(');
	const alpha = values.length > 3 ? values[3] : 1;

	if (alpha < 0.5) {
		return null;
	}

	const channel = (value: number): number => {
		const v = srgb ? value : value / 255;

		return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
	};

	return (
		0.2126 * channel(values[0]) +
		0.7152 * channel(values[1]) +
		0.0722 * channel(values[2])
	);
};

/**
 * In the dark mode of the theme the text of party blocks is light. Elements with an own
 * light background (cards, panels, tables, forms) are marked and keep dark text.
 */
const mark = (): void => {
	const dark = document.documentElement.classList.contains('dark');

	document
		.querySelectorAll<HTMLElement>(`[${ATTRIBUTE}]`)
		.forEach((element) => element.removeAttribute(ATTRIBUTE));

	if (!dark) {
		return;
	}

	document
		.querySelectorAll<HTMLElement>('.am-party, .am-party *')
		.forEach((element) => {
			const style = getComputedStyle(element);
			const value = luminance(style.backgroundColor);

			if (value !== null) {
				element.setAttribute(
					ATTRIBUTE,
					value > 0.45 ? 'light' : 'dark'
				);
			}
		});
};

/**
 * Watch the color scheme of the theme (class "dark" on the html element).
 */
export const initDarkSurfaces = (): void => {
	let scheduled = false;
	const schedule = (): void => {
		if (scheduled) {
			return;
		}

		scheduled = true;
		window.requestAnimationFrame(() => {
			scheduled = false;
			mark();
		});
	};

	new MutationObserver(schedule).observe(document.documentElement, {
		attributes: true,
		attributeFilter: ['class'],
	});

	schedule();
};
