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

/**
 * Append the selected amount to the donation link.
 *
 * @param root
 */
export const initDonate = (root: HTMLElement): void => {
	const submit = root.querySelector<HTMLAnchorElement>(
		'.donate-block__submit'
	);
	const buttons = root.querySelectorAll<HTMLButtonElement>(
		'.donate-block__amount'
	);

	if (!submit) {
		return;
	}

	const base = submit.dataset.baseUrl || submit.getAttribute('href') || '#';

	buttons.forEach((button) => {
		button.addEventListener('click', () => {
			buttons.forEach((other) => {
				other.classList.toggle('is-active', other === button);
				other.setAttribute('aria-pressed', `${other === button}`);
			});

			if (base.startsWith('#')) {
				submit.setAttribute('href', base);
				submit.dataset.amount = button.dataset.amount;

				return;
			}

			try {
				const url = new URL(base, window.location.href);

				url.searchParams.set('amount', button.dataset.amount || '');
				submit.setAttribute('href', url.toString());
			} catch {
				submit.setAttribute('href', base);
			}
		});
	});
};
