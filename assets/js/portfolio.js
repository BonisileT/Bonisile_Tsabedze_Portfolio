/* Renders the public portfolio from window.PORTFOLIO_CONTENT (data/content.js).
   Also listens for live-preview messages when shown inside edit.html. */
(function () {
  'use strict';

  var PF = window.PF;
  var h = PF.h, safeUrl = PF.safeUrl;

  var DEFAULT_ORDER = ['about', 'education', 'skills', 'projects', 'certificates', 'achievements', 'info', 'goals', 'contact'];
  var content = window.PORTFOLIO_CONTENT || {};
  var overrides = {}; // path -> data URL for files staged in the editor but not yet published

  /* ---------- small helpers ---------- */
  function text(v) { return v === null || v === undefined ? '' : String(v); }
  function arr(v) { return Array.isArray(v) ? v : []; }
  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function asset(p) { return overrides[p] || p; }
  function paragraphs(s) {
    return text(s).split(/\n\s*\n/).map(function (x) { return x.trim(); }).filter(Boolean);
  }
  function store(key, val) {
    try { if (val === undefined) return localStorage.getItem(key); localStorage.setItem(key, val); } catch (e) { return null; }
    return null;
  }

  /** Link that is skipped entirely when the URL is empty or unsafe. */
  function link(label, url, cls, extra) {
    var u = safeUrl(url);
    if (!u) return null;
    var external = /^(https?:)?\/\//i.test(u);
    var attrs = { class: cls || null, href: asset(u), target: external ? '_blank' : null, rel: external ? 'noopener' : null };
    if (extra) Object.keys(extra).forEach(function (k) { attrs[k] = extra[k]; });
    return h('a', attrs, label);
  }

  function image(src, alt) {
    var u = safeUrl(src);
    if (!u) return null;
    var el = h('img', { src: asset(u), alt: alt || '', loading: 'lazy' });
    el.addEventListener('error', function () { var f = el.closest('figure'); if (f) f.remove(); });
    return h('figure', null, el);
  }

  function lead(s) { return s.lead ? h('p', { class: 'lead' }, s.lead) : null; }

  function placeholder(s) {
    if (!s.placeholderTitle && !s.placeholderText) return null;
    return h('article', { class: 'panel placeholder' },
      h('span', { class: 'symbol' }, '＋'),
      h('div', null, h('h3', null, s.placeholderTitle), s.placeholderText ? h('p', null, s.placeholderText) : null));
  }

  function titleParts(title) {
    var words = text(title).trim().split(/\s+/);
    if (words.length < 2) return [text(title)];
    return [words.slice(0, -1).join(' ') + ' ', h('em', null, words[words.length - 1])];
  }

  /* ---------- section bodies ---------- */
  var BODY = {
    about: function (s) {
      return [lead(s), paragraphs(s.body).map(function (p) { return h('p', { class: 'prose' }, p); })];
    },

    education: function (s) {
      return [lead(s), h('div', { class: 'stack' }, arr(s.items).map(function (it) {
        return h('article', { class: 'panel timeline' },
          h('span', { class: 'symbol' }, 'ED'),
          h('div', null,
            h('h3', null, it.degree),
            it.institution ? h('p', null, it.institution) : null,
            it.period ? h('p', null, it.period) : null,
            it.status ? h('span', { class: 'tag' }, it.status) : null));
      }))];
    },

    skills: function (s) {
      var pro = arr(s.professional);
      return [
        lead(s),
        h('div', { class: 'cards' }, arr(s.items).map(function (it) {
          return h('article', { class: 'panel' },
            h('span', { class: 'symbol' }, text(it.icon) || '•'),
            h('h3', null, it.title),
            it.description ? h('p', null, it.description) : null);
        })),
        pro.length ? h('h3', { class: 'subheading' }, s.professionalTitle || 'Professional skills') : null,
        pro.length ? h('div', { class: 'chips' }, pro.map(function (x) { return h('span', null, x); })) : null
      ];
    },

    projects: function (s) {
      var items = arr(s.items);
      if (!items.length) return [lead(s), placeholder(s)];
      return [lead(s), h('div', { class: 'project-grid' }, items.map(function (p) {
        var tech = arr(p.technologies);
        var links = [link('Live demo ↗', p.link), link('Source code ↗', p.repo), link('Evidence ↗', p.evidence)].filter(Boolean);
        return h('article', { class: 'panel project' },
          image(p.image, p.title),
          h('div', { class: 'body' },
            (p.status || p.period) ? h('div', { class: 'meta-row' },
              p.status ? h('span', { class: 'tag' }, p.status) : null,
              p.period ? h('span', { class: 'when' }, p.period) : null) : null,
            h('h3', null, p.title),
            p.summary ? h('p', { class: 'prose' }, p.summary) : null,
            tech.length ? h('div', { class: 'chips small' }, tech.map(function (t) { return h('span', null, t); })) : null,
            links.length ? h('div', { class: 'links' }, links) : null));
      }))];
    },

    certificates: function (s, c) {
      var items = arr(s.items);
      var cvLink = c.cv && link(c.cv.viewLabel || 'View CV ↗', c.cv.file);
      var body = items.length ? h('div', { class: 'cert-grid' }, items.map(function (it) {
        var links = [link('View certificate ↗', it.file), link('Verify credential ↗', it.credentialUrl)].filter(Boolean);
        return h('article', { class: 'panel cert' },
          PF.isImagePath(it.file) ? image(it.file, it.title) : null,
          h('div', { class: 'body' },
            h('div', { class: 'meta-row' },
              h('span', { class: 'symbol sm' }, '✧'),
              it.date ? h('span', { class: 'when' }, it.date) : null),
            h('h3', null, it.title),
            it.issuer ? h('p', null, it.issuer) : null,
            links.length ? h('div', { class: 'links' }, links) : null));
      })) : placeholder(s);
      return [lead(s), body, cvLink ? h('p', { class: 'cv-link' }, cvLink) : null];
    },

    achievements: function (s) {
      var items = arr(s.items);
      if (!items.length) return [lead(s), placeholder(s)];
      return [lead(s), h('div', { class: 'stack' }, items.map(function (it) {
        return h('article', { class: 'panel timeline' },
          h('span', { class: 'symbol' }, '★'),
          h('div', null,
            h('h3', null, it.title),
            it.date ? h('span', { class: 'tag' }, it.date) : null,
            it.description ? h('p', { class: 'prose' }, it.description) : null));
      }))];
    },

    info: function (s) {
      var items = arr(s.items);
      if (!items.length) return [lead(s), placeholder(s)];
      return [lead(s), h('div', { class: 'info-grid' }, items.map(function (it) {
        return h('article', { class: 'panel' },
          h('h3', null, it.title),
          paragraphs(it.body).map(function (p) { return h('p', { class: 'prose' }, p); }));
      }))];
    },

    goals: function (s) {
      var interests = arr(s.interests);
      return [
        lead(s),
        h('ul', { class: 'goals' }, arr(s.items).map(function (g) { return h('li', null, g); })),
        interests.length ? h('div', { class: 'interest' },
          h('h3', null, s.interestsTitle || 'Career interests'),
          h('p', null, interests.join(' · '))) : null
      ];
    },

    contact: function (s) {
      var phones = arr(s.phones).filter(Boolean);
      var socials = arr(s.socials).filter(function (x) { return x && x.label; });
      var wa = PF.waHref(s.whatsapp);
      var mail = safeUrl(s.email) ? 'mailto:' + s.email : '';
      var mailSubject = mail && s.emailSubject ? mail + '?subject=' + encodeURIComponent(s.emailSubject) : mail;
      return [lead(s), h('div', { class: 'contact-grid' },
        h('article', { class: 'panel' },
          s.email ? h('div', { class: 'contact-row' }, h('small', null, 'EMAIL'), h('a', { href: mail }, s.email)) : null,
          phones.length ? h('div', { class: 'contact-row' }, h('small', null, phones.length > 1 ? 'PHONES' : 'PHONE'),
            phones.map(function (p) { return h('a', { href: PF.telHref(p) }, p); })) : null,
          s.location ? h('div', { class: 'contact-row' }, h('small', null, 'LOCATION'), h('span', null, s.location)) : null,
          wa ? h('a', { class: 'btn primary full', href: wa, target: '_blank', rel: 'noopener' }, s.whatsappLabel || 'Chat on WhatsApp ↗') : null),
        h('article', { class: 'panel' },
          s.emailBoxTitle ? h('h3', null, s.emailBoxTitle) : null,
          s.emailBoxText ? h('p', null, s.emailBoxText) : null,
          mail ? h('a', { class: 'btn outline', href: mailSubject }, s.emailButton || 'Compose email ↗') : null,
          socials.length ? h('div', { class: 'social' }, socials.map(function (x) {
            return link(x.label, x.url) || h('span', null, x.label + ' profile to be added');
          })) : null))];
    }
  };

  /* ---------- page ---------- */
  function visibleSections(c) {
    var S = c.sections || {};
    var order = arr(c.layout).filter(function (id) { return DEFAULT_ORDER.indexOf(id) !== -1; });
    DEFAULT_ORDER.forEach(function (id) { if (order.indexOf(id) === -1) order.push(id); });
    return order.filter(function (id) { return S[id] && S[id].show !== false; });
  }

  function applyTheme(meta) {
    var accent = /^#[0-9a-f]{6}$/i.test(meta.accent || '') ? meta.accent : '#37c47b';
    var r = parseInt(accent.slice(1, 3), 16), g = parseInt(accent.slice(3, 5), 16), b = parseInt(accent.slice(5, 7), 16);
    var lum = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    var root = document.documentElement;
    root.style.setProperty('--accent', accent);
    root.style.setProperty('--on-accent', lum > 0.55 ? '#08140d' : '#ffffff');
    var saved = store('portfolioTheme');
    var light = (saved || meta.defaultTheme || 'dark') === 'light';
    document.body.classList.toggle('light', light);
    syncToggle();
  }

  function setMeta(sel, attr, val) {
    var el = document.querySelector(sel);
    if (el && val) el.setAttribute(attr, val);
  }

  function renderHero(c, ids) {
    var hero = c.hero || {};
    var contact = (c.sections || {}).contact || {};
    var target = ids.indexOf(hero.primaryTarget) !== -1 ? hero.primaryTarget : ids[0];
    var cv = c.cv || {};
    var mail = contact.email && safeUrl(contact.email) ? 'mailto:' + contact.email : '';
    return h('section', { class: 'hero', id: 'home' },
      h('div', { class: 'grid' }),
      h('div', { class: 'hero-content wrap' },
        hero.eyebrow ? h('p', { class: 'eyebrow' }, h('i'), ' ' + hero.eyebrow) : null,
        h('h1', null, text(hero.firstName), hero.lastName ? h('br') : null, hero.lastName ? h('span', null, hero.lastName + '.') : null),
        hero.role ? h('p', { class: 'role' }, hero.role) : null,
        hero.intro ? h('p', { class: 'intro' }, hero.intro) : null,
        h('div', { class: 'actions' },
          target && hero.primaryLabel ? h('a', { class: 'btn primary', href: '#' + target }, hero.primaryLabel) : null,
          link(cv.downloadLabel || 'Download CV ↓', cv.file, 'btn outline', { download: '' })),
        h('div', { class: 'quick' },
          contact.location ? h('span', null, '⌖ ' + contact.location) : null,
          mail ? h('a', { href: mail }, '✉ ' + contact.email) : null)),
      h('div', { class: 'orbit' }));
  }

  function render() {
    var c = content || {};
    var meta = c.meta || {};
    var S = c.sections || {};
    var ids = visibleSections(c);
    var hero = c.hero || {};

    applyTheme(meta);
    if (meta.siteTitle) document.title = meta.siteTitle;
    setMeta('meta[name="description"]', 'content', meta.description);
    setMeta('meta[property="og:title"]', 'content', meta.siteTitle);
    setMeta('meta[property="og:description"]', 'content', meta.description);

    // brand + nav
    var initials = (text(hero.firstName).charAt(0) + text(hero.lastName).charAt(0)).toUpperCase() || 'PF';
    PF.mount(document.getElementById('brand'), initials, h('span', null, '.'));
    PF.mount(document.getElementById('nav'),
      ids.filter(function (id) { return S[id].nav; }).map(function (id) {
        return h('a', { href: '#' + id }, S[id].navLabel || S[id].title);
      }));

    // sections
    var n = 0;
    var sections = ids.map(function (id, i) {
      var s = S[id];
      n += 1;
      var cls = 'section' + (id === 'contact' ? ' contact' : (i % 2 ? ' tint' : ''));
      return h('section', { class: cls, id: id },
        h('div', { class: 'wrap' },
          h('p', { class: 'kicker' }, pad(n) + ' / ' + (s.kicker || id)),
          h('h2', null, titleParts(s.title)),
          BODY[id](s, c)));
    });
    PF.mount(document.getElementById('app'), renderHero(c, ids), sections);

    // footer
    var fullName = (text(hero.firstName) + ' ' + text(hero.lastName)).trim();
    PF.mount(document.getElementById('footer'),
      h('span', null, '© ' + new Date().getFullYear() + (fullName ? ' ' + fullName : '')),
      h('a', { href: '#home' }, 'Back to top ↑'),
      (c.footer && c.footer.tagline) ? h('span', null, c.footer.tagline) : null);
  }

  /* ---------- theme toggle ---------- */
  var toggle = document.getElementById('themeToggle');
  function syncToggle() {
    if (toggle) toggle.textContent = document.body.classList.contains('light') ? '☾' : '☼';
  }
  if (toggle) {
    toggle.addEventListener('click', function () {
      var light = document.body.classList.toggle('light');
      store('portfolioTheme', light ? 'light' : 'dark');
      syncToggle();
    });
  }

  /* ---------- live preview (only when embedded in edit.html) ---------- */
  if (window.parent !== window) {
    window.addEventListener('message', function (e) {
      if (e.source !== window.parent || !e.data) return;
      var d = e.data;
      if (d.type === 'pf-content') { content = d.content; render(); }
      else if (d.type === 'pf-asset') { overrides[d.path] = d.url; }
      else if (d.type === 'pf-scroll') {
        var el = document.getElementById(d.id);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  render();
  if (window.parent !== window) window.parent.postMessage({ type: 'pf-ready' }, '*');
})();
