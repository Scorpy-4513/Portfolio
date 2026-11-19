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
  const currentPage = window.location.pathname.split('/').pop() || 'index.php';
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

const savedTheme = localStorage.getItem('theme') || root.dataset.defaultTheme || 'light';
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

//FEATURE 6: SKILL BAR ANIMATION
const skillBars = document.querySelectorAll('.skill-bar span');

if (skillBars.length > 0) {
  window.addEventListener('load', () => {
    skillBars.forEach((bar) => {
      bar.style.width = bar.dataset.level + '%';
    });
  });
}
