// Mobile navigation.
const navToggle = document.getElementById('navToggle');
const navLinks = document.getElementById('navLinks');

if (navToggle && navLinks) {
  navToggle.addEventListener('click', () => {
    const isOpen = navLinks.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', String(isOpen));
  });
}

// Keep the active navigation item correct for both the original .html pages
// and the database-backed .php pages.
function setActiveNavLink() {
  const currentPage = (window.location.pathname.split('/').pop() || 'index.php').replace(/\.html$/, '.php');
  document.querySelectorAll('.nav-links a').forEach((link) => {
    const linkPage = (link.getAttribute('href') || '').replace(/\.html$/, '.php');
    if (linkPage === currentPage) {
      link.classList.add('active');
    }
  });
}
setActiveNavLink();

// Restore the original public site's dark/light toggle on database-backed PHP
// pages, whose navigation currently contains an Admin link instead of a button.
const navRight = document.querySelector('.nav-right');
let themeToggle = document.getElementById('themeToggle');
if (!themeToggle && navRight && navRight.querySelector('a[href*="admin/"]')) {
  themeToggle = document.createElement('button');
  themeToggle.type = 'button';
  themeToggle.id = 'themeToggle';
  themeToggle.className = 'theme-toggle';
  themeToggle.setAttribute('aria-label', 'Toggle dark mode');
  navRight.insertBefore(themeToggle, navRight.firstChild);
}

const root = document.documentElement;
function applyTheme(theme) {
  const selectedTheme = theme === 'dark' ? 'dark' : 'light';
  root.setAttribute('data-theme', selectedTheme);
  if (themeToggle) {
    themeToggle.textContent = selectedTheme === 'dark' ? 'Light mode' : 'Dark mode';
    themeToggle.setAttribute('aria-pressed', String(selectedTheme === 'dark'));
  }
}

let savedTheme = 'light';
try {
  savedTheme = localStorage.getItem('theme') || 'light';
} catch (error) {
  // The site still works when browser storage is disabled.
}
applyTheme(savedTheme);

if (themeToggle) {
  themeToggle.addEventListener('click', () => {
    const newTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    applyTheme(newTheme);
    try {
      localStorage.setItem('theme', newTheme);
    } catch (error) {
      // Theme changes for this page view even when browser storage is disabled.
    }
  });
}

// Scroll-to-top button.
const scrollTopBtn = document.getElementById('scrollTopBtn');
if (scrollTopBtn) {
  window.addEventListener('scroll', () => {
    scrollTopBtn.classList.toggle('visible', window.scrollY > 400);
  });
  scrollTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

// Preserve validation for the original static contact form. The PHP contact
// form submits to the server and is intentionally not intercepted here.
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  const nameInput = document.getElementById('name');
  const emailInput = document.getElementById('email');
  const messageInput = document.getElementById('message');
  const nameError = document.getElementById('nameError');
  const emailError = document.getElementById('emailError');
  const messageError = document.getElementById('messageError');
  const formSuccess = document.getElementById('formSuccess');
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function showError(input, errorEl, message) {
    input.classList.add('invalid');
    errorEl.textContent = message;
  }
  function clearError(input, errorEl) {
    input.classList.remove('invalid');
    errorEl.textContent = '';
  }
  function validateForm() {
    let isValid = true;
    if (nameInput.value.trim().length < 2) {
      showError(nameInput, nameError, 'Please enter your full name.');
      isValid = false;
    } else clearError(nameInput, nameError);

    if (!emailPattern.test(emailInput.value.trim())) {
      showError(emailInput, emailError, 'Please enter a valid email address.');
      isValid = false;
    } else clearError(emailInput, emailError);

    if (messageInput.value.trim().length < 10) {
      showError(messageInput, messageError, 'Message should be at least 10 characters.');
      isValid = false;
    } else clearError(messageInput, messageError);
    return isValid;
  }

  contactForm.addEventListener('submit', (event) => {
    event.preventDefault();
    if (validateForm()) {
      formSuccess.textContent = 'Thanks! Your message has been sent.';
      contactForm.reset();
    } else {
      formSuccess.textContent = '';
    }
  });
  [nameInput, emailInput, messageInput].forEach((input) => {
    input.addEventListener('input', () => {
      if (input.classList.contains('invalid')) validateForm();
    });
  });
}

// Skill bar animation for legacy pages that use data-level attributes.
const skillBars = document.querySelectorAll('.skill-bar span[data-level]');
if (skillBars.length > 0) {
  window.addEventListener('load', () => {
    skillBars.forEach((bar) => {
      bar.style.width = `${Math.max(0, Math.min(100, Number(bar.dataset.level) || 0))}%`;
    });
  });
}
