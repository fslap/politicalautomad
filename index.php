<?php
/*
 * politicalautomad - entry point template
 * (c) 2026 Florian Leon Steenbuck
 */

require __DIR__ . '/vendor/autoload.php'; // or Automad bootstrap

// Example usage of components
use Automad\Party\All;

echo "<h1>PoliticalAutomad Template Ready</h1>";

// Demo render (would be in Automad template context)
$data = [
    'title' => 'Art & Political Motivation',
    'left' => ['title' => 'Creative Vision', 'content' => 'Art inspires change.', 'image' => 'https://picsum.photos/id/1015/600/300'],
    'right' => ['title' => 'Political Action', 'content' => 'Turn ideas into policy.', 'image' => 'https://picsum.photos/id/201/600/300'],
    'cta' => 'Become Active'
];

echo All::render('ArtMotivation', $data);

echo "<p><em>This is a template port. Full components from v2- branches to be added following the rules.</em></p>";