<?php

namespace Automad\Party;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Core\Blocks;
use Automad\Models\Search\Replacement;
use Automad\Test\Mock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PartyBlocksTest extends TestCase {
	public static function setUpBeforeClass(): void {
		BlockBootstrap::init();
	}

	public static function dataForTestComponents(): array {
		$data = array();

		foreach (All::$components as $name => $class) {
			$data[$name] = array($class);
		}

		return $data;
	}

	public function testClearedFieldsDoNotFallBackToDefaults(): void {
		$block = new Image\Image();

		$this->assertSame('inner-container', $block->prepare(array())['classes']);
		$this->assertSame('', $block->prepare(array('classes' => ''))['classes']);
	}

	#[DataProvider('dataForTestComponents')]
	public function testComponentIsRegistered(string $class): void {
		$block = new $class();

		$this->assertInstanceOf(AbstractDynamicTemplateBlock::class, $block);
		$this->assertTrue(Blocks::getDynamicRegister()->type($block->type()));
		$this->assertStringStartsWith('party', $block->type());
	}

	public function testCoreBlocksAreNotShadowed(): void {
		$register = Blocks::getDynamicRegister();

		foreach (array('paragraph', 'header', 'image', 'gallery', 'quote', 'buttons') as $type) {
			$this->assertFalse($register->type($type));
		}
	}

	#[DataProvider('dataForTestComponents')]
	public function testDefinitionIsValid(string $class): void {
		$definition = (new $class())->editorDefinition();
		$types = array('text', 'textarea', 'markdown', 'html', 'number', 'select', 'toggle', 'image', 'url', 'color', 'strings', 'json', 'list', 'map');

		$this->assertNotEmpty($definition['title']);
		$this->assertNotEmpty($definition['icon']);
		$this->assertNotEmpty($definition['fields']);
		$this->assertNotFalse(json_encode($definition));

		$check = function (array $fields) use (&$check, $types): void {
			foreach ($fields as $field) {
				$this->assertArrayHasKey('name', $field);
				$this->assertContains($field['type'], $types, $field['name']);

				if ($field['type'] === 'list') {
					$this->assertNotEmpty($field['fields'], $field['name']);
					$check($field['fields']);
				}

				if ($field['type'] === 'select') {
					$this->assertNotEmpty($field['options'], $field['name']);
				}
			}
		};

		$check($definition['fields']);
	}

	public function testEscaping(): void {
		$Mock = new Mock();
		$Automad = $Mock->createAutomad();
		$block = new Quote\Quote();

		$html = $block->render(
			array('id' => 'x', 'type' => $block->type(), 'data' => array('quote_text' => '<script>alert(1)</script>'), 'tunes' => array()),
			$Automad
		);

		$this->assertStringNotContainsString('<script>', $html);
		$this->assertStringContainsString('&lt;script&gt;', $html);
	}

	public function testFormRejectsIncompleteSubmissions(): void {
		$Mock = new Mock();
		$Automad = $Mock->createAutomad();
		$block = new Form\Form();

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST = array('form_section_id' => 'join', 'firstname' => '', 'lastname' => 'Muster', 'email' => 'max@example.com');

		$html = $block->render(
			array('id' => 'x', 'type' => $block->type(), 'data' => array('section_id' => 'join', 'mail_to' => 'team@example.com'), 'tunes' => array()),
			$Automad
		);

		$_POST = array();
		unset($_SERVER['REQUEST_METHOD']);

		$this->assertStringContainsString('party-form__message--error', $html);
		$this->assertStringContainsString('<form', $html);
	}

	public function testFormRequiresTurnstileWhenConfigured(): void {
		$Mock = new Mock();
		$Automad = $Mock->createAutomad();
		$block = new Form\Form();

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST = array('form_section_id' => 'join2', 'firstname' => 'Max', 'lastname' => 'Muster', 'email' => 'max@example.com');

		$html = $block->render(
			array('id' => 'x', 'type' => $block->type(), 'data' => array('section_id' => 'join2', 'mail_to' => 'team@example.com', 'turnstile_site_key' => 'site-key'), 'tunes' => array()),
			$Automad
		);

		$_POST = array();
		unset($_SERVER['REQUEST_METHOD']);

		$this->assertStringContainsString('party-form__message--error', $html);
		$this->assertStringContainsString('data-sitekey="site-key"', $html);
	}

	public function testListFieldsAreRendered(): void {
		$Mock = new Mock();
		$Automad = $Mock->createAutomad();
		$block = new RegionScroll\RegionScroll();

		$html = $block->render(
			array('id' => 'x', 'type' => $block->type(), 'data' => array(
				'regions' => array(
					array('region_id' => 'sh,schleswigholstein', 'region_name' => 'Schleswig-Holstein', 'region_description' => 'Nord')
				)
			), 'tunes' => array()),
			$Automad
		);

		$this->assertStringContainsString('<a id="sh"></a>', $html);
		$this->assertStringContainsString('<a id="schleswigholstein"></a>', $html);
		$this->assertStringContainsString('Schleswig-Holstein', $html);
	}

	public function testMapDataIsPassedAsAttribute(): void {
		$Mock = new Mock();
		$Automad = $Mock->createAutomad();
		$block = new LeafletMap\LeafletMap();

		$html = $block->render(
			array('id' => 'x', 'type' => $block->type(), 'data' => array(
				'markers' => array(
					array('title' => '</script><b>', 'lat' => '52.5', 'lng' => '13.4'),
					array('title' => 'no coordinates', 'lat' => '', 'lng' => '')
				)
			), 'tunes' => array()),
			$Automad
		);

		preg_match('/data-party-map="([^"]+)"/', $html, $matches);
		$config = json_decode(html_entity_decode($matches[1], ENT_QUOTES), true);

		$this->assertCount(1, $config['markers']);
		$this->assertSame('</script><b>', $config['markers'][0]['title']);
		$this->assertStringNotContainsString('</script>', $html);
	}

	#[DataProvider('dataForTestComponents')]
	public function testRenderThroughCoreBlocks(string $class): void {
		$Mock = new Mock();
		$Automad = $Mock->createAutomad();
		$block = new $class();

		$html = Blocks::render(
			array('blocks' => array(array('id' => 'test', 'type' => $block->type(), 'data' => $block->defaults(), 'tunes' => array()))),
			$Automad
		);

		$this->assertStringContainsString('data-am-party="' . $block->type() . '"', $html);
	}

	#[DataProvider('dataForTestComponents')]
	public function testRenderWithDefaults(string $class): void {
		$Mock = new Mock();
		$Automad = $Mock->createAutomad();
		$block = new $class();

		$html = $block->render(
			array('id' => 'test', 'type' => $block->type(), 'data' => $block->defaults(), 'tunes' => array()),
			$Automad
		);

		// Empty components like an anchor or a separator still render a wrapper.
		$this->assertStringContainsString('data-am-party="' . $block->type() . '"', $html);
		$this->assertStringContainsString('am-party-' . $block->slug(), $html);
		$this->assertStringNotContainsString('{{', $html);
	}

	public function testSearchAndReplace(): void {
		$Mock = new Mock();
		$Automad = $Mock->createAutomad();
		$block = new Documents\Documents();
		$data = array('id' => 'x', 'type' => $block->type(), 'data' => array(
			'title' => 'Satzung der Partei',
			'documents' => array(array('title' => 'Satzung', 'file' => '/shared/satzung.pdf'))
		));

		$this->assertSame('Satzung der Partei Satzung', $block->toString($data, $Automad->ComponentCollection));

		$replaced = $block->replace($data, $Automad->ComponentCollection, Replacement::buildRegex('Satzung', false, false), 'Programm', false);

		$this->assertSame('Programm der Partei', $replaced['data']['title']);
		$this->assertSame('Programm', $replaced['data']['documents'][0]['title']);
	}

	public function testVerticalSliderRendersNestedComponents(): void {
		$Mock = new Mock();
		$Automad = $Mock->createAutomad();
		$block = new VerticalSlider\VerticalSlider();

		$html = $block->render(
			array('id' => 'x', 'type' => $block->type(), 'data' => array(
				'items' => array(
					array('component' => 'partyQuote', 'data' => array('quote_text' => 'Slide quote')),
					array('type' => 'MARKDOWN_SECTION', 'title' => 'Legacy slide', 'markdown_content' => '**bold**'),
					array('component' => 'partyVerticalSlider', 'data' => array())
				)
			), 'tunes' => array()),
			$Automad
		);

		$this->assertStringContainsString('Slide quote', $html);
		$this->assertStringContainsString('Legacy slide', $html);
		$this->assertStringContainsString('<strong>bold</strong>', $html);
		$this->assertSame(2, substr_count($html, 'class="vertical-slide '));
	}
}
