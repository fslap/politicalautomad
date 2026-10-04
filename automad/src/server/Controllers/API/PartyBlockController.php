<?php
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

namespace Automad\Controllers\API;

use Automad\API\Response;
use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Core\Automad;
use Automad\Core\Blocks;
use Automad\Core\Request;
use Automad\Engine\Processors\URLProcessor;
use Automad\System\Asset;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * The party block controller renders previews of party blocks for the block editor.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class PartyBlockController {
	/**
	 * Render a single party block with the given data.
	 *
	 * @return Response the response object
	 */
	public static function preview(): Response {
		$Response = new Response();
		$type = (string) Request::post('type');
		$data = json_decode((string) Request::post('data'), true);
		$register = Blocks::getDynamicRegister();

		if (!$register || !$register->type($type)) {
			return $Response->setError('Unknown party block type');
		}

		$block = $register->object($type);

		if (!$block instanceof AbstractDynamicTemplateBlock) {
			return $Response->setError('Unknown party block type');
		}

		$Automad = Automad::fromCache();
		$Page = $Automad->getPage((string) Request::post('url'));

		if ($Page) {
			$Automad->Context->set($Page);
		}

		try {
			$html = $block->render(
				array('id' => 'preview', 'type' => $type, 'data' => is_array($data) ? $data : array(), 'tunes' => array()),
				$Automad
			);
		} catch (\Throwable $error) {
			return $Response->setError($error->getMessage());
		}

		if ($Page) {
			$html = URLProcessor::resolveUrls($html, 'relativeUrlToBase', array($Page));
		}

		$html = URLProcessor::resolveUrls($html, 'absoluteUrlToRoot');
		$assets = '';

		if (is_readable(AM_BASE_DIR . '/automad/dist/build/party/index.css')) {
			$assets = Asset::css('dist/build/party/index.css') . Asset::js('dist/build/party/index.js');
		}

		return $Response->setData(array('html' => $html, 'assets' => $assets));
	}
}
