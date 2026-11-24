(function () {
  var $ = function (i) { return document.getElementById(i); };
  function getCsrf() {
  var m = document.querySelector('meta[name=csrf]');
  var h = document.querySelector('input[name=csrf]');
  return (m && m.content) || (h && h.value) || '';
}
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
 var lastId = 0;
  // set lastId from messages already on page
  // (your history is rendered by PHP, so we start polling after that)
  window.send = function () {
  var t = msg.value.trim(); if (!t) return;
  add(t, true); msg.value = '';
  var fd = new FormData();
  fd.append('csrf', getCsrf());
  fd.append('action', 'send');
  fd.append('message', t);
  fetch('support.php', { method: 'POST', body: fd, credentials: 'same-origin' })
    .then(function (r) { if (!r.ok) throw new Error('bad'); return r.json(); })
    .then(function (j) { if (!j.ok) throw new Error('bad'); })
    .catch(function () { add('Connection problem. Please try again.', false); });
};

  // TRUE ADMIN: poll every 3 sec for real admin replies
  setInterval(function () {
    if (!log) return;
    fetch('support.php?action=poll&last_id=' + lastId, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (rows) {
        rows.forEach(function (row) {
          if (row.sender === 'agent') {
            add(row.message, false);
          }
          lastId = Math.max(lastId, parseInt(row.id, 10));
        });
      });
  }, 3000);
  if (log && msg) msg.addEventListener('keydown', function (e) { if (e.key === 'Enter') window.send(); });
})();
