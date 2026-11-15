<?php

namespace Automad\Party;

use Automad\Party\ScrollReference\ScrollReference;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @testCase
 */
class ScrollReferenceTest extends TestCase {
	public static function dataForTestGroup(): array {
		$items = array(array('title' => 'Hin- und Rückweg kostenlos', 'text' => 'Zurück über die Grenze'), array('title' => 'Transoperation kostenlos', 'description' => 'Versicherungen ermöglichen'));

		return array(
			'title + items' => array(array('de' => array(array('title' => 'Sexarbeit und Schutz', 'items' => array(array('title' => 'Kondomzwang abschaffen'))), array('title' => 'Freiheit und Selbstbestimmung', 'items' => $items)))),
			'map of groups' => array(array('de' => array('Sexarbeit und Schutz' => array(array('title' => 'Kondomzwang abschaffen')), 'Freiheit und Selbstbestimmung' => $items))),
			'category field' => array(array('de' => array(
				array('title' => 'Kondomzwang abschaffen', 'category' => 'Sexarbeit und Schutz'),
				$items[0] + array('category' => 'Freiheit und Selbstbestimmung'),
				$items[1] + array('category' => 'Freiheit und Selbstbestimmung')
			)))
		);
	}

	#[DataProvider('dataForTestGroup')]
	public function testGroup(array $json): void {
		$file = sys_get_temp_dir() . '/scroll-reference-test-' . uniqid() . '.json';
		file_put_contents($file, json_encode($json));

		$context = (new ScrollReference())->context(array(
			'section_id' => 'freedom',
			'headline' => 'Freiheit und Selbstbestimmung',
			'items' => array(),
			'source_path' => $file,
			'source_key' => 'de',
			'match' => 'freiheit und selbstbestimmung',
			'classes' => ''
		));

		unlink($file);

		$this->assertSame(array('Hin- und Rückweg kostenlos', 'Transoperation kostenlos'), array_column($context['items'], 'subject'));
		$this->assertSame('Versicherungen ermöglichen', $context['items'][1]['text']);
	}

	public function testMissingAndNonJsonFilesAreIgnored(): void {
		$this->assertNull(ScrollReference::loadJson('/does/not/exist.json'));
		$this->assertNull(ScrollReference::loadJson('/etc/passwd'));
	}
}
