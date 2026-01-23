/**
 * CreativeLink AI Search - Standalone Widget for WordPress
 *
 * Usage: Add this script to your WordPress site, then add the following
 * anywhere in your theme (header.php, footer.php, or via a plugin):
 *
 * <script src="https://your-cdn.com/chat-widget-standalone.js"></script>
 *
 * Or paste the entire contents into a Custom HTML block or your theme's JS.
 */

(function() {
  'use strict';

  // ==========================================================================
  // Configuration - Update this URL after deploying your worker
  // ==========================================================================
  const WORKER_URL = 'https://design-links-chat.jfx1026.workers.dev';

  // ==========================================================================
  // Inject Styles
  // ==========================================================================
  const styles = `
    /* CreativeLink AI Chat Widget */
    .cl-chat-trigger {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 999999;
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: #2563EB;
      color: white;
      border: none;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .cl-chat-trigger:hover {
      background: #1D4ED8;
      transform: scale(1.05);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .cl-chat-trigger svg {
      width: 24px !important;
      height: 24px !important;
      min-width: 24px !important;
      min-height: 24px !important;
      stroke: white !important;
      fill: none !important;
    }

    .cl-chat-trigger .cl-icon-close {
      display: none;
    }

    .cl-chat-trigger.active .cl-icon-chat {
      display: none;
    }

    .cl-chat-trigger.active .cl-icon-close {
      display: block;
    }

    /* Chat Panel */
    .cl-chat-panel {
      position: fixed;
      bottom: 96px;
      right: 24px;
      width: 380px;
      max-width: calc(100vw - 48px);
      height: 500px;
      max-height: calc(100vh - 120px);
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
      display: none;
      flex-direction: column;
      overflow: hidden;
      z-index: 999998;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      font-size: 15px;
      line-height: 1.5;
    }

    .cl-chat-panel.open {
      display: flex;
    }

    /* Header */
    .cl-chat-header {
      padding: 16px 20px;
      border-bottom: 1px solid #e5e5e5;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #fafafa;
    }

    .cl-chat-header h3 {
      margin: 0;
      font-size: 16px;
      font-weight: 600;
      color: #0a0a0a;
    }

    .cl-chat-close {
      background: none;
      border: none;
      cursor: pointer;
      padding: 4px;
      color: #737373;
      transition: color 0.15s;
    }

    .cl-chat-close:hover {
      color: #0a0a0a;
    }

    .cl-chat-close svg {
      width: 20px !important;
      height: 20px !important;
      min-width: 20px !important;
      min-height: 20px !important;
      stroke: currentColor !important;
      fill: none !important;
    }

    /* Messages */
    .cl-chat-messages {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .cl-chat-messages::-webkit-scrollbar {
      width: 6px;
    }

    .cl-chat-messages::-webkit-scrollbar-thumb {
      background: #e5e5e5;
      border-radius: 3px;
    }

    /* Welcome */
    .cl-chat-welcome {
      text-align: center;
      padding: 20px 10px;
      color: #525252;
    }

    .cl-chat-welcome-icon {
      margin-bottom: 12px;
      text-align: center;
      display: flex;
      justify-content: center;
    }

    .cl-chat-welcome-icon svg {
      width: 48px !important;
      height: 48px !important;
    }

    .cl-chat-welcome h4 {
      font-size: 16px;
      font-weight: 600;
      color: #0a0a0a;
      margin: 0 0 8px 0;
    }

    .cl-chat-welcome p {
      font-size: 14px;
      margin: 0 0 16px 0;
      color: #525252;
    }

    .cl-chat-suggestions {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      justify-content: center;
    }

    .cl-chat-suggestion {
      padding: 8px 12px;
      background: #f5f5f5;
      border: 1px solid #e5e5e5;
      border-radius: 8px;
      font-size: 13px;
      color: #525252;
      cursor: pointer;
      transition: all 0.15s;
    }

    .cl-chat-suggestion:hover {
      background: #dbeafe;
      border-color: #2563EB;
      color: #2563EB;
    }

    /* Message Bubbles */
    .cl-message {
      max-width: 85%;
      animation: clMessageSlide 0.2s ease;
    }

    @keyframes clMessageSlide {
      from { opacity: 0; transform: translateY(8px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .cl-message.user {
      align-self: flex-end;
    }

    .cl-message.assistant {
      align-self: flex-start;
    }

    .cl-message-content {
      padding: 12px 16px;
      border-radius: 12px;
      font-size: 14px;
      line-height: 1.6;
    }

    .cl-message.user .cl-message-content {
      background: #2563EB;
      color: white;
      border-bottom-right-radius: 4px;
    }

    .cl-message.assistant .cl-message-content {
      background: #f5f5f5;
      color: #0a0a0a;
      border: 1px solid #e5e5e5;
      border-bottom-left-radius: 4px;
    }

    .cl-message.assistant .cl-message-content a {
      color: #2563EB;
      text-decoration: underline;
      display: block;
      margin-bottom: 16px;
    }

    .cl-message.assistant .cl-message-content a:hover {
      color: #1D4ED8;
    }

    .cl-message.assistant .cl-message-content strong {
      font-weight: 600;
    }

    .cl-message.assistant .cl-message-content p {
      margin: 0 0 8px 0;
    }

    .cl-message.assistant .cl-message-content p:last-child {
      margin-bottom: 0;
    }

    /* Loading */
    .cl-message.loading .cl-message-content {
      display: flex;
      align-items: center;
      gap: 4px;
      padding: 16px 20px;
    }

    .cl-typing-dot {
      width: 8px;
      height: 8px;
      background: #737373;
      border-radius: 50%;
      animation: clTypingBounce 1.4s infinite ease-in-out;
    }

    .cl-typing-dot:nth-child(1) { animation-delay: 0s; }
    .cl-typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .cl-typing-dot:nth-child(3) { animation-delay: 0.4s; }

    @keyframes clTypingBounce {
      0%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-6px); }
    }

    /* Input */
    .cl-chat-input-area {
      padding: 16px;
      border-top: 1px solid #e5e5e5;
      background: #ffffff;
    }

    .cl-chat-input-form {
      display: flex;
      gap: 12px;
      align-items: flex-end;
    }

    .cl-chat-input {
      flex: 1;
      padding: 12px 16px;
      border: 1px solid #e5e5e5;
      border-radius: 12px;
      font-size: 14px;
      font-family: inherit;
      resize: none;
      min-height: 44px;
      max-height: 100px;
      line-height: 1.4;
      transition: border-color 0.15s, box-shadow 0.15s;
    }

    .cl-chat-input:focus {
      outline: none;
      border-color: #2563EB;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .cl-chat-input::placeholder {
      color: #737373;
    }

    .cl-chat-send {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: #2563EB;
      color: white;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.15s;
      flex-shrink: 0;
    }

    .cl-chat-send:hover:not(:disabled) {
      background: #1D4ED8;
    }

    .cl-chat-send:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .cl-chat-send svg {
      width: 18px !important;
      height: 18px !important;
      min-width: 18px !important;
      min-height: 18px !important;
      fill: white !important;
    }

    /* Error */
    .cl-chat-error {
      padding: 12px 16px;
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 8px;
      color: #dc2626;
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* Mobile */
    @media (max-width: 480px) {
      .cl-chat-trigger {
        bottom: 16px;
        right: 16px;
        width: 52px;
        height: 52px;
      }

      .cl-chat-panel {
        bottom: 0;
        right: 0;
        width: 100%;
        max-width: 100%;
        height: 100%;
        max-height: 100%;
        border-radius: 0;
      }

      .cl-chat-suggestions {
        flex-direction: column;
      }

      .cl-chat-suggestion {
        text-align: center;
      }
    }
  `;

  // Inject styles
  const styleSheet = document.createElement('style');
  styleSheet.textContent = styles;
  document.head.appendChild(styleSheet);

  // ==========================================================================
  // Create Widget HTML
  // ==========================================================================
  const widgetHTML = `
    <button class="cl-chat-trigger" id="cl-chat-trigger" aria-label="Open chat">
      <svg class="cl-icon-chat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
      </svg>
      <svg class="cl-icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>

    <div class="cl-chat-panel" id="cl-chat-panel">
      <div class="cl-chat-header">
        <h3>AI Search</h3>
        <button class="cl-chat-close" id="cl-chat-close" aria-label="Close">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>
      <div class="cl-chat-messages" id="cl-chat-messages">
        <div class="cl-chat-welcome" id="cl-chat-welcome">
          <div class="cl-chat-welcome-icon"><svg viewBox="0 0 512 512" width="48" height="48"><rect x="96" y="96" width="320" height="320" rx="32" fill="#cbd5e1"/><rect x="144" y="144" width="224" height="224" rx="16" fill="#2563EB"/><text x="256" y="290" text-anchor="middle" font-family="Arial, sans-serif" font-size="120" font-weight="bold" fill="white">AI</text><rect x="136" y="32" width="24" height="64" rx="4" fill="#64748b"/><rect x="216" y="32" width="24" height="64" rx="4" fill="#64748b"/><rect x="272" y="32" width="24" height="64" rx="4" fill="#64748b"/><rect x="352" y="32" width="24" height="64" rx="4" fill="#64748b"/><rect x="136" y="416" width="24" height="64" rx="4" fill="#64748b"/><rect x="216" y="416" width="24" height="64" rx="4" fill="#64748b"/><rect x="272" y="416" width="24" height="64" rx="4" fill="#64748b"/><rect x="352" y="416" width="24" height="64" rx="4" fill="#64748b"/><rect x="32" y="136" width="64" height="24" rx="4" fill="#64748b"/><rect x="32" y="216" width="64" height="24" rx="4" fill="#64748b"/><rect x="32" y="272" width="64" height="24" rx="4" fill="#64748b"/><rect x="32" y="352" width="64" height="24" rx="4" fill="#64748b"/><rect x="416" y="136" width="64" height="24" rx="4" fill="#64748b"/><rect x="416" y="216" width="64" height="24" rx="4" fill="#64748b"/><rect x="416" y="272" width="64" height="24" rx="4" fill="#64748b"/><rect x="416" y="352" width="64" height="24" rx="4" fill="#64748b"/></svg></div>
          <p>Use AI chat to search through over 3500 links from the Weekly Creative Links archive.</p>
          <div class="cl-chat-suggestions">
            <button class="cl-chat-suggestion">Typography resources</button>
            <button class="cl-chat-suggestion">Design systems</button>
            <button class="cl-chat-suggestion">Pain Points</button>
          </div>
        </div>
      </div>
      <div class="cl-chat-input-area">
        <form class="cl-chat-input-form" id="cl-chat-form">
          <textarea class="cl-chat-input" id="cl-chat-input" placeholder="Ask about design resources..." rows="1"></textarea>
          <button type="submit" class="cl-chat-send" id="cl-chat-send" aria-label="Send">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
            </svg>
          </button>
        </form>
      </div>
    </div>
  `;

  // Create container and inject HTML
  const container = document.createElement('div');
  container.id = 'cl-chat-widget';
  container.innerHTML = widgetHTML;
  document.body.appendChild(container);

  // ==========================================================================
  // Widget Logic
  // ==========================================================================
  const trigger = document.getElementById('cl-chat-trigger');
  const panel = document.getElementById('cl-chat-panel');
  const closeBtn = document.getElementById('cl-chat-close');
  const messages = document.getElementById('cl-chat-messages');
  const form = document.getElementById('cl-chat-form');
  const input = document.getElementById('cl-chat-input');
  const sendBtn = document.getElementById('cl-chat-send');
  const welcome = document.getElementById('cl-chat-welcome');

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
    div.innerHTML = '⚠️ ' + escapeHtml(msg);
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
        body: JSON.stringify({ messages: conversationHistory })
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

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

})();
