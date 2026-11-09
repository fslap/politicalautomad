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
 * Generates a colorful stained glass window as SVG (no religious symbols).
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
	const ARTIFACTS = array(
		'bird', 'star', 'sun', 'moon', 'coins', 'scales', 'book', 'heart', 'key', 'eye', 'flame', 'leaf',
		'fist', 'megaphone', 'ballot', 'handshake', 'tree', 'gear', 'lightning', 'bike', 'house', 'globe',
		'feather', 'note', 'mountain', 'wave', 'train', 'sprout'
	);
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

	/**
	 * Shapes of the center window.
	 */
	const WINDOWS = array('deco', 'hex', 'round', 'portal', 'arch');
	private array $artifacts;
	private float $h;
	private array $palette;
	private array $parts = array();

	private \Random\Randomizer $rng;

	/**
	 * Symbols that were already used.
	 *
	 * @var string[]
	 */
	private array $used = array();
	private float $w;

	/**
	 * @param int|string $seed
	 * @param string $palette
	 * @param array $artifacts the artifacts to use (empty = all)
	 * @param float $width
	 * @param float $height
	 */
	/**
	 * The shape of the center window.
	 */
	private string $window = 'deco';

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
		$this->used = array();
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
		$this->centerWindow($w * 0.5, $h * 0.07, $w * 0.5, $h * 0.88);

		// Small tiles with symbols at the top.
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
	 * Set the shape of the center window.
	 *
	 * @param string $shape
	 * @return self
	 */
	public function withWindow(string $shape): self {
		$this->window = in_array($shape, self::WINDOWS, true) ? $shape : 'deco';

		return $this;
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
			'bird' => "<path d='M6 44 C30 40 40 46 48 54 C56 40 72 30 96 26 C80 40 70 52 64 62 C76 64 86 70 94 82 C76 78 62 76 52 72 C44 80 30 84 14 82 C26 76 34 68 38 62 C28 56 16 52 6 44 Z' fill='$light'/><circle cx='56' cy='56' r='3' fill='$lead'/>",
			'star' => "<path d='M50 6 L61 38 L95 38 L67 58 L78 92 L50 72 L22 92 L33 58 L5 38 L39 38 Z' fill='$gold'/>",
			'sun' => "<circle cx='50' cy='50' r='22' fill='$gold'/>" . join('', array_map(fn ($i) => sprintf("<path d='M50 4 L56 22 L44 22 Z' fill='%s' transform='rotate(%d 50 50)'/>", $gold, $i * 30), range(0, 11))),
			'moon' => "<path d='M62 10 A40 40 0 1 0 90 72 A32 32 0 1 1 62 10 Z' fill='$light'/>",
			'coins' => "<circle cx='40' cy='58' r='22' fill='$gold'/><circle cx='40' cy='58' r='14' fill='none' stroke='$lead' stroke-width='3'/><circle cx='64' cy='38' r='18' fill='$gold'/><circle cx='64' cy='38' r='11' fill='none' stroke='$lead' stroke-width='3'/>",
			'scales' => "<rect x='47' y='16' width='6' height='70' fill='$gold'/><rect x='16' y='24' width='68' height='6' fill='$gold'/><path d='M10 56 L22 30 L34 56 Z M66 56 L78 30 L90 56 Z' fill='$light'/><rect x='34' y='84' width='32' height='8' fill='$gold'/>",
			'book' => "<path d='M8 22 C24 16 40 18 50 26 C60 18 76 16 92 22 L92 82 C76 76 60 78 50 86 C40 78 24 76 8 82 Z' fill='$light'/><path d='M50 26 L50 86' stroke='$lead' stroke-width='4'/><path d='M18 36 H40 M18 48 H40 M60 36 H82 M60 48 H82' stroke='$accent' stroke-width='4'/>",
			'heart' => "<path d='M50 88 C14 62 6 40 18 24 C30 10 46 16 50 30 C54 16 70 10 82 24 C94 40 86 62 50 88 Z' fill='#d81b3a'/>",
			'key' => "<circle cx='28' cy='50' r='18' fill='none' stroke='$gold' stroke-width='9'/><rect x='44' y='46' width='48' height='9' fill='$gold'/><rect x='72' y='54' width='8' height='14' fill='$gold'/><rect x='84' y='54' width='8' height='10' fill='$gold'/>",
			'eye' => "<path d='M6 50 C26 22 74 22 94 50 C74 78 26 78 6 50 Z' fill='$light'/><circle cx='50' cy='50' r='16' fill='$accent'/><circle cx='50' cy='50' r='7' fill='$lead'/>",
			'flame' => "<path d='M50 6 C62 26 82 36 76 64 C72 84 58 94 50 94 C42 94 26 86 24 64 C22 46 38 40 40 24 C46 34 48 40 50 6 Z' fill='#f57c00'/><path d='M50 46 C58 58 64 66 60 78 C58 86 52 88 50 88 C46 88 40 84 40 76 C40 66 48 62 50 46 Z' fill='$gold'/>",
			'leaf' => "<path d='M14 86 C14 40 44 12 90 12 C90 58 62 88 14 86 Z' fill='#43a047'/><path d='M18 82 L76 24' stroke='$light' stroke-width='4'/>",
			'fist' => "<rect x='36' y='70' width='28' height='26' fill='$accent'/><rect x='26' y='24' width='14' height='30' rx='6' fill='$light'/><rect x='38' y='18' width='14' height='34' rx='6' fill='$light'/><rect x='50' y='18' width='14' height='34' rx='6' fill='$light'/><rect x='62' y='22' width='14' height='32' rx='6' fill='$light'/><path d='M26 46 H78 V64 C78 70 72 74 66 74 H36 C30 74 26 70 26 64 Z' fill='$light'/><path d='M20 42 C30 40 40 46 44 56 L36 62 C30 56 24 52 20 52 Z' fill='$light'/>",
			'megaphone' => "<path d='M12 40 H32 L78 16 V84 L32 60 H12 Z' fill='$gold'/><rect x='30' y='60' width='12' height='26' fill='$light'/><path d='M86 34 Q96 50 86 66' fill='none' stroke='$gold' stroke-width='5'/>",
			'ballot' => "<path d='M36 8 H64 V46 H36 Z' fill='#fff'/><path d='M41 26 L48 33 L59 18' fill='none' stroke='$accent' stroke-width='5'/><rect x='14' y='44' width='72' height='46' fill='$light'/><rect x='30' y='42' width='40' height='6' fill='$lead'/><rect x='38' y='62' width='24' height='10' fill='$gold'/>",
			'handshake' => "<path d='M4 42 L30 34 L50 44 L70 34 L96 42 V62 L72 72 L50 64 L28 72 L4 62 Z' fill='#f2c9a0'/><path d='M30 34 L52 52 L72 38 M40 58 L50 66 M50 54 L60 62' fill='none' stroke='$lead' stroke-width='3'/><rect x='0' y='38' width='12' height='28' fill='$accent'/><rect x='88' y='38' width='12' height='28' fill='$accent'/>",
			'tree' => "<rect x='44' y='60' width='12' height='32' fill='#8d5a2b'/><circle cx='50' cy='38' r='28' fill='#43a047'/><circle cx='32' cy='54' r='16' fill='#2e7d32'/><circle cx='68' cy='54' r='16' fill='#2e7d32'/>",
			'gear' => join('', array_map(fn ($i) => sprintf("<rect x='43' y='4' width='14' height='20' fill='%s' transform='rotate(%d 50 50)'/>", $gold, $i * 45), range(0, 7))) . "<circle cx='50' cy='50' r='30' fill='$gold'/><circle cx='50' cy='50' r='12' fill='$light'/>",
			'lightning' => "<path d='M58 4 L22 56 H46 L38 96 L80 38 H54 Z' fill='$gold'/>",
			'bike' => "<circle cx='24' cy='66' r='18' fill='none' stroke='$light' stroke-width='6'/><circle cx='76' cy='66' r='18' fill='none' stroke='$light' stroke-width='6'/><path d='M24 66 L42 36 H66 L76 66 M42 36 L54 66 H24 M62 26 H72 M38 30 H48' fill='none' stroke='$gold' stroke-width='6'/>",
			'house' => "<path d='M8 50 L50 12 L92 50 Z' fill='$accent'/><rect x='18' y='50' width='64' height='42' fill='$light'/><rect x='42' y='64' width='16' height='28' fill='$gold'/><rect x='24' y='58' width='12' height='12' fill='$gold'/><rect x='64' y='58' width='12' height='12' fill='$gold'/>",
			'globe' => "<circle cx='50' cy='50' r='40' fill='#1e88e5'/><path d='M50 10 C30 30 30 70 50 90 C70 70 70 30 50 10 Z M10 50 H90 M18 30 H82 M18 70 H82' fill='none' stroke='$light' stroke-width='4'/><path d='M30 30 C40 36 44 44 38 52 C30 56 26 46 30 30 Z M60 56 C70 52 78 58 74 70 C66 74 58 66 60 56 Z' fill='#43a047'/>",
			'feather' => "<path d='M86 8 C42 14 20 50 18 92 C42 72 72 52 86 8 Z' fill='$light'/><path d='M18 92 L72 26' stroke='$accent' stroke-width='4'/>",
			'note' => "<ellipse cx='30' cy='78' rx='16' ry='12' fill='$gold'/><ellipse cx='74' cy='68' rx='16' ry='12' fill='$gold'/><path d='M44 78 V22 L88 12 V68' fill='none' stroke='$gold' stroke-width='7'/>",
			'mountain' => "<path d='M2 90 L36 28 L54 58 L68 40 L98 90 Z' fill='$accent'/><path d='M36 28 L45 44 L27 44 Z M68 40 L74 50 L62 50 Z' fill='#fff'/>",
			'wave' => "<path d='M4 58 C20 38 36 38 50 58 C64 78 80 78 96 58 V94 H4 Z' fill='#1e88e5'/><path d='M4 44 C20 24 36 24 50 44 C64 64 80 64 96 44' fill='none' stroke='$light' stroke-width='6'/>",
			'train' => "<rect x='14' y='18' width='72' height='56' rx='10' fill='$accent'/><rect x='22' y='28' width='24' height='18' fill='$light'/><rect x='54' y='28' width='24' height='18' fill='$light'/><circle cx='30' cy='80' r='8' fill='$lead'/><circle cx='70' cy='80' r='8' fill='$lead'/><path d='M8 94 H92' stroke='$gold' stroke-width='5'/>",
			'sprout' => "<path d='M50 90 V50' stroke='#2e7d32' stroke-width='6'/><path d='M50 58 C30 58 16 44 14 22 C36 22 48 36 50 58 Z' fill='#66bb6a'/><path d='M50 48 C66 48 82 36 86 16 C64 16 52 30 50 48 Z' fill='#43a047'/><rect x='28' y='86' width='44' height='8' fill='#8d5a2b'/>",
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
	 * The center window (the bullet points sit on top of it). Its shape and the pattern
	 * inside depend on the window type.
	 *
	 * @param float $cx
	 * @param float $top
	 * @param float $w
	 * @param float $h
	 */
	private function centerWindow(float $cx, float $top, float $w, float $h): void {
		$lead = $this->palette['lead'];
		$gold = $this->palette['gold'];
		$x1 = $cx - $w / 2;
		$x2 = $cx + $w / 2;
		$bottom = $top + $h;
		$cy = $top + $h / 2;
		$r = fn (float $v) => round($v, 1);

		$path = match ($this->window) {
			'round' => sprintf('M %s %s A %s %s 0 1 1 %s %s A %s %s 0 1 1 %s %s Z', $r($cx - min($w, $h) / 2), $r($cy), $r(min($w, $h) / 2), $r(min($w, $h) / 2), $r($cx + min($w, $h) / 2), $r($cy), $r(min($w, $h) / 2), $r(min($w, $h) / 2), $r($cx - min($w, $h) / 2), $r($cy)),
			'hex' => sprintf('M %s %s L %s %s L %s %s L %s %s L %s %s L %s %s Z', $r($x1), $r($cy), $r($x1 + $w * 0.22), $r($top), $r($x2 - $w * 0.22), $r($top), $r($x2), $r($cy), $r($x2 - $w * 0.22), $r($bottom), $r($x1 + $w * 0.22), $r($bottom)),
			'portal' => sprintf('M %1$s %2$s L %1$s %3$s A %4$s %4$s 0 0 1 %5$s %3$s L %5$s %2$s Z', $r($x1), $r($bottom), $r($top + $w / 2), $r($w / 2), $r($x2)),
			'arch' => sprintf('M %1$s %2$s L %1$s %3$s Q %1$s %4$s %5$s %4$s Q %6$s %4$s %6$s %3$s L %6$s %2$s Z', $r($x1), $r($bottom), $r($top + $w * 0.62), $r($top), $r($cx), $r($x2)),
			// Art deco: stepped top, the middle step is the highest.
			default => sprintf(
				'M %s %s V %s H %s V %s H %s V %s H %s V %s H %s V %s H %s V %s Z',
				$r($x1),
				$r($bottom),
				$r($top + $h * 0.16),
				$r($x1 + $w * 0.14),
				$r($top + $h * 0.07),
				$r($x1 + $w * 0.3),
				$r($top),
				$r($x2 - $w * 0.3),
				$r($top + $h * 0.07),
				$r($x2 - $w * 0.14),
				$r($top + $h * 0.16),
				$r($x2),
				$r($bottom)
			)
		};

		$id = 'sg-window-' . $this->rng->getInt(1000, 9999);
		$this->parts[] = sprintf('<clipPath id="%s"><path d="%s"/></clipPath>', $id, $path);

		$inner = match ($this->window) {
			'round' => $this->ringPattern($cx, $cy, min($w, $h) / 2),
			'hex' => $this->honeycombPattern($x1, $top, $w, $h),
			'deco' => $this->fanPattern($cx, $bottom, $w, $h),
			default => $this->panePattern($x1, $top, $w, $h)
		};

		$this->parts[] = sprintf('<g clip-path="url(#%s)">%s</g>', $id, $inner);
		$this->parts[] = sprintf('<path d="%s" fill="none" stroke="%s" stroke-width="26"/>', $path, $lead);
		$this->parts[] = sprintf('<path d="%s" fill="none" stroke="%s" stroke-width="10"/>', $path, $gold);
	}

	/**
	 * Light rays fanning out from the bottom (art deco).
	 *
	 * @param float $cx
	 * @param float $bottom
	 * @param float $w
	 * @param float $h
	 * @return string
	 */
	private function fanPattern(float $cx, float $bottom, float $w, float $h): string {
		$out = '';
		$count = 22;
		$length = sqrt($w * $w / 4 + $h * $h) * 1.1;

		for ($i = 0; $i < $count; $i++) {
			$a1 = M_PI + M_PI * $i / $count;
			$a2 = M_PI + M_PI * ($i + 1) / $count;
			$out .= sprintf(
				'<polygon points="%s,%s %s,%s %s,%s" fill="%s" stroke="%s" stroke-width="5"/>',
				round($cx, 1),
				round($bottom, 1),
				round($cx + cos($a1) * $length, 1),
				round($bottom + sin($a1) * $length, 1),
				round($cx + cos($a2) * $length, 1),
				round($bottom + sin($a2) * $length, 1),
				$this->rng->getInt(0, 7) === 0 ? $this->glass() : $this->light(),
				$this->palette['lead']
			);
		}

		// Arcs across the rays.
		foreach (array(0.35, 0.6, 0.85) as $f) {
			$out .= sprintf('<circle cx="%s" cy="%s" r="%s" fill="none" stroke="%s" stroke-width="6"/>', round($cx, 1), round($bottom, 1), round($h * $f, 1), $this->palette['lead']);
		}

		return $out;
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
	 * A honeycomb of small hexagons (hex window).
	 *
	 * @param float $x
	 * @param float $y
	 * @param float $w
	 * @param float $h
	 * @return string
	 */
	private function honeycombPattern(float $x, float $y, float $w, float $h): string {
		$out = '';
		$size = $w / 10;
		$dx = $size * 1.5;
		$dy = $size * sqrt(3);
		$col = 0;

		for ($px = $x - $size; $px < $x + $w + $size; $px += $dx) {
			$offset = $col % 2 ? $dy / 2 : 0;

			for ($py = $y - $dy + $offset; $py < $y + $h + $dy; $py += $dy) {
				$points = array();

				for ($k = 0; $k < 6; $k++) {
					$a = M_PI / 3 * $k;
					$points[] = round($px + cos($a) * $size, 1) . ',' . round($py + sin($a) * $size, 1);
				}

				$out .= sprintf('<polygon points="%s" fill="%s" stroke="%s" stroke-width="5"/>', join(' ', $points), $this->rng->getInt(0, 6) === 0 ? $this->glass() : $this->light(), $this->palette['lead']);
			}

			$col++;
		}

		return $out;
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
	 * Panes in rows (portal and arch windows).
	 *
	 * @param float $x
	 * @param float $y
	 * @param float $w
	 * @param float $h
	 * @return string
	 */
	private function panePattern(float $x, float $y, float $w, float $h): string {
		$out = '';
		$bh = $w / 9;
		$bw = $w / 4;
		$row = 0;

		for ($py = $y; $py < $y + $h; $py += $bh) {
			$offset = $row % 2 ? -$bw / 2 : 0;

			for ($px = $x + $offset; $px < $x + $w; $px += $bw) {
				$out .= sprintf(
					'<rect x="%s" y="%s" width="%s" height="%s" fill="%s" stroke="%s" stroke-width="5"/>',
					round($px, 1),
					round($py, 1),
					round($bw, 1),
					round($bh, 1),
					$this->rng->getInt(0, 6) === 0 ? $this->glass() : $this->light(),
					$this->palette['lead']
				);
			}

			$row++;
		}

		return $out;
	}

	/**
	 * Pick a random artifact.
	 *
	 * @param array $preferred
	 * @return string
	 */
	private function pickArtifact(array $preferred = array()): string {
		$pool = array_values(array_intersect($preferred, $this->artifacts)) ?: $this->artifacts;
		// Symbols are only repeated when all symbols of the pool were used.
		$unused = array_values(array_diff($pool, $this->used)) ?: $pool;
		$artifact = $unused[$this->rng->getInt(0, count($unused) - 1)];
		$this->used[] = $artifact;

		return $artifact;
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
	 * Concentric rings with spokes (round window).
	 *
	 * @param float $cx
	 * @param float $cy
	 * @param float $radius
	 * @return string
	 */
	private function ringPattern(float $cx, float $cy, float $radius): string {
		$out = '';
		$rings = 5;

		for ($i = $rings; $i >= 1; $i--) {
			$out .= sprintf('<circle cx="%s" cy="%s" r="%s" fill="%s" stroke="%s" stroke-width="6"/>', round($cx, 1), round($cy, 1), round($radius * $i / $rings, 1), $i === $rings ? $this->glass() : $this->light(), $this->palette['lead']);
		}

		for ($i = 0; $i < 16; $i++) {
			$a = 2 * M_PI * $i / 16;
			$out .= sprintf('<line x1="%s" y1="%s" x2="%s" y2="%s" stroke="%s" stroke-width="5"/>', round($cx + cos($a) * $radius * 0.2, 1), round($cy + sin($a) * $radius * 0.2, 1), round($cx + cos($a) * $radius, 1), round($cy + sin($a) * $radius, 1), $this->palette['lead']);
		}

		return $out;
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
