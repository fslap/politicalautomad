/*
 * International Political Party Zone for all Partys also Locally
 *
 *                      #;:#;:#;:
 *                       #;:#;:            #;:#;:#;
 *            %&                         #;: #;:;:#;:#;:#;:#
 *    %$%    %$%           #;:    #;:     #;: #;:;:#;:#;:
 *    ____________                ;:      #;:  #;: #;:#;:#;:#;
 *   /§§§§§§§§§§§|                                    ;:#;:#;:
 *   |###########/  / &/      #;:#;:#;:#;:#;:        #;:#;: #;: #;
 *   /%%%%%%%%%%/   |$$$/        #;:#;:#;:#;:#;#;:#;:;:#;:#;:
 *  /######### /   |$$$/          #;:#;:#;:#;:#;:#;:#;:#;:#;:#;:
 * /%%%%%%%%%%/   \CCC/           #;:#;:#;:#;:#;:#;:#;:#;:
 * \#########|   \\|      _\    :#;:  :#;:#;:  #;:#;:#;:#;:
 *  \________/   /[[]]\  \_/   :#\:#\:#\:#\:#;:
 *   \====__/    \xxxx/              #\:#\:#;:#;:
 *   |""""""\    |yyy/                #;: #;:#;:#;: |#;:\
 *   |~~~~~~/ #   |c|                 ;:#;:\;:#;:   |#;:;:
 *   +~~~~~/  |   \t/                  #\:#;\       |##;/
 *    +~~~/   |#
 *     +~/\       \*+ ~
 *                      #
 *
 *
 *
 * https://north.sbdp.ro https://mid.sbdp.ro/ https://pacif.sbdp.ro/ https://low.sbdp.ro/
 * (c) Florian Leon Steenbuck
 *
 * Copyright (c) 2026 by Florian Leon Steenbuck
 * https://kil.ls https://fslap.de
 *
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

import './styles/index.less';
import { initCaseMap } from './components/CaseMap';
import { initDonate } from './components/Donate';
import { initMap } from './components/Map';
import { initTurnstile } from './components/Turnstile';

/**
 * Behaviours of party blocks. Every behaviour is initialized once per element.
 */
const behaviours: Array<
	[string, (element: HTMLElement) => void | Promise<void>]
> = [
	['[data-party-map]', initMap],
	['[data-party-case-map]', initCaseMap],
	['[data-party-donate]', initDonate],
	['.cf-turnstile', initTurnstile],
];

const init = (): void => {
	behaviours.forEach(([selector, callback]) => {
		document.querySelectorAll<HTMLElement>(selector).forEach((element) => {
			if (element.dataset.partyInitialized === selector) {
				return;
			}

			element.dataset.partyInitialized = selector;

			Promise.resolve(callback(element)).catch((error) => {
				console.error(error);
			});
		});
	});
};

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', init);
} else {
	init();
}
