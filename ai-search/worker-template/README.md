# AI Search Worker Template

This is a ready-to-deploy Cloudflare Worker that powers the AI Search WordPress plugin. It uses **Cloudflare Workers AI** (free tier available) to process natural language queries and return relevant content from your site.

## Quick Start

### Prerequisites

- A [Cloudflare account](https://dash.cloudflare.com/sign-up) (free)
- [Node.js](https://nodejs.org/) installed on your computer
- [Wrangler CLI](https://developers.cloudflare.com/workers/wrangler/install-and-update/) installed

### Setup Steps

1. **Install Wrangler** (if not already installed):
   ```bash
   npm install -g wrangler
   ```

2. **Login to Cloudflare**:
   ```bash
   wrangler login
   ```

3. **Navigate to this folder**:
   ```bash
   cd worker-template
   ```

4. **Deploy the worker**:
   ```bash
   wrangler deploy
   ```

5. **Copy your worker URL** - it will look like:
   ```
   https://ai-search-worker.YOUR-SUBDOMAIN.workers.dev
   ```

6. **Configure the WordPress plugin**:
   - Go to Settings > AI Search in your WordPress admin
   - Paste your worker URL in the API Endpoint field
   - Save changes

That's it! Your AI Search widget should now be working.

## Customization

### Change the Worker Name

Edit `wrangler.toml` and change the `name` field:

```toml
name = "my-site-search"
```

### Use a Different AI Model

Edit `src/index.js` and change the model in the `AI.run()` call:

```javascript
const response = await env.AI.run('@cf/meta/llama-3.1-8b-instruct', {
```

Available models: https://developers.cloudflare.com/workers-ai/models/

### Customize the AI Behavior

Edit the `buildSystemPrompt()` function in `src/index.js` to change how the AI responds to queries.

## How It Works

1. The WordPress plugin sends your site's post data (titles, excerpts, URLs) along with the user's query
2. The worker builds a prompt that includes this context
3. Cloudflare Workers AI processes the query and finds relevant posts
4. Results are streamed back to the plugin as Server-Sent Events
5. The plugin displays the results as clickable cards

## Troubleshooting

**Widget not appearing?**
- Make sure the API Endpoint is configured in WordPress settings
- Check that your worker deployed successfully with `wrangler tail`

**CORS errors?**
- The worker includes CORS headers by default
- Make sure you're using the full worker URL including `https://`

**No results showing?**
- Check the browser console for errors
- Verify your site has published posts
- Try adjusting the search scope in WordPress settings

## Cost

Cloudflare Workers AI includes a free tier:
- 10,000 neurons per day (roughly thousands of queries)
- No credit card required

For most sites, the free tier is more than enough.
