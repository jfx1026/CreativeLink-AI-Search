=== AI Site Search ===
Contributors: johnfreeborn
Tags: ai, search, chat, widget, natural language
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI Site Search is a simple, AI-powered search for your WordPress site.

== Description ==

AI Site Search adds a floating chat widget to your WordPress site that lets visitors search your content using natural language. The plugin sends your post data (titles, excerpts, URLs) to your configured AI backend, which processes queries and streams responses back via Server-Sent Events.

Features:

* AI-powered search using natural language
* Floating chat widget that works on any page
* Configurable search scope (whole site, category, tag, or keyword)
* Structured results with titles and excerpts
* Mobile responsive design
* Customizable button color and widget text

**Note:** This plugin requires an AI backend service. A ready-to-deploy Cloudflare Worker template is included in the `worker-template` folder.

== Installation ==

1. Upload the `ai-site-search` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Deploy the included worker template (see Backend Setup below)
4. Go to Settings > AI Site Search and paste your worker URL
5. The chat widget will appear on your site once configured

== Backend Setup ==

A Cloudflare Worker template is included in the `worker-template` folder. To deploy it:

1. Install [Wrangler CLI](https://developers.cloudflare.com/workers/wrangler/install-and-update/): `npm install -g wrangler`
2. Login to Cloudflare: `wrangler login`
3. Navigate to the worker-template folder: `cd worker-template`
4. Deploy: `wrangler deploy`
5. Copy the URL (e.g., `https://ai-search-worker.your-subdomain.workers.dev`)
6. Paste this URL in Settings > AI Site Search > API Endpoint

The worker uses Cloudflare Workers AI which has a generous free tier (no credit card required).

== Configuration ==

Go to Settings > AI Site Search to customize:

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
