# ArtMotivation Component

Self-contained political/art motivation split-view component.

**Adapted from**: v2-artmotivation branch of politicalpartysite

**Rules followed**:
- Array data passing only
- Mustache construct/init in base
- Templates self-contained (no import refs in twig)
- Mustache + Twig support
- Specific CSS only (no full theme base)
- No data files contents (no afd/military)

## Data Example (passed as array)

```php
$data = [
    'title' => 'Why We Fight',
    'left' => ['title' => 'Art', 'content' => '...', 'image' => '...'],
    'right' => ['title' => 'Politics', 'content' => '...'],
    'cta' => 'Get Involved'
];

$component = new \Automad\Party\ArtMotivation\ArtMotivation($data);
echo $component->render($data);  // note: pass data again for render
```

Or via All::render('ArtMotivation', $data);

See parent AbstractMultiBlock for more methods.