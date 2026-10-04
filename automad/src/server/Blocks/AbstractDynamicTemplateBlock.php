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

namespace Automad\Blocks;

use Automad\Blocks\Utils\Attr;
use Automad\Core\Automad;
use Automad\Models\ComponentCollection;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * The base class for all template based party blocks (v2-political).
 *
 * A party block is a self-contained component that lives in its own directory
 * below automad/src/server/Party. It consists of a PHP class that extends this class,
 * a mustache (and optionally a twig) template and a definition of fields that is
 * used by the dashboard in order to create the editor block that is available
 * in the "+" toolbox of the block editor.
 *
 * Block data that is saved by the editor is merged with the field defaults, passed to
 * the context() method and finally rendered with the template engine. The output is
 * wrapped in an element that carries the "am-party" class. That class is used to
 * inject the party stylesheets and scripts into the page.
 *
 * Field definitions are arrays with the following keys:
 *
 * - name: the data key
 * - type: text, textarea, markdown, html, number, select, toggle, image, url, color, strings, json, list or map
 * - bind: (map only) the names of the fields that are edited by the map editor (markers, geojson, center, zoom, tileUrl, height)
 * - object: (map only) an optional json field that contains the bound values
 * - optionsFrom: (select only) build options from a list field, e.g. array('field' => 'markers', 'value' => 'id', 'label' => 'title')
 * - label: the field label in the dashboard
 * - default: the default value
 * - placeholder: an optional placeholder
 * - help: an optional help text
 * - options: an array of value => label pairs for select fields
 * - fields: the field definitions of a single item in a list field
 * - itemTitle: the name of a sub field that is used as title of list items
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 *
 * @psalm-type PartyField = array{
 *		name: string,
 *		type: string,
 *		label?: string,
 *		default?: mixed,
 *		placeholder?: string,
 *		help?: string,
 *		options?: array<string, string>,
 *		fields?: array,
 *		itemTitle?: string
 * }
 */
abstract class AbstractDynamicTemplateBlock extends AbstractDynamicBlock {
	/**
	 * The base class that is added to the wrapping element of every party block.
	 */
	const BASE_CLASS = 'am-party';

	/**
	 * The block type prefix that is used to avoid collisions with core block types.
	 */
	const TYPE_PREFIX = 'party';

	/**
	 * The template name.
	 */
	private string $templateName = '';

	/**
	 * The constructor.
	 *
	 * @param string $path the component directory
	 * @param false|string $name the template name, defaults to the lowercase short class name
	 * @param string $engine the preferred template engine (mustache or twig)
	 */
	public function __construct(
		private string $path,
		false|string $name = false,
		private string $engine = 'mustache'
	) {
		if ($name === false) {
			$name = strtolower((new \ReflectionClass($this))->getShortName());
		}

		$this->templateName = $name;
	}

	/**
	 * Build the template context from the block data that is already merged with the field defaults.
	 *
	 * @param array $config
	 * @return array
	 */
	abstract public function context(array $config): array;

	/**
	 * Return the default data of a block.
	 *
	 * @return array
	 */
	public function defaults(): array {
		$defaults = array();

		foreach ($this->fields() as $field) {
			if (self::isVirtual($field)) {
				continue;
			}

			$defaults[$field['name']] = $field['default'] ?? self::emptyValue($field['type']);
		}

		return $defaults;
	}

	/**
	 * Return the full block definition that is sent to the dashboard.
	 *
	 * @return array
	 */
	public function editorDefinition(): array {
		$definition = $this->definition();

		return array(
			'type' => $this->type(),
			'component' => $this->shortName(),
			'title' => $definition['title'] ?? $this->shortName(),
			'icon' => $definition['icon'] ?? 'puzzle',
			'description' => $definition['description'] ?? '',
			'stretchable' => $definition['stretchable'] ?? true,
			'fields' => $this->fields(),
			'defaults' => $this->defaults()
		);
	}

	/**
	 * Return the full field definitions.
	 *
	 * @return array<int, PartyField>
	 */
	public function fields(): array {
		return $this->definition()['fields'] ?? array();
	}

	/**
	 * The component directory.
	 *
	 * @return string
	 */
	public function getPath(): string {
		return $this->path;
	}

	/**
	 * The template name.
	 *
	 * @return string
	 */
	public function name(): string {
		return $this->templateName;
	}

	/**
	 * Merge block data with the field defaults and normalize the field values.
	 *
	 * @param array $data
	 * @return array
	 */
	public function prepare(array $data): array {
		$prepared = $data;

		foreach ($this->fields() as $field) {
			if (self::isVirtual($field)) {
				continue;
			}

			$name = $field['name'];
			$value = $data[$name] ?? null;

			// Only missing values fall back to the default in order to allow for clearing fields.
			if ($value === null) {
				$value = $field['default'] ?? self::emptyValue($field['type']);
			}

			$prepared[$name] = self::normalize($field, $value);
		}

		return $prepared;
	}

	/**
	 * Render a party dynamic block.
	 *
	 * @param array $block
	 * @param Automad $Automad
	 * @return string the rendered HTML
	 */
	public function render(array $block, Automad $Automad): string {
		$html = $this->renderInner($block['data'] ?? array());

		if (trim($html) === '') {
			return '';
		}

		$attr = Attr::render(
			$block['tunes'] ?? array(),
			array(self::BASE_CLASS, self::BASE_CLASS . '-' . $this->slug())
		);

		return '<div ' . $attr . ' data-am-party="' . $this->type() . '">' . $html . '</div>';
	}

	/**
	 * Render the inner HTML of a block without the wrapping element.
	 *
	 * @param array $data
	 * @return string
	 */
	public function renderInner(array $data): string {
		$ctx = $this->context($this->prepare($data));

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

	/**
	 * Search and replace inside all string values of a block.
	 *
	 * @param array $block
	 * @param ComponentCollection|null $ComponentCollection
	 * @param string $searchRegex
	 * @param string $replace
	 * @param bool $replaceInPublishedComponent
	 * @return array
	 */
	public function replace(
		array $block,
		?ComponentCollection $ComponentCollection,
		string $searchRegex,
		string $replace,
		bool $replaceInPublishedComponent
	): array {
		$replaceRecursive = function (mixed $value) use (&$replaceRecursive, $searchRegex, $replace): mixed {
			if (is_array($value)) {
				return array_map($replaceRecursive, $value);
			}

			if (is_string($value)) {
				return preg_replace($searchRegex, $replace, $value) ?? $value;
			}

			return $value;
		};

		$block['data'] = $replaceRecursive($block['data'] ?? array());

		return $block;
	}

	/**
	 * The short class name of the component.
	 *
	 * @return string
	 */
	public function shortName(): string {
		return (new \ReflectionClass($this))->getShortName();
	}

	/**
	 * The kebab case version of the component name, e.g. "party-header".
	 *
	 * @return string
	 */
	public function slug(): string {
		return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $this->shortName()) ?? '');
	}

	/**
	 * Return a searchable string representation of a block.
	 *
	 * @param array $block
	 * @param ComponentCollection|null $ComponentCollection
	 * @return string
	 */
	public function toString(array $block, ?ComponentCollection $ComponentCollection): string {
		$strings = array();

		array_walk_recursive($block['data'], function (mixed $value) use (&$strings): void {
			if (is_string($value) && !preg_match('/^(https?:|\/|#)/', $value)) {
				$strings[] = strip_tags($value);
			}
		});

		return trim(join(' ', $strings));
	}

	/**
	 * The block type that is used to store the block in the page data, e.g. "partyGallery".
	 *
	 * @return string
	 */
	public function type(): string {
		return self::TYPE_PREFIX . $this->shortName();
	}

	/**
	 * The editor definition of the block.
	 * Components return an array with a title, an icon (Bootstrap icon name),
	 * an optional description and the list of fields.
	 *
	 * @return array{title: string, icon: string, description?: string, stretchable?: bool, fields: array}
	 */
	abstract protected function definition(): array;

	/**
	 * Return a JSON string that is safe to be used inside of HTML attributes and script tags.
	 *
	 * @param mixed $value
	 * @return string
	 */
	protected function json(mixed $value): string {
		return (string) json_encode(
			$value,
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
		);
	}

	/**
	 * Render the context with mustache.
	 *
	 * @param array $ctx
	 * @return string
	 */
	protected function renderMustache(array $ctx): string {
		$dir = $this->path . '/template/mustache';
		$templateFile = $dir . '/' . $this->templateName . '.mustache';

		if (!is_file($templateFile)) {
			return '';
		}

		self::loadVendor();

		if (!class_exists(\Mustache_Engine::class)) {
			return '';
		}

		$engine = new \Mustache_Engine(array(
			'loader' => new \Mustache_Loader_FilesystemLoader($dir, array('extension' => '.mustache')),
			'partials_loader' => new \Mustache_Loader_FilesystemLoader($dir, array('extension' => '.mustache')),
			'entity_flags' => ENT_QUOTES,
			'charset' => 'UTF-8'
		));

		return $engine->render($this->templateName, $ctx);
	}

	/**
	 * Render the context with twig.
	 *
	 * @param array $ctx
	 * @return string
	 */
	protected function renderTwig(array $ctx): string {
		$dir = $this->path . '/template/twig';
		$templateFile = $dir . '/' . $this->templateName . '.twig';

		if (!is_file($templateFile)) {
			return '';
		}

		self::loadVendor();

		if (!class_exists(\Twig\Environment::class)) {
			return '';
		}

		$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader($dir), array(
			'autoescape' => 'html',
			'strict_variables' => false,
		));

		return $twig->render($this->templateName . '.twig', $ctx);
	}

	/**
	 * Split a comma separated "desktop[,mobile]" image value.
	 *
	 * @param string $value
	 * @return array{desktop: string, mobile: string}
	 */
	protected function splitImages(string $value): array {
		$images = array_values(array_filter(array_map('trim', explode(',', $value))));
		$desktop = $images[0] ?? '';

		return array(
			'desktop' => $desktop,
			'mobile' => $images[1] ?? $desktop
		);
	}

	/**
	 * Return the empty value for a given field type.
	 *
	 * @param string $type
	 * @return mixed
	 */
	private static function emptyValue(string $type): mixed {
		return match ($type) {
			'list', 'strings' => array(),
			'toggle' => false,
			'json' => null,
			default => ''
		};
	}

	/**
	 * Virtual fields like the map editor don't store a value on their own
	 * but edit the values of other fields.
	 *
	 * @param array $field
	 * @return bool
	 */
	private static function isVirtual(array $field): bool {
		return $field['type'] === 'map';
	}

	/**
	 * Load the vendor autoloader that provides mustache and twig.
	 */
	private static function loadVendor(): void {
		static $loaded = false;

		if ($loaded) {
			return;
		}

		$autoload = AM_BASE_DIR . '/lib/vendor/autoload.php';

		if (is_readable($autoload)) {
			require_once $autoload;
		}

		$loaded = true;
	}

	/**
	 * Normalize a field value according to its type.
	 *
	 * @param array $field
	 * @param mixed $value
	 * @return mixed
	 */
	private static function normalize(array $field, mixed $value): mixed {
		switch ($field['type']) {
			case 'toggle':
				return $value === true || $value === 1 || $value === '1' || $value === 'true';
			case 'number':
				return is_numeric($value) ? $value + 0 : ($field['default'] ?? '');
			case 'strings':
				if (is_string($value)) {
					$value = preg_split('/\r?\n/', $value) ?: array();
				}

				return array_values(array_filter(array_map('strval', (array) $value), fn ($item) => trim($item) !== ''));
			case 'json':
				if (is_string($value)) {
					$decoded = json_decode($value, true);

					return json_last_error() === JSON_ERROR_NONE ? $decoded : ($field['default'] ?? null);
				}

				return $value;
			case 'list':
				if (!is_array($value)) {
					return array();
				}

				$subFields = $field['fields'] ?? array();

				return array_values(array_map(function (mixed $item) use ($subFields): mixed {
					if (!is_array($item)) {
						return $item;
					}

					foreach ($subFields as $subField) {
						$name = $subField['name'];
						$subValue = $item[$name] ?? null;

						if ($subValue === null) {
							$subValue = $subField['default'] ?? self::emptyValue($subField['type']);
						}

						$item[$name] = self::normalize($subField, $subValue);
					}

					return $item;
				}, $value));
			default:
				return is_scalar($value) ? (string) $value : $value;
		}
	}
}
