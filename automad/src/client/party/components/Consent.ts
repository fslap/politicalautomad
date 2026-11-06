/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

/**
 * GDPR lander for external embeds (like the cascade pages of the original site).
 *
 * Iframes of party blocks are rendered without src, the URL is stored in data-consent-src.
 * Nothing is requested from the provider before a visitor has confirmed the transfer of
 * personal data to that provider. The consent is remembered for the browser session.
 */

interface Provider {
	key: string;
	domain: string;
	company: string;
	date: string;
	hosts: string[];
}

const providers: Provider[] = [
	{
		key: 'youtube',
		domain: 'youtube.com',
		company: 'Google LLC',
		date: '29.08.2025',
		hosts: ['youtube.com', 'youtube-nocookie.com', 'youtu.be'],
	},
	{
		key: 'spotify',
		domain: 'open.spotify.com',
		company: 'Spotify AB',
		date: '13.08.2025',
		hosts: ['spotify.com'],
	},
	{
		key: 'tidal',
		domain: 'tidal.com',
		company: 'TIDAL Music AS',
		date: '29.08.2025',
		hosts: ['tidal.com'],
	},
	{
		key: 'apple',
		domain: 'music.apple.com',
		company: 'Apple Inc.',
		date: '',
		hosts: ['apple.com'],
	},
	{
		key: 'deezer',
		domain: 'deezer.com',
		company: 'Deezer S.A.',
		date: '',
		hosts: ['deezer.com'],
	},
	{
		key: 'soundcloud',
		domain: 'soundcloud.com',
		company: 'SoundCloud Global Limited & Co. KG',
		date: '',
		hosts: ['soundcloud.com'],
	},
	{
		key: 'vimeo',
		domain: 'vimeo.com',
		company: 'Vimeo.com, Inc.',
		date: '',
		hosts: ['vimeo.com'],
	},
];

const STORAGE_PREFIX = 'party-consent-';
const CONSENT_EVENT = 'party-consent';

/**
 * Find the provider of a URL. Unknown hosts get a generic provider.
 *
 * @param url
 * @return the provider
 */
export const providerOf = (url: string): Provider => {
	let host = '';

	try {
		host = new URL(url, window.location.href).hostname.replace(
			/^www\./,
			''
		);
	} catch {
		host = '';
	}

	const known = providers.find((provider) =>
		provider.hosts.some((h) => host === h || host.endsWith('.' + h))
	);

	return (
		known ?? {
			key: host || 'extern',
			domain: host || 'extern',
			company: 'den Betreiber dieser Domain',
			date: '',
			hosts: [host],
		}
	);
};

/**
 * Whether the visitor has already allowed a provider in this session.
 *
 * @param provider
 * @return true if allowed
 */
export const hasConsent = (provider: Provider): boolean => {
	try {
		return sessionStorage.getItem(STORAGE_PREFIX + provider.key) === '1';
	} catch {
		return false;
	}
};

const storeConsent = (provider: Provider, value: boolean): void => {
	try {
		if (value) {
			sessionStorage.setItem(STORAGE_PREFIX + provider.key, '1');
		} else {
			sessionStorage.removeItem(STORAGE_PREFIX + provider.key);
		}
	} catch {
		// Storage is not available (private mode), the consent is only valid for this view.
	}
};

const escape = (text: string): string =>
	text.replace(
		/[&<>"']/g,
		(char) =>
			({
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#39;',
			})[char]
	);

/**
 * Load the content of an iframe after the consent was given.
 *
 * @param iframe
 */
const load = (iframe: HTMLIFrameElement): void => {
	const src = iframe.dataset.consentSrc;

	if (src && iframe.getAttribute('src') !== src) {
		iframe.setAttribute('src', src);
	}

	iframe.closest('.party-consent')?.classList.add('party-consent--loaded');
};

/**
 * Wrap an iframe in order to place the lander on top of it.
 *
 * @param iframe
 * @return the wrapper
 */
const wrap = (iframe: HTMLIFrameElement): HTMLElement => {
	const existing = iframe.closest<HTMLElement>('.party-consent');

	if (existing && existing.contains(iframe)) {
		return existing;
	}

	const wrapper = document.createElement('div');

	wrapper.className = 'party-consent';
	iframe.parentNode.insertBefore(wrapper, iframe);
	wrapper.appendChild(iframe);

	return wrapper;
};

/**
 * Show the lander (consent form) on top of an iframe.
 *
 * @param iframe
 * @param onAccept
 */
const showLander = (iframe: HTMLIFrameElement, onAccept: () => void): void => {
	const wrapper = wrap(iframe);
	const provider = providerOf(iframe.dataset.consentSrc || '');

	wrapper.querySelector('.party-consent__lander')?.remove();
	wrapper.classList.remove('party-consent--loaded');

	const lander = document.createElement('form');
	const id = `party-consent-${Math.random().toString(36).slice(2, 9)}`;
	const date = provider.date ? ' (Stand ' + escape(provider.date) + ')' : '';
	const selection = iframe.dataset.consentTitle
		? `<p class="party-consent__selection">${'Ausgewählt: '}<strong>${escape(iframe.dataset.consentTitle)}</strong></p>`
		: '';

	lander.className = 'party-consent__lander';
	lander.innerHTML = `
		<p class="party-consent__text">Erlaube die Weitergabe von persönlichen Daten nach EU Recht über die Domain <a href="https://${escape(provider.domain)}" target="_blank" rel="noopener">${escape(provider.domain)}</a>${' an ' + escape(provider.company) + date + ' und die Anzeige der Inhalte dieser Domain.'}</p>
		${selection}
		<label class="party-consent__check" for="${id}">
			<input type="checkbox" id="${id}" required>
			<span>Ich stimme zu</span>
		</label>
		<button type="submit" class="party-consent__load">Inhalt laden</button>
		<p class="party-consent__hint" hidden>Bitte zuerst die Zustimmung bestätigen.</p>
	`;

	lander.addEventListener('submit', (event) => {
		event.preventDefault();

		const checkbox = lander.querySelector<HTMLInputElement>('input');

		if (!checkbox.checked) {
			lander.querySelector<HTMLElement>('.party-consent__hint').hidden =
				false;

			return;
		}

		storeConsent(provider, true);
		accept();
		// Other embeds of the same provider on this page are loaded as well.
		window.dispatchEvent(
			new CustomEvent(CONSENT_EVENT, { detail: provider.key })
		);
	});

	const accept = (): void => {
		window.removeEventListener(CONSENT_EVENT, onConsent);

		if (!lander.isConnected) {
			return;
		}

		lander.remove();
		load(iframe);
		addBackLink(iframe);
		onAccept();
	};

	const onConsent = (event: Event): void => {
		if ((event as CustomEvent).detail === provider.key) {
			accept();
		}
	};

	window.addEventListener(CONSENT_EVENT, onConsent);
	wrapper.appendChild(lander);
};

/**
 * The "Zurück" link of the original lander: unloads the content and revokes the consent.
 *
 * @param iframe
 */
const addBackLink = (iframe: HTMLIFrameElement): void => {
	const wrapper = wrap(iframe);

	if (wrapper.querySelector('.party-consent__back')) {
		return;
	}

	const back = document.createElement('button');

	back.type = 'button';
	back.className = 'party-consent__back';
	back.textContent = 'Zurück (Zustimmung widerrufen)';
	back.addEventListener('click', () => {
		const provider = providerOf(iframe.dataset.consentSrc || '');

		storeConsent(provider, false);
		iframe.removeAttribute('src');
		back.remove();
		showLander(iframe, () => {});
	});

	wrapper.appendChild(back);
};

const pending = new WeakMap<HTMLIFrameElement, Promise<void>>();

/**
 * Resolve as soon as the content of an iframe may be loaded. The iframe is loaded then.
 *
 * @param iframe
 * @return a promise that resolves after the consent
 */
export const requestConsent = (iframe: HTMLIFrameElement): Promise<void> => {
	if (pending.has(iframe)) {
		return pending.get(iframe);
	}

	const promise = new Promise<void>((resolve) => {
		const src = iframe.dataset.consentSrc;

		if (!src) {
			resolve();

			return;
		}

		if (hasConsent(providerOf(src))) {
			wrap(iframe);
			load(iframe);
			addBackLink(iframe);
			resolve();

			return;
		}

		showLander(iframe, resolve);
	});

	pending.set(iframe, promise);

	return promise;
};

/**
 * Load another URL in an iframe (e.g. a track of the music player). Without consent
 * the lander is shown for the selected content.
 *
 * @param iframe
 * @param url
 * @param title
 */
export const loadWithConsent = (
	iframe: HTMLIFrameElement,
	url: string,
	title: string = ''
): void => {
	iframe.dataset.consentSrc = url;
	iframe.dataset.consentTitle = title;

	if (hasConsent(providerOf(url))) {
		wrap(iframe);
		load(iframe);
		addBackLink(iframe);

		return;
	}

	iframe.removeAttribute('src');
	showLander(iframe, () => {});
};

/**
 * Behaviour for all iframes with data-consent-src.
 *
 * @param iframe
 */
export const initConsentFrame = (iframe: HTMLElement): void => {
	requestConsent(iframe as HTMLIFrameElement);
};
