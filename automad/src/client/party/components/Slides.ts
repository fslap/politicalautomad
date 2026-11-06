/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

/**
 * Slides (sketch "First Page" … "Last Page"): arrows to the previous and next slide, an
 * overview menu of all slides and the "Mehr" arrow of the title.
 */

const slides = (): HTMLElement[] =>
	Array.from(document.querySelectorAll<HTMLElement>('[data-party-slide]'));

/**
 * The block (wrapper) of a slide or of another party element.
 *
 * @param element
 * @return the block
 */
const blockOf = (element: Element): HTMLElement =>
	(element.closest('.am-block, am-stretched') as HTMLElement) ??
	(element as HTMLElement);

/**
 * The next or previous block of the page after an element.
 *
 * @param element
 * @param direction
 * @return the target or null
 */
const sibling = (element: HTMLElement, direction: 1 | -1): HTMLElement => {
	const own =
		element.closest<HTMLElement>('.am-party, am-stretched, .am-block') ??
		element;
	const container =
		own.closest<HTMLElement>('main, article, .std-content') ??
		document.body;
	const ownRect = own.getBoundingClientRect();

	// All blocks of the page (party blocks and other Automad blocks).
	const candidates = Array.from(
		container.querySelectorAll<HTMLElement>(
			'.am-party, .am-block, am-stretched > *, :scope > *'
		)
	).filter((block) => {
		const rect = block.getBoundingClientRect();

		return (
			rect.height > 0 &&
			!block.contains(own) &&
			!own.contains(block) &&
			!block.closest('.party-slide__overview')
		);
	});

	let target: HTMLElement = null;
	let best = direction === 1 ? Infinity : -Infinity;

	candidates.forEach((block) => {
		const top = block.getBoundingClientRect().top;

		if (direction === 1 && top > ownRect.bottom - 8 && top < best) {
			best = top;
			target = block;
		}

		if (direction === -1 && top < ownRect.top - 8 && top > best) {
			best = top;
			target = block;
		}
	});

	return target;
};

const smooth = (): ScrollBehavior =>
	window.matchMedia('(prefers-reduced-motion: reduce)').matches
		? 'auto'
		: 'smooth';

const scrollTo = (target: HTMLElement): void => {
	if (!target) {
		return;
	}

	target.scrollIntoView({ behavior: smooth(), block: 'start' });
};

/**
 * Scroll to the next or previous block. Without a block (e.g. at the end of the
 * page) the page scrolls by one screen, so the arrows always scroll.
 *
 * @param element
 * @param direction
 */
const go = (element: HTMLElement, direction: 1 | -1): void => {
	const target = sibling(element, direction);

	if (target) {
		scrollTo(target);

		return;
	}

	(document.scrollingElement ?? document.documentElement).scrollBy({
		top: direction * window.innerHeight * 0.9,
		behavior: smooth(),
	});
};

const closeMenus = (): void => {
	document.querySelectorAll('.party-slide__overview').forEach((menu) => {
		menu.remove();
	});

	document
		.querySelectorAll('[data-slide-menu][aria-expanded="true"]')
		.forEach((button) => button.setAttribute('aria-expanded', 'false'));
};

/**
 * Open the overview of all slides.
 *
 * @param slide
 * @param button
 */
const openMenu = (slide: HTMLElement, button: HTMLElement): void => {
	const open = button.getAttribute('aria-expanded') === 'true';

	closeMenus();

	if (open) {
		return;
	}

	const menu = document.createElement('nav');

	menu.className = 'party-slide__overview';
	menu.setAttribute('aria-label', 'Folien');

	slides().forEach((other, index) => {
		const link = document.createElement('button');

		link.type = 'button';
		link.textContent = `${index + 1}. ${other.dataset.slideTitle || 'Folie'}`;
		link.classList.toggle('is-current', other === slide);
		link.addEventListener('click', () => {
			closeMenus();
			scrollTo(blockOf(other));
		});

		menu.appendChild(link);
	});

	button.setAttribute('aria-expanded', 'true');
	button.parentElement.appendChild(menu);
};

/**
 * Initialize a slide.
 *
 * @param slide
 */
export const initSlide = (slide: HTMLElement): void => {
	slide.querySelector('[data-slide-prev]')?.addEventListener('click', () => {
		go(slide, -1);
	});

	slide.querySelector('[data-slide-next]')?.addEventListener('click', () => {
		go(slide, 1);
	});

	const menu = slide.querySelector<HTMLElement>('[data-slide-menu]');

	menu?.addEventListener('click', (event) => {
		event.stopPropagation();
		openMenu(slide, menu);
	});
};

/**
 * The "Mehr" arrow of a title: without target it scrolls to the next block.
 *
 * @param link
 */
export const initMore = (link: HTMLElement): void => {
	link.addEventListener('click', (event) => {
		const href = link.getAttribute('href') || '#';

		if (href !== '#') {
			return;
		}

		event.preventDefault();
		go(link, link.dataset.direction === 'up' ? -1 : 1);
	});
};

/**
 * Lists of maps in slides get a button that shows them in full width when they are long
 * ("Full wenn Liste lang").
 *
 * @param container
 */
export const initListExpand = (container: HTMLElement): void => {
	const limit = Number(container.dataset.listExpand || 0);

	if (!limit) {
		return;
	}

	const check = (): void => {
		const list = container.querySelector('.case-leaflet-region__list');

		if (!list || container.querySelector('.party-slide__expand')) {
			return;
		}

		if (list.children.length < limit) {
			return;
		}

		const button = document.createElement('button');

		button.type = 'button';
		button.className = 'party-slide__expand';
		button.textContent = 'Liste groß anzeigen';
		button.addEventListener('click', () => {
			const expanded = container.classList.toggle('is-list-expanded');

			// Leaflet needs to know the size of the map when it is visible again.
			if (!expanded) {
				window.dispatchEvent(new Event('resize'));
			}

			button.textContent = expanded
				? 'Karte anzeigen'
				: 'Liste groß anzeigen';
		});

		list.parentElement.insertBefore(button, list);
	};

	// The list is filled by the case map script.
	new MutationObserver(check).observe(container, {
		childList: true,
		subtree: true,
	});
	check();
};

document.addEventListener('click', (event) => {
	if (!(event.target as Element).closest?.('.party-slide__overview')) {
		closeMenus();
	}
});

document.addEventListener('keydown', (event) => {
	if (event.key === 'Escape') {
		closeMenus();
	}
});

/**
 * Houses that roll in when they are scrolled into view.
 *
 * @param element
 */
export const initRollView = (element: HTMLElement): void => {
	if (!('IntersectionObserver' in window)) {
		element.classList.add('is-in');

		return;
	}

	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					element.classList.add('is-in');
					observer.disconnect();
				}
			});
		},
		{ threshold: 0.3 }
	);

	observer.observe(element);
};
