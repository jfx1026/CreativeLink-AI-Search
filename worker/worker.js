/**
 * Cloudflare Worker: AI Chat Search for Weekly Design Links
 *
 * Endpoints:
 * - POST /chat: Chat with Claude about design links (streaming)
 * - GET /health: Health check
 * - GET /refresh: Force refresh the links cache
 */

const CACHE_KEY = 'design-links-index';
const CACHE_TTL = 24 * 60 * 60; // 24 hours in seconds

export default {
  async fetch(request, env, ctx) {
    // Handle CORS preflight
    if (request.method === 'OPTIONS') {
      return handleCORS(request, env);
    }

    const url = new URL(request.url);
    const path = url.pathname;

    try {
      let response;

      switch (path) {
        case '/chat':
          if (request.method !== 'POST') {
            response = jsonResponse({ error: 'Method not allowed' }, 405);
          } else {
            response = await handleChat(request, env, ctx);
          }
          break;

        case '/health':
          response = jsonResponse({ status: 'ok', timestamp: new Date().toISOString() });
          break;

        case '/refresh':
          if (request.method === 'POST') {
            await refreshLinksCache(env);
            response = jsonResponse({ status: 'cache refreshed' });
          } else {
            response = jsonResponse({ error: 'Method not allowed' }, 405);
          }
          break;

        default:
          response = jsonResponse({ error: 'Not found' }, 404);
      }

      return addCORSHeaders(response, request, env);
    } catch (error) {
      console.error('Worker error:', error);
      return addCORSHeaders(
        jsonResponse({ error: 'Internal server error', message: error.message }, 500),
        request,
        env
      );
    }
  }
};

// ==========================================================================
// CORS Handling
// ==========================================================================

function handleCORS(request, env) {
  const headers = new Headers();
  const origin = request.headers.get('Origin') || '';

  // Allow localhost for development and production domain
  // Allow null origin for file:// protocol during development
  if (origin === 'null' || origin === '' || !origin) {
    headers.set('Access-Control-Allow-Origin', '*');
  } else if (origin.includes('localhost') || origin.includes('127.0.0.1') || origin.includes('johnfreeborn.com')) {
    headers.set('Access-Control-Allow-Origin', origin);
  }

  headers.set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  headers.set('Access-Control-Allow-Headers', 'Content-Type');
  headers.set('Access-Control-Max-Age', '86400');

  return new Response(null, { status: 204, headers });
}

function addCORSHeaders(response, request, env) {
  const origin = request.headers.get('Origin') || '';
  const headers = new Headers(response.headers);

  // Allow null origin for file:// protocol during development
  if (origin === 'null' || origin === '' || !origin) {
    headers.set('Access-Control-Allow-Origin', '*');
  } else if (origin.includes('localhost') || origin.includes('127.0.0.1') || origin.includes('johnfreeborn.com')) {
    headers.set('Access-Control-Allow-Origin', origin);
  }

  return new Response(response.body, {
    status: response.status,
    statusText: response.statusText,
    headers
  });
}

// ==========================================================================
// Chat Handler
// ==========================================================================

async function handleChat(request, env, ctx) {
  const body = await request.json();
  const { messages } = body;

  if (!messages || !Array.isArray(messages)) {
    return jsonResponse({ error: 'Messages array required' }, 400);
  }

  // Get or refresh the links index
  const linksIndex = await getLinksIndex(env, ctx);

  // Get the latest user message for retrieval
  const lastUserMessage = messages.filter(m => m.role === 'user').pop();
  const query = lastUserMessage ? lastUserMessage.content.toLowerCase() : '';

  // Retrieve relevant links based on query (simple keyword matching)
  const relevantLinks = retrieveRelevantLinks(linksIndex, query, 30);

  // Build system prompt with only relevant links
  const systemPrompt = buildSystemPrompt(relevantLinks, linksIndex.length);

  try {
    // Use Cloudflare Workers AI (free tier)
    const aiMessages = [
      { role: 'system', content: systemPrompt },
      ...messages
    ];

    const stream = await env.AI.run('@cf/meta/llama-3.1-8b-instruct', {
      messages: aiMessages,
      stream: true
    });

    // Return the stream directly
    return new Response(stream, {
      headers: {
        'Content-Type': 'text/event-stream',
        'Cache-Control': 'no-cache',
        'Connection': 'keep-alive'
      }
    });
  } catch (error) {
    console.error('AI error:', error);
    return jsonResponse({ error: 'AI service error', details: error.message }, 502);
  }
}

// Improved keyword-based retrieval
function retrieveRelevantLinks(linksIndex, query, maxLinks = 30) {
  const queryLower = query.toLowerCase();
  const queryTerms = queryLower.split(/\s+/).filter(t => t.length > 2);

  // Common words to ignore
  const stopWords = new Set(['the', 'and', 'for', 'are', 'but', 'not', 'you', 'all', 'can', 'had', 'her', 'was', 'one', 'our', 'out', 'has', 'have', 'been', 'were', 'they', 'this', 'that', 'with', 'from', 'about', 'into', 'some', 'find', 'show', 'looking', 'want', 'need', 'please', 'thanks', 'help', 'something', 'anything', 'related', 'links', 'resources']);

  const meaningfulTerms = queryTerms.filter(t => !stopWords.has(t));
  if (meaningfulTerms.length === 0) return [];

  const scoredLinks = [];

  for (const post of linksIndex) {
    for (const link of post.links) {
      const text = `${link.title} ${link.description || ''}`.toLowerCase();
      let score = 0;
      let matchedTerms = 0;

      // Check for full query phrase match (highest priority)
      if (text.includes(queryLower)) {
        score += 10;
      }

      // Check each meaningful term
      for (const term of meaningfulTerms) {
        if (text.includes(term)) {
          matchedTerms++;
          score += 2;

          // Boost if term is in title specifically
          if (link.title.toLowerCase().includes(term)) {
            score += 3;
          }
        }
      }

      // Only include if at least half of meaningful terms match
      const matchThreshold = Math.max(1, Math.ceil(meaningfulTerms.length / 2));
      if (matchedTerms >= matchThreshold && score > 0) {
        scoredLinks.push({
          ...link,
          postTitle: post.title,
          postDate: post.date,
          postUrl: post.postUrl,
          score,
          matchedTerms
        });
      }
    }
  }

  // Sort by score descending, then by matched terms
  scoredLinks.sort((a, b) => {
    if (b.score !== a.score) return b.score - a.score;
    return b.matchedTerms - a.matchedTerms;
  });

  return scoredLinks.slice(0, maxLinks);
}

function buildSystemPrompt(relevantLinks, totalPosts) {
  if (relevantLinks.length === 0) {
    return `You help users find design resources from John Freeborn's "Weekly Design Links" archive (${totalPosts} posts).

IMPORTANT: No matches were found for this query in the archive. Tell the user no relevant links were found and suggest they:
1. Try different keywords
2. Browse the archive at johnfreeborn.com/words

DO NOT make up or invent any links. You can ONLY recommend links that are explicitly provided to you.`;
  }

  const linksContext = relevantLinks.map(link => {
    const desc = link.description ? link.description : '';
    // Create deep link with Text Fragment API to scroll to the link title
    const textFragment = encodeURIComponent(link.title).replace(/%20/g, '%20');
    const deepLink = `${link.postUrl}#:~:text=${textFragment}`;
    return `LINK: "${link.title}"${desc ? ` - ${desc}` : ''}\nPOST: [${link.postTitle}](${deepLink})`;
  }).join('\n\n');

  return `You help users find design resources from John Freeborn's "Weekly Design Links" archive.

Here are relevant links I found:

${linksContext}

FORMAT YOUR RESPONSE LIKE THIS:
**Link Title**
Brief description of the resource
[Weekly Design Links – MM/DD/YY](post-url)

EXAMPLE:
**Arcade Game Typography**
A book exploring the typography of classic arcade games.
[Weekly Design Links – 01/13/26](https://johnfreeborn.com/words/weekly-design-links-01-13-26/)

Instructions:
- Share 3-5 of the most relevant results
- Show the link title in bold, then description, then the post link
- Copy the post link exactly as shown above
- Be conversational and helpful`;
}

// ==========================================================================
// Links Index Management
// ==========================================================================

async function getLinksIndex(env, ctx) {
  // Try to get from cache first
  const cached = await env.LINKS_CACHE.get(CACHE_KEY, { type: 'json' });

  if (cached) {
    return cached;
  }

  // Fetch and cache in background if not available
  const linksIndex = await fetchAndProcessLinks(env);

  // Store in KV with TTL
  ctx.waitUntil(
    env.LINKS_CACHE.put(CACHE_KEY, JSON.stringify(linksIndex), {
      expirationTtl: CACHE_TTL
    })
  );

  return linksIndex;
}

async function refreshLinksCache(env) {
  const linksIndex = await fetchAndProcessLinks(env);
  await env.LINKS_CACHE.put(CACHE_KEY, JSON.stringify(linksIndex), {
    expirationTtl: CACHE_TTL
  });
  return linksIndex;
}

async function fetchAndProcessLinks(env) {
  const allPosts = [];
  let page = 1;
  const perPage = 100;
  let hasMore = true;

  // Fetch all posts from WordPress (paginated)
  while (hasMore) {
    const url = `${env.WORDPRESS_API_URL}?categories=${env.CATEGORY_ID}&per_page=${perPage}&page=${page}&_fields=id,title,content,date,link`;

    const response = await fetch(url);

    if (!response.ok) {
      if (response.status === 400) {
        // No more pages
        hasMore = false;
        break;
      }
      throw new Error(`WordPress API error: ${response.status}`);
    }

    const posts = await response.json();

    if (posts.length === 0) {
      hasMore = false;
    } else {
      allPosts.push(...posts);
      page++;

      // Check if we've reached the last page
      const totalPages = parseInt(response.headers.get('X-WP-TotalPages') || '1');
      if (page > totalPages) {
        hasMore = false;
      }
    }
  }

  // Process each post to extract links
  const processedPosts = allPosts.map(post => ({
    id: post.id,
    title: decodeHTMLEntities(post.title.rendered),
    date: new Date(post.date).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    }),
    postUrl: post.link,
    links: extractLinks(post.content.rendered)
  }));

  return processedPosts;
}

// ==========================================================================
// HTML Parsing & Link Extraction
// ==========================================================================

function extractLinks(htmlContent) {
  const links = [];

  // Match anchor tags with href attributes
  const linkRegex = /<a\s+[^>]*href=["']([^"']+)["'][^>]*>([^<]*)<\/a>/gi;

  // Match list items or paragraphs that might contain descriptions
  const contextRegex = /<li[^>]*>([\s\S]*?)<\/li>|<p[^>]*>([\s\S]*?)<\/p>/gi;

  let match;
  const seenUrls = new Set();

  // First, try to extract links with context from list items
  while ((match = contextRegex.exec(htmlContent)) !== null) {
    const blockContent = match[1] || match[2];
    const blockLinks = extractLinksFromBlock(blockContent);

    for (const link of blockLinks) {
      if (!seenUrls.has(link.url) && isExternalLink(link.url)) {
        seenUrls.add(link.url);
        links.push(link);
      }
    }
  }

  // Fallback: extract any remaining links not in list items
  while ((match = linkRegex.exec(htmlContent)) !== null) {
    const url = match[1];
    const title = decodeHTMLEntities(match[2].trim());

    if (!seenUrls.has(url) && isExternalLink(url) && title) {
      seenUrls.add(url);
      links.push({ url, title, description: '' });
    }
  }

  return links;
}

function extractLinksFromBlock(blockContent) {
  const links = [];
  const linkRegex = /<a\s+[^>]*href=["']([^"']+)["'][^>]*>([^<]*)<\/a>/gi;

  let match;
  while ((match = linkRegex.exec(blockContent)) !== null) {
    const url = match[1];
    const title = decodeHTMLEntities(match[2].trim());

    if (title) {
      // Try to extract description - text after the link
      const afterLink = blockContent.substring(match.index + match[0].length);
      let description = '';

      // Look for text after a dash, colon, or just plain text
      const descMatch = afterLink.match(/^[\s]*[-–—:]\s*([^<]+)/);
      if (descMatch) {
        description = decodeHTMLEntities(descMatch[1].trim());
      }

      links.push({ url, title, description });
    }
  }

  return links;
}

function isExternalLink(url) {
  // Skip internal links, anchors, and common non-content URLs
  if (!url ||
      url.startsWith('#') ||
      url.startsWith('mailto:') ||
      url.startsWith('javascript:') ||
      url.includes('johnfreeborn.com') ||
      url.includes('wordpress.com/signup')) {
    return false;
  }
  return true;
}

function decodeHTMLEntities(text) {
  if (!text) return '';

  const entities = {
    '&amp;': '&',
    '&lt;': '<',
    '&gt;': '>',
    '&quot;': '"',
    '&#039;': "'",
    '&apos;': "'",
    '&#8211;': '\u2013',
    '&#8212;': '\u2014',
    '&#8216;': '\u2018',
    '&#8217;': '\u2019',
    '&#8220;': '\u201C',
    '&#8221;': '\u201D',
    '&nbsp;': ' ',
    '&#038;': '&',
    '&hellip;': '...'
  };

  let decoded = text;
  for (const [entity, char] of Object.entries(entities)) {
    decoded = decoded.replace(new RegExp(entity, 'g'), char);
  }

  // Handle numeric entities
  decoded = decoded.replace(/&#(\d+);/g, (match, dec) => String.fromCharCode(dec));

  // Strip any remaining HTML tags
  decoded = decoded.replace(/<[^>]+>/g, '');

  return decoded.trim();
}

// ==========================================================================
// Utilities
// ==========================================================================

function jsonResponse(data, status = 200) {
  return new Response(JSON.stringify(data), {
    status,
    headers: {
      'Content-Type': 'application/json'
    }
  });
}
