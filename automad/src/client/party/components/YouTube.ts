/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

/**
 * A minimal subset of the YouTube IFrame player API.
 */
export interface YouTubePlayer {
	playVideo(): void;
	pauseVideo(): void;
	mute(): void;
	unMute(): void;
	isMuted(): boolean;
	seekTo(seconds: number, allowSeekAhead: boolean): void;
	getCurrentTime(): number;
	getPlayerState(): number;
}

let apiPromise: Promise<any> = null;

/**
 * Load the YouTube IFrame API once.
 *
 * @return the YT namespace
 */
export const loadYouTubeApi = (): Promise<any> => {
	if (apiPromise) {
		return apiPromise;
	}

	apiPromise = new Promise((resolve) => {
		const w = window as any;

		if (w.YT && w.YT.Player) {
			resolve(w.YT);

			return;
		}

		const previous = w.onYouTubeIframeAPIReady;

		w.onYouTubeIframeAPIReady = () => {
			if (typeof previous === 'function') {
				previous();
			}

			resolve(w.YT);
		};

		const script = document.createElement('script');

		script.src = 'https://www.youtube.com/iframe_api';
		script.async = true;
		document.head.appendChild(script);
	});

	return apiPromise;
};

/**
 * Connect a player to an existing iframe (that has enablejsapi=1 in its URL).
 *
 * @param iframe
 * @param events
 * @return the player
 */
export const connectPlayer = async (
	iframe: HTMLIFrameElement,
	events: Record<string, (event: any) => void> = {}
): Promise<YouTubePlayer> => {
	const YT = await loadYouTubeApi();

	return new Promise((resolve) => {
		const player = new YT.Player(iframe, {
			events: {
				...events,
				onReady: (event: any) => {
					events.onReady?.(event);
					resolve(player);
				},
			},
		});
	});
};

/**
 * Call a callback when an element enters or leaves the viewport.
 *
 * @param element
 * @param callback
 * @param threshold
 */
export const observeVisibility = (
	element: HTMLElement,
	callback: (visible: boolean) => void,
	threshold: number = 0.35
): void => {
	if (!('IntersectionObserver' in window)) {
		return;
	}

	new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => callback(entry.isIntersecting));
		},
		{ threshold }
	).observe(element);
};
