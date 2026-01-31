=== AI Search ===
Contributors: johnfreeborn
Tags: ai, search, chat, widget, natural language
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered chat widget to search through your site's content using natural language.

== Description ==

AI Search adds a floating chat widget to your WordPress site that allows visitors to search through your content using natural language queries powered by AI.

Features:

* AI-powered search using natural language
* Floating chat widget that works on any page
* Configurable search scope (whole site, category, tag, or keyword)
* Structured results with titles and excerpts
* Mobile responsive design
* Customizable button color and widget text

**Note:** This plugin requires an external AI backend service to function. You'll need to deploy your own Cloudflare Worker or similar backend that processes queries and returns AI-generated responses.

== Installation ==

1. Upload the `ai-search` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > AI Search and configure your API endpoint
4. The chat widget will appear on your site once configured

== Configuration ==

Go to Settings > AI Search to customize:

* API Endpoint (required) - URL of your AI backend service
* Button color
* Search scope (whole site, category, tag, or keyword filter)
* Maximum posts to include in search context
* Welcome text and placeholder text
* Suggestion buttons

== External Services ==

This plugin connects to an external AI service that you configure. When a visitor uses the chat widget:

**Data transmitted:**
* The visitor's search query
* Conversation history within the current session
* Post titles, excerpts, URLs, and dates from your site (based on your search scope settings)

**Why this is needed:**
The external service processes natural language queries using AI and returns relevant results from your content.

You are responsible for the AI backend service you configure, including its data handling and privacy practices.

== Privacy ==

This plugin does not collect or store personal data directly. However, visitor search queries are sent to the external AI service you configure. Site administrators should:

* Update their privacy policy to disclose the use of AI-powered search
* Inform visitors that their queries are processed by an external service
* Review the privacy practices of your chosen AI backend

== Changelog ==

= 1.0.0 =
* Initial release
