/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

interface Step {
	total: number;
	part_text: string;
	label: string;
}

/**
 * Years as readable text (1.67 → "1 Jahr 8 Monate").
 *
 * @param years
 * @return the text
 */
const duration = (years: number, words: string[]): string => {
	const months = Math.round(years * 12);
	const y = Math.floor(months / 12);
	const m = months % 12;
	const parts: string[] = [];

	if (y > 0) {
		parts.push(`${y} ${y === 1 ? words[0] : words[1]}`);
	}

	if (m > 0) {
		parts.push(`${m} ${m === 1 ? words[2] : words[3]}`);
	}

	return parts.length ? parts.join(' ') : `0 ${words[3]}`;
};

/**
 * Animated stages: the bars grow when the chart scrolls into view, the slider highlights the
 * stage for the chosen time.
 *
 * @param element
 */
export const initStepBars = (element: HTMLElement): void => {
	let steps: Step[] = [];
	let words = ['Jahr', 'Jahre', 'Monat', 'Monate'];

	try {
		words = JSON.parse(element.dataset.words || '[]');
	} catch {
		// Keep the defaults.
	}

	if (words.length < 4) {
		words = ['Jahr', 'Jahre', 'Monat', 'Monate'];
	}

	try {
		steps = JSON.parse(element.dataset.partyStepBars || '[]');
	} catch {
		steps = [];
	}

	const show = (): void => element.classList.add('step-bars--visible');

	if ('IntersectionObserver' in window) {
		const observer = new IntersectionObserver(
			(entries) => {
				if (entries.some((entry) => entry.isIntersecting)) {
					show();
					observer.disconnect();
				}
			},
			{ threshold: 0.2 }
		);

		observer.observe(element);
	} else {
		show();
	}

	const input = element.querySelector<HTMLInputElement>(
		'.step-bars__slider input'
	);
	const output = element.querySelector<HTMLOutputElement>(
		'.step-bars__slider-value'
	);
	const result = element.querySelector<HTMLElement>('.step-bars__result');
	const rows = Array.from(
		element.querySelectorAll<HTMLElement>('.step-bars__row')
	);

	if (!input) {
		return;
	}

	const update = (): void => {
		const value = parseFloat(input.value);
		let index = -1;

		steps.forEach((step, i) => {
			if (value + 0.05 >= step.total) {
				index = i;
			}
		});

		if (output) {
			output.textContent = duration(value, words);
		}

		rows.forEach((row, i) => {
			row.classList.toggle('step-bars__row--active', i === index);
		});

		element.style.setProperty(
			'--sb-marker',
			`${(value / parseFloat(input.max)) * 100}%`
		);

		if (result) {
			result.textContent =
				index < 0
					? element.dataset.none || ''
					: (element.dataset.result || '{part}')
							.replace('{part}', steps[index].part_text)
							.replace('{label}', steps[index].label);
		}
	};

	input.addEventListener('input', update);
	update();
};
