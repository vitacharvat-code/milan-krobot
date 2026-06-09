/* Milan Krobot – main.js */

// --- Navigation: scroll state & mobile menu ---
const nav       = document.getElementById('nav');
const navBurger = document.getElementById('navBurger');
const navLinks  = document.getElementById('navLinks');

window.addEventListener('scroll', () => {
  nav.classList.toggle('scrolled', window.scrollY > 50);
}, { passive: true });

navBurger.addEventListener('click', () => {
  const open = navBurger.classList.toggle('open');
  navLinks.classList.toggle('open', open);
  document.body.style.overflow = open ? 'hidden' : '';
});

navLinks.querySelectorAll('.nav__link').forEach(link => {
  link.addEventListener('click', () => {
    navBurger.classList.remove('open');
    navLinks.classList.remove('open');
    document.body.style.overflow = '';
  });
});

// --- Count-up animation ---
function countUp(el) {
  const target   = parseInt(el.dataset.count, 10);
  const suffix   = el.dataset.suffix || '';
  const duration = 1400;
  const start    = performance.now();
  const tick = (now) => {
    const progress = Math.min((now - start) / duration, 1);
    const eased    = 1 - Math.pow(1 - progress, 3);
    el.textContent = Math.floor(eased * target) + suffix;
    if (progress < 1) requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
}

// --- Scroll animations ---
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (!entry.isIntersecting) return;
    const el    = entry.target;
    const delay = parseInt(el.dataset.delay || '0', 10);
    setTimeout(() => {
      el.classList.add('visible');
      el.querySelectorAll('[data-count]').forEach(countEl => countUp(countEl));
    }, delay);
    observer.unobserve(el);
  });
}, { threshold: 0.12 });

document.querySelectorAll('[data-animate]').forEach(el => observer.observe(el));

// --- Gallery lightbox ---
const galleryItems = Array.from(document.querySelectorAll('.gallery__item'));
const lightbox     = document.getElementById('lightbox');
const lightboxImg  = document.getElementById('lightboxImg');
const lightboxClose = document.getElementById('lightboxClose');
const lightboxPrev = document.getElementById('lightboxPrev');
const lightboxNext = document.getElementById('lightboxNext');

const photos = galleryItems.map(item => {
  const img = item.querySelector('img');
  return { src: img.src, alt: img.alt };
});

let activeIndex = 0;

function openLightbox(index) {
  activeIndex = index;
  lightboxImg.src = photos[index].src;
  lightboxImg.alt = photos[index].alt;
  lightbox.classList.add('open');
  document.body.style.overflow = 'hidden';
  lightboxClose.focus();
}

function closeLightbox() {
  lightbox.classList.remove('open');
  document.body.style.overflow = '';
}

function showPhoto(index) {
  activeIndex = (index + photos.length) % photos.length;
  lightboxImg.src = photos[activeIndex].src;
  lightboxImg.alt = photos[activeIndex].alt;
}

galleryItems.forEach((item, i) => {
  item.addEventListener('click', () => openLightbox(i));
  item.setAttribute('tabindex', '0');
  item.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openLightbox(i); }
  });
});

lightboxClose.addEventListener('click', closeLightbox);
lightbox.addEventListener('click', e => { if (e.target === lightbox) closeLightbox(); });
lightboxPrev.addEventListener('click', e => { e.stopPropagation(); showPhoto(activeIndex - 1); });
lightboxNext.addEventListener('click', e => { e.stopPropagation(); showPhoto(activeIndex + 1); });

document.addEventListener('keydown', e => {
  if (!lightbox.classList.contains('open')) return;
  if (e.key === 'Escape')      closeLightbox();
  if (e.key === 'ArrowLeft')   showPhoto(activeIndex - 1);
  if (e.key === 'ArrowRight')  showPhoto(activeIndex + 1);
});

// Touch swipe support for lightbox
let touchStartX = 0;
lightbox.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
lightbox.addEventListener('touchend', e => {
  const diff = touchStartX - e.changedTouches[0].clientX;
  if (Math.abs(diff) > 50) showPhoto(diff > 0 ? activeIndex + 1 : activeIndex - 1);
}, { passive: true });

// --- Contact form ---
const contactForm = document.getElementById('contactForm');
const submitBtn   = document.getElementById('submitBtn');
const formSuccess = document.getElementById('formSuccess');

contactForm.addEventListener('submit', async e => {
  e.preventDefault();
  submitBtn.textContent = 'Odesílám…';
  submitBtn.disabled = true;

  try {
    const res  = await fetch(contactForm.action, {
      method: 'POST',
      body: new FormData(contactForm),
      headers: { Accept: 'application/json' },
    });
    const data = await res.json();

    if (data.success) {
      contactForm.reset();
      formSuccess.hidden        = false;
      submitBtn.style.display   = 'none';
    } else {
      throw new Error(data.message || 'Server error');
    }
  } catch {
    submitBtn.textContent = 'Odeslat zprávu';
    submitBtn.disabled = false;
    alert('Nastala chyba. Zkuste to prosím znovu nebo napište přímo na krobot.milan@seznam.cz');
  }
});

// --- Smooth active nav link highlight on scroll ---
const sections = document.querySelectorAll('section[id]');
const navAnchors = document.querySelectorAll('.nav__link:not(.nav__link--cta)');

const sectionObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (!entry.isIntersecting) return;
    const id = entry.target.id;
    navAnchors.forEach(a => {
      a.classList.toggle('active', a.getAttribute('href') === `#${id}`);
    });
  });
}, { rootMargin: '-40% 0px -55% 0px' });

sections.forEach(s => sectionObserver.observe(s));

// --- Gallery: show 8 random, rest hidden ---
const galleryGrid = document.querySelector('.gallery__grid');
const loadMoreBtn = document.getElementById('galleryLoadMore');
const allGalleryItems = document.querySelectorAll('.gallery__item');

function initGallery() {
  const total = allGalleryItems.length;
  const showCount = 8;
  const indices = Array.from({ length: total }, (_, i) => i);

  // Shuffle and select 8 random
  for (let i = indices.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [indices[i], indices[j]] = [indices[j], indices[i]];
  }
  const visibleIndices = new Set(indices.slice(0, showCount));

  // Hide items not in visible set
  allGalleryItems.forEach((item, i) => {
    if (!visibleIndices.has(i)) {
      item.style.display = 'none';
    }
  });

  // Show button if there are hidden items
  if (total > showCount) {
    loadMoreBtn.style.display = 'inline-block';
  }
}

loadMoreBtn.addEventListener('click', () => {
  // Show all hidden items
  allGalleryItems.forEach(item => {
    item.style.display = '';
  });
  loadMoreBtn.style.display = 'none';
});

initGallery();

// --- Audio samples for references ---
const audioSampleBtns = document.querySelectorAll('.audio-sample-btn');
const audioPlayer = document.getElementById('audio-player');
const audioSamples = {
  'bmw': 'assets/audio/bmw.mp3',
  'elementstore': 'assets/audio/elementstore.mp3',
  'dmg-mori': 'assets/audio/dmg-mori.mp3',
};

if (audioPlayer) {
  audioSampleBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const audioKey = btn.dataset.audio;
      const audioSrc = audioSamples[audioKey];
      if (audioSrc) {
        audioPlayer.src = audioSrc;
        audioPlayer.play();
      }
    });
  });
}
