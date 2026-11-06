/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

import { requestConsent } from './Consent';
import { parseConfig } from './Map';
import {
	connectPlayer,
	observeVisibility,
	type YouTubePlayer,
} from './YouTube';

interface VideoCommentaryConfig {
	videoId: string;
	start: number;
	follow: boolean;
	pauseOnLeave: boolean;
}

/**
 * Connect the timecoded comments with the YouTube player.
 *
 * @param root
 */
export const initVideoCommentary = async (root: HTMLElement): Promise<void> => {
	const config = parseConfig<VideoCommentaryConfig>(
		root,
		'data-party-video-commentary'
	);
	const iframe = root.querySelector<HTMLIFrameElement>(
		'.video-commentary__frame iframe'
	);
	const list = root.querySelector<HTMLElement>('.video-commentary__comments');
	const comments = Array.from(
		root.querySelectorAll<HTMLElement>('.video-commentary__comment')
	);

	if (!iframe || !config.videoId) {
		return;
	}

	let player: YouTubePlayer = null;
	let active: HTMLElement = null;
	let timer: number = null;

	const setActive = (seconds: number): void => {
		let current: HTMLElement = null;

		comments.forEach((comment) => {
			if (Number(comment.dataset.time) <= seconds + 0.25) {
				current = comment;
			}
		});

		if (current === active) {
			return;
		}

		active?.classList.remove('is-active');
		current?.classList.add('is-active');
		active = current;

		if (current && list && config.follow) {
			// Scroll inside of the list only, the page itself is not moved.
			const top =
				current.offsetTop - list.offsetTop - list.clientHeight / 3;

			list.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
		}
	};

	const startTimer = (): void => {
		if (timer === null) {
			timer = window.setInterval(() => {
				if (player) {
					setActive(player.getCurrentTime());
				}
			}, 400);
		}
	};

	const stopTimer = (): void => {
		if (timer !== null) {
			window.clearInterval(timer);
			timer = null;
		}
	};

	root.addEventListener('click', (event) => {
		const button = (event.target as HTMLElement).closest<HTMLElement>(
			'[data-seek]'
		);

		if (!button) {
			return;
		}

		const seconds = Number(button.dataset.seek);

		setActive(seconds);

		if (player) {
			player.seekTo(seconds, true);
			player.playVideo();
		} else if (iframe.getAttribute('src')) {
			// Fallback without the API: reload the embed at the position.
			const url = new URL(iframe.src);

			url.searchParams.set('start', `${Math.floor(seconds)}`);
			url.searchParams.set('autoplay', '1');
			iframe.src = url.toString();
		}
	});

	// Nothing is loaded from YouTube before the visitor has agreed (GDPR lander).
	await requestConsent(iframe);

	try {
		player = await connectPlayer(iframe, {
			onStateChange: (event: any) => {
				// 1 = playing
				event.data === 1 ? startTimer() : stopTimer();
				setActive(player?.getCurrentTime() ?? 0);
			},
		});
	} catch (error) {
		console.warn('YouTube API not available', error);
	}

	if (config.pauseOnLeave) {
		observeVisibility(
			root,
			(visible) => {
				if (!visible && player && player.getPlayerState() === 1) {
					player.pauseVideo();
				}
			},
			0.1
		);
	}
};
