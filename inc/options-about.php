<?php
/**
 * Options page: About — mirrors ../jbnewgen/src/globals/About.ts.
 * Default values copied verbatim from ../jbnewgen/src/lib/about-copy.ts and
 * ../jbnewgen/src/lib/content.ts (teamIntro) — the same live copy Payload uses
 * as its own field defaults.
 */

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

add_action( 'carbon_fields_register_fields', function () {

	Container::make( 'theme_options', __( 'About', 'jbnewgen' ) )
		->set_page_menu_title( __( 'About Pages', 'jbnewgen' ) )
		->set_icon( 'dashicons-info-outline' )

		->add_tab( __( 'The Company', 'jbnewgen' ), array(

			Field::make( 'textarea', 'company_hero_body', __( 'Opening paragraph', 'jbnewgen' ) )
				->set_default_value( "We are not a generic consulting firm. We are the India team you don't have yet - with 35 years of market experience, a live network of 7,000+ channel partners, and a track record of driving 200–500% revenue growth for the organisations we work with." ),

			Field::make( 'text', 'company_who_title', __( 'Section heading', 'jbnewgen' ) )
				->set_default_value( 'The India expertise that global startups need - and rarely find in one place' ),

			Field::make( 'complex', 'company_who_intro', __( 'Section paragraphs', 'jbnewgen' ) )
				->set_help_text( __( 'One row per paragraph.', 'jbnewgen' ) )
				->add_fields( array(
					Field::make( 'textarea', 'text', __( 'Paragraph', 'jbnewgen' ) )->set_required( true ),
				) )
				->set_header_template( '<%- text %>' )
				->set_default_value( array(
					array( 'text' => "JB NewGen Enterprises is a Mumbai-based market entry and GTM consultancy built specifically to help US, EU, and global startups establish, operate, and scale in India. We combine deep India market knowledge with the accountability and reporting standards that global founders and investors expect." ),
					array( 'text' => "We are not a research firm that hands you a market report. We are not an agency that runs campaigns in isolation. We are an execution partner - the organisation that sits alongside your team from your first conversation about India to your first hundred customers in the market." ),
					array( 'text' => "India is one of the world's most consequential growth opportunities. It is also one of the most structurally complex markets to enter alone. That complexity - the multi-tier distribution landscape, the regulatory environment, the relationship-driven business culture, the India-specific digital infrastructure - is exactly what we exist to navigate on your behalf." ),
				) ),

			Field::make( 'complex', 'company_stats', __( 'The four numbers', 'jbnewgen' ) )
				->set_max( 4 )
				->set_help_text( __( 'Four is the designed maximum.', 'jbnewgen' ) )
				->add_fields( array(
					Field::make( 'text', 'stat_value', __( 'Number', 'jbnewgen' ) )
						->set_required( true )
						->set_help_text( __( 'e.g. "7,000+". This is the part that counts up on screen.', 'jbnewgen' ) ),
					Field::make( 'textarea', 'desc', __( 'Description', 'jbnewgen' ) )->set_required( true ),
				) )
				->set_header_template( '<%- stat_value %>' )
				->set_default_value( array(
					array( 'stat_value' => '35+', 'desc' => 'Years of India market experience led by CEO Joyjeet Bose across Tata, Bharti, and enterprise technology sectors' ),
					array( 'stat_value' => '7,000+', 'desc' => 'Channel partners in our active India network across Tier 1, Tier 2, and Tier 3 markets - available to your launch from Day 1' ),
					array( 'stat_value' => '30,000+', 'desc' => 'Retail touchpoints across our distribution ecosystem - built over decades of on-ground market development' ),
					array( 'stat_value' => '200–500%', 'desc' => 'Revenue growth delivered across multiple organisations through our GTM and channel development frameworks' ),
				) ),

		) )

		->add_tab( __( 'Our CEO', 'jbnewgen' ), array(

			Field::make( 'text', 'ceo_role', __( 'Title (the small orange badge)', 'jbnewgen' ) )
				->set_default_value( 'Founder & CEO - JB NewGen Enterprises' ),

			Field::make( 'image', 'ceo_photo', __( 'Photo', 'jbnewgen' ) )
				->set_help_text( __( 'Portrait orientation, cropped to 3:4 from the top. Leave blank to keep the current photo.', 'jbnewgen' ) ),

			Field::make( 'text', 'ceo_linkedin', __( 'LinkedIn profile URL', 'jbnewgen' ) )
				->set_attribute( 'type', 'url' )
				->set_default_value( 'https://www.linkedin.com/in/joyjeet-bose' )
				->set_help_text( __( 'Sits behind the "+" badge on the photo. Leave blank and the badge will not appear.', 'jbnewgen' ) ),

			Field::make( 'text', 'ceo_hero_subtitle', __( 'Headline', 'jbnewgen' ) )
				->set_default_value( "35 Years Building India's Markets. Now Building Your India Entry." ),

			Field::make( 'textarea', 'ceo_hero_body', __( 'Opening paragraph', 'jbnewgen' ) )
				->set_default_value( "Joyjeet has spent three and a half decades doing what most India market entry consultants only talk about - building national channel networks from zero, driving revenue growth of 200–500% across multiple organisations, and establishing distribution infrastructure across India's most complex market segments." ),

			Field::make( 'complex', 'ceo_creds', __( 'Credential numbers', 'jbnewgen' ) )
				->set_max( 4 )
				->set_help_text( __( 'Four is the designed maximum.', 'jbnewgen' ) )
				->add_fields( array(
					Field::make( 'text', 'stat_value', __( 'Number', 'jbnewgen' ) )->set_required( true ),
					Field::make( 'text', 'label', __( 'Caption', 'jbnewgen' ) )->set_required( true ),
				) )
				->set_header_template( '<%- stat_value %> — <%- label %>' )
				->set_default_value( array(
					array( 'stat_value' => '35+', 'label' => 'Years of India market experience' ),
					array( 'stat_value' => '7,000+', 'label' => 'Channel partners built and managed' ),
					array( 'stat_value' => '200–500%', 'label' => 'Revenue growth delivered' ),
					array( 'stat_value' => '30,000+', 'label' => 'Retail touchpoints established' ),
				) ),

			Field::make( 'textarea', 'ceo_profile_p1', __( 'Profile — first paragraph', 'jbnewgen' ) )
				->set_default_value( "Joyjeet Bose's career began in India's telecommunications sector at a time when the country was in the early stages of its digital revolution. Over the three and a half decades that followed, he built and led commercial organisations that took products from concept to national distribution - navigating India's regulatory complexity, relationship-driven business culture, and extraordinary geographic and demographic diversity at every step." ),

			Field::make( 'textarea', 'ceo_profile_quote', __( 'Pull quote', 'jbnewgen' ) )
				->set_help_text( __( 'The large quoted passage set apart from the body text.', 'jbnewgen' ) )
				->set_default_value( "India is not one market. It's 28 states, 8 union territories, hundreds of languages, multiple tiers of distribution, and buyers who make decisions in ways that surprise every foreign company that hasn't done the work of understanding them. I've spent 35 years doing that work." ),

			Field::make( 'text', 'ceo_profile_quote_attr', __( 'Pull quote — attribution', 'jbnewgen' ) )
				->set_default_value( 'Joyjeet Bose, Founder & CEO' ),

			Field::make( 'complex', 'ceo_profile_rest', __( 'Profile — remaining paragraphs', 'jbnewgen' ) )
				->set_help_text( __( 'One row per paragraph.', 'jbnewgen' ) )
				->add_fields( array(
					Field::make( 'textarea', 'text', __( 'Paragraph', 'jbnewgen' ) )->set_required( true ),
				) )
				->set_header_template( '<%- text %>' )
				->set_default_value( array(
					array( 'text' => "At Tata Teleservices, Joyjeet built and scaled sales organisations operating across multiple Indian states - establishing the channel infrastructure, distributor relationships, and retail networks that gave Tata's telecom products national reach. At Hexacom India Ltd (part of the Airtel group) and Bharti BT Internet Ltd, he led commercial functions through periods of rapid market expansion, building distribution models that balanced Tier 1 city penetration with Tier 2 and Tier 3 market development." ),
					array( 'text' => "Across these engagements and more, Joyjeet built channel networks totalling 7,000+ partners and 30,000+ retail touchpoints - consistently driving revenue growth of 200% to 500% through structured GTM frameworks, disciplined channel management, and the relationship-building that defines how business actually gets done in India." ),
					array( 'text' => "He founded JB NewGen Enterprises to make this expertise accessible to global startups - organisations that have extraordinary products and serious India ambitions, but need an on-ground partner who has already done the work of understanding the market they're about to enter." ),
				) ),

		) )

		->add_tab( __( 'Core Team', 'jbnewgen' ), array(

			Field::make( 'html', 'team_intro_note' )
				->set_html( '<p>' . esc_html__( 'The people themselves are edited under the Core Team post type in the sidebar menu.', 'jbnewgen' ) . '</p>' ),

			Field::make( 'text', 'team_intro_title', __( 'Headline under the page title', 'jbnewgen' ) )
				->set_default_value( 'We draw on our global network to assemble a team of experts.' ),

			Field::make( 'textarea', 'team_intro_body', __( 'Supporting paragraph', 'jbnewgen' ) )
				->set_default_value( 'We also bring a strong interest in coaching and capability building, with an emphasis on emotional intelligence and effective stakeholder relationships.' ),

		) );

} );
