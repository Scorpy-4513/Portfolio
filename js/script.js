//FEATURE 1: MOBILE NAVIGATION TOGGLE
const navToggle = document.getElementById('navToggle');
const navLinks = document.getElementById('navLinks');

if (navToggle && navLinks) {
  navToggle.addEventListener('click', () => {
    const isOpen = navLinks.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', isOpen);
  });
}

//FEATURE 2: ACTIVE NAVIGATION HIGHLIGHT
function setActiveNavLink() {
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  const links = document.querySelectorAll('.nav-links a');

  links.forEach(link => {
    const linkPage = link.getAttribute('href');
    if (linkPage === currentPage) {
      link.classList.add('active');
    }
  });
}
setActiveNavLink();

//FEATURE 3: DARK / LIGHT MODE
const themeToggle = document.getElementById('themeToggle');
const root = document.documentElement;

function applyTheme(theme) {
  root.setAttribute('data-theme', theme);
  if (themeToggle) {
    themeToggle.textContent = theme === 'dark' ? 'Dark|Light' : 'Light|Dark';
  }
}

const savedTheme = localStorage.getItem('theme') || 'light';
applyTheme(savedTheme);

if (themeToggle) {
  themeToggle.addEventListener('click', () => {
    const currentTheme = root.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    applyTheme(newTheme);
    localStorage.setItem('theme', newTheme);
  });
}

//FEATURE 4: SCROLL-TO-TOP BUTTON
const scrollTopBtn = document.getElementById('scrollTopBtn');

if (scrollTopBtn) {
  window.addEventListener('scroll', () => {
    if (window.scrollY > 400) {
      scrollTopBtn.classList.add('visible');
    } else {
      scrollTopBtn.classList.remove('visible');
    }
  });

  scrollTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

//FEATURE 5: CONTACT FORM VALIDATION
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
    } else {
      clearError(nameInput, nameError);
    }

    if (!emailPattern.test(emailInput.value.trim())) {
      showError(emailInput, emailError, 'Please enter a valid email address.');
      isValid = false;
    } else {
      clearError(emailInput, emailError);
    }

    if (messageInput.value.trim().length < 10) {
      showError(messageInput, messageError, 'Message should be at least 10 characters.');
      isValid = false;
    } else {
      clearError(messageInput, messageError);
    }

    return isValid;
  }

  contactForm.addEventListener('submit', (e) => {
    e.preventDefault();

    if (validateForm()) {
      formSuccess.textContent = 'Thanks! Your message has been sent.';
      contactForm.reset();
    } else {
      formSuccess.textContent = '';
    }
  });

  [nameInput, emailInput, messageInput].forEach((input) => {
    input.addEventListener('input', () => {
      if (input.classList.contains('invalid')) {
        validateForm();
      }
    });
  });
}

//FEATURE 6: SKILL BAR ANIMATION
const skillBars = document.querySelectorAll('.skill-bar span');

if (skillBars.length > 0) {
  window.addEventListener('load', () => {
    skillBars.forEach((bar) => {
      bar.style.width = bar.dataset.level + '%';
    });
  });
}
