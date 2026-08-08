/* Aura Interiors — front-end interactions (vanilla JS, no dependencies) */
(function () {
  'use strict';

  var BASE = window.SITE_BASE_URL || '';

  /* ---------------- Header scroll state ---------------- */
  var header = document.getElementById('siteHeader');
  function onScroll() {
    if (!header) return;
    header.classList.toggle('is-scrolled', window.scrollY > 12);
  }
  document.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ---------------- Mobile nav ---------------- */
  var toggle = document.getElementById('navToggle');
  var nav = document.getElementById('mainNav');
  var backdrop = document.getElementById('mobileNavBackdrop');
  function closeNav() {
    toggle && toggle.classList.remove('is-open');
    nav && nav.classList.remove('is-open');
    backdrop && backdrop.classList.remove('is-open');
    toggle && toggle.setAttribute('aria-expanded', 'false');
  }
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      toggle.classList.toggle('is-open', open);
      backdrop && backdrop.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    backdrop && backdrop.addEventListener('click', closeNav);
    nav.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', closeNav); });
  }

  /* ---------------- Scroll reveal ---------------- */
  var revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && revealEls.length) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.01, rootMargin: '0px 0px 150px 0px' });
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('is-visible'); });
  }

  /* ---------------- Animated counters (hero / trust stats) ---------------- */
  function animateCount(el) {
    var raw = el.getAttribute('data-count') || el.textContent;
    var match = raw.match(/^([\d,]+)(\+?)$/);
    if (!match) return;
    var target = parseInt(match[1].replace(/,/g, ''), 10);
    var suffix = match[2] || '';
    var duration = 1400;
    var start = null;
    function step(ts) {
      if (!start) start = ts;
      var progress = Math.min((ts - start) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(eased * target).toLocaleString() + suffix;
      if (progress < 1) requestAnimationFrame(step);
      else el.textContent = target.toLocaleString() + suffix;
    }
    requestAnimationFrame(step);
  }
  var counters = document.querySelectorAll('[data-count]');
  if ('IntersectionObserver' in window && counters.length) {
    var cio = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCount(entry.target);
          cio.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach(function (el) { cio.observe(el); });
  }

  /* ---------------- Portfolio / gallery filters ---------------- */
  document.querySelectorAll('[data-filter-group]').forEach(function (group) {
    var targetSelector = group.getAttribute('data-filter-group');
    var items = document.querySelectorAll(targetSelector);
    group.querySelectorAll('.filter-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        group.querySelectorAll('.filter-btn').forEach(function (b) { b.classList.remove('is-active'); });
        btn.classList.add('is-active');
        var cat = btn.getAttribute('data-filter');
        items.forEach(function (item) {
          var itemCat = item.getAttribute('data-category');
          var show = cat === 'all' || itemCat === cat;
          item.style.display = show ? '' : 'none';
        });
      });
    });
  });

  /* ---------------- Before / After sliders ---------------- */
  document.querySelectorAll('.ba-slider').forEach(function (slider) {
    var before = slider.querySelector('.ba-slider__before');
    var handle = slider.querySelector('.ba-handle');
    if (!before || !handle) return;
    var dragging = false;

    function setPosition(clientX) {
      var rect = slider.getBoundingClientRect();
      var pct = ((clientX - rect.left) / rect.width) * 100;
      pct = Math.max(0, Math.min(100, pct));
      before.style.clipPath = 'inset(0 ' + (100 - pct) + '% 0 0)';
      handle.style.left = pct + '%';
    }
    setPosition(slider.getBoundingClientRect().left + slider.getBoundingClientRect().width / 2);

    handle.addEventListener('mousedown', function (e) { dragging = true; e.preventDefault(); });
    document.addEventListener('mouseup', function () { dragging = false; });
    document.addEventListener('mousemove', function (e) { if (dragging) setPosition(e.clientX); });

    handle.addEventListener('touchstart', function () { dragging = true; }, { passive: true });
    document.addEventListener('touchend', function () { dragging = false; });
    document.addEventListener('touchmove', function (e) {
      if (dragging && e.touches[0]) setPosition(e.touches[0].clientX);
    }, { passive: true });

    slider.addEventListener('click', function (e) {
      if (e.target === handle || handle.contains(e.target)) return;
      setPosition(e.clientX);
    });
  });

  /* ---------------- Lightbox (gallery + project images) ---------------- */
  var lightbox = document.getElementById('lightbox');
  if (lightbox) {
    var lbImg = lightbox.querySelector('img');
    var triggers = Array.prototype.slice.call(document.querySelectorAll('[data-lightbox]'));
    var current = 0;

    function openLightbox(index) {
      current = index;
      lbImg.src = triggers[index].getAttribute('data-lightbox');
      lightbox.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
      lightbox.classList.remove('is-open');
      document.body.style.overflow = '';
    }
    function nav(dir) {
      current = (current + dir + triggers.length) % triggers.length;
      lbImg.src = triggers[current].getAttribute('data-lightbox');
    }
    triggers.forEach(function (t, i) {
      t.addEventListener('click', function (e) { e.preventDefault(); openLightbox(i); });
    });
    var closeBtn = lightbox.querySelector('.lightbox__close');
    var prevBtn = lightbox.querySelector('.lightbox__nav--prev');
    var nextBtn = lightbox.querySelector('.lightbox__nav--next');
    closeBtn && closeBtn.addEventListener('click', closeLightbox);
    prevBtn && prevBtn.addEventListener('click', function () { nav(-1); });
    nextBtn && nextBtn.addEventListener('click', function () { nav(1); });
    lightbox.addEventListener('click', function (e) { if (e.target === lightbox) closeLightbox(); });
    document.addEventListener('keydown', function (e) {
      if (!lightbox.classList.contains('is-open')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') nav(-1);
      if (e.key === 'ArrowRight') nav(1);
    });
  }

  /* ---------------- Analytics click tracking ---------------- */
  window.trackEvent = function (type, reference) {
    try {
      var url = BASE + '/ajax/track-event.php';
      var data = new URLSearchParams({ type: type, reference: reference || '' });
      if (navigator.sendBeacon) {
        navigator.sendBeacon(url, data);
      } else {
        fetch(url, { method: 'POST', body: data, keepalive: true });
      }
    } catch (e) { /* never block the click */ }
  };

  /* ---------------- Consultation / quote form (AJAX) ---------------- */
  document.querySelectorAll('.js-consult-form').forEach(function (form) {
    var msgBox = form.querySelector('.form-msg');
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      form.querySelectorAll('.field-error').forEach(function (el) { el.style.display = 'none'; });
      msgBox && msgBox.classList.remove('is-success', 'is-error');

      var submitBtn = form.querySelector('[type="submit"]');
      var originalText = submitBtn ? submitBtn.innerHTML : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Sending...';
      }

      fetch(BASE + '/ajax/submit-inquiry.php', {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data.success) {
            form.reset();
            if (msgBox) {
              msgBox.textContent = data.message || 'Thank you! Our team will contact you shortly.';
              msgBox.classList.add('is-success');
            }
            trackEvent('inquiry_submit', form.getAttribute('data-source') || 'form');
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
          } else {
            if (data.errors) {
              Object.keys(data.errors).forEach(function (field) {
                var errEl = form.querySelector('[data-error-for="' + field + '"]');
                if (errEl) {
                  errEl.textContent = data.errors[field];
                  errEl.style.display = 'block';
                }
              });
            }
            if (msgBox) {
              msgBox.textContent = data.message || 'Please check the highlighted fields and try again.';
              msgBox.classList.add('is-error');
            }
          }
        })
        .catch(function () {
          if (msgBox) {
            msgBox.textContent = 'Something went wrong. Please try again or WhatsApp us directly.';
            msgBox.classList.add('is-error');
          }
        })
        .finally(function () {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
          }
        });
    });
  });
})();
