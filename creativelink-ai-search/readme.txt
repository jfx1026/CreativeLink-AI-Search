=== CreativeLink AI Search ===
Contributors: johnfreeborn
Tags: ai, search, chat, design, links
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered chat widget to search through the Weekly Creative Links archive.

== Description ==

CreativeLink AI Search adds a floating chat widget to your WordPress site that allows visitors to search through over 3500 curated design links from the Weekly Creative Links newsletter archive using AI.

Features:

* AI-powered search using natural language
* Floating chat widget that works on any page
* Deep links to specific resources within archive posts
* Mobile responsive design
* Customizable button color

== Installation ==

1. Upload the `creativelink-ai-search` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The chat widget will automatically appear on your site

== Configuration ==

Go to Settings > CreativeLink AI to customize:

* Button color

== External Services ==

This plugin connects to an external AI service to process search queries. When a visitor uses the chat widget:

**Service:** CreativeLink AI Backend (hosted on Cloudflare Workers)
**Endpoint:** https://design-links-chat.jfx1026.workers.dev

**Data transmitted:**
* The visitor's search query
* Conversation history within the current session
* Post titles, excerpts, URLs, and dates from your site (based on your search scope settings)

**Why this is needed:**
The external service processes natural language queries using AI and returns relevant results from your content.

**Data retention:**
Queries are processed in real-time and are not stored permanently on the external service.

By using this plugin, you agree to the use of this external service. If you have concerns about data privacy, please review your site's privacy policy and consider informing your visitors about the use of AI-powered search.

== Privacy ==

This plugin does not collect or store personal data directly. However, visitor search queries are sent to an external AI service for processing. Site administrators should:

* Update their privacy policy to disclose the use of AI-powered search
* Inform visitors that their queries are processed by an external service

== Changelog ==

= 1.0.0 =
* Initial release
