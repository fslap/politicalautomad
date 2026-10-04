# Party Components

Party components are self-contained Automad blocks that were ported from
[politicalpartysite](https://github.com/fslap/politicalpartysite). Every component
registered in `All.php` automatically shows up in the **+** toolbox of the block
editor in the dashboard — no client code has to be touched to add a new one.

## How it works

1. `App.php` calls `BlockBootstrap::init()`, which registers all components of
   `All::$components` in a `DynamicBlockRegister` that is used by
   `Automad\Core\Blocks` to render, search and replace blocks.
2. The app bootstrap API (`AppController::bootstrap`) sends the editor definition of
   every component (`editorDefinition()`) to the dashboard.
3. The dashboard creates one EditorJS tool per definition
   (`automad/src/client/admin/editor/blocks/PartyBlock.ts`). A block shows a summary
   of its content, a modal form that is generated from the field definitions and an
   optional live preview (`PartyBlockController::preview`).
4. On the website, every block is wrapped in
   `<div class="am-block am-party am-party-{slug}" data-am-party="party{Name}">`.
   When such an element is found, the party stylesheet and script
   (`automad/dist/build/party/index.{css,js}`, source in `automad/src/client/party`)
   are injected into the page.

Block types are prefixed with `party` (e.g. `partyGallery`) so that party components
never shadow core blocks like `gallery`, `image` or `quote`.

## Adding a component

```
Party/
└── MyComponent/
    ├── MyComponent.php
    └── template/
        ├── mustache/mycomponent.mustache
        └── twig/mycomponent.twig   (optional fallback)
```

```php
namespace Automad\Party\MyComponent;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

class MyComponent extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'mycomponent');
	}

	public function context(array $config): array {
		return array(
			'title' => $config['title'],
			'items' => $config['items'],
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Meine Component',
			'icon' => 'stars', // a Bootstrap icon name
			'description' => 'Kurze Beschreibung für das Dashboard.',
			'fields' => array(
				self::titleField(),
				array(
					'name' => 'items',
					'type' => 'list',
					'label' => 'Einträge',
					'itemTitle' => 'name',
					'fields' => array(
						array('name' => 'name', 'type' => 'text', 'label' => 'Name'),
						array('name' => 'image', 'type' => 'image', 'label' => 'Bild')
					)
				),
				self::classesField('inner-container')
			)
		);
	}
}
```

Finally add the class to `All::$components`. Styles go to
`automad/src/client/party/styles` and scripts to `automad/src/client/party`.

### Field types

| Type       | Editor field                             | Stored value          |
| ---------- | ---------------------------------------- | --------------------- |
| `text`     | input                                    | string                |
| `textarea` | textarea                                 | string                |
| `markdown` | Markdown editor                          | string                |
| `html`     | code editor                              | string (raw HTML)     |
| `number`   | number input                             | number                |
| `select`   | select, requires `options`               | string                |
| `toggle`   | toggle                                   | bool                  |
| `image`    | image picker (page and shared files)     | string                |
| `url`      | link field with page autocompletion      | string                |
| `color`    | color picker                             | string                |
| `strings`  | textarea, one entry per line             | string[]              |
| `json`     | code editor                              | decoded JSON          |
| `list`     | repeatable items, requires `fields`      | array of objects      |
| `map`      | visual map editor (Leaflet.draw)         | — (edits other fields)|

### Map editor

The `map` field is a port of the Leaflet editor of politicalpartysite. It edits the
fields that are named in `bind` (`markers`, `geojson`, `center`, `zoom`, `tileUrl`,
`height`) — or the keys of a `json` field when `object` is set (see the Split
component). Markers are drawn, dragged and deleted on the map and keep their
additional data like title or region. Lines, polygons, rectangles and circles are
stored as GeoJSON, circles as points with a `radius` property. The current view is
stored as the initial view.

```php
array(
	'name' => 'map_editor',
	'type' => 'map',
	'label' => 'Karten-Editor',
	'bind' => array('markers' => 'markers', 'geojson' => 'geojson_content', 'center' => 'map_center', 'zoom' => 'map_zoom')
)
```

A `select` field can get its options from a list field with `optionsFrom`, e.g. the
marker selection of the cases:
`'optionsFrom' => array('field' => 'markers', 'value' => 'id', 'label' => 'title')`.

Missing values fall back to the field `default`. Values that were cleared in the
editor are kept empty.

## Form component

The form is processed by Automad when a recipient is set and no external target URL
is configured. Mails are sent with the SMTP settings of the dashboard. When a
Turnstile site key is set, the secret key must be provided by the environment
variable `PARTY_TURNSTILE_SECRET` (or a PHP constant with the same name) — secrets
are never stored in the page data.
