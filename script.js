/**
 * Velocity - Modern Single-Page Website
 * Minimal JavaScript for interactions
 */

(function() {
  'use strict';

  // ==========================================================================
  // DOM Elements
  // ==========================================================================
  const header = document.getElementById('header');
  const mobileMenuBtn = document.getElementById('mobile-menu-btn');
  const navLinks = document.getElementById('nav-links');
  const ctaForm = document.getElementById('cta-form');
  const fadeElements = document.querySelectorAll('.fade-in');

  // ==========================================================================
  // Header Scroll Effect
  // ==========================================================================
  let lastScrollY = 0;
  let ticking = false;

  function updateHeader() {
    const scrollY = window.scrollY;

    if (scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }

    lastScrollY = scrollY;
    ticking = false;
  }

  function onScroll() {
    if (!ticking) {
      window.requestAnimationFrame(updateHeader);
      ticking = true;
    }
  }

  window.addEventListener('scroll', onScroll, { passive: true });

  // ==========================================================================
  // Mobile Menu Toggle
  // ==========================================================================
  function toggleMobileMenu() {
    mobileMenuBtn.classList.toggle('active');
    navLinks.classList.toggle('mobile-open');

    const isOpen = navLinks.classList.contains('mobile-open');
    mobileMenuBtn.setAttribute('aria-expanded', isOpen);
  }

  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', toggleMobileMenu);
  }

  // Close mobile menu when clicking on a link
  if (navLinks) {
    navLinks.addEventListener('click', function(e) {
      if (e.target.classList.contains('nav-link')) {
        mobileMenuBtn.classList.remove('active');
        navLinks.classList.remove('mobile-open');
        mobileMenuBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Close mobile menu when clicking outside
  document.addEventListener('click', function(e) {
    if (navLinks && navLinks.classList.contains('mobile-open')) {
      if (!navLinks.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
        mobileMenuBtn.classList.remove('active');
        navLinks.classList.remove('mobile-open');
        mobileMenuBtn.setAttribute('aria-expanded', 'false');
      }
    }
  });

  // ==========================================================================
  // Intersection Observer for Fade-in Animations
  // ==========================================================================
  const observerOptions = {
    root: null,
    rootMargin: '0px 0px -50px 0px',
    threshold: 0.1
  };

  const observerCallback = function(entries, observer) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  };

  const observer = new IntersectionObserver(observerCallback, observerOptions);

  fadeElements.forEach(function(element) {
    observer.observe(element);
  });

  // ==========================================================================
  // Smooth Scroll for Anchor Links
  // ==========================================================================
  document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
    anchor.addEventListener('click', function(e) {
      const href = this.getAttribute('href');

      // Skip if it's just "#"
      if (href === '#') return;

      const target = document.querySelector(href);

      if (target) {
        e.preventDefault();

        const headerHeight = header.offsetHeight;
        const targetPosition = target.getBoundingClientRect().top + window.scrollY;
        const offsetPosition = targetPosition - headerHeight - 20;

        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth'
        });
      }
    });
  });

  // ==========================================================================
  // Form Handling (Web Awesome components)
  // ==========================================================================
  if (ctaForm) {
    ctaForm.addEventListener('submit', function(e) {
      e.preventDefault();

      // Web Awesome uses wa-input custom element
      const emailInput = this.querySelector('wa-input');
      const submitBtn = this.querySelector('wa-button[type="submit"]');

      // Get value from wa-input (web component)
      const email = emailInput ? emailInput.value : '';

      // Validate email
      if (!email || !isValidEmail(email)) {
        shakeElement(emailInput);
        return;
      }

      // Store original button content
      const originalContent = submitBtn.innerHTML;

      // Simulate form submission
      submitBtn.innerHTML = '<wa-spinner></wa-spinner> Submitting...';
      submitBtn.disabled = true;

      // Simulate async request
      setTimeout(function() {
        submitBtn.innerHTML = '<wa-icon name="check" slot="prefix"></wa-icon> Success!';

        // Clear the input
        if (emailInput) {
          emailInput.value = '';
        }

        // Reset button after 3 seconds
        setTimeout(function() {
          submitBtn.innerHTML = originalContent;
          submitBtn.disabled = false;
        }, 3000);
      }, 1000);
    });
  }

  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  function shakeElement(element) {
    if (!element) return;
    element.style.animation = 'shake 0.5s ease';
    element.addEventListener('animationend', function() {
      element.style.animation = '';
    }, { once: true });
  }

  // Add shake animation dynamically
  const style = document.createElement('style');
  style.textContent = `
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
      20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
  `;
  document.head.appendChild(style);

  // ==========================================================================
  // Keyboard Navigation
  // ==========================================================================
  document.addEventListener('keydown', function(e) {
    // Close mobile menu on Escape
    if (e.key === 'Escape' && navLinks.classList.contains('mobile-open')) {
      mobileMenuBtn.classList.remove('active');
      navLinks.classList.remove('mobile-open');
      mobileMenuBtn.setAttribute('aria-expanded', 'false');
      mobileMenuBtn.focus();
    }
  });

  // ==========================================================================
  // Initial State
  // ==========================================================================
  // Check initial scroll position
  updateHeader();

  // Set initial ARIA state for mobile menu
  if (mobileMenuBtn) {
    mobileMenuBtn.setAttribute('aria-expanded', 'false');
    mobileMenuBtn.setAttribute('aria-controls', 'nav-links');
  }

  // ==========================================================================
  // Theme Color Picker
  // ==========================================================================
  const themeColorPicker = document.getElementById('theme-color-picker');

  if (themeColorPicker) {
    // Wait for Web Awesome to be ready
    customElements.whenDefined('wa-color-picker').then(function() {
      // Load saved theme color from localStorage
      const savedColor = localStorage.getItem('theme-color');
      if (savedColor) {
        themeColorPicker.value = savedColor;
        applyThemeColor(savedColor);
      }

      // Listen for color changes (real-time as user adjusts)
      themeColorPicker.addEventListener('input', function(e) {
        applyThemeColor(themeColorPicker.value);
      });

      // Save color on final selection
      themeColorPicker.addEventListener('change', function(e) {
        const color = themeColorPicker.value;
        localStorage.setItem('theme-color', color);
        applyThemeColor(color);
      });
    });
  }

  /**
   * Apply theme color to CSS custom properties
   * @param {string} hexColor - Hex color value (e.g., "#2563EB")
   */
  function applyThemeColor(hexColor) {
    const root = document.documentElement;

    // Parse hex to RGB
    const rgb = hexToRgb(hexColor);
    if (!rgb) return;

    // Generate color variants
    const primaryColor = hexColor;
    const primaryHover = adjustBrightness(hexColor, -15);
    const primaryLight = adjustBrightness(hexColor, 85, 0.15);

    // Apply to CSS custom properties
    root.style.setProperty('--color-primary', primaryColor);
    root.style.setProperty('--color-primary-hover', primaryHover);
    root.style.setProperty('--color-primary-light', primaryLight);

    // Update Web Awesome component variables
    root.style.setProperty('--wa-color-brand-600', primaryColor);
    root.style.setProperty('--wa-color-brand-700', primaryHover);
  }

  /**
   * Convert hex color to RGB object
   * @param {string} hex - Hex color string
   * @returns {object|null} RGB values or null if invalid
   */
  function hexToRgb(hex) {
    // Remove # if present
    hex = hex.replace(/^#/, '');

    // Handle shorthand hex (e.g., "03F")
    if (hex.length === 3) {
      hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
    }

    const result = /^([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
      r: parseInt(result[1], 16),
      g: parseInt(result[2], 16),
      b: parseInt(result[3], 16)
    } : null;
  }

  /**
   * Convert RGB to hex color
   * @param {number} r - Red (0-255)
   * @param {number} g - Green (0-255)
   * @param {number} b - Blue (0-255)
   * @returns {string} Hex color string
   */
  function rgbToHex(r, g, b) {
    return '#' + [r, g, b].map(function(x) {
      const hex = Math.round(Math.max(0, Math.min(255, x))).toString(16);
      return hex.length === 1 ? '0' + hex : hex;
    }).join('');
  }

  /**
   * Adjust brightness of a hex color
   * @param {string} hex - Hex color string
   * @param {number} percent - Percentage to adjust (-100 to 100)
   * @param {number} [saturationMultiplier] - Optional saturation adjustment for light colors
   * @returns {string} Adjusted hex color
   */
  function adjustBrightness(hex, percent, saturationMultiplier) {
    const rgb = hexToRgb(hex);
    if (!rgb) return hex;

    if (percent > 0) {
      // Lighten: blend with white
      const factor = percent / 100;
      return rgbToHex(
        rgb.r + (255 - rgb.r) * factor,
        rgb.g + (255 - rgb.g) * factor,
        rgb.b + (255 - rgb.b) * factor
      );
    } else {
      // Darken: reduce values
      const factor = 1 + (percent / 100);
      return rgbToHex(
        rgb.r * factor,
        rgb.g * factor,
        rgb.b * factor
      );
    }
  }

})();
