/* ─── LNC MAIN.JS — Vanilla JS Interactions ─────────────────
   No frameworks. Handles: nav scroll, tabs, booking steps,
   invoice tabs, team cards, legal sidebar, smooth scroll.
──────────────────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function () {

  /* ── NAV SCROLL ── */
  const nav = document.querySelector('.nav');
  if (nav) {
    const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 60);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ── SMOOTH ANCHOR SCROLL ── */
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
    });
  });

  /* ── GENERIC TABS (data-tabs / data-tab-content) ── */
  function initTabs(containerSelector, tabSelector, contentSelector, activeClass) {
    document.querySelectorAll(containerSelector).forEach(container => {
      const tabs    = container.querySelectorAll(tabSelector);
      const panels  = container.querySelectorAll(contentSelector);
      tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => {
          tabs.forEach(t => t.classList.remove(activeClass || 'active'));
          panels.forEach(p => p.style.display = 'none');
          tab.classList.add(activeClass || 'active');
          if (panels[i]) panels[i].style.display = '';
        });
      });
      // init: show first
      if (panels.length) {
        panels.forEach((p, i) => p.style.display = i === 0 ? '' : 'none');
        if (tabs.length) tabs[0].classList.add(activeClass || 'active');
      }
    });
  }

  /* ── ZONE TABS (hotels page) ── */
  initTabs('.hotels-tabs-wrapper', '.zone-tab', '.zone-panel');

  /* ── EXPERIENCE BAR TABS ── */
  const expBarItems = document.querySelectorAll('.exp-bar__item[data-cat]');
  const pkgCards    = document.querySelectorAll('.pkg-card[data-cat]');
  if (expBarItems.length && pkgCards.length) {
    expBarItems.forEach(btn => {
      btn.addEventListener('click', () => {
        const cat = btn.dataset.cat;
        expBarItems.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        pkgCards.forEach(card => {
          if (cat === 'all' || card.dataset.cat === cat) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  }

  /* ── EXPERIENCE PAGE TABS ── */
  const expTabBtns   = document.querySelectorAll('.exp-tab-btn[role="tab"]');
  const expTabPanels = document.querySelectorAll('.exp-tab-panel[role="tabpanel"]');
  if (expTabBtns.length) {
    function activateExpTab(btn, panel) {
      expTabBtns.forEach(b => {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
        b.setAttribute('tabindex', '-1');
      });
      expTabPanels.forEach(p => { p.hidden = true; });
      btn.classList.add('active');
      btn.setAttribute('aria-selected', 'true');
      btn.setAttribute('tabindex', '0');
      if (panel) panel.hidden = false;
    }
    expTabBtns.forEach((btn, i) => {
      btn.addEventListener('click', () => {
        activateExpTab(btn, expTabPanels[i]);
        const tabsEl = document.querySelector('.exp-tabs');
        if (tabsEl) window.scrollTo({ top: tabsEl.offsetTop - 72, behavior: 'smooth' });
      });
      // Arrow key navigation for keyboard users
      btn.addEventListener('keydown', e => {
        const idx = Array.from(expTabBtns).indexOf(btn);
        if (e.key === 'ArrowRight') {
          e.preventDefault();
          const next = expTabBtns[(idx + 1) % expTabBtns.length];
          next.focus(); activateExpTab(next, expTabPanels[(idx + 1) % expTabBtns.length]);
        } else if (e.key === 'ArrowLeft') {
          e.preventDefault();
          const prev = expTabBtns[(idx - 1 + expTabBtns.length) % expTabBtns.length];
          prev.focus(); activateExpTab(prev, expTabPanels[(idx - 1 + expTabBtns.length) % expTabBtns.length]);
        }
      });
    });
    // Init: first tab active (already set via PHP hidden attr, just sync JS state)
    if (expTabBtns[0]) expTabBtns[0].classList.add('active');
  }

  /* ── INVOICE STAGE TABS ── */
  const invTabBtns   = document.querySelectorAll('.inv-tab-btn');
  const invTabPanels = document.querySelectorAll('.inv-tab-panel');
  if (invTabBtns.length) {
    invTabBtns.forEach((btn, i) => {
      btn.addEventListener('click', () => {
        invTabBtns.forEach(b => b.classList.remove('act'));
        invTabPanels.forEach(p => p.style.display = 'none');
        btn.classList.add('act');
        if (invTabPanels[i]) invTabPanels[i].style.display = '';
      });
    });
    invTabPanels.forEach((p, i) => p.style.display = i === 0 ? '' : 'none');
    invTabBtns[0] && invTabBtns[0].classList.add('act');
  }

  /* ── LEGAL SIDEBAR HIGHLIGHT ── */
  const legalLinks   = document.querySelectorAll('.sidebar-nav-link[data-target]');
  const legalSections = document.querySelectorAll('.legal-section');
  if (legalLinks.length && legalSections.length) {
    legalLinks.forEach(link => {
      link.addEventListener('click', () => {
        const target = document.getElementById(link.dataset.target);
        if (target) {
          legalLinks.forEach(l => l.classList.remove('active'));
          link.classList.add('active');
          const offset = target.getBoundingClientRect().top + window.scrollY - 160;
          window.scrollTo({ top: offset, behavior: 'smooth' });
        }
      });
    });
    // Scroll spy
    window.addEventListener('scroll', () => {
      let current = '';
      legalSections.forEach(sec => {
        if (window.scrollY >= sec.offsetTop - 180) current = sec.id;
      });
      legalLinks.forEach(l => l.classList.toggle('active', l.dataset.target === current));
    }, { passive: true });
  }

  /* ── TEAM CARD EXPAND ── */
  const memberCards    = document.querySelectorAll('.member-card[data-member]');
  const memberExpanded = document.getElementById('member-expanded');
  if (memberCards.length && memberExpanded) {
    memberCards.forEach(card => {
      card.addEventListener('click', () => {
        const idx = card.dataset.member;
        const data = window.LNC_TEAM[idx];
        if (!data) return;
        if (memberExpanded.dataset.open === idx) {
          memberExpanded.style.display = 'none';
          memberExpanded.dataset.open = '';
          return;
        }
        memberExpanded.dataset.open = idx;
        const initials = data.initial || data.name.charAt(0).toUpperCase();
        const accentColors = ['#2cb896','#38a8d8','#c4964a','#2cb896'];
        const accentColor  = accentColors[idx % accentColors.length];
        const initFontSize = initials.length > 1 ? '20px' : '28px';
        memberExpanded.innerHTML = `
          <div>
            <div style="height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;background:linear-gradient(160deg,#111a0f 0%,#1e2a20 100%);position:relative;overflow:hidden;">
              <div style="position:absolute;inset:0;background:radial-gradient(circle at 50% 80%,rgba(44,184,150,.1) 0%,transparent 60%);"></div>
              <div style="width:88px;height:88px;border-radius:50%;border:2px solid ${accentColor};display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.04);position:relative;margin-bottom:12px;">
                <span style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:${initFontSize};color:${accentColor};letter-spacing:-.02em;">${initials}</span>
              </div>
              <span style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.3);">LOMBOK, INDONESIA</span>
            </div>
            <div style="margin-top:16px;">
              <span style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.35);display:block;margin-bottom:6px;">Languages</span>
              <p style="font-family:'MuseoModerno',sans-serif;font-size:13px;color:rgba(255,255,255,.65);">${data.lang}</p>
            </div>
            <div style="margin-top:14px;">
              <span style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.35);display:block;margin-bottom:6px;">Expertise</span>
              <p style="font-family:'Museo',sans-serif;font-size:12px;color:rgba(255,255,255,.5);line-height:1.7;">${data.cert}</p>
            </div>
          </div>
          <div>
            <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#2cb896;margin-bottom:8px;">${data.role}</p>
            <h3 style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:32px;color:#fff;margin-bottom:8px;">${data.name}</h3>
            <p style="font-family:'MuseoModerno',sans-serif;font-weight:500;font-size:12px;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:20px;">${data.spec}</p>
            <p style="font-family:'Museo',sans-serif;font-size:15px;color:rgba(255,255,255,.7);line-height:1.85;margin-bottom:24px;">${data.bio}</p>
            <a href="booking.php" style="display:inline-block;padding:12px 28px;background:#2cb896;color:#fff;font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:11px;letter-spacing:.12em;text-transform:uppercase;">Book a Journey</a>
          </div>
          <button onclick="this.closest('.member-expanded').style.display='none';this.closest('.member-expanded').dataset.open='';"
            style="grid-column:1/-1;background:none;border:none;color:rgba(255,255,255,.35);font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:10px;letter-spacing:.14em;text-transform:uppercase;cursor:pointer;text-align:left;padding:16px 0 0;border-top:1px solid rgba(255,255,255,.07);">
            Close ↑
          </button>
        `;
        memberExpanded.style.display = 'grid';
        memberExpanded.scrollIntoView({ block: 'nearest' });
      });
    });
  }

  /* ── BOOKING MULTI-STEP FORM ── */
  const bookingForm = document.getElementById('booking-form');
  if (bookingForm) {
    let currentStep = 0;
    const steps      = bookingForm.querySelectorAll('.booking-step');
    const bubbles    = document.querySelectorAll('.progress-step__bubble');
    const connectors = document.querySelectorAll('.progress-connector');
    const btnNext    = document.getElementById('btn-next');
    const btnBack    = document.getElementById('btn-back');

    function getVal(name) {
      const el = bookingForm.querySelector(`[name="${name}"]`);
      if (!el) return '';
      if (el.type === 'radio') {
        const checked = bookingForm.querySelector(`[name="${name}"]:checked`);
        return checked ? checked.value : '';
      }
      return el.value || '';
    }

    function populateReview() {
      const selected = bookingForm.querySelector('.pkg-option.selected');
      const map = {
        'summary-experience':   selected ? selected.dataset.title    : '—',
        'summary-duration':     selected ? selected.dataset.duration : '—',
        'summary-dates':        getVal('dates')  || '—',
        'summary-guests':       getVal('guests') ? getVal('guests') + ' guest(s)' : '—',
        'summary-accommodation':getVal('accommodation') || '—',
        'summary-name':         getVal('name')   || '—',
        'summary-email':        getVal('email')  || '—',
        'summary-phone':        getVal('phone')  || '—',
      };
      Object.entries(map).forEach(([id, val]) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
      });
    }

    function updateSidebar() {
      const selected = bookingForm.querySelector('.pkg-option.selected');
      const hint = document.getElementById('booking-summary-content');
      if (selected) {
        if (hint) hint.style.display = 'none';
        [['summary-experience', selected.dataset.title], ['summary-price', selected.dataset.price]].forEach(([id, val]) => {
          const row = document.getElementById(id + '-row');
          const el  = document.getElementById(id + '-sidebar');
          if (row) row.style.display = '';
          if (el)  el.textContent = val;
        });
      }
      const dates  = getVal('dates');
      const guests = getVal('guests');
      if (dates) {
        const row = document.getElementById('summary-dates-row');
        const el  = document.getElementById('summary-dates-sidebar');
        if (row) row.style.display = '';
        if (el)  el.textContent = dates;
      }
      if (guests) {
        const row = document.getElementById('summary-guests-row');
        const el  = document.getElementById('summary-guests-sidebar');
        if (row) row.style.display = '';
        if (el)  el.textContent = guests + ' guest(s)';
      }
    }

    function showStep(n) {
      steps.forEach((s, i) => s.style.display = i === n ? '' : 'none');
      bubbles.forEach((b, i) => {
        b.classList.remove('progress-step__bubble--active', 'progress-step__bubble--done');
        if (i < n) { b.classList.add('progress-step__bubble--done'); b.textContent = '✓'; }
        else if (i === n) b.classList.add('progress-step__bubble--active');
        else b.textContent = (i + 1).toString();
      });
      connectors.forEach((c, i) => c.classList.toggle('progress-connector--done', i < n));
      btnBack.style.display = n > 0 ? '' : 'none';
      btnNext.textContent   = n < steps.length - 1 ? 'Continue →' : 'Submit Request ✓';
      if (n === steps.length - 1) populateReview();
      currentStep = n;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function clearStepErrors(step) {
      step.querySelectorAll('.bk-field-error').forEach(el => el.classList.remove('bk-field-error'));
      step.querySelectorAll('.bk-error-msg').forEach(el => el.remove());
      step.querySelectorAll('.pkg-option--error').forEach(el => el.classList.remove('pkg-option--error'));
    }

    function showFieldError(field, msg) {
      field.classList.add('bk-field-error');
      const existing = field.parentNode.querySelector('.bk-error-msg');
      if (!existing) {
        const span = document.createElement('span');
        span.className = 'bk-error-msg';
        span.textContent = msg;
        field.parentNode.appendChild(span);
      }
    }

    function validateStep(n) {
      const step = steps[n];
      clearStepErrors(step);
      let valid = true;

      if (n === 0) {
        // Step 1: package must be selected
        const selected = bookingForm.querySelector('.pkg-option.selected');
        if (!selected) {
          bookingForm.querySelectorAll('.pkg-option').forEach(o => o.classList.add('pkg-option--error'));
          const hint = document.createElement('p');
          hint.className = 'bk-error-msg';
          hint.style.marginTop = '8px';
          hint.style.fontSize = '13px';
          hint.textContent = 'Please select an experience to continue.';
          step.querySelector('.bk-step__sub').insertAdjacentElement('afterend', hint);
          valid = false;
        }
      }

      if (n === 2) {
        // Step 3: name and email required
        const nameEl  = bookingForm.querySelector('[name="name"]');
        const emailEl = bookingForm.querySelector('[name="email"]');
        if (nameEl && nameEl.value.trim().length < 2) {
          showFieldError(nameEl, 'Please enter your full name.');
          valid = false;
        }
        if (emailEl) {
          const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailPattern.test(emailEl.value.trim())) {
            showFieldError(emailEl, 'Please enter a valid email address.');
            valid = false;
          }
        }
      }

      if (!valid) {
        // Scroll to first error
        const firstError = step.querySelector('.bk-field-error, .pkg-option--error, .bk-error-msg');
        if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      return valid;
    }

    btnNext && btnNext.addEventListener('click', () => {
      if (currentStep < steps.length - 1) {
        if (!validateStep(currentStep)) return;
        showStep(currentStep + 1);
      } else {
        // Final submit — add loading state
        btnNext.classList.add('btn--loading');
        btnNext.disabled = true;
        bookingForm.action = 'process-booking.php';
        bookingForm.method = 'POST';
        bookingForm.submit();
      }
    });

    btnBack && btnBack.addEventListener('click', () => {
      if (currentStep > 0) showStep(currentStep - 1);
    });

    // Package selection cards
    const pkgOptions = bookingForm.querySelectorAll('.pkg-option');
    pkgOptions.forEach(opt => {
      opt.addEventListener('click', () => {
        pkgOptions.forEach(o => {
          o.classList.remove('selected');
          o.style.borderColor = '#e0d8ce';
          const dot = o.querySelector('.pkg-radio-dot');
          if (dot) { dot.style.background = 'transparent'; dot.style.borderColor = '#c5b9ad'; }
          const r = o.querySelector('input[type="radio"]');
          if (r) r.checked = false;
        });
        opt.classList.add('selected');
        opt.style.borderColor = '#2cb896';
        const dot = opt.querySelector('.pkg-radio-dot');
        if (dot) { dot.style.background = '#2cb896'; dot.style.borderColor = '#2cb896'; }
        const r = opt.querySelector('input[type="radio"]');
        if (r) r.checked = true;
        updateSidebar();
      });
    });

    // Source pill styling
    bookingForm.querySelectorAll('[name="source"]').forEach(r => {
      r.addEventListener('change', () => {
        bookingForm.querySelectorAll('[name="source"]').forEach(rb => {
          const lbl = rb.closest('label');
          if (lbl) {
            lbl.style.borderColor = rb.checked ? '#2cb896' : '#e0d8ce';
            lbl.style.color       = rb.checked ? '#2cb896' : '#8a7d6e';
            lbl.style.background  = rb.checked ? '#f0faf7' : '#fff';
          }
        });
      });
    });

    // Accommodation pill styling
    bookingForm.querySelectorAll('[name="accommodation"]').forEach(r => {
      r.addEventListener('change', () => {
        bookingForm.querySelectorAll('[name="accommodation"]').forEach(rb => {
          const lbl = rb.closest('label');
          if (lbl) {
            lbl.style.borderColor = rb.checked ? '#2cb896' : '#e0d8ce';
            lbl.style.background  = rb.checked ? '#f0faf7' : '#fff';
          }
        });
      });
    });

    // Sync sidebar on input changes
    bookingForm.querySelectorAll('[name="dates"], [name="guests"]').forEach(el => {
      el.addEventListener('change', updateSidebar);
    });

    // Auto-select preloaded package (from URL ?package= or PHP $prefill_exp)
    const preSelected = bookingForm.querySelector('.pkg-option.selected');
    if (preSelected) {
      preSelected.style.borderColor = '#2cb896';
      const dot = preSelected.querySelector('.pkg-radio-dot');
      if (dot) { dot.style.background = '#2cb896'; dot.style.borderColor = '#2cb896'; }
      updateSidebar();
    }

    showStep(0);
  }

  /* ── PRINT INVOICE ── */
  const printBtn = document.getElementById('btn-print');
  if (printBtn) printBtn.addEventListener('click', () => window.print());

  /* ── HERO ENTRANCE ANIMATION ── */
  const heroEl = document.querySelector('.hero');
  if (heroEl) {
    requestAnimationFrame(() => {
      setTimeout(() => heroEl.classList.add('hero--loaded'), 80);
    });
  }

  /* ── SCROLL REVEAL (IntersectionObserver) ── */
  if ('IntersectionObserver' in window) {

    const revealObs = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          revealObs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.10, rootMargin: '0px 0px -40px 0px' });

    // Block elements — simple fade-up
    ['.eyebrow', '.section-title', '.section-body', '.editorial', '.partners',
     '.packages-header', '.zone-tabs', '.steps::before']
      .forEach(sel => {
        document.querySelectorAll(sel).forEach(el => {
          el.classList.add('reveal', 'reveal--fade');
          revealObs.observe(el);
        });
      });

    // Grid/card items — scale + stagger
    ['.pkg-card', '.hotel-card', '.member-card', '.testi-card',
     '.trust-card', '.pillar', '.step', '.partners__logo-item']
      .forEach(sel => {
        document.querySelectorAll(sel).forEach((el, i) => {
          el.classList.add('reveal', 'reveal--scale');
          const d = i % 4;
          if (d > 0) el.classList.add(`reveal--d${d}`);
          revealObs.observe(el);
        });
      });

    // Stats bar — slide up one by one
    document.querySelectorAll('.stat').forEach((el, i) => {
      el.classList.add('reveal');
      if (i > 0) el.classList.add(`reveal--d${Math.min(i, 6)}`);
      revealObs.observe(el);
    });

    // Section headings / pillars — slide from left
    document.querySelectorAll('.section > .container > .eyebrow, .section > .container > .section-title').forEach((el, i) => {
      el.classList.add('reveal', 'reveal--left');
      revealObs.observe(el);
    });
  }

  /* ── STAT COUNTER ANIMATION ── */
  const statNums = document.querySelectorAll('.stat__num');
  if (statNums.length && 'IntersectionObserver' in window) {
    const counterObs = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el       = entry.target;
        const original = el.textContent.trim();
        const numMatch = original.match(/([\d]+)/);
        if (!numMatch) return;
        const target = parseInt(numMatch[1], 10);
        const prefix = original.slice(0, original.search(/\d/));
        const suffix = original.slice(original.search(/\d/) + numMatch[1].length);
        const duration = 1800;
        const startTime = performance.now();
        function tick(now) {
          const p    = Math.min((now - startTime) / duration, 1);
          const ease = 1 - Math.pow(1 - p, 3);
          el.textContent = prefix + Math.round(ease * target) + suffix;
          if (p < 1) requestAnimationFrame(tick);
          else el.textContent = original;
        }
        requestAnimationFrame(tick);
        counterObs.unobserve(el);
      });
    }, { threshold: 0.6 });
    statNums.forEach(el => counterObs.observe(el));
  }

});

/* ── MOBILE HAMBURGER MENU ── */
(function () {
  const hamburger  = document.getElementById('nav-hamburger');
  const mobileMenu = document.getElementById('nav-mobile-menu');
  const closeBtn   = document.getElementById('nav-mobile-close');

  if (!hamburger || !mobileMenu) return;

  function isOpen() {
    return mobileMenu.classList.contains('open');
  }

  function openMenu() {
    mobileMenu.classList.add('open');
    mobileMenu.removeAttribute('hidden');
    hamburger.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
    // Move focus into menu for keyboard/screen reader users
    const firstLink = mobileMenu.querySelector('.nav__mobile-link, a, button');
    if (firstLink) firstLink.focus();
  }

  function closeMenu() {
    mobileMenu.classList.remove('open');
    mobileMenu.setAttribute('hidden', '');
    hamburger.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    hamburger.focus();
  }

  hamburger.addEventListener('click', openMenu);
  closeBtn  && closeBtn.addEventListener('click', closeMenu);

  // Close when any mobile link is clicked
  mobileMenu.querySelectorAll('.nav__mobile-link').forEach(link => {
    link.addEventListener('click', closeMenu);
  });

  // Close on ESC key
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && isOpen()) closeMenu();
  });

  // Close on overlay tap (clicking the dark backdrop outside the links area)
  mobileMenu.addEventListener('click', e => {
    if (e.target === mobileMenu) closeMenu();
  });

  // Trap focus inside menu when open
  mobileMenu.addEventListener('keydown', e => {
    if (e.key !== 'Tab' || !isOpen()) return;
    const focusable = Array.from(mobileMenu.querySelectorAll('a, button, [tabindex]:not([tabindex="-1"])'));
    const first = focusable[0];
    const last  = focusable[focusable.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault(); last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault(); first.focus();
    }
  });
})();

/* ================================================
   LNC UI IMPROVEMENTS v1 — Mobile Sticky CTA
   ================================================ */
(function () {
  var mobileCta = document.getElementById('mobile-sticky-cta');
  if (!mobileCta) return;
  var shown = false;
  window.addEventListener('scroll', function () {
    if (window.scrollY > 400 && !shown) {
      mobileCta.classList.add('is-visible');
      shown = true;
    } else if (window.scrollY <= 400 && shown) {
      mobileCta.classList.remove('is-visible');
      shown = false;
    }
  }, { passive: true });
}());


/* ================================================
   LNC UI IMPROVEMENTS v2 — Auto Reveal Classes
   Automatically marks key page elements as .reveal
   so the existing IntersectionObserver animates them
   ================================================ */
(function () {
  // Auto-apply .reveal to section headings, cards, and key elements
  // Skip the hero section
  var revealSelectors = [
    'section:not(.hero) h2',
    'section:not(.hero) h3',
    'section:not(.hero) .eyebrow',
    'section:not(.hero) .hero__eyebrow',
    '.tour-card',
    '.package-card',
    '.hotel-card',
    '.testimonial-card',
    '.philosophy__item',
    '.how-it-works__step',
    '.trust__item',
    '.team-card',
    '.gallery-item',
    '.stat-item',
    '.experience-card'
  ];

  revealSelectors.forEach(function(sel) {
    document.querySelectorAll(sel).forEach(function(el, i) {
      el.classList.add('reveal');
      // Stagger delay for grouped elements (cards, items)
      if (i % 3 === 1) el.classList.add('reveal--delay-1');
      if (i % 3 === 2) el.classList.add('reveal--delay-2');
    });
  });
}());
