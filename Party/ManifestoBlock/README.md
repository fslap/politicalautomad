# ManifestoBlock Component

Political program / manifesto sections component.

Follows exact same rules as ArtMotivation:
- Self-contained in Party/ManifestoBlock/
- Array data passing only
- Mustache first via AbstractMultiBlock
- Templates self-contained (imports removed)
- Specific CSS only
- Full stump + license header

Register in Party/All.php and use via All::render('ManifestoBlock', $data) or new ManifestoBlock($data)->render($data)