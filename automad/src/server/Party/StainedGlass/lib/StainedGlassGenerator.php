<?php
/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

namespace Automad\Party\StainedGlass;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Generates a colorful church window (stained glass) as SVG.
 *
 * The composition follows classic stained glass windows: a mosaic of glass panes with lead
 * lines, a framed border, a pointed (gothic) arch in the center with a light brick pattern,
 * rose windows with rays in the upper corners, medallions in the side panels and scattered
 * artifacts. Every seed creates a different window, the same seed always the same window.
 * There is no banner or text in the image.
 *
 * @author Florian Leon Steenbuck
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
final class StainedGlassGenerator {
	/**
	 * The available artifacts (symbols) in a 100 × 100 box.
	 */
	const ARTIFACTS = array('dove', 'star', 'sun', 'moon', 'coins', 'scales', 'book', 'chalice', 'heart', 'key', 'crown', 'eye', 'flame', 'leaf', 'hands');
	/**
	 * The color palettes: glass colors, light colors for the center window, gold and lead.
	 */
	const PALETTES = array(
		'classic' => array(
			'glass' => array('#1e4fa3', '#2c6fc9', '#5b2a86', '#7a3fb0', '#c1272d', '#2e8b57', '#f5c400', '#3f8fd8'),
			'light' => array('#cfe6f7', '#a9d3f0', '#e8f4fb', '#8cc2ea', '#f6efc9'),
			'gold' => '#f5c400',
			'lead' => '#1b1b22'
		),
		'fire' => array(
			'glass' => array('#b3121d', '#e0461b', '#f28c18', '#f5c400', '#6d0f1a', '#8e2a8f', '#d9381e'),
			'light' => array('#fde7b3', '#fbd38d', '#fff3d6', '#f9c174'),
			'gold' => '#ffd54a',
			'lead' => '#1f1311'
		),
		'ocean' => array(
			'glass' => array('#0b3c6d', '#125ea8', '#1b8ac1', '#11a3a3', '#2fbfa0', '#253b8f', '#5bc0eb'),
			'light' => array('#d7f1fa', '#b7e4f5', '#eaf8fc', '#a1d9ee'),
			'gold' => '#f2d16b',
			'lead' => '#0f1720'
		),
		'forest' => array(
			'glass' => array('#1f5e3a', '#2e8b57', '#6aa84f', '#a4c639', '#7b5e2a', '#c99a2e', '#3f7f6e'),
			'light' => array('#e3f3d6', '#cfe8bd', '#f2f8e8', '#bfe0a6'),
			'gold' => '#e9c46a',
			'lead' => '#17201a'
		),
		'party' => array(
			'glass' => array('#bd2f3e', '#8b0e1c', '#bd400a', '#191c79', '#323239', '#e91e63', '#ff8f00'),
			'light' => array('#f7e3e5', '#fbd2b8', '#fff1e6', '#e9d6f2'),
			'gold' => '#ff8f00',
			'lead' => '#141418'
		),
		'rainbow' => array(
			'glass' => array('#e53935', '#fb8c00', '#fdd835', '#43a047', '#1e88e5', '#5e35b1', '#d81b60', '#00acc1'),
			'light' => array('#fffde7', '#e3f2fd', '#f3e5f5', '#e8f5e9', '#fff3e0'),
			'gold' => '#fdd835',
			'lead' => '#151515'
		)
	);
	private array $artifacts;
	private float $h;
	private array $palette;
	private array $parts = array();

	private \Random\Randomizer $rng;
	private float $w;

	/**
	 * @param int|string $seed
	 * @param string $palette
	 * @param array $artifacts the artifacts to use (empty = all)
	 * @param float $width
	 * @param float $height
	 */
	public function __construct(string|int $seed, string $palette = 'classic', array $artifacts = array(), float $width = 1600, float $height = 1000) {
		$this->rng = new \Random\Randomizer(new \Random\Engine\Mt19937(is_int($seed) ? $seed : crc32((string) $seed)));
		$this->palette = self::PALETTES[$palette] ?? self::PALETTES['classic'];
		$this->artifacts = array_values(array_intersect($artifacts ?: self::ARTIFACTS, self::ARTIFACTS)) ?: self::ARTIFACTS;
		$this->w = $width;
		$this->h = $height;
	}

	/**
	 * Generate the SVG markup.
	 *
	 * @return string
	 */
	public function svg(): string {
		$this->parts = array();
		$w = $this->w;
		$h = $this->h;
		$lead = $this->palette['lead'];
		$border = 34;

		$this->mosaic(0, 0, $w, $h, 9, 7);
		$this->rays($w * 0.18, $h * 0.22, $w * 0.25, 18);
		$this->rays($w * 0.82, $h * 0.22, $w * 0.25, 18);

		// Side panels with medallions and artifacts.
		$side = $w * 0.26;

		foreach (array(array($border + $side * 0.5, 'left'), array($w - $border - $side * 0.5, 'right')) as [$cx, $pos]) {
			$this->rose($cx, $h * 0.22, min($side * 0.42, $h * 0.18), 16);
			$this->medallion($cx, $h * 0.6, min($side * 0.26, $h * 0.11));
			$this->panel($cx - $side * 0.42, $h * 0.76, $side * 0.84, $h * 0.18);
			$this->scatter($cx, $h * 0.42, $side * 0.4, $h * 0.08, 'coins', 3);
		}

		// The center window.
		$this->gothicWindow($w * 0.5, $h * 0.08, $w * 0.4, $h * 0.86);

		// Small cross / star tiles at the top.
		foreach (array(0.36, 0.64) as $x) {
			$this->tile($w * $x, $h * 0.07, 54, $this->pickArtifact(array('star', 'flame', 'leaf')));
		}

		$this->frame($border);

		return sprintf(
			'<svg class="stained-glass__svg" viewBox="0 0 %d %d" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">'
			. '<defs><linearGradient id="sg-shine" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity="0.35"/><stop offset="0.45" stop-color="#fff" stop-opacity="0"/><stop offset="1" stop-color="#000" stop-opacity="0.18"/></linearGradient></defs>'
			. '<rect width="100%%" height="100%%" fill="%s"/>%s'
			. '<rect width="100%%" height="100%%" fill="url(#sg-shine)" pointer-events="none"/></svg>',
			$w,
			$h,
			$lead,
			join('', $this->parts)
		);
	}

	/**
	 * Draw an artifact centered at a point.
	 *
	 * @param string $name
	 * @param float $cx
	 * @param float $cy
	 * @param float $size
	 */
	private function artifact(string $name, float $cx, float $cy, float $size): void {
		$scale = $size / 100;
		$gold = $this->palette['gold'];
		$lead = $this->palette['lead'];
		$light = '#fffaf0';
		$accent = $this->glass();

		$shapes = match ($name) {
			'dove' => "<path d='M12 58 C30 40 48 42 58 50 C66 34 80 26 94 28 C84 36 80 44 78 52 C86 54 92 60 94 66 C76 66 62 70 50 78 C40 84 26 84 16 76 C26 74 32 70 34 66 C26 66 18 63 12 58 Z' fill='$light'/><circle cx='80' cy='35' r='3' fill='$lead'/><path d='M92 30 L100 32 L92 36 Z' fill='$gold'/>",
			'star' => "<path d='M50 6 L61 38 L95 38 L67 58 L78 92 L50 72 L22 92 L33 58 L5 38 L39 38 Z' fill='$gold'/>",
			'sun' => "<circle cx='50' cy='50' r='22' fill='$gold'/>" . join('', array_map(fn ($i) => sprintf("<path d='M50 4 L56 22 L44 22 Z' fill='%s' transform='rotate(%d 50 50)'/>", $gold, $i * 30), range(0, 11))),
			'moon' => "<path d='M62 10 A40 40 0 1 0 90 72 A32 32 0 1 1 62 10 Z' fill='$light'/>",
			'coins' => "<circle cx='40' cy='58' r='22' fill='$gold'/><circle cx='40' cy='58' r='14' fill='none' stroke='$lead' stroke-width='3'/><circle cx='64' cy='38' r='18' fill='$gold'/><circle cx='64' cy='38' r='11' fill='none' stroke='$lead' stroke-width='3'/>",
			'scales' => "<rect x='47' y='16' width='6' height='70' fill='$gold'/><rect x='16' y='24' width='68' height='6' fill='$gold'/><path d='M10 56 L22 30 L34 56 Z M66 56 L78 30 L90 56 Z' fill='$light'/><rect x='34' y='84' width='32' height='8' fill='$gold'/>",
			'book' => "<path d='M8 22 C24 16 40 18 50 26 C60 18 76 16 92 22 L92 82 C76 76 60 78 50 86 C40 78 24 76 8 82 Z' fill='$light'/><path d='M50 26 L50 86' stroke='$lead' stroke-width='4'/><path d='M18 36 H40 M18 48 H40 M60 36 H82 M60 48 H82' stroke='$accent' stroke-width='4'/>",
			'chalice' => "<path d='M22 14 H78 C78 42 66 56 50 58 C34 56 22 42 22 14 Z' fill='$gold'/><rect x='46' y='56' width='8' height='22' fill='$gold'/><path d='M28 92 C30 80 70 80 72 92 Z' fill='$gold'/><circle cx='50' cy='32' r='7' fill='$accent'/>",
			'heart' => "<path d='M50 88 C14 62 6 40 18 24 C30 10 46 16 50 30 C54 16 70 10 82 24 C94 40 86 62 50 88 Z' fill='#d81b3a'/>",
			'key' => "<circle cx='28' cy='50' r='18' fill='none' stroke='$gold' stroke-width='9'/><rect x='44' y='46' width='48' height='9' fill='$gold'/><rect x='72' y='54' width='8' height='14' fill='$gold'/><rect x='84' y='54' width='8' height='10' fill='$gold'/>",
			'crown' => "<path d='M10 76 L16 30 L34 52 L50 20 L66 52 L84 30 L90 76 Z' fill='$gold'/><rect x='10' y='76' width='80' height='10' fill='$gold'/><circle cx='50' cy='62' r='6' fill='$accent'/>",
			'eye' => "<path d='M6 50 C26 22 74 22 94 50 C74 78 26 78 6 50 Z' fill='$light'/><circle cx='50' cy='50' r='16' fill='$accent'/><circle cx='50' cy='50' r='7' fill='$lead'/>",
			'flame' => "<path d='M50 6 C62 26 82 36 76 64 C72 84 58 94 50 94 C42 94 26 86 24 64 C22 46 38 40 40 24 C46 34 48 40 50 6 Z' fill='#f57c00'/><path d='M50 46 C58 58 64 66 60 78 C58 86 52 88 50 88 C46 88 40 84 40 76 C40 66 48 62 50 46 Z' fill='$gold'/>",
			'leaf' => "<path d='M14 86 C14 40 44 12 90 12 C90 58 62 88 14 86 Z' fill='#43a047'/><path d='M18 82 L76 24' stroke='$light' stroke-width='4'/>",
			'hands' => "<path d='M50 12 C42 18 36 40 34 58 L30 88 H48 L50 60 Z' fill='#f2c9a0'/><path d='M50 12 C58 18 64 40 66 58 L70 88 H52 L50 60 Z' fill='#e8b88a'/><path d='M28 86 H72 V96 H28 Z' fill='#c1272d'/>",
			default => ''
		};

		if ($shapes === '') {
			return;
		}

		$this->parts[] = sprintf(
			'<g transform="translate(%s %s) scale(%s)" stroke="%s" stroke-width="3" stroke-linejoin="round">%s</g>',
			round($cx - 50 * $scale, 1),
			round($cy - 50 * $scale, 1),
			round($scale, 3),
			$lead,
			$shapes
		);
	}

	/**
	 * A random float.
	 *
	 * @param float $min
	 * @param float $max
	 * @return float
	 */
	private function float(float $min, float $max): float {
		return $min + ($max - $min) * $this->rng->getFloat(0, 1);
	}

	/**
	 * The outer frame with small alternating tiles.
	 *
	 * @param float $size
	 */
	private function frame(float $size): void {
		$w = $this->w;
		$h = $this->h;
		$lead = $this->palette['lead'];
		$stone = '#8f8b6e';
		$this->parts[] = sprintf(
			'<path d="M0 0H%1$sV%2$sH0Z M%3$s %3$sV%4$sH%5$sV%3$sZ" fill="%6$s" fill-rule="evenodd" stroke="%7$s" stroke-width="6"/>',
			$w,
			$h,
			$size,
			$h - $size,
			$w - $size,
			$stone,
			$lead
		);

		$step = 60;
		$s = $size * 0.42;

		for ($x = $step / 2; $x < $w; $x += $step) {
			foreach (array($size / 2, $h - $size / 2) as $y) {
				$this->parts[] = sprintf('<rect x="%s" y="%s" width="%s" height="%s" fill="%s" stroke="%s" stroke-width="3"/>', round($x - $s / 2, 1), round($y - $s / 2, 1), round($s, 1), round($s, 1), ((int) ($x / $step)) % 2 ? $this->palette['gold'] : $this->glass(), $lead);
			}
		}

		for ($y = $step / 2 + $size; $y < $h - $size; $y += $step) {
			foreach (array($size / 2, $w - $size / 2) as $x) {
				$this->parts[] = sprintf('<rect x="%s" y="%s" width="%s" height="%s" fill="%s" stroke="%s" stroke-width="3"/>', round($x - $s / 2, 1), round($y - $s / 2, 1), round($s, 1), round($s, 1), ((int) ($y / $step)) % 2 ? $this->palette['gold'] : $this->glass(), $lead);
			}
		}
	}

	/**
	 * A random color of the glass palette.
	 *
	 * @return string
	 */
	private function glass(): string {
		return $this->palette['glass'][$this->rng->getInt(0, count($this->palette['glass']) - 1)];
	}

	/**
	 * The pointed arch window in the center with a light brick pattern.
	 *
	 * @param float $cx
	 * @param float $top
	 * @param float $w
	 * @param float $h
	 */
	private function gothicWindow(float $cx, float $top, float $w, float $h): void {
		$lead = $this->palette['lead'];
		$x1 = $cx - $w / 2;
		$x2 = $cx + $w / 2;
		$bottom = $top + $h;
		$springLine = $top + $w * 0.62;
		$arch = sprintf(
			'M %1$s %2$s L %1$s %3$s Q %1$s %4$s %5$s %4$s Q %6$s %4$s %6$s %3$s L %6$s %2$s Z',
			round($x1, 1),
			round($bottom, 1),
			round($springLine, 1),
			round($top, 1),
			round($cx, 1),
			round($x2, 1)
		);

		$id = 'sg-arch-' . $this->rng->getInt(1000, 9999);
		$this->parts[] = sprintf('<clipPath id="%s"><path d="%s"/></clipPath>', $id, $arch);

		// Light brick pattern inside of the arch.
		$bricks = '';
		$bh = $w / 9;
		$bw = $w / 4;
		$row = 0;

		for ($y = $top; $y < $bottom; $y += $bh) {
			$offset = $row % 2 ? -$bw / 2 : 0;

			for ($x = $x1 + $offset; $x < $x2; $x += $bw) {
				$bricks .= sprintf(
					'<rect x="%s" y="%s" width="%s" height="%s" fill="%s" stroke="%s" stroke-width="5"/>',
					round($x, 1),
					round($y, 1),
					round($bw, 1),
					round($bh, 1),
					$this->rng->getInt(0, 5) === 0 ? $this->glass() : $this->light(),
					$lead
				);
			}

			$row++;
		}

		$this->parts[] = sprintf('<g clip-path="url(#%s)">%s</g>', $id, $bricks);

		// Golden tracery: frame, center line and a small rose.
		$gold = $this->palette['gold'];
		$this->parts[] = sprintf('<path d="%s" fill="none" stroke="%s" stroke-width="26"/>', $arch, $lead);
		$this->parts[] = sprintf('<path d="%s" fill="none" stroke="%s" stroke-width="10"/>', $arch, $gold);
		$this->parts[] = sprintf(
			'<line x1="%1$s" y1="%2$s" x2="%1$s" y2="%3$s" stroke="%4$s" stroke-width="14"/><line x1="%1$s" y1="%2$s" x2="%1$s" y2="%3$s" stroke="%5$s" stroke-width="5"/>',
			round($cx, 1),
			round($springLine + $w * 0.1, 1),
			round($bottom, 1),
			$lead,
			$gold
		);

		$this->rose($cx, $top + $w * 0.3, $w * 0.16, 12);
	}

	/**
	 * A random light color.
	 *
	 * @return string
	 */
	private function light(): string {
		return $this->palette['light'][$this->rng->getInt(0, count($this->palette['light']) - 1)];
	}

	/**
	 * A round medallion with an artifact.
	 *
	 * @param float $cx
	 * @param float $cy
	 * @param float $r
	 */
	private function medallion(float $cx, float $cy, float $r): void {
		$lead = $this->palette['lead'];

		$this->parts[] = sprintf('<circle cx="%s" cy="%s" r="%s" fill="%s" stroke="%s" stroke-width="10"/>', round($cx, 1), round($cy, 1), round($r * 1.18, 1), $this->glass(), $lead);
		$this->parts[] = sprintf('<circle cx="%s" cy="%s" r="%s" fill="%s" stroke="%s" stroke-width="7"/>', round($cx, 1), round($cy, 1), round($r, 1), $this->palette['gold'], $lead);
		$this->artifact($this->pickArtifact(), $cx, $cy, $r * 1.45);
	}

	/**
	 * A mosaic of irregular glass panes.
	 *
	 * @param float $x
	 * @param float $y
	 * @param float $w
	 * @param float $h
	 * @param int $cols
	 * @param int $rows
	 */
	private function mosaic(float $x, float $y, float $w, float $h, int $cols, int $rows): void {
		$grid = array();
		$cw = $w / $cols;
		$ch = $h / $rows;

		for ($r = 0; $r <= $rows; $r++) {
			for ($c = 0; $c <= $cols; $c++) {
				$edge = $r === 0 || $c === 0 || $r === $rows || $c === $cols;
				$grid[$r][$c] = array(
					$x + $c * $cw + ($edge ? 0 : $this->float(-0.3, 0.3) * $cw),
					$y + $r * $ch + ($edge ? 0 : $this->float(-0.3, 0.3) * $ch)
				);
			}
		}

		for ($r = 0; $r < $rows; $r++) {
			for ($c = 0; $c < $cols; $c++) {
				$a = $grid[$r][$c];
				$b = $grid[$r][$c + 1];
				$d = $grid[$r + 1][$c];
				$e = $grid[$r + 1][$c + 1];

				// Split some panes diagonally for a more organic look.
				if ($this->rng->getInt(0, 2) === 0) {
					$this->polygon(array($a, $b, $e), $this->glass(), 7);
					$this->polygon(array($a, $e, $d), $this->glass(), 7);
				} else {
					$this->polygon(array($a, $b, $e, $d), $this->glass(), 7);
				}
			}
		}
	}

	/**
	 * A rectangular panel with a sunburst and an artifact.
	 *
	 * @param float $x
	 * @param float $y
	 * @param float $w
	 * @param float $h
	 */
	private function panel(float $x, float $y, float $w, float $h): void {
		$color = $this->glass();
		$this->polygon(array(array($x, $y), array($x + $w, $y), array($x + $w, $y + $h), array($x, $y + $h)), $color, 9);

		$cx = $x + $w / 2;
		$cy = $y + $h / 2;

		for ($i = 0; $i < 12; $i++) {
			$angle = M_PI / 6 * $i;
			$this->parts[] = sprintf(
				'<line x1="%s" y1="%s" x2="%s" y2="%s" stroke="%s" stroke-width="3" stroke-opacity="0.6"/>',
				round($cx, 1),
				round($cy, 1),
				round($cx + cos($angle) * $w * 0.6, 1),
				round($cy + sin($angle) * $h * 0.6, 1),
				$this->palette['gold']
			);
		}

		$this->artifact($this->pickArtifact(), $cx, $cy, min($w, $h) * 0.85);
	}

	/**
	 * Pick a random artifact.
	 *
	 * @param array $preferred
	 * @return string
	 */
	private function pickArtifact(array $preferred = array()): string {
		$pool = array_values(array_intersect($preferred, $this->artifacts)) ?: $this->artifacts;

		return $pool[$this->rng->getInt(0, count($pool) - 1)];
	}

	/**
	 * Add a polygon.
	 *
	 * @param array $points
	 * @param string $fill
	 * @param float $stroke
	 * @param float $opacity
	 */
	private function polygon(array $points, string $fill, float $stroke = 6, float $opacity = 1): void {
		$this->parts[] = sprintf(
			'<polygon points="%s" fill="%s" stroke="%s" stroke-width="%s" stroke-linejoin="round"%s/>',
			join(' ', array_map(fn ($p) => round($p[0], 1) . ',' . round($p[1], 1), $points)),
			$fill,
			$this->palette['lead'],
			$stroke,
			$opacity < 1 ? ' fill-opacity="' . $opacity . '"' : ''
		);
	}

	/**
	 * Golden rays around a point.
	 *
	 * @param float $cx
	 * @param float $cy
	 * @param float $length
	 * @param int $count
	 */
	private function rays(float $cx, float $cy, float $length, int $count): void {
		$gold = $this->palette['gold'];

		for ($i = 0; $i < $count; $i++) {
			$angle = (2 * M_PI / $count) * $i + $this->float(-0.05, 0.05);
			$spread = 0.05;
			$p1 = array($cx + cos($angle - $spread) * $length, $cy + sin($angle - $spread) * $length);
			$p2 = array($cx + cos($angle + $spread) * $length, $cy + sin($angle + $spread) * $length);

			$this->polygon(array(array($cx, $cy), $p1, $p2), $i % 2 ? $gold : $this->light(), 4, 0.85);
		}
	}

	/**
	 * A rose window with segments and an artifact in the center.
	 *
	 * @param float $cx
	 * @param float $cy
	 * @param float $r
	 * @param int $segments
	 */
	private function rose(float $cx, float $cy, float $r, int $segments): void {
		$lead = $this->palette['lead'];
		$a = $this->glass();
		$b = $this->palette['gold'];

		$this->parts[] = sprintf('<circle cx="%s" cy="%s" r="%s" fill="%s" stroke="%s" stroke-width="12"/>', round($cx, 1), round($cy, 1), round($r, 1), $this->light(), $lead);

		for ($i = 0; $i < $segments; $i++) {
			$a1 = (2 * M_PI / $segments) * $i;
			$a2 = (2 * M_PI / $segments) * ($i + 1);
			$this->polygon(array(
				array($cx + cos($a1) * $r * 0.55, $cy + sin($a1) * $r * 0.55),
				array($cx + cos($a1) * $r, $cy + sin($a1) * $r),
				array($cx + cos($a2) * $r, $cy + sin($a2) * $r),
				array($cx + cos($a2) * $r * 0.55, $cy + sin($a2) * $r * 0.55)
			), $i % 2 ? $a : $b, 5);
		}

		$this->parts[] = sprintf('<circle cx="%s" cy="%s" r="%s" fill="%s" stroke="%s" stroke-width="8"/>', round($cx, 1), round($cy, 1), round($r * 0.55, 1), $this->light(), $lead);
		$this->artifact($this->pickArtifact(), $cx, $cy, $r * 0.85);
	}

	/**
	 * Scatter small artifacts.
	 *
	 * @param float $cx
	 * @param float $cy
	 * @param float $rx
	 * @param float $ry
	 * @param string $artifact
	 * @param int $count
	 */
	private function scatter(float $cx, float $cy, float $rx, float $ry, string $artifact, int $count): void {
		if (!in_array($artifact, $this->artifacts, true)) {
			return;
		}

		for ($i = 0; $i < $count; $i++) {
			$this->artifact($artifact, $cx + $this->float(-$rx, $rx), $cy + $this->float(-$ry, $ry), $this->float(40, 70));
		}
	}

	/**
	 * A small square tile with an artifact.
	 *
	 * @param float $cx
	 * @param float $cy
	 * @param float $size
	 * @param string $artifact
	 */
	private function tile(float $cx, float $cy, float $size, string $artifact): void {
		$s = $size / 2;
		$this->polygon(array(array($cx - $s, $cy - $s), array($cx + $s, $cy - $s), array($cx + $s, $cy + $s), array($cx - $s, $cy + $s)), $this->light(), 6);
		$this->artifact($artifact, $cx, $cy, $size * 0.8);
	}
}
