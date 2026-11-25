// 1) Dark mode toggle (preference saved in the browser)
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

// 2) Login cooldown countdown (server enforces the lock; this is just the display)
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

// 3) Live search + status filter (no Filter button): reload only the table rows
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

// 4) Admin: change a feedback's status instantly from the table
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
