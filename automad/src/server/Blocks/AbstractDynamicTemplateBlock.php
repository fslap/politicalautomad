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
 * \#########|   \\\|      _\    :#;:  :#;:#;:  #;:#;:#;:#;:
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
namespace Automad\Blocks;

use Automad\Core\Automad;
use Automad\Models\ComponentCollection;
use Automad\Party\Traits\PartyBlockDefaults;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * The abstract base block for usage of external templates dynamic.
 *
 * Each component lives under Party/{ComponentName}/ with:
 * - template/mustache/{blockname}.mustache  (primary)
 * - template/twig/{blockname}.twig          (fallback; self-contained, no {% include %} stubs)
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 *
 */
abstract class AbstractDynamicTemplateBlock extends AbstractDynamicBlock {
	use PartyBlockDefaults;

	private string $blockName = '';

	public function __construct(
		private string $path,
		false|string $name = false,
		private string $engine = 'mustache',
		private string|array $source = []
	) {
		if ($name === false) {
			$name = strtolower((new \ReflectionClass($this))->getShortName());
		}
		$this->blockName = $name;
	}

	public function name(): string {
		return $this->blockName;
	}

	public function getPath(): string {
		return $this->path;
	}

	abstract public function context(array $config): array;

	/**
	 * Render a party dynamic block.
	 *
	 * @param array $block
	 * @param Automad $Automad
	 * @return string the rendered HTML
	 */
	public function render(array $block, Automad $Automad): string {
		$config = $block['data'] ?? [];
		$ctx = $this->context($config);

		if ($this->engine === 'twig') {
			$html = $this->renderTwig($ctx);
			if ($html !== '') {
				return $html;
			}
		}

		$html = $this->renderMustache($ctx);
		if ($html !== '') {
			return $html;
		}

		return $this->renderTwig($ctx);
	}

	protected function renderMustache(array $ctx): string {
		$templateFile = $this->path . '/template/mustache/' . $this->blockName . '.mustache';
		if (!is_file($templateFile)) {
			return '';
		}

		$this->loadVendor();

		if (!class_exists(\Mustache_Engine::class)) {
			return '';
		}

		$loader = new \Mustache_Loader_FilesystemLoader(
			$this->path . '/template/mustache',
			['extension' => '.mustache']
		);

		$engine = new \Mustache_Engine([
			'loader' => $loader,
			'entity_flags' => ENT_QUOTES,
		]);

		return $engine->render($this->blockName, $ctx);
	}

	protected function renderTwig(array $ctx): string {
		$templateFile = $this->path . '/template/twig/' . $this->blockName . '.twig';
		if (!is_file($templateFile)) {
			return '';
		}

		$this->loadVendor();

		if (!class_exists(\Twig\Environment::class)) {
			return '';
		}

		$loader = new \Twig\Loader\FilesystemLoader($this->path . '/template/twig');
		$twig = new \Twig\Environment($loader, [
			'autoescape' => 'html',
			'strict_variables' => false,
		]);

		return $twig->render($this->blockName . '.twig', $ctx);
	}

	private function loadVendor(): void {
		static $loaded = false;
		if ($loaded) {
			return;
		}

		$autoload = AM_BASE_DIR . '/lib/vendor/autoload.php';
		if (is_readable($autoload)) {
			require_once $autoload;
		}

		if (class_exists(\Mustache_Autoloader::class)) {
			\Mustache_Autoloader::register();
		}

		$loaded = true;
	}
}