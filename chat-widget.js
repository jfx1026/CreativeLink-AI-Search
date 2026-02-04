/**
 * Chat Widget - AI Search for Weekly Design Links
 * Integrates with Cloudflare Worker backend for Claude AI responses
 */

(function() {
  'use strict';

  // ==========================================================================
  // Configuration
  // ==========================================================================
  const CONFIG = {
    workerUrl: 'https://design-links-chat.jfx1026.workers.dev',
    maxMessages: 50 // Limit conversation history
  };

  // ==========================================================================
  // DOM Elements
  // ==========================================================================
  let triggerBtn;
  let drawer;
  let drawerClose;
  let drawerOverlay;
  let messagesContainer;
  let inputForm;
  let inputField;
  let sendBtn;
  let welcomeScreen;

  // ==========================================================================
  // State
  // ==========================================================================
  let conversationHistory = [];
  let isStreaming = false;

  // ==========================================================================
  // Initialization
  // ==========================================================================
  function init() {
    // Cache DOM elements
    triggerBtn = document.getElementById('chat-widget-trigger');
    drawer = document.getElementById('chat-drawer');
    drawerClose = document.getElementById('chat-drawer-close');
    drawerOverlay = document.getElementById('chat-drawer-overlay');
    messagesContainer = document.getElementById('chat-messages');
    inputForm = document.getElementById('chat-input-form');
    inputField = document.getElementById('chat-input');
    sendBtn = document.getElementById('chat-send-btn');
    welcomeScreen = document.getElementById('chat-welcome');

    if (!triggerBtn || !drawer) {
      console.warn('Chat widget elements not found');
      return;
    }

    // Bind event listeners
    triggerBtn.addEventListener('click', toggleDrawer);

    // Close button
    if (drawerClose) {
      drawerClose.addEventListener('click', closeDrawer);
    }

    // Overlay click to close
    if (drawerOverlay) {
      drawerOverlay.addEventListener('click', closeDrawer);
    }

    // Escape key to close
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && drawer.classList.contains('open')) {
        closeDrawer();
      }
    });

    inputForm.addEventListener('submit', handleSubmit);

    inputField.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        handleSubmit(e);
      }
    });

    // Auto-resize textarea
    inputField.addEventListener('input', autoResizeInput);

    // Suggestion click handlers
    const suggestions = document.querySelectorAll('.chat-suggestion');
    suggestions.forEach(function(suggestion) {
      suggestion.addEventListener('click', function() {
        const query = this.textContent;
        inputField.value = query;
        handleSubmit(new Event('submit'));
      });
    });
  }

  // ==========================================================================
  // Drawer Toggle
  // ==========================================================================
  function toggleDrawer() {
    if (drawer.classList.contains('open')) {
      closeDrawer();
    } else {
      openDrawer();
    }
  }

  function openDrawer() {
    drawer.classList.add('open');
    drawerOverlay.classList.add('open');
    triggerBtn.classList.add('active');
    document.body.style.overflow = 'hidden';
    setTimeout(function() {
      inputField.focus();
    }, 300);
  }

  function closeDrawer() {
    drawer.classList.remove('open');
    drawerOverlay.classList.remove('open');
    triggerBtn.classList.remove('active');
    document.body.style.overflow = '';
  }

  // ==========================================================================
  // Input Handling
  // ==========================================================================
  function autoResizeInput() {
    inputField.style.height = 'auto';
    inputField.style.height = Math.min(inputField.scrollHeight, 120) + 'px';
  }

  function handleSubmit(e) {
    e.preventDefault();

    const message = inputField.value.trim();
    if (!message || isStreaming) return;

    // Hide welcome screen
    if (welcomeScreen) {
      welcomeScreen.style.display = 'none';
    }

    // Add user message to UI
    addMessage('user', message);

    // Add to conversation history
    conversationHistory.push({
      role: 'user',
      content: message
    });

    // Trim history if too long
    if (conversationHistory.length > CONFIG.maxMessages) {
      conversationHistory = conversationHistory.slice(-CONFIG.maxMessages);
    }

    // Clear input
    inputField.value = '';
    inputField.style.height = 'auto';

    // Send to API
    sendMessage();
  }

  // ==========================================================================
  // Message Display
  // ==========================================================================
  function addMessage(role, content) {
    const messageEl = document.createElement('div');
    messageEl.className = 'chat-message ' + role;

    const contentEl = document.createElement('div');
    contentEl.className = 'chat-message-content';

    if (role === 'assistant') {
      contentEl.innerHTML = parseMarkdown(content);
    } else {
      contentEl.textContent = content;
    }

    messageEl.appendChild(contentEl);
    messagesContainer.appendChild(messageEl);
    scrollToBottom();

    return contentEl;
  }

  function addLoadingMessage() {
    const messageEl = document.createElement('div');
    messageEl.className = 'chat-message assistant loading';
    messageEl.id = 'loading-message';

    const contentEl = document.createElement('div');
    contentEl.className = 'chat-message-content';
    contentEl.innerHTML = '<span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>';

    messageEl.appendChild(contentEl);
    messagesContainer.appendChild(messageEl);
    scrollToBottom();
  }

  function removeLoadingMessage() {
    const loadingEl = document.getElementById('loading-message');
    if (loadingEl) {
      loadingEl.remove();
    }
  }

  function addErrorMessage(error) {
    const errorEl = document.createElement('div');
    errorEl.className = 'chat-error';
    errorEl.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>' + escapeHtml(error) + '</span>';
    messagesContainer.appendChild(errorEl);
    scrollToBottom();
  }

  function scrollToBottom() {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  // ==========================================================================
  // API Communication
  // ==========================================================================
  async function sendMessage() {
    isStreaming = true;
    sendBtn.disabled = true;
    addLoadingMessage();

    try {
      const response = await fetch(CONFIG.workerUrl + '/chat', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          messages: conversationHistory
        })
      });

      if (!response.ok) {
        throw new Error('Failed to get response');
      }

      removeLoadingMessage();

      // Create message element for streaming
      const contentEl = addMessage('assistant', '');
      let fullResponse = '';

      // Process streaming response
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

            if (data === '[DONE]') {
              continue;
            }

            try {
              const parsed = JSON.parse(data);

              // Handle Cloudflare Workers AI format (response can be null at end)
              if (parsed.response && typeof parsed.response === 'string') {
                fullResponse += parsed.response;
                contentEl.innerHTML = parseMarkdown(fullResponse);
                scrollToBottom();
              }
            } catch (parseError) {
              // Skip invalid JSON lines
            }
          }
        }
      }

      // Add assistant response to history
      if (fullResponse) {
        conversationHistory.push({
          role: 'assistant',
          content: fullResponse
        });
      }

    } catch (error) {
      console.error('Chat error:', error);
      removeLoadingMessage();
      addErrorMessage('Sorry, something went wrong. Please try again.');
    } finally {
      isStreaming = false;
      sendBtn.disabled = false;
      inputField.focus();
    }
  }

  // ==========================================================================
  // Markdown Parser (Simple)
  // ==========================================================================
  function parseMarkdown(text) {
    if (!text) return '';

    let html = escapeHtml(text);

    // Links: [text](url)
    html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');

    // Bold: **text** or __text__
    html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/__([^_]+)__/g, '<strong>$1</strong>');

    // Italic: *text* or _text_
    html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');
    html = html.replace(/_([^_]+)_/g, '<em>$1</em>');

    // Line breaks
    html = html.replace(/\n\n/g, '</p><p>');
    html = html.replace(/\n/g, '<br>');

    // Wrap in paragraph if not already
    if (!html.startsWith('<p>')) {
      html = '<p>' + html + '</p>';
    }

    // Simple list handling
    html = html.replace(/<p>- /g, '<ul><li>');
    html = html.replace(/<br>- /g, '</li><li>');

    // Clean up unclosed lists
    if (html.includes('<ul>')) {
      const parts = html.split('<ul>');
      for (let i = 1; i < parts.length; i++) {
        if (parts[i].includes('</li>') && !parts[i].includes('</ul>')) {
          const endIndex = parts[i].lastIndexOf('</li>');
          parts[i] = parts[i].substring(0, endIndex + 5) + '</ul>' + parts[i].substring(endIndex + 5);
        }
      }
      html = parts.join('<ul>');
    }

    return html;
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // ==========================================================================
  // Initialize on DOM Ready
  // ==========================================================================
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
