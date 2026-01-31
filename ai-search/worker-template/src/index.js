/**
 * AI Search - Cloudflare Worker Template
 *
 * This worker processes search queries using Cloudflare Workers AI
 * and returns results in the format expected by the AI Search WordPress plugin.
 */

export default {
  async fetch(request, env) {
    // Handle CORS preflight
    if (request.method === 'OPTIONS') {
      return new Response(null, {
        headers: corsHeaders(),
      });
    }

    // Only accept POST to /chat
    const url = new URL(request.url);
    if (request.method !== 'POST' || url.pathname !== '/chat') {
      return new Response('Not found', { status: 404 });
    }

    try {
      const { messages, context, scope } = await request.json();

      // Build the system prompt with context
      const systemPrompt = buildSystemPrompt(context, scope);

      // Prepare messages for the AI
      const aiMessages = [
        { role: 'system', content: systemPrompt },
        ...messages.map(m => ({ role: m.role, content: m.content }))
      ];

      // Query Cloudflare Workers AI
      const aiResponse = await env.AI.run('@cf/meta/llama-3.1-8b-instruct', {
        messages: aiMessages,
      });

      const responseText = aiResponse.response || '';

      // Extract post numbers mentioned in the response
      const mentionedNumbers = extractPostNumbers(responseText, context.length);

      // Build results from the context using mentioned numbers
      const results = mentionedNumbers.map(num => {
        const post = context[num - 1];
        if (post) {
          return {
            title: post.title,
            url: post.url,
            excerpt: post.excerpt || post.title
          };
        }
        return null;
      }).filter(Boolean);

      // Clean the response text (remove the POSTS: line if present)
      let cleanText = responseText.replace(/\n*POSTS:\s*[\d,\s]+\s*$/, '').trim();

      // Build SSE response
      let sseBody = '';

      // Send the text response
      if (cleanText) {
        sseBody += `data: ${JSON.stringify({ response: cleanText })}\n\n`;
      }

      // Send structured results
      if (results.length > 0) {
        sseBody += `data: ${JSON.stringify({ results })}\n\n`;
      }

      // Send done signal
      sseBody += 'data: [DONE]\n\n';

      return new Response(sseBody, {
        headers: {
          'Content-Type': 'text/event-stream',
          'Cache-Control': 'no-cache',
          ...corsHeaders(),
        },
      });

    } catch (error) {
      console.error('Error:', error);
      return new Response(JSON.stringify({ error: error.message }), {
        status: 500,
        headers: {
          'Content-Type': 'application/json',
          ...corsHeaders(),
        },
      });
    }
  },
};

function corsHeaders() {
  return {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'POST, OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type',
  };
}

/**
 * Build a system prompt that includes the site content as context
 */
function buildSystemPrompt(context, scope) {
  let contextText = '';

  if (context && context.length > 0) {
    contextText = context.map((post, i) =>
      `[${i + 1}] "${post.title}"\n${post.excerpt}`
    ).join('\n\n');
  }

  return `You are a helpful search assistant for a website. Help visitors find relevant content.

AVAILABLE CONTENT (${scope || 'All posts'}):
${contextText || 'No content available.'}

INSTRUCTIONS:
1. Answer based ONLY on the available content above.
2. Write a brief, friendly response (1-2 sentences).
3. End your response with "POSTS:" followed by the numbers of relevant posts (comma-separated).
4. List up to 5 relevant posts, most relevant first.
5. If nothing matches, just respond politely without the POSTS line.

EXAMPLE:
User: Do you have articles about photography?
Assistant: Yes, I found some great photography resources for you!

POSTS: 3, 7, 12`;
}

/**
 * Extract post numbers from the AI response
 */
function extractPostNumbers(response, maxNum) {
  // Look for "POSTS:" followed by numbers
  const match = response.match(/POSTS:\s*([\d,\s]+)/i);

  if (match) {
    const numbers = match[1]
      .split(/[,\s]+/)
      .map(n => parseInt(n.trim(), 10))
      .filter(n => !isNaN(n) && n >= 1 && n <= maxNum);

    // Return unique numbers, max 5
    return [...new Set(numbers)].slice(0, 5);
  }

  return [];
}
