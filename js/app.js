(function () {
  var $ = function (i) { return document.getElementById(i); };
  var csrf = (document.querySelector('meta[name=csrf]') || {}).content || '';
  var tools = document.querySelector('.tools');
  var np = $('notePanel'), up = $('userPanel'), bell = $('bell'), ub = $('userBtn');

  function closeAll() {
    [np, up].forEach(function (p) { if (p) p.classList.remove('open'); });
    if (bell) bell.setAttribute('aria-expanded', 'false');
    if (ub) ub.setAttribute('aria-expanded', 'false');
  }
  function toggle(panel, btn) {
    return function (e) {
      e.stopPropagation();
      var open = !panel.classList.contains('open');
      closeAll();
      if (open) { panel.classList.add('open'); btn.setAttribute('aria-expanded', 'true'); }
    };
  }
  if (bell && np) bell.onclick = toggle(np, bell);
  if (ub && up) ub.onclick = toggle(up, ub);
  document.addEventListener('click', function (e) { if (tools && !tools.contains(e.target)) closeAll(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAll(); });

  function post(data) {
    var fd = new FormData();
    fd.append('csrf', csrf);
    Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
    return fetch('notifications.php', { method: 'POST', body: fd, credentials: 'same-origin' });
  }
  function refreshBadge() {
    var n = document.querySelectorAll('.nt.un').length, b = $('badge');
    if (b) { b.textContent = n; b.style.display = n ? 'grid' : 'none'; }
  }
  if (np) {
    var all = $('markAll');
    if (all) all.onclick = function () {
      post({ action: 'read_all' }).then(function () {
        np.querySelectorAll('.nt').forEach(function (b) { b.classList.remove('un'); });
        refreshBadge();
      });
    };
    np.querySelectorAll('.nt').forEach(function (b) {
      b.onclick = function () {
        var href = b.dataset.href;
        post({ action: 'read', id: b.dataset.id }).then(function () {
          b.classList.remove('un'); refreshBadge();
          if (href) location.href = href;
        });
      };
    });
  }

  /* Customer service chat */
  var log = $('log'), msg = $('msg');
  function add(t, me) {
    var m = document.createElement('div');
    m.className = 'm' + (me ? ' me' : '');
    m.innerHTML = '<span class="av">🐾</span><p></p>';
    m.querySelector('p').textContent = t;
    log.appendChild(m); log.scrollTop = log.scrollHeight;
  }
  window.send = function () {
    var t = msg.value.trim(); if (!t) return;
    add(t, true); msg.value = '';
    var fd = new FormData(); fd.append('csrf', csrf); fd.append('message', t);
    fetch('chat.php?action=send', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) { add(j.reply || 'Sorry, something went wrong.', false); })
      .catch(function () { add('Connection problem. Please try again.', false); });
  };
  if (log && msg) msg.addEventListener('keydown', function (e) { if (e.key === 'Enter') window.send(); });
})();
