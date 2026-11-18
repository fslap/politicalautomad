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

namespace Automad\Party\StepBars;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Core\Str;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component StepBars: stages on an exact, linear time axis (years). Every stage has a
 * total length (e.g. the time in work) and a highlighted part (e.g. the time of the benefit).
 * The bars grow when they scroll into view; a slider shows the stage for a chosen time.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class StepBars extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'stepbars');
	}

	public function context(array $config): array {
		$max = max(1.0, (float) str_replace(',', '.', (string) $config['scale_max']));
		$unit = trim((string) $config['unit']) ?: 'J';
		$words = array_map(fn ($w) => trim((string) $w), array($config['word_year'] ?? '', $config['word_years'] ?? '', $config['word_month'] ?? '', $config['word_months'] ?? ''));
		$words = array_map(fn ($w, $d) => $w !== '' ? $w : $d, $words, array('Jahr', 'Jahre', 'Monat', 'Monate'));
		$steps = array();

		foreach ((array) $config['steps'] as $step) {
			if (!is_array($step)) {
				continue;
			}

			$total = self::number($step['total'] ?? '');
			$part = min($total, self::number($step['part'] ?? ''));

			if ($total <= 0) {
				continue;
			}

			$steps[] = array(
				'label' => trim((string) ($step['label'] ?? '')) ?: self::duration($total, $words),
				'total' => $total,
				'part' => $part,
				'total_text' => self::duration($total, $words),
				'part_text' => self::duration($part, $words),
				'style' => '--sb-w: ' . self::percent($total, $max) . '%; --sb-p: ' . self::percent($part, $total) . '%;'
			);
		}

		usort($steps, fn ($a, $b) => $a['total'] <=> $b['total']);

		foreach ($steps as $i => &$step) {
			$step['index'] = $i;
			$step['style'] .= ' --sb-delay: ' . ($i * 90) . 'ms;';
		}

		unset($step);

		$ticks = array();
		$every = max(1, (int) $config['tick_every']);

		for ($year = 0; $year <= $max + 0.001; $year += $every) {
			$ticks[] = array('label' => $year . $unit, 'style' => 'left: ' . self::percent($year, $max) . '%;');
		}

		$text = trim((string) $config['text']);

		return array(
			'section_id' => preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $config['section_id']),
			'kicker' => trim((string) $config['kicker']),
			'title' => $config['title'],
			'text_html' => $text === '' ? '' : Str::markdown($text),
			'highlight' => trim((string) $config['highlight']),
			'highlight_label' => trim((string) $config['highlight_label']),
			'steps' => $steps,
			'has_steps' => !empty($steps),
			'ticks' => $ticks,
			'total_label' => trim((string) $config['total_label']),
			'part_label' => trim((string) $config['part_label']),
			'slider' => (bool) $config['slider'] && !empty($steps),
			'slider_label' => trim((string) $config['slider_label']),
			'slider_max' => $max,
			'steps_json' => $this->json(array_map(fn ($s) => array('total' => $s['total'], 'part_text' => $s['part_text'], 'label' => $s['label']), $steps)),
			'none_text' => trim((string) $config['none_text']),
			'result_text' => trim((string) $config['result_text']) ?: '{part}',
			'unit' => $unit,
			'words_json' => $this->json($words),
			'classes' => $this->getClasses($config)
		);
	}

	/**
	 * A duration in years as readable text (e.g. 1.67 → 1 Jahr 8 Monate).
	 *
	 * @param float $years
	 * @param array $words year, years, month, months
	 * @return string
	 */
	public static function duration(float $years, array $words = array('Jahr', 'Jahre', 'Monat', 'Monate')): string {
		$months = (int) round($years * 12);
		$y = intdiv($months, 12);
		$m = $months % 12;
		$parts = array();

		if ($y > 0) {
			$parts[] = $y . ' ' . ($y === 1 ? $words[0] : $words[1]);
		}

		if ($m > 0) {
			$parts[] = $m . ' ' . ($m === 1 ? $words[2] : $words[3]);
		}

		return $parts ? implode(' ', $parts) : '0 ' . $words[3];
	}

	protected function definition(): array {
		return array(
			'title' => 'Stufen auf der Zeitachse (animiert)',
			'icon' => 'bar-chart-steps',
			'description' => 'Stufen als Balken auf einer genauen Zeitachse in Jahren, mit hervorgehobenem Anteil. Die Balken wachsen beim Hineinscrollen, ein Schieberegler zeigt die passende Stufe.',
			'fields' => array(
				self::sectionIdField('stufen'),
				array('name' => 'kicker', 'type' => 'text', 'label' => 'Kleine Zeile über dem Titel', 'default' => ''),
				self::titleField('Stufen'),
				array('name' => 'text', 'type' => 'markdown', 'label' => 'Text (Markdown)', 'default' => ''),
				array('name' => 'highlight', 'type' => 'text', 'label' => 'Kennzahl (z.B. 900 EUR)', 'default' => ''),
				array('name' => 'highlight_label', 'type' => 'text', 'label' => 'Beschriftung der Kennzahl', 'default' => ''),
				array(
					'name' => 'steps',
					'type' => 'list',
					'label' => 'Stufen',
					'addLabel' => 'Stufe hinzufügen',
					'itemTitle' => 'label',
					'default' => array(),
					'fields' => array(
						array('name' => 'label', 'type' => 'text', 'label' => 'Bezeichnung (optional)'),
						array('name' => 'total', 'type' => 'text', 'label' => 'Gesamtlänge in Jahren (z.B. 1.67 für 1 Jahr 8 Monate)'),
						array('name' => 'part', 'type' => 'text', 'label' => 'Hervorgehobener Anteil in Jahren (z.B. 0.5)')
					)
				),
				array('name' => 'total_label', 'type' => 'text', 'label' => 'Legende Gesamtlänge', 'default' => 'Gesamt'),
				array('name' => 'part_label', 'type' => 'text', 'label' => 'Legende Anteil', 'default' => 'Anteil'),
				array('name' => 'scale_max', 'type' => 'text', 'label' => 'Ende der Skala in Jahren', 'default' => '40'),
				array('name' => 'tick_every', 'type' => 'number', 'label' => 'Markierung alle … Jahre', 'default' => 5),
				array('name' => 'unit', 'type' => 'text', 'label' => 'Einheit an der Skala', 'default' => 'J'),
				array('name' => 'word_year', 'type' => 'text', 'label' => 'Wort für 1 Jahr', 'default' => 'Jahr'),
				array('name' => 'word_years', 'type' => 'text', 'label' => 'Wort für Jahre', 'default' => 'Jahre'),
				array('name' => 'word_month', 'type' => 'text', 'label' => 'Wort für 1 Monat', 'default' => 'Monat'),
				array('name' => 'word_months', 'type' => 'text', 'label' => 'Wort für Monate', 'default' => 'Monate'),
				array('name' => 'slider', 'type' => 'toggle', 'label' => 'Schieberegler anzeigen', 'default' => true),
				array('name' => 'slider_label', 'type' => 'text', 'label' => 'Text am Schieberegler', 'default' => 'Wie lange?'),
				array('name' => 'result_text', 'type' => 'text', 'label' => 'Ergebnis ({part} = Anteil, {label} = Stufe)', 'default' => '{part}'),
				array('name' => 'none_text', 'type' => 'text', 'label' => 'Text unterhalb der ersten Stufe', 'default' => ''),
				self::classesField('')
			)
		);
	}

	private static function number(mixed $value): float {
		return max(0.0, (float) str_replace(',', '.', trim((string) $value)));
	}

	private static function percent(float $value, float $of): string {
		return rtrim(rtrim(number_format(min(100, $value / max(0.0001, $of) * 100), 3, '.', ''), '0'), '.');
	}
}
