/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

import { parseConfig } from './Map';
import { connectPlayer, observeVisibility, type YouTubePlayer } from './YouTube';

interface StainedGlassConfig {
	autoplay: boolean;
	restart: boolean;
	volume: number;
}

/**
 * Play the song while the section is visible.
 *
 * Browsers only allow autoplay with sound after the visitor interacted with the page
 * (a click, a tap or a key press). Otherwise the song starts muted and a "sound on"
 * button is shown.
 *
 * @param root
 */
export const initStainedGlass = async (root: HTMLElement): Promise<void> => {
	const config = parseConfig<StainedGlassConfig>(root, 'data-party-stained-glass');
	const iframe = root.querySelector<HTMLIFrameElement>('.stained-glass__frame iframe');
	const soundButton = root.querySelector<HTMLButtonElement>('.stained-glass__sound');

	if (!iframe || !config.autoplay) {
		return;
	}

	let player: YouTubePlayer = null;
	let visible = false;

	const hasUserActivation = (): boolean => {
		const activation = (navigator as any).userActivation;

		return activation ? !!activation.hasBeenActive : false;
	};

	const play = (): void => {
		if (!player) {
			return;
		}

		if (config.restart) {
			player.seekTo(0, true);
		}

		if (hasUserActivation()) {
			player.unMute();
			soundButton?.setAttribute('hidden', '');
		} else {
			player.mute();
			soundButton?.removeAttribute('hidden');
		}

		player.playVideo();
	};

	soundButton?.addEventListener('click', () => {
		player?.unMute();
		player?.playVideo();
		soundButton.setAttribute('hidden', '');
	});

	try {
		player = await connectPlayer(iframe, {
			onReady: (event: any) => {
				event.target.setVolume?.(config.volume ?? 60);
			},
		});
	} catch (error) {
		console.warn('YouTube API not available', error);

		return;
	}

	observeVisibility(root, (isVisible) => {
		if (isVisible === visible) {
			return;
		}

		visible = isVisible;
		visible ? play() : player.pauseVideo();
	});
};
