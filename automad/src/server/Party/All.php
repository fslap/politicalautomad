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

namespace Automad\Party;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Registry of self-contained party components under automad/src/server/Party/.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
final class All {
	/** @var array<string, class-string> */
	public static array $components = array(
		'ArtMotivation' => ArtMotivation\ArtMotivation::class,
		'CardRow' => CardRow\CardRow::class,
		'CaseLeafletRegion' => CaseLeafletRegion\CaseLeafletRegion::class,
		'ContactCloser' => ContactCloser\ContactCloser::class,
		'DocumentCard' => DocumentCard\DocumentCard::class,
		'Documents' => Documents\Documents::class,
		'DonateBlock' => DonateBlock\DonateBlock::class,
		'EventBlock' => EventBlock\EventBlock::class,
		'Form' => Form\Form::class,
		'Gallery' => Gallery\Gallery::class,
		'GazeBreaker' => GazeBreaker\GazeBreaker::class,
		'HeroTitle' => HeroTitle\HeroTitle::class,
		'HouseRow' => HouseRow\HouseRow::class,
		'HoverCorner' => HoverCorner\HoverCorner::class,
		'HoverDecor' => HoverDecor\HoverDecor::class,
		'Image' => Image\Image::class,
		'InfoStack' => InfoStack\InfoStack::class,
		'LandscapeScene' => LandscapeScene\LandscapeScene::class,
		'LeafletMap' => LeafletMap\LeafletMap::class,
		'ListCard' => ListCard\ListCard::class,
		'Markdown' => Markdown\Markdown::class,
		'MarkdownViewer' => MarkdownViewer\MarkdownViewer::class,
		'MusicAnchor' => MusicAnchor\MusicAnchor::class,
		'MusicPlayer' => MusicPlayer\MusicPlayer::class,
		'NextArrow' => NextArrow\NextArrow::class,
		'PartyHeader' => PartyHeader\PartyHeader::class,
		'Quote' => Quote\Quote::class,
		'RegionScroll' => RegionScroll\RegionScroll::class,
		'RollTiles' => RollTiles\RollTiles::class,
		'ScrollElement' => ScrollElement\ScrollElement::class,
		'ScrollReference' => ScrollReference\ScrollReference::class,
		'SecurityConcept' => SecurityConcept\SecurityConcept::class,
		'SideMenuText' => SideMenuText\SideMenuText::class,
		'SkylineBreaker' => SkylineBreaker\SkylineBreaker::class,
		'SlideCards' => SlideCards\SlideCards::class,
		'SlideDocument' => SlideDocument\SlideDocument::class,
		'SlideFirst' => SlideFirst\SlideFirst::class,
		'SlideListForm' => SlideListForm\SlideListForm::class,
		'SlideMap' => SlideMap\SlideMap::class,
		'SlideViewer' => SlideViewer\SlideViewer::class,
		'Split' => Split\Split::class,
		'StainedGlass' => StainedGlass\StainedGlass::class,
		'TeamBlock' => TeamBlock\TeamBlock::class,
		'TextSplit' => TextSplit\TextSplit::class,
		'VerticalSlider' => VerticalSlider\VerticalSlider::class,
		'VideoCommentary' => VideoCommentary\VideoCommentary::class,
		'ViewboxHover' => ViewboxHover\ViewboxHover::class,
		'WaterAnimation' => WaterAnimation\WaterAnimation::class,
		'WaveLine' => WaveLine\WaveLine::class,
		'WpButton' => WpButton\WpButton::class,
		'WpColumns' => WpColumns\WpColumns::class,
		'WpGroup' => WpGroup\WpGroup::class,
		'WpHeading' => WpHeading\WpHeading::class,
		'WpImage' => WpImage\WpImage::class,
		'WpParagraph' => WpParagraph\WpParagraph::class,
		'WpQuote' => WpQuote\WpQuote::class,
		'WpSeparator' => WpSeparator\WpSeparator::class,
		'WpSpacer' => WpSpacer\WpSpacer::class,
	);

	public static function getAll(): array {
		return self::$components;
	}

	public static function make(string $name): ?object {
		if (!isset(self::$components[$name])) {
			return null;
		}

		return new (self::$components[$name])();
	}
}
