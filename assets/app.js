// File: assets/app.js | Purpose: Run theme, login countdown, live filter, and admin status UI.
// Access: Guest, user, and admin pages. Tables: None; server endpoints handle database work.

// dark mode: toggle the page theme and remember the preference in this browser.
(function () {
  var root = document.documentElement, btn = document.getElementById('themeToggle');
  function label() { if (btn) btn.textContent = root.getAttribute('data-bs-theme') === 'dark' ? '☀️ Light mode' : '🌙 Dark mode'; }
  label();
  if (btn) btn.addEventListener('click', function () {
    var next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    root.setAttribute('data-bs-theme', next);
    try { localStorage.setItem('theme', next); } catch (e) {}
    label();
  });
})();

// countdown: display the remaining lockout time; the server remains the source of enforcement.
(function () {
  var cd = document.getElementById('countdown');
  if (!cd) return;
  var s = parseInt(cd.dataset.seconds, 10) || 0;
  (function tick() {
    cd.textContent = s;
    if (s <= 0) { window.location.href = 'login.php'; return; }
    s--; setTimeout(tick, 1000);
  })();
})();

// live filter: refresh only table rows as the search text or status selection changes.
(function () {
  var form = document.getElementById('filterForm'), body = document.getElementById('feedbackBody');
  if (!form || !body) return;
  var timer;
  async function run() {
    var params = new URLSearchParams(new FormData(form));
    try {
      var ajaxParams = new URLSearchParams(params); ajaxParams.set('ajax', '1');
      var r = await fetch('index.php?' + ajaxParams.toString(), { credentials: 'same-origin' });
      if (r.redirected) { window.location.reload(); return; }
      body.innerHTML = await r.text();
      history.replaceState(null, '', 'index.php?' + params.toString());
    } catch (e) { /* keep the current rows if the request fails */ }
  }
  form.addEventListener('submit', function (e) { e.preventDefault(); run(); });
  form.querySelector('[name=status]').addEventListener('change', run);
  form.querySelector('[name=q]').addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(run, 300); });
})();

// admin status update: save the selected status and restore the old value if saving fails.
document.addEventListener('change', async function (e) {
  var sel = e.target.closest ? e.target.closest('.status-select') : null;
  if (!sel) return;
  var msg = sel.parentElement.querySelector('.status-msg');
  var token = document.querySelector('meta[name=csrf-token]').content;
  var data = new FormData();
  data.append('id', sel.dataset.id); data.append('status', sel.value); data.append('csrf', token);
  msg.textContent = 'Saving...'; msg.className = 'status-msg small ms-1 text-muted';
  try {
    var r = await fetch('update_status.php', { method: 'POST', body: data, credentials: 'same-origin' });
    var j = await r.json();
    if (!r.ok || !j.ok) throw new Error(j.error || 'Failed');
    sel.dataset.current = sel.value;
    sel.className = 'form-select form-select-sm status-select status-' + sel.value.toLowerCase();
    msg.textContent = '✓ Saved'; msg.className = 'status-msg small ms-1 text-success';
  } catch (err) {
    sel.value = sel.dataset.current;
    msg.textContent = 'Failed'; msg.className = 'status-msg small ms-1 text-danger';
  }
  setTimeout(function () { msg.textContent = ''; }, 2000);
});
