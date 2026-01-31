/**
 * AI Search - WordPress Plugin
 */

(function() {
  'use strict';

  // Get settings from WordPress
  const WORKER_URL = window.creativeLinkAI ? window.creativeLinkAI.workerUrl : '';
  const SEARCH_CONTEXT = window.creativeLinkAI ? window.creativeLinkAI.searchContext : [];
  const SEARCH_SCOPE = window.creativeLinkAI ? window.creativeLinkAI.searchScope : 'Whole site';

  // DOM Elements
  const trigger = document.getElementById('cl-chat-trigger');
  const panel = document.getElementById('cl-chat-panel');
  const closeBtn = document.getElementById('cl-chat-close');
  const messages = document.getElementById('cl-chat-messages');
  const form = document.getElementById('cl-chat-form');
  const input = document.getElementById('cl-chat-input');
  const sendBtn = document.getElementById('cl-chat-send');
  const welcome = document.getElementById('cl-chat-welcome');

  if (!trigger || !panel) return;

  let conversationHistory = [];
  let isStreaming = false;

  // Toggle panel
  function togglePanel() {
    const isOpen = panel.classList.toggle('open');
    trigger.classList.toggle('active', isOpen);
    if (isOpen) input.focus();
  }

  trigger.addEventListener('click', togglePanel);
  closeBtn.addEventListener('click', togglePanel);

  // Suggestions
  document.querySelectorAll('.cl-chat-suggestion').forEach(btn => {
    btn.addEventListener('click', () => {
      input.value = btn.textContent;
      handleSubmit(new Event('submit'));
    });
  });

  // Form submit
  form.addEventListener('submit', handleSubmit);
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSubmit(e);
    }
  });

  // Auto-resize input
  input.addEventListener('input', () => {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 100) + 'px';
  });

  function handleSubmit(e) {
    e.preventDefault();
    const message = input.value.trim();
    if (!message || isStreaming) return;

    if (welcome) welcome.style.display = 'none';

    addMessage('user', message);
    conversationHistory.push({ role: 'user', content: message });

    input.value = '';
    input.style.height = 'auto';

    sendMessage();
  }

  function addMessage(role, content) {
    const div = document.createElement('div');
    div.className = 'cl-message ' + role;

    const contentDiv = document.createElement('div');
    contentDiv.className = 'cl-message-content';
    contentDiv.innerHTML = role === 'assistant' ? parseMarkdown(content) : escapeHtml(content);

    div.appendChild(contentDiv);
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;

    return contentDiv;
  }

  function addLoading() {
    const div = document.createElement('div');
    div.className = 'cl-message assistant loading';
    div.id = 'cl-loading';
    div.innerHTML = '<div class="cl-message-content"><span class="cl-typing-dot"></span><span class="cl-typing-dot"></span><span class="cl-typing-dot"></span></div>';
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }

  function removeLoading() {
    const el = document.getElementById('cl-loading');
    if (el) el.remove();
  }

  function addError(msg) {
    const div = document.createElement('div');
    div.className = 'cl-chat-error';
    div.textContent = msg;
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }

  async function sendMessage() {
    isStreaming = true;
    sendBtn.disabled = true;
    addLoading();

    try {
      const response = await fetch(WORKER_URL + '/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          messages: conversationHistory,
          context: SEARCH_CONTEXT,
          scope: SEARCH_SCOPE
        })
      });

      if (!response.ok) throw new Error('Failed to get response');

      removeLoading();

      const contentEl = addMessage('assistant', '');
      let fullResponse = '';

      const reader = response.body.getReader();
      const decoder = new TextDecoder();

      while (true) {
        const { done, value } = await reader.read();
        if (done) break;

        const chunk = decoder.decode(value, { stream: true });
        const lines = chunk.split('\n');

        for (const line of lines) {
          if (line.startsWith('data: ')) {
            const data = line.slice(6);
            if (data === '[DONE]') continue;

            try {
              const parsed = JSON.parse(data);
              if (parsed.response && typeof parsed.response === 'string') {
                fullResponse += parsed.response;
                contentEl.innerHTML = parseMarkdown(fullResponse);
                messages.scrollTop = messages.scrollHeight;
              }
              // Handle structured results
              if (parsed.results && Array.isArray(parsed.results)) {
                contentEl.innerHTML += renderResults(parsed.results);
                messages.scrollTop = messages.scrollHeight;
              }
            } catch (e) {}
          }
        }
      }

      if (fullResponse) {
        conversationHistory.push({ role: 'assistant', content: fullResponse });
      }

    } catch (error) {
      console.error('Chat error:', error);
      removeLoading();
      addError('Sorry, something went wrong. Please try again.');
    } finally {
      isStreaming = false;
      sendBtn.disabled = false;
      input.focus();
    }
  }

  function parseMarkdown(text) {
    if (!text) return '';
    let html = escapeHtml(text);
    html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>');
    html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\n\n/g, '</p><p>');
    html = html.replace(/\n/g, '<br>');
    if (!html.startsWith('<p>')) html = '<p>' + html + '</p>';
    return html;
  }

  function renderResults(results) {
    if (!results || !results.length) return '';
    let html = '<div class="cl-results">';
    for (const result of results) {
      const title = escapeHtml(result.title || '');
      const excerpt = escapeHtml(result.excerpt || result.title || '');
      const url = result.url || '#';
      html += '<div class="cl-result">';
      html += '<div class="cl-result-title">' + title + '</div>';
      html += '<a href="' + url + '" class="cl-result-link">' + excerpt + '</a>';
      html += '</div>';
    }
    html += '</div>';
    return html;
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

})();
