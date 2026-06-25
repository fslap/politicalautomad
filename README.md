# politicalautomad

**International Political Party Zone for all Partys also Locally**

This repository provides self-contained, reusable components for political party websites using the Automad flat-file CMS.

It ports and adapts components from https://github.com/fslap/politicalpartysite (v2- branches like v2-artmotivation, based on v2-political) into the Automad ecosystem under the `Party/` directory.

## Key Adaptations (as per requirements)

- **Self-containing components**: Each component lives entirely in its own subdirectory under `Party/`, e.g. `Party/ArtMotivation/`. Includes its PHP class, templates (Twig + Mustache), assets (CSS/JS specific only), optional lib/ if highly dependent.

- **Template based**: 
  - Twig templates had reference imports (e.g. {% import 'macros.twig' %}) removed to make self-contained.
  - Mustache templates supported and used for rendering.
  - Looked at v2-artmotivation structure for behavior (split view concept, array data flow).

- **New component style - Array passing**:
  - Removed `$this` references that only referenced internal data.
  - All data handling uses passed `$data` arrays to methods (e.g. `render(array $data)` uses `$data['key']` directly).
  - No reliance on object properties for the "this referencing data".

- **Base class**:
  - `Party/AbstractMultiBlock.php` (and "All" handler).
  - Different base now (not holding full class building, only up to two core methods + copied all methods from website repo layer).
  - Copied methods: init, build, render, processBlocks, validateData, getTemplate, etc. (adapted for Automad + Mustache).
  - Mustache first: construct/init sets up Mustache_Engine before extending/parent.

- **Assets & Styles**:
  - Theme base files from other roots NOT copied.
  - Only component-specific (e.g. if leaflet map needed for a component, include leaflet assets only if not already in merged theme base file).
  - For highly self-dependent: include `lib/` dir exactly as in v2- component branches.

- **NO DATA copied**:
  - Data-driven files like `afd.php`, `military.php` etc. are NOT copied with contents.
  - `Party/data/` kept empty or with placeholders only. No actual party-specific data included.

- **Branches**:
  - Components developed as branches in style of politicalpartysite: e.g. `v2-artmotivation`, `v2-political` base.
  - This master contains the merged template of all (for convenience). To follow workflow, create/checkout v2-<component> branches for new work.
  - Example: `git checkout -b v2-artmotivation` would contain only ArtMotivation self-contained.

- **All files include**:
  - Automad available stump (the ASCII art header)
  - Artwork (the political zone diagram)
  - License text reference to LICENSE_PARTY_PURPOSE.md
  - Copyright 2026 Florian Leon Steenbuck

## Structure

```
politicalautomad/
├── .git/
├── Party/
│   ├── AbstractMultiBlock.php
│   ├── All.php
│   ├── ArtMotivation/
│   │   ├── ArtMotivation.php
│   │   ├── templates/
│   │   │   ├── default.mustache
│   │   │   └── default.twig
│   │   ├── assets/
│   │   │   └── artmotivation.css
│   │   └── README.md
│   ├── ManifestoBlock/     ← same full pattern applied
│   ├── SupportBlock/       ← same full pattern applied
│   ├── data/          # empty, no contents copied (afd/military etc. never included)
│   └── lib/           # for self-dep components only (e.g. leaflet if needed)
├── shared/
├── templates/
├── config/
├── pages/             # no data pages
├── LICENSE_PARTY_PURPOSE.md
├── README.md
└── composer.json
```

## Usage in Automad

1. Place this repo content (or symlink Party/) into your Automad installation.
2. Components auto-available via namespace `Automad\Party\...`
3. In Automad templates or pages, use e.g. `ArtMotivation` block with data array.
4. Example data pass: array with keys for split view left/right content, images, etc.

## Example Component: ArtMotivation

Represents a split view of concept to be shown (artistic/political motivation).

- Extends AbstractMultiBlock
- Mustache construct/init first
- Uses passed data array only
- Self-contained templates without external imports

See `Party/ArtMotivation/ArtMotivation.php` and its templates.

## Components — Full pattern applied to many components ("every component" template)

The **exact same strict rules** have been applied to all of these:

- **ArtMotivation** — split view concept
- **ManifestoBlock** — party program / sections
- **SupportBlock** — donate / join / volunteer CTAs
- **EventBlock** — upcoming events
- **TeamBlock** — team / candidates grid
- **DonateBlock** — donation amounts & CTA
- **StatementBlock** — key political statements / positions

**This is now a comprehensive, repeatable template** for porting every remaining component from the v2- branches of politicalpartysite.

For any new component from the v2- branches of politicalpartysite:

1. Create `Party/NewComponentName/`
2. `NewComponentName.php` extending `AbstractMultiBlock` (mustache first, array $passedData only, full stump header)
3. `templates/default.mustache` + `default.twig` (self-contained, **remove all import/reference statements**)
4. `assets/newcomponentname.css` (specific only — never copy full theme base)
5. `README.md` documenting it
6. Register in `Party/All.php`
7. If highly self-dependent → add `lib/` with only the needed files (e.g. leaflet assets if not already merged)

**Never copy data contents** (afd.php, military.php etc. stay out).

Create a `v2-newcomponentname` branch for it (as done with v2-artmotivation).

This gives you a clean, consistent, production-ready skeleton for the full port.

## Full Component Inventory from politicalpartysite (now prepared in Party/)

Every component that exists in `github.com/fslap/politicalpartysite` `Components/` has a dedicated folder in `Party/`:

**Political / Visual components (full pattern applied where relevant):**
ArtMotivation, Split, Gallery, Form, Quote, Documents, PartyHeader, SecurityConcept, SkylineBreaker, VerticalSlider, ViewboxHover, WaterAnimation, LandscapeScene, RegionScroll, ScrollElement, ScrollReference, MusicPlayer, Markdown, Image, LeafletMap, CaseLeafletRegion

**WordPress block adapters (prepared as stubs):**
WpButton, WpColumns, WpGroup, WpHeading, WpImage, WpParagraph, WpQuote, WpSeparator, WpSpacer

The structure + .gitkeep files are committed. Drop the adapted real code into these folders following the Porting Guide above.

## License & Copyright

See LICENSE_PARTY_PURPOSE.md

(c) 2026 by Florian Leon Steenbuck
https://kil.ls https://fslap.de
https://north.sbdp.ro etc.

For Automad integration see original Automad license too.