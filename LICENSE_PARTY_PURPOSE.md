# LICENSE_PARTY_PURPOSE.md

This is a template license for the political party components in Automad.

For full license see original in politicalpartysite or Automad.

(c) 2026 Florian Leon Steenbuck - https://kil.ls https://fslap.de

Purpose: To enable political parties to have self-contained, reusable components for their websites built on Automad CMS, ported from Twig/Mustache based components in v2- branches.

No data contents (like afd.php, military.php) are included in this template to keep it generic. 

## Usage
- Components live self-contained in Party/<ComponentName>/
- Extend AbstractMultiBlock
- Use array passed data, not $this references for data
- Mustache first in construct/init
- Templates (twig/mustache) are self-contained, imports/references removed
- For assets: only component specific (e.g. leaflet if needed and not merged in theme base)
- For lib: only if highly self-dependent component, copied from v2- branch example

See README.md for details.