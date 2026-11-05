/* Portfolio editor (edit.html).
   - Edits the content object that index.html renders (data/content.js).
   - Shows a live preview in an iframe.
   - Publishes straight to GitHub (one commit) using the GitHub REST API and a personal access token.
   Nothing is sent anywhere except api.github.com, and only when you press a GitHub button. */
(function () {
  'use strict';

  var PF = window.PF;
  var h = PF.h, isImagePath = PF.isImagePath;
  function $(s, r) { return (r || document).querySelector(s); }
  function clone(o) { return JSON.parse(JSON.stringify(o)); }

  var IDS = ['about', 'education', 'skills', 'projects', 'certificates', 'achievements', 'info', 'goals', 'contact'];
  var NAMES = {
    about: 'About', education: 'Education', skills: 'Skills', projects: 'Projects', certificates: 'Certificates',
    achievements: 'Achievements', info: 'Important information', goals: 'Career goals', contact: 'Contact'
  };
  var CONTENT_PATH = 'data/content.js';
  var MAX_FILE = 25 * 1024 * 1024;

  /* ---------- storage (never throws) ---------- */
  function store(kind) {
    return {
      get: function (k) { try { return window[kind].getItem(k); } catch (e) { return null; } },
      set: function (k, v) { try { window[kind].setItem(k, v); } catch (e) { /* ignore */ } },
      del: function (k) { try { window[kind].removeItem(k); } catch (e) { /* ignore */ } }
    };
  }
  var ls = store('localStorage'), ss = store('sessionStorage');

  /* ---------- state ---------- */
  var original = clone(window.PORTFOLIO_CONTENT || {});
  var content = normalize(clone(original));
  var dirty = false;
  var staged = {};       // path -> {b64, dataUrl, size, type, name}  — uploaded to GitHub on publish
  var previewUrls = {};  // path -> data URL, so staged files show in the preview
  var currentTab = 'hero';
  var busy = false;
  var logLines = [];
  var commitMsg = 'Update portfolio content';
  var cfg = loadCfg();
  var openItems = typeof WeakSet === 'function' ? new WeakSet() : { add: function () {}, has: function () { return false; }, delete: function () {} };

  function normalize(c) {
    c = c && typeof c === 'object' ? c : {};
    c.version = 1;
    ['meta', 'hero', 'cv', 'footer', 'sections'].forEach(function (k) { if (!c[k] || typeof c[k] !== 'object') c[k] = {}; });
    IDS.forEach(function (id) {
      var s = c.sections[id];
      if (!s || typeof s !== 'object') s = c.sections[id] = { show: false, nav: false, navLabel: NAMES[id], kicker: NAMES[id], title: NAMES[id], lead: '' };
    });
    var S = c.sections;
    [[S.education, 'items'], [S.skills, 'items'], [S.skills, 'professional'], [S.projects, 'items'], [S.certificates, 'items'],
      [S.achievements, 'items'], [S.info, 'items'], [S.goals, 'items'], [S.goals, 'interests'], [S.contact, 'phones'], [S.contact, 'socials']
    ].forEach(function (p) { if (!Array.isArray(p[0][p[1]])) p[0][p[1]] = []; });
    var order = (Array.isArray(c.layout) ? c.layout : []).filter(function (id, i, a) { return IDS.indexOf(id) !== -1 && a.indexOf(id) === i; });
    IDS.forEach(function (id) { if (order.indexOf(id) === -1) order.push(id); });
    c.layout = order;
    return c;
  }

  /* ---------- tiny UI helpers ---------- */
  var toastTimer;
  function toast(msg) {
    var t = $('#toast');
    t.textContent = msg; t.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { t.hidden = true; }, 3500);
  }
  function updateState() {
    var s = $('#state');
    s.textContent = dirty ? 'Unpublished changes' : 'All changes saved';
    s.className = 'state' + (dirty ? ' dirty' : '');
  }
  var tPrev, tDraft;
  function changed() {
    dirty = true; updateState();
    clearTimeout(tPrev); tPrev = setTimeout(sendPreview, 120);
    clearTimeout(tDraft); tDraft = setTimeout(function () {
      ls.set('pf-draft', JSON.stringify({ content: content, at: Date.now() }));
    }, 500);
  }
  function showBanner(msg, buttons) {
    var b = $('#banner');
    PF.mount(b, h('span', null, msg), buttons || []);
    b.hidden = false;
  }
  function hideBanner() { $('#banner').hidden = true; }

  /* ---------- form field builders ---------- */
  var uid = 0;
  function fld(label, control, hint) {
    var id = 'f' + (++uid);
    control.id = id;
    return h('div', { class: 'fld' }, h('label', { for: id }, label), control, hint ? h('small', null, hint) : null);
  }
  function onInput(opts) { return (opts && opts.onInput) || changed; }

  function fText(obj, key, label, opts) {
    opts = opts || {};
    var el = opts.rows ? h('textarea', { rows: opts.rows }) : h('input', { type: opts.type || 'text', autocomplete: 'off' });
    el.value = obj[key] === undefined || obj[key] === null ? '' : obj[key];
    if (opts.placeholder) el.placeholder = opts.placeholder;
    if (opts.list) el.setAttribute('list', opts.list);
    el.addEventListener('input', function () { obj[key] = el.value; onInput(opts)(); });
    return fld(label, el, opts.hint);
  }
  function fLines(obj, key, label, opts) {
    opts = opts || {};
    var el = h('textarea', { rows: opts.rows || 4 });
    el.value = (obj[key] || []).join('\n');
    el.addEventListener('input', function () {
      obj[key] = el.value.split('\n').map(function (s) { return s.trim(); }).filter(Boolean);
      onInput(opts)();
    });
    return fld(label, el, opts.hint || 'One per line.');
  }
  function fCheck(obj, key, label, opts) {
    opts = opts || {};
    var el = h('input', { type: 'checkbox' });
    el.checked = obj[key] !== false && !!obj[key];
    el.addEventListener('change', function () { obj[key] = el.checked; onInput(opts)(); });
    return h('label', { class: 'check' }, el, h('span', null, label, opts.hint ? h('small', { class: 'hint' }, ' — ' + opts.hint) : null));
  }
  function fSelect(obj, key, label, options) {
    var el = h('select');
    if (!options.some(function (o) { return o[0] === obj[key]; })) options = options.concat([[obj[key] || '', obj[key] || '—']]);
    options.forEach(function (o) { el.append(h('option', { value: o[0], selected: obj[key] === o[0] }, o[1])); });
    el.addEventListener('change', function () { obj[key] = el.value; changed(); });
    return fld(label, el);
  }
  function fColor(obj, key, label) {
    var el = h('input', { type: 'color' });
    el.value = /^#[0-9a-f]{6}$/i.test(obj[key] || '') ? obj[key] : '#37c47b';
    el.addEventListener('input', function () { obj[key] = el.value; changed(); });
    return fld(label, el);
  }
  function fFile(obj, key, label, opts) {
    opts = opts || {};
    var input = h('input', { type: 'text', placeholder: 'assets/… or https://…', autocomplete: 'off' });
    input.value = obj[key] || '';
    var thumb = h('img', { class: 'thumb', alt: '', hidden: true });
    var picker = h('input', { type: 'file', accept: opts.accept || null, hidden: true });
    function refresh() {
      var v = input.value.trim();
      var src = previewUrls[v] || v;
      thumb.hidden = !(isImagePath(v) && src);
      if (!thumb.hidden) thumb.src = src;
    }
    thumb.addEventListener('error', function () { thumb.hidden = true; });
    input.addEventListener('input', function () { obj[key] = input.value.trim(); refresh(); changed(); });
    picker.addEventListener('change', function () {
      var f = picker.files && picker.files[0];
      if (!f) return;
      stageFile(f, opts.dir || 'assets/uploads').then(function (path) {
        input.value = path; obj[key] = path; refresh(); changed();
        toast('File added — it uploads when you publish.');
      }).catch(function (e) { toast(e.message); });
      picker.value = '';
    });
    var up = h('button', { type: 'button', class: 'btn sm', onclick: function () { picker.click(); } }, 'Upload file…');
    var clear = h('button', { type: 'button', class: 'btn sm', onclick: function () { input.value = ''; obj[key] = ''; refresh(); changed(); } }, 'Clear');
    refresh();
    var wrap = fld(label, input, opts.hint);
    // the label points at the text input; put the extra controls in a row with it
    var row = h('div', { class: 'file-row' });
    wrap.replaceChild(row, input);
    row.append(input, up, clear, picker, thumb);
    return wrap;
  }

  function two() { return h('div', { class: 'two' }, Array.prototype.slice.call(arguments)); }
  function card(title, note) {
    var kids = Array.prototype.slice.call(arguments, 2);
    return h('section', { class: 'card' }, title ? h('h2', null, title) : null, note ? h('p', { class: 'note' }, note) : null, kids);
  }
  function head(title, sub) {
    return [h('h1', null, title), sub ? h('p', { class: 'sub' }, sub) : null];
  }
  function datalist(id, values) {
    return h('datalist', { id: id }, values.map(function (v) { return h('option', { value: v }); }));
  }

  /** Section header card: visibility + heading text. */
  function sectionHead(id, withLead) {
    var s = content.sections[id];
    return card('Section heading', null,
      fCheck(s, 'show', 'Show this section on the website', { hint: 'turns on automatically when you add the first item' }),
      two(fText(s, 'kicker', 'Small label above the title'), fText(s, 'title', 'Section title')),
      withLead === false ? null : fText(s, 'lead', 'Intro line (optional)', { rows: 2 }));
  }
  function placeholderCard(id) {
    var s = content.sections[id];
    return card('Message shown while empty', 'Visitors see this until you add your first item.',
      fText(s, 'placeholderTitle', 'Title'), fText(s, 'placeholderText', 'Text', { rows: 2 }));
  }

  /** Reorderable list of items, each in a collapsible box. */
  function listEditor(arr, spec) {
    var wrap = h('div', { class: 'list' });
    function titleOf(it) { return (spec.title(it) || '').trim() || 'Untitled ' + spec.noun; }
    function iconBtn(label, aria, fn) {
      return h('button', { type: 'button', 'aria-label': aria, title: aria, onclick: function (e) { e.preventDefault(); e.stopPropagation(); fn(); } }, label);
    }
    function draw() {
      wrap.replaceChildren();
      if (!arr.length) wrap.append(h('p', { class: 'empty' }, 'No ' + (spec.plural || spec.noun + 's') + ' yet.'));
      arr.forEach(function (it, i) {
        var title = h('span', { class: 'sum-title' }, titleOf(it));
        var sum = h('summary', null, title, h('span', { class: 'sum-btns' },
          iconBtn('↑', 'Move up', function () { if (i > 0) { arr.splice(i - 1, 0, arr.splice(i, 1)[0]); changed(); draw(); } }),
          iconBtn('↓', 'Move down', function () { if (i < arr.length - 1) { arr.splice(i + 1, 0, arr.splice(i, 1)[0]); changed(); draw(); } }),
          iconBtn('✕', 'Delete', function () {
            if (window.confirm('Delete "' + titleOf(it) + '"?')) { arr.splice(i, 1); changed(); draw(); }
          })));
        var det = h('details', { class: 'item', open: openItems.has(it) }, sum, h('div', { class: 'item-body' }, spec.fields(it)));
        det.addEventListener('toggle', function () { if (det.open) openItems.add(it); else openItems.delete(it); });
        det.addEventListener('input', function () { title.textContent = titleOf(it); });
        wrap.append(det);
      });
      wrap.append(h('button', {
        type: 'button', class: 'btn add',
        onclick: function () {
          var it = spec.blank();
          arr.push(it); openItems.add(it);
          if (spec.section && content.sections[spec.section].show === false) content.sections[spec.section].show = true;
          changed(); draw();
          var last = wrap.querySelectorAll('details.item'); last = last[last.length - 1];
          if (last) { last.scrollIntoView({ block: 'center' }); var first = last.querySelector('input,textarea'); if (first) first.focus(); }
        }
      }, '+ Add ' + spec.noun));
    }
    draw();
    return wrap;
  }

  /* ---------- tabs ---------- */
  function tabHero() {
    var hero = content.hero, cv = content.cv;
    return [
      head('Hero & CV', 'The first thing visitors see at the top of the page.'),
      card('Hero', null,
        fText(hero, 'eyebrow', 'Small line above your name'),
        two(fText(hero, 'firstName', 'First name'), fText(hero, 'lastName', 'Last name')),
        fText(hero, 'role', 'Role / headline'),
        fText(hero, 'intro', 'Introduction', { rows: 4 }),
        two(fText(hero, 'primaryLabel', 'Main button text'),
          fSelect(hero, 'primaryTarget', 'Main button scrolls to', IDS.map(function (id) { return [id, NAMES[id]]; })))),
      card('CV', 'Upload a PDF or paste a link. Leave empty to hide the CV buttons.',
        fFile(cv, 'file', 'CV file', { dir: 'assets/evidence', accept: '.pdf,application/pdf' }),
        two(fText(cv, 'downloadLabel', 'Hero button text'), fText(cv, 'viewLabel', 'Link text under Certificates')))
    ];
  }

  function tabAbout() {
    return [head('About', 'A short introduction about you.'), sectionHead('about'),
      card('Text', null, fText(content.sections.about, 'body', 'About text', { rows: 8, hint: 'Leave a blank line between paragraphs.' }))];
  }

  function tabEducation() {
    var s = content.sections.education;
    return [head('Education'), sectionHead('education'),
      card('Qualifications', null, listEditor(s.items, {
        noun: 'qualification', section: 'education', blank: function () { return { degree: '', institution: '', period: '', status: '' }; },
        title: function (it) { return it.degree; },
        fields: function (it) {
          return [fText(it, 'degree', 'Qualification'), fText(it, 'institution', 'Institution'),
            two(fText(it, 'period', 'Period (optional)', { placeholder: '2025 – 2027' }), fText(it, 'status', 'Status tag', { placeholder: 'Currently pursuing' }))];
        }
      }))];
  }

  function tabSkills() {
    var s = content.sections.skills;
    return [head('Skills'), sectionHead('skills'),
      card('Technical skills', 'Each skill becomes a card. The icon can be a symbol, emoji or two letters.',
        datalist('icons', ['⌘', '⚙', '⌁', '</>', '▤', '◈', '⌨', '⛨', '▦', '☁', '✎', '⚡', '★']),
        listEditor(s.items, {
          noun: 'skill', section: 'skills', blank: function () { return { icon: '•', title: '', description: '' }; },
          title: function (it) { return it.title; },
          fields: function (it) {
            return [two(fText(it, 'icon', 'Icon', { list: 'icons' }), fText(it, 'title', 'Skill name')), fText(it, 'description', 'Description', { rows: 2 })];
          }
        })),
      card('Professional skills', null,
        fText(s, 'professionalTitle', 'Heading'), fLines(s, 'professional', 'Skills', { rows: 7 }))];
  }

  function tabProjects() {
    var s = content.sections.projects;
    return [head('Projects', 'Add coursework, practical work and personal projects.'), sectionHead('projects'),
      card('Your projects', null, datalist('pstatus', ['Completed', 'In progress', 'Prototype', 'Planned']), listEditor(s.items, {
        noun: 'project', section: 'projects',
        blank: function () { return { title: '', status: '', period: '', summary: '', technologies: [], image: '', link: '', repo: '', evidence: '' }; },
        title: function (it) { return it.title; },
        fields: function (it) {
          return [fText(it, 'title', 'Project title'),
            two(fText(it, 'status', 'Status', { list: 'pstatus' }), fText(it, 'period', 'Date / period (optional)', { placeholder: 'Mar 2026' })),
            fText(it, 'summary', 'What it is and what you did', { rows: 4 }),
            fLines(it, 'technologies', 'Technologies used', { rows: 3 }),
            fFile(it, 'image', 'Screenshot / cover image (optional)', { dir: 'assets/uploads', accept: 'image/*' }),
            two(fText(it, 'link', 'Live demo link (optional)', { type: 'url', placeholder: 'https://…' }), fText(it, 'repo', 'Source code link (optional)', { type: 'url', placeholder: 'https://github.com/…' })),
            fFile(it, 'evidence', 'Evidence file or link (report, PDF…)', { dir: 'assets/evidence', accept: '.pdf,image/*,.doc,.docx' })];
        }
      })),
      placeholderCard('projects')];
  }

  function tabCertificates() {
    var s = content.sections.certificates;
    return [head('Certificates'), sectionHead('certificates'),
      card('Your certificates', null, listEditor(s.items, {
        noun: 'certificate', section: 'certificates', blank: function () { return { title: '', issuer: '', date: '', file: '', credentialUrl: '' }; },
        title: function (it) { return it.title; },
        fields: function (it) {
          return [fText(it, 'title', 'Certificate name'),
            two(fText(it, 'issuer', 'Issued by'), fText(it, 'date', 'Date (optional)', { placeholder: 'Oct 2026' })),
            fFile(it, 'file', 'Certificate image or PDF', { dir: 'assets/evidence', accept: 'image/*,.pdf,application/pdf' }),
            fText(it, 'credentialUrl', 'Verification link (optional)', { type: 'url', placeholder: 'https://…' })];
        }
      })),
      placeholderCard('certificates')];
  }

  function tabAchievements() {
    var s = content.sections.achievements;
    return [head('Achievements', 'Awards, competitions, results and milestones.'), sectionHead('achievements'),
      card('Your achievements', null, listEditor(s.items, {
        noun: 'achievement', section: 'achievements', blank: function () { return { title: '', date: '', description: '' }; },
        title: function (it) { return it.title; },
        fields: function (it) {
          return [fText(it, 'title', 'Achievement'), fText(it, 'date', 'Date (optional)'), fText(it, 'description', 'Details (optional)', { rows: 3 })];
        }
      })),
      placeholderCard('achievements')];
  }

  function tabInfo() {
    var s = content.sections.info;
    return [head('Important information', 'Anything a recruiter should know: availability, languages, references, notice, etc.'), sectionHead('info'),
      card('Information boxes', null, listEditor(s.items, {
        noun: 'information box', plural: 'information boxes', section: 'info', blank: function () { return { title: '', body: '' }; },
        title: function (it) { return it.title; },
        fields: function (it) { return [fText(it, 'title', 'Heading'), fText(it, 'body', 'Text', { rows: 4, hint: 'Leave a blank line between paragraphs.' })]; }
      })),
      placeholderCard('info')];
  }

  function tabGoals() {
    var s = content.sections.goals;
    return [head('Career goals'), sectionHead('goals'),
      card('Goals', null, fLines(s, 'items', 'Goals', { rows: 5 })),
      card('Career interests', null, fText(s, 'interestsTitle', 'Heading'), fLines(s, 'interests', 'Interests', { rows: 5 }))];
  }

  function tabContact() {
    var s = content.sections.contact;
    return [head('Contact details', 'Shown in the Contact section and under your name at the top.'), sectionHead('contact'),
      card('Details', null,
        fText(s, 'email', 'Email address', { type: 'email' }),
        fLines(s, 'phones', 'Phone numbers', { rows: 3, hint: 'One per line, with the country code, e.g. +268 7960 0000.' }),
        fText(s, 'location', 'Location'),
        two(fText(s, 'whatsapp', 'WhatsApp number', { hint: 'Digits with country code, no + sign. Leave empty to hide the button.', placeholder: '26879603047' }),
          fText(s, 'whatsappLabel', 'WhatsApp button text'))),
      card('Email box', null,
        fText(s, 'emailBoxTitle', 'Heading'), fText(s, 'emailBoxText', 'Text', { rows: 2 }),
        two(fText(s, 'emailSubject', 'Default email subject'), fText(s, 'emailButton', 'Button text'))),
      card('Social & professional links', 'Leave a link empty to show "… profile to be added".', listEditor(s.socials, {
        noun: 'link', blank: function () { return { label: '', url: '' }; },
        title: function (it) { return it.label; },
        fields: function (it) { return [fText(it, 'label', 'Name', { placeholder: 'LinkedIn' }), fText(it, 'url', 'Link', { type: 'url', placeholder: 'https://…' })]; }
      }))];
  }

  function tabSite() {
    var m = content.meta;
    var rows = h('div', { class: 'layout-list' });
    function draw() {
      rows.replaceChildren();
      content.layout.forEach(function (id, i) {
        var s = content.sections[id];
        var up = h('button', { type: 'button', class: 'btn sm', 'aria-label': 'Move ' + NAMES[id] + ' up', onclick: function () { if (i > 0) { content.layout.splice(i - 1, 0, content.layout.splice(i, 1)[0]); changed(); draw(); } } }, '↑');
        var down = h('button', { type: 'button', class: 'btn sm', 'aria-label': 'Move ' + NAMES[id] + ' down', onclick: function () { if (i < content.layout.length - 1) { content.layout.splice(i + 1, 0, content.layout.splice(i, 1)[0]); changed(); draw(); } } }, '↓');
        var label = h('input', { type: 'text', value: s.navLabel || '', 'aria-label': 'Menu label for ' + NAMES[id], placeholder: 'Menu label' });
        label.addEventListener('input', function () { s.navLabel = label.value; changed(); });
        rows.append(h('div', { class: 'layout-row' },
          h('span', { class: 'sum-btns' }, up, down),
          h('strong', null, NAMES[id]),
          fCheck(s, 'show', 'Show'),
          fCheck(s, 'nav', 'In menu'),
          label));
      });
    }
    draw();
    return [head('Site & layout'),
      card('Website', null,
        fText(m, 'siteTitle', 'Browser tab title'),
        fText(m, 'description', 'Short description (used by search engines and link previews)', { rows: 2 }),
        two(fColor(m, 'accent', 'Accent colour'), fSelect(m, 'defaultTheme', 'Default theme', [['dark', 'Dark'], ['light', 'Light']]))),
      card('Footer', null, fText(content.footer, 'tagline', 'Text on the right of the footer')),
      card('Sections', 'Choose what appears, the order, and what is in the top menu.', rows)];
  }

  /* ---------- GitHub ---------- */
  function guessRepo() {
    var m = location.hostname.match(/^([^.]+)\.github\.io$/i);
    if (!m) return {};
    var seg = location.pathname.split('/').filter(Boolean)[0];
    return { owner: m[1], repo: seg && !/\.html?$/i.test(seg) ? seg : m[1] + '.github.io' };
  }
  function loadCfg() {
    var g = guessRepo(), saved = {};
    try { saved = JSON.parse(ls.get('pf-gh') || '{}'); } catch (e) { saved = {}; }
    var remembered = ls.get('pf-token');
    return {
      owner: saved.owner || g.owner || '', repo: saved.repo || g.repo || '', branch: saved.branch || 'main',
      token: ss.get('pf-token') || remembered || '', remember: !!remembered
    };
  }
  function saveCfg() {
    ls.set('pf-gh', JSON.stringify({ owner: cfg.owner.trim(), repo: cfg.repo.trim(), branch: cfg.branch.trim() }));
    if (cfg.remember) { ls.set('pf-token', cfg.token); ss.del('pf-token'); } else { ss.set('pf-token', cfg.token); ls.del('pf-token'); }
  }
  function log(msg) {
    logLines.push(msg);
    var el = $('#log');
    if (el) { el.hidden = false; el.textContent = logLines.join('\n'); el.scrollTop = el.scrollHeight; }
  }
  function branchPath() { return cfg.branch.trim().split('/').map(encodeURIComponent).join('/'); }
  function repoBase() { return '/repos/' + encodeURIComponent(cfg.owner.trim()) + '/' + encodeURIComponent(cfg.repo.trim()); }

  function gh(path, opts) {
    opts = opts || {};
    var headers = { Accept: opts.accept || 'application/vnd.github+json', Authorization: 'Bearer ' + cfg.token.trim(), 'X-GitHub-Api-Version': '2022-11-28' };
    if (opts.body) headers['Content-Type'] = 'application/json';
    return fetch('https://api.github.com' + path, { method: opts.method || 'GET', headers: headers, body: opts.body }).then(function (r) {
      if (!r.ok) {
        return r.json().catch(function () { return {}; }).then(function (j) {
          var hint = '';
          if (r.status === 401) hint = ' — the token is wrong or expired.';
          else if (r.status === 403 || r.status === 404) hint = ' — check the owner/repository names, and that the token has "Contents: Read and write" access to this repository.';
          else if (r.status === 409) hint = ' — the repository looks empty. Upload your files to it first.';
          else if (r.status === 422) hint = ' — the branch may be protected or the name is wrong.';
          throw new Error('GitHub said ' + r.status + (j.message ? ' (' + j.message + ')' : '') + hint);
        });
      }
      return opts.raw ? r.text() : r.json();
    }, function () { throw new Error('Could not reach GitHub. Check your internet connection.'); });
  }
  function requireCfg() {
    if (!cfg.owner.trim() || !cfg.repo.trim() || !cfg.token.trim()) { go('publish'); toast('Fill in your GitHub details first.'); return false; }
    return true;
  }

  function serialize(c) {
    return '/* Portfolio content. This file is rewritten by edit.html — you normally never edit it by hand. */\nwindow.PORTFOLIO_CONTENT = ' + JSON.stringify(c, null, 2) + ';\n';
  }
  function parseContent(text) {
    var t = String(text).trim();
    try { var j = JSON.parse(t); if (j && typeof j === 'object') return j; } catch (e) { /* fall through */ }
    var a = t.indexOf('{'), b = t.lastIndexOf('}');
    if (a < 0 || b < a) throw new Error('This file does not look like portfolio content.');
    return JSON.parse(t.slice(a, b + 1));
  }

  function testConnection() {
    if (!requireCfg()) return;
    runBusy('Checking…', function () {
      return gh(repoBase()).then(function (r) {
        log('✔ Connected to ' + r.full_name + ' (default branch: ' + r.default_branch + ').');
        if (r.permissions && r.permissions.push === false) log('⚠ This token can read but not write. Give it "Contents: Read and write".');
        if (r.default_branch && r.default_branch !== cfg.branch.trim()) log('ℹ The repository’s default branch is “' + r.default_branch + '” but the Branch field says “' + cfg.branch.trim() + '”. Use the branch that GitHub Pages publishes.');
      });
    });
  }

  function loadLatest() {
    if (!requireCfg()) return;
    if (dirty && !window.confirm('Replace your unpublished changes with the version on GitHub?')) return;
    runBusy('Loading…', function () {
      return gh(repoBase() + '/contents/' + CONTENT_PATH + '?ref=' + encodeURIComponent(cfg.branch.trim()), { accept: 'application/vnd.github.raw+json', raw: true }).then(function (text) {
        replaceContent(parseContent(text), false);
        original = clone(content); ls.del('pf-draft');
        log('✔ Loaded the latest content from GitHub.');
        toast('Loaded latest from GitHub.');
      });
    });
  }

  function publish() {
    if (busy || !requireCfg()) return;
    if (!content.hero.firstName && !content.hero.lastName && !window.confirm('Your name is empty. Publish anyway?')) return;
    runBusy('Publishing…', function () {
      var base = repoBase();
      log('Publishing to ' + cfg.owner + '/' + cfg.repo + ' (' + cfg.branch + ')…');
      var headSha, baseTree, entries = [];
      return gh(base + '/git/ref/heads/' + branchPath()).then(function (ref) {
        headSha = ref.object.sha;
        return gh(base + '/git/commits/' + headSha);
      }).then(function (commit) {
        baseTree = commit.tree.sha;
        var json = JSON.stringify(content);
        var chain = Promise.resolve();
        Object.keys(staged).forEach(function (path) {
          if (json.indexOf(path) === -1) { log('Skipped ' + path + ' (not used on the site).'); return; }
          chain = chain.then(function () {
            log('Uploading ' + path + '…');
            return gh(base + '/git/blobs', { method: 'POST', body: JSON.stringify({ content: staged[path].b64, encoding: 'base64' }) })
              .then(function (b) { entries.push({ path: path, mode: '100644', type: 'blob', sha: b.sha }); });
          });
        });
        return chain;
      }).then(function () {
        return gh(base + '/git/blobs', { method: 'POST', body: JSON.stringify({ content: serialize(content), encoding: 'utf-8' }) });
      }).then(function (b) {
        entries.push({ path: CONTENT_PATH, mode: '100644', type: 'blob', sha: b.sha });
        return gh(base + '/git/trees', { method: 'POST', body: JSON.stringify({ base_tree: baseTree, tree: entries }) });
      }).then(function (tree) {
        return gh(base + '/git/commits', { method: 'POST', body: JSON.stringify({ message: commitMsg.trim() || 'Update portfolio content', tree: tree.sha, parents: [headSha] }) });
      }).then(function (commit) {
        return gh(base + '/git/refs/heads/' + branchPath(), { method: 'PATCH', body: JSON.stringify({ sha: commit.sha }) }).then(function () { return commit; });
      }).then(function (commit) {
        staged = {};
        original = clone(content);
        dirty = false; updateState();
        ls.del('pf-draft');
        log('✔ Published. Commit ' + commit.sha.slice(0, 7) + ' — the live site refreshes in about a minute.');
        toast('Published! The live site updates in about a minute.');
      });
    });
  }

  function runBusy(label, fn) {
    if (busy) return;
    busy = true; setBusy(label);
    var p;
    try { p = Promise.resolve(fn()); } catch (e) { p = Promise.reject(e); }
    p.catch(function (e) { log('✖ ' + e.message); toast('Something went wrong — see the log in Publish & backup.'); if (currentTab !== 'publish') go('publish'); })
      .then(function () { busy = false; setBusy(); });
  }
  function setBusy(label) {
    var b = $('#btnPublish');
    b.disabled = busy; b.textContent = busy && label ? label : 'Publish';
    Array.prototype.forEach.call(document.querySelectorAll('[data-gh]'), function (x) { x.disabled = busy; });
  }

  function tabPublish() {
    var onSite = !!guessRepo().owner;
    var stagedPaths = Object.keys(staged);
    var logBox = h('pre', { class: 'log', id: 'log', 'aria-live': 'polite' }, logLines.join('\n'));
    logBox.hidden = !logLines.length;
    function bind(obj, key, label, opts) {
      opts = opts || {}; opts.onInput = saveCfg; return fText(obj, key, label, opts);
    }
    var ghBtns = h('div', { class: 'row-btns' },
      h('button', { type: 'button', class: 'btn primary', 'data-gh': '', onclick: publish }, 'Publish changes'),
      h('button', { type: 'button', class: 'btn', 'data-gh': '', onclick: testConnection }, 'Test connection'),
      h('button', { type: 'button', class: 'btn', 'data-gh': '', onclick: loadLatest }, 'Load latest from GitHub'));
    ghBtns.addEventListener('click', function () { logBox.hidden = false; });

    var importer = h('input', { type: 'file', accept: '.js,.json,application/json', hidden: true });
    importer.addEventListener('change', function () {
      var f = importer.files && importer.files[0]; if (!f) return;
      f.text().then(function (t) {
        replaceContent(parseContent(t), true);
        toast('Imported. Review the changes, then publish.');
      }).catch(function (e) { toast(e.message); });
      importer.value = '';
    });

    return [
      head('Publish & backup', 'Publishing saves your changes to GitHub in one commit; GitHub Pages then updates the live site.'),
      card('1. Connect to your GitHub repository', null,
        h('ol', { class: 'steps' },
          h('li', null, 'Open ', h('a', { href: 'https://github.com/settings/personal-access-tokens/new', target: '_blank', rel: 'noopener' }, 'GitHub → New fine-grained token'), '.'),
          h('li', null, 'Under "Repository access" choose "Only select repositories" and pick this portfolio repository.'),
          h('li', null, 'Under "Permissions → Repository" set "Contents" to "Read and write". Set a short expiry (e.g. 90 days).'),
          h('li', null, 'Generate the token, copy it and paste it below.')),
        two(bind(cfg, 'owner', 'GitHub username', { placeholder: 'your-username' }), bind(cfg, 'repo', 'Repository name', { placeholder: 'your-username.github.io' })),
        two(bind(cfg, 'branch', 'Branch', { placeholder: 'main' }), bind(cfg, 'token', 'Access token', { type: 'password', placeholder: 'github_pat_…' })),
        fCheck(cfg, 'remember', 'Remember the token on this device', { onInput: saveCfg, hint: 'otherwise it is forgotten when you close this tab. Only tick this on your own computer.' }),
        onSite ? h('p', { class: 'hint' }, 'Owner and repository were filled in from this page’s address.') : null),
      card('2. Publish', 'Anything you uploaded (images, PDFs) is sent in the same commit.',
        fText({ get v() { return commitMsg; }, set v(x) { commitMsg = x; } }, 'v', 'Commit message (optional)', { onInput: function () {} }),
        ghBtns, logBox,
        stagedPaths.length ? h('ul', { class: 'staged' }, stagedPaths.map(function (p) {
          return h('li', null, p + ' (' + Math.round(staged[p].size / 1024) + ' KB) ', h('a', { href: staged[p].dataUrl, download: staged[p].name }, 'download'));
        })) : null),
      card('Backup & offline use', 'Don’t want to use a token? Download the content file and replace data/content.js in your repository by hand (upload any new files to the matching folder too).',
        h('div', { class: 'row-btns' },
          h('button', { type: 'button', class: 'btn', onclick: function () { downloadText('content.js', serialize(content), 'text/javascript'); } }, 'Download content.js'),
          h('button', { type: 'button', class: 'btn', onclick: function () { importer.click(); } }, 'Import content file…'),
          h('button', { type: 'button', class: 'btn danger', onclick: function () {
            if (window.confirm('Discard all unpublished changes?')) { replaceContent(clone(original), false); ls.del('pf-draft'); toast('Changes discarded.'); }
          } }, 'Discard unpublished changes'),
          importer))
    ];
  }

  function downloadText(name, text, type) {
    var url = URL.createObjectURL(new Blob([text], { type: type }));
    var a = h('a', { href: url, download: name });
    document.body.append(a); a.click(); a.remove();
    setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
  }

  function replaceContent(obj, markDirty) {
    content = normalize(obj);
    dirty = !!markDirty; updateState();
    if (markDirty) changed();
    renderTab(); sendPreview();
  }

  /* ---------- file staging ---------- */
  function bufToB64(buf) {
    var s = '', u = new Uint8Array(buf);
    for (var i = 0; i < u.length; i += 0x8000) s += String.fromCharCode.apply(null, u.subarray(i, i + 0x8000));
    return btoa(s);
  }
  function shrinkImage(file) {
    return createImageBitmap(file).then(function (bmp) {
      var scale = Math.min(1, 1600 / Math.max(bmp.width, bmp.height));
      var c = document.createElement('canvas');
      c.width = Math.round(bmp.width * scale); c.height = Math.round(bmp.height * scale);
      var ctx = c.getContext('2d');
      ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height);
      ctx.drawImage(bmp, 0, 0, c.width, c.height);
      return new Promise(function (res, rej) { c.toBlob(function (b) { b ? res(b) : rej(new Error('shrink failed')); }, 'image/jpeg', 0.85); });
    });
  }
  function stageFile(file, dir) {
    if (file.size > MAX_FILE) return Promise.reject(new Error('That file is larger than 25 MB.'));
    var big = /^image\/(jpeg|png|webp)$/.test(file.type) && file.size > 1.5 * 1024 * 1024;
    var prep = big ? shrinkImage(file).then(function (b) { return { blob: b, name: file.name.replace(/\.[^.]+$/, '') + '.jpg', type: 'image/jpeg' }; }, function () { return { blob: file, name: file.name, type: file.type }; })
      : Promise.resolve({ blob: file, name: file.name, type: file.type });
    return prep.then(function (p) {
      return p.blob.arrayBuffer().then(function (buf) {
        var name = p.name.toLowerCase().replace(/[^a-z0-9._-]+/g, '-').replace(/-+\./g, '.').replace(/^-+|-+$/g, '') || 'file';
        var path = dir + '/' + name, n = 2;
        while (staged[path] && staged[path].size !== buf.byteLength) path = dir + '/' + name.replace(/(\.[^.]*)?$/, function (ext) { return '-' + (n++) + (ext || ''); });
        var b64 = bufToB64(buf);
        var type = p.type || 'application/octet-stream';
        staged[path] = { b64: b64, dataUrl: 'data:' + type + ';base64,' + b64, size: buf.byteLength, type: type, name: path.split('/').pop() };
        previewUrls[path] = staged[path].dataUrl;
        postPreview({ type: 'pf-asset', path: path, url: staged[path].dataUrl });
        return path;
      });
    });
  }

  /* ---------- preview ---------- */
  var pv = $('#pv'), pvReady = false;
  function postPreview(msg) { if (pvReady && pv.contentWindow) pv.contentWindow.postMessage(msg, '*'); }
  function sendPreview() { postPreview({ type: 'pf-content', content: content }); }
  window.addEventListener('message', function (e) {
    if (e.source === pv.contentWindow && e.data && e.data.type === 'pf-ready') {
      pvReady = true;
      Object.keys(previewUrls).forEach(function (p) { postPreview({ type: 'pf-asset', path: p, url: previewUrls[p] }); });
      sendPreview();
      var t = TABS.filter(function (x) { return x.id === currentTab; })[0];
      if (t && t.scroll) postPreview({ type: 'pf-scroll', id: t.scroll });
    }
  });

  /* ---------- tab navigation ---------- */
  var TABS = [
    { id: 'hero', label: 'Hero & CV', scroll: 'home', build: tabHero },
    { id: 'about', label: 'About', scroll: 'about', build: tabAbout },
    { id: 'education', label: 'Education', scroll: 'education', build: tabEducation },
    { id: 'skills', label: 'Skills', scroll: 'skills', build: tabSkills },
    { id: 'projects', label: 'Projects', scroll: 'projects', build: tabProjects },
    { id: 'certificates', label: 'Certificates', scroll: 'certificates', build: tabCertificates },
    { id: 'achievements', label: 'Achievements', scroll: 'achievements', build: tabAchievements },
    { id: 'info', label: 'Important info', scroll: 'info', build: tabInfo },
    { id: 'goals', label: 'Career goals', scroll: 'goals', build: tabGoals },
    { id: 'contact', label: 'Contact details', scroll: 'contact', build: tabContact },
    { sep: true },
    { id: 'site', label: 'Site & layout', build: tabSite },
    { id: 'publish', label: 'Publish & backup', build: tabPublish }
  ];

  function renderTabs() {
    PF.mount($('#tabs'), TABS.map(function (t) {
      if (t.sep) return h('div', { class: 'sep' });
      return h('button', { type: 'button', 'aria-current': t.id === currentTab ? 'true' : null, onclick: function () { go(t.id); } }, t.label);
    }));
  }
  function renderTab() {
    var t = TABS.filter(function (x) { return x.id === currentTab; })[0] || TABS[0];
    var panel = $('#panel'), top = panel.scrollTop;
    PF.mount(panel, t.build());
    panel.scrollTop = top;
  }
  function go(id) {
    currentTab = id;
    renderTabs(); renderTab();
    $('#panel').scrollTop = 0;
    var t = TABS.filter(function (x) { return x.id === id; })[0];
    if (t && t.scroll) postPreview({ type: 'pf-scroll', id: t.scroll });
  }

  /* ---------- boot ---------- */
  $('#btnPublish').addEventListener('click', publish);
  $('#btnPreview').addEventListener('click', function () { document.body.classList.add('show-preview'); });
  $('#pvClose').addEventListener('click', function () { document.body.classList.remove('show-preview'); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') document.body.classList.remove('show-preview'); });
  $('#pvDevice').addEventListener('click', function () {
    var phone = $('#pvFrame').classList.toggle('phone');
    this.textContent = phone ? 'Full width' : 'Phone width';
  });
  window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

  renderTabs(); renderTab(); updateState();

  // offer to restore an unpublished draft from a previous visit
  (function () {
    var raw = ls.get('pf-draft'); if (!raw) return;
    try {
      var d = JSON.parse(raw);
      if (!d || !d.content || JSON.stringify(normalize(clone(d.content))) === JSON.stringify(content)) { ls.del('pf-draft'); return; }
      showBanner('You have unpublished changes from ' + new Date(d.at).toLocaleString() + '. (Files you uploaded last time are not kept — upload them again if needed.)', [
        h('button', { type: 'button', class: 'btn sm', onclick: function () { replaceContent(d.content, true); hideBanner(); } }, 'Restore them'),
        h('button', { type: 'button', class: 'btn sm', onclick: function () { ls.del('pf-draft'); hideBanner(); } }, 'Discard')
      ]);
    } catch (e) { ls.del('pf-draft'); }
  })();
})();
