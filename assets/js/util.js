/* Shared helpers used by index.html and edit.html.
   Everything is built with DOM methods (never innerHTML) so content typed in the
   editor can never inject markup or scripts into the public page. */
(function (global) {
  'use strict';

  /** Tiny DOM builder: h('a', {href:'#', class:'x'}, 'text', childNode, [more]) */
  function h(tag, attrs) {
    var el = document.createElement(tag);
    var a = attrs || {};
    Object.keys(a).forEach(function (k) {
      var v = a[k];
      if (v === null || v === undefined || v === false) return;
      if (k === 'class') el.className = v;
      else if (k === 'text') el.textContent = v;
      else if (k === 'value' || k === 'checked' || k === 'selected' || k === 'disabled' || k === 'hidden') el[k] = v;
      else if (k.slice(0, 2) === 'on' && typeof v === 'function') el.addEventListener(k.slice(2), v);
      else if (v === true) el.setAttribute(k, '');
      else el.setAttribute(k, v);
    });
    var kids = Array.prototype.slice.call(arguments, 2);
    (function add(list) {
      list.forEach(function (kid) {
        if (kid === null || kid === undefined || kid === false) return;
        if (Array.isArray(kid)) return add(kid);
        el.appendChild(kid && kid.nodeType ? kid : document.createTextNode(String(kid)));
      });
    })(kids);
    return el;
  }

  /** Replace an element's children with nodes/strings/arrays (null and false are skipped). */
  function mount(el) {
    while (el.firstChild) el.removeChild(el.firstChild);
    (function add(list) {
      list.forEach(function (kid) {
        if (kid === null || kid === undefined || kid === false) return;
        if (Array.isArray(kid)) return add(kid);
        el.appendChild(kid && kid.nodeType ? kid : document.createTextNode(String(kid)));
      });
    })(Array.prototype.slice.call(arguments, 1));
    return el;
  }

  /** Only allow safe link types. Returns '' for anything else (e.g. javascript:). */
  function safeUrl(u) {
    u = String(u === null || u === undefined ? '' : u).replace(/[\u0000-\u001f\u007f]/g, '').trim();
    if (!u) return '';
    if (/^data:(image\/(png|jpe?g|gif|webp)|application\/pdf)[;,]/i.test(u)) return u;
    var m = u.match(/^([a-z][a-z0-9+.-]*):/i);
    if (m && ['http', 'https', 'mailto', 'tel'].indexOf(m[1].toLowerCase()) === -1) return '';
    return u;
  }

  function telHref(s) {
    var d = String(s || '').replace(/[^\d+]/g, '');
    return d ? 'tel:' + d : '';
  }

  function waHref(s) {
    var d = String(s || '').replace(/\D/g, '');
    return d ? 'https://wa.me/' + d : '';
  }

  function isImagePath(p) {
    return /\.(png|jpe?g|gif|webp|avif)$/i.test(p || '') || /^data:image\//i.test(p || '');
  }

  global.PF = { h: h, mount: mount, safeUrl: safeUrl, telHref: telHref, waHref: waHref, isImagePath: isImagePath };
})(window);
