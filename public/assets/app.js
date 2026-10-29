(function () {
  'use strict';

  var APP = window.APP || {};
  var csrfMeta = document.querySelector('meta[name="csrf"]');
  var csrf = csrfMeta ? csrfMeta.content : '';
  var pollMs = Math.max(1000, APP.poll || 2500);

  function byId(id) { return document.getElementById(id); }
  function on(el, ev, fn) { if (el) el.addEventListener(ev, fn); }

  function requestJson(path, body) {
    var opts = {
      method: body === undefined ? 'GET' : 'POST',
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
      cache: 'no-store'
    };
    if (body !== undefined) {
      opts.headers['Content-Type'] = 'application/json';
      opts.headers['X-CSRF'] = csrf;
      opts.body = JSON.stringify(body);
    }
    return fetch(path, opts).then(function (res) {
      return res.json().catch(function () { return {}; }).then(function (data) {
        if (!res.ok || data.ok === false) {
          throw new Error(data.error || 'Da ist etwas schiefgelaufen.');
        }
        return data;
      });
    });
  }

  var toastTimer = null;
  function toast(message, kind, sticky) {
    var el = byId('toast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'toast';
      el.className = 'toast';
      document.body.appendChild(el);
    }
    el.textContent = message;
    el.className = 'toast' + (kind ? ' ' + kind : '');
    el.hidden = false;
    clearTimeout(toastTimer);
    if (!sticky) {
      toastTimer = setTimeout(function () { el.hidden = true; }, 3200);
    }
  }

  // ------------------------------------------------------------- Anlegen

  function initCreate() {
    var terms = byId('terms');
    var counter = byId('termCounter');
    var grid = byId('grid');
    var freeCenter = byId('free_center');
    if (!terms || !counter) return;

    var packs = APP.packs || {};

    function needed() {
      var size = parseInt(grid.value, 10);
      return size * size - (freeCenter.checked ? 1 : 0);
    }

    function uniqueLines() {
      var seen = {};
      var out = [];
      terms.value.split(/\r?\n/).forEach(function (line) {
        var clean = line.trim();
        if (!clean) return;
        var key = clean.toLowerCase();
        if (seen[key]) return;
        seen[key] = true;
        out.push(clean);
      });
      return out;
    }

    function update() {
      var count = uniqueLines().length;
      var need = needed();
      counter.textContent = count + ' Begriffe · ' + need + ' nötig';
      counter.className = 'counter ' + (count < need ? 'warn' : 'good');
    }

    function hint(message, kind) {
      var el = byId('packHint');
      if (!el) return;
      el.textContent = message;
      el.className = 'packs-hint' + (kind ? ' ' + kind : '');
    }

    function addPack(id) {
      var pack = packs[id];
      if (!pack || !pack.terms) return;

      var current = uniqueLines();
      var have = {};
      current.forEach(function (term) { have[term.toLowerCase()] = true; });

      var added = 0;
      pack.terms.forEach(function (term) {
        var key = term.toLowerCase();
        if (have[key]) return;
        have[key] = true;
        current.push(term);
        added++;
      });

      terms.value = current.join('\n');
      update();
      hint(added > 0
        ? pack.label + ': ' + added + ' Begriffe ergänzt (' + current.length + ' gesamt).'
        : pack.label + ' war komplett schon drin.', added > 0 ? 'good' : '');
    }

    Array.prototype.forEach.call(document.querySelectorAll('.chip[data-pack]'), function (chip) {
      on(chip, 'click', function () {
        addPack(chip.getAttribute('data-pack'));
      });
    });

    on(byId('clearTerms'), 'click', function () {
      terms.value = '';
      update();
      hint('Liste geleert.', '');
      terms.focus();
    });

    on(terms, 'input', update);
    on(grid, 'change', update);
    on(freeCenter, 'change', update);
    update();
  }

  // --------------------------------------------------------------- Board

  function renderBoard(board, card, terms, grid) {
    board.style.setProperty('--grid', grid);
    board.textContent = '';
    card.forEach(function (termId, idx) {
      var cell = document.createElement('button');
      cell.type = 'button';
      cell.className = 'cell' + (termId === 0 ? ' cell-free' : '');
      cell.dataset.idx = String(idx);
      if (termId === 0) cell.disabled = true;

      var span = document.createElement('span');
      span.className = 'cell-text';
      span.textContent = termId === 0 ? 'FREI' : (terms[termId] || '?');
      cell.appendChild(span);
      board.appendChild(cell);
    });
  }

  function linesFor(grid) {
    var lines = [];
    var r, c, i;
    for (r = 0; r < grid; r++) {
      var row = [];
      for (c = 0; c < grid; c++) row.push(r * grid + c);
      lines.push(row);
    }
    for (c = 0; c < grid; c++) {
      var col = [];
      for (r = 0; r < grid; r++) col.push(r * grid + c);
      lines.push(col);
    }
    var d1 = [], d2 = [];
    for (i = 0; i < grid; i++) {
      d1.push(i * grid + i);
      d2.push(i * grid + (grid - 1 - i));
    }
    lines.push(d1);
    lines.push(d2);
    return lines;
  }

  function bestLine(card, marks, grid) {
    var set = {};
    marks.forEach(function (m) { set[m] = true; });
    var best = 0;
    linesFor(grid).forEach(function (line) {
      var hits = 0;
      line.forEach(function (idx) {
        if (card[idx] === 0 || set[idx]) hits++;
      });
      if (hits > best) best = hits;
    });
    return best;
  }

  // ---------------------------------------------------------------- Host

  function initHost() {
    var page = document.body;
    var badge = byId('statusBadge');
    var roundNum = byId('roundNum');
    var playerCount = byId('playerCount');
    var tbody = byId('playersBody');
    var winsList = byId('winsList');
    var emptyHint = byId('emptyHint');
    var noWinsHint = byId('noWinsHint');
    var flash = byId('winFlash');
    var flashName = byId('winFlashName');

    var statusLabels = { lobby: 'Warteraum', running: 'läuft', finished: 'beendet' };
    var seenWins = {};
    var firstRender = true;

    function joinUrl() {
      return location.origin + '/beitreten?code=' + encodeURIComponent(APP.code);
    }

    function paintQr() {
      var box = byId('qrCode');
      var url = joinUrl();
      var label = byId('joinUrl');
      if (label) label.textContent = url.replace(/^https?:\/\//, '');
      if (!box || !window.qrcode) return;
      try {
        var qr = window.qrcode(0, 'M');
        qr.addData(url);
        qr.make();
        box.innerHTML = qr.createSvgTag({ cellSize: 4, margin: 0 });
      } catch (e) {
        box.textContent = '';
      }
    }

    function showFlash(name) {
      if (!flash || !flashName) return;
      flashName.textContent = name + ' hat BINGO!';
      flash.hidden = false;
      setTimeout(function () { flash.hidden = true; }, 2600);
    }

    function renderPlayers(players) {
      if (!tbody) return;
      if (playerCount) playerCount.textContent = players.length;
      if (emptyHint) emptyHint.hidden = players.length > 0;

      tbody.textContent = '';
      players.forEach(function (p, i) {
        var tr = document.createElement('tr');

        var cells = [
          { text: String(i + 1), cls: 'muted' },
          { text: p.name },
          { text: String(p.marks) }
        ];

        cells.forEach(function (c) {
          var td = document.createElement('td');
          td.textContent = c.text;
          if (c.cls) td.className = c.cls;
          tr.appendChild(td);
        });

        var tdBar = document.createElement('td');
        var bar = document.createElement('span');
        bar.className = 'bar';
        var fill = document.createElement('span');
        fill.style.width = Math.round((p.best / Math.max(1, p.total)) * 100) + '%';
        bar.appendChild(fill);
        tdBar.appendChild(bar);
        tdBar.appendChild(document.createTextNode(p.best + '/' + p.total));
        tr.appendChild(tdBar);

        var tdWins = document.createElement('td');
        if (p.wins > 0) {
          var pill = document.createElement('span');
          pill.className = 'pill pill-win';
          pill.textContent = String(p.wins);
          tdWins.appendChild(pill);
        } else {
          tdWins.textContent = '–';
        }
        tr.appendChild(tdWins);

        tbody.appendChild(tr);
      });
    }

    function renderWins(wins, round) {
      if (!winsList) return;
      var current = wins.filter(function (w) { return w.round === round; });
      if (noWinsHint) noWinsHint.hidden = current.length > 0;

      winsList.textContent = '';
      current.forEach(function (w) {
        var li = document.createElement('li');
        var name = document.createElement('b');
        name.textContent = w.name;
        var meta = document.createElement('span');
        meta.className = 'muted';
        meta.textContent = ' ' + w.time + ' · ' + w.type + ' ' + (w.index + 1);
        li.appendChild(name);
        li.appendChild(meta);
        winsList.appendChild(li);

        var key = w.name + '|' + w.round + '|' + w.type + '|' + w.index;
        if (!seenWins[key]) {
          seenWins[key] = true;
          if (!firstRender) showFlash(w.name);
        }
      });
      firstRender = false;
    }

    function render(data) {
      var game = data.game || {};
      if (badge) {
        badge.textContent = statusLabels[game.status] || game.status;
        badge.className = 'badge badge-' + game.status;
      }
      page.dataset.status = game.status;
      if (roundNum) roundNum.textContent = game.round;
      renderPlayers(data.players || []);
      renderWins(data.wins || [], game.round);
    }

    function loop() {
      requestJson('/api/status?code=' + encodeURIComponent(APP.code) + '&admin=' + encodeURIComponent(APP.admin))
        .then(render)
        .catch(function () { /* Netzwerk aussetzen – nächster Versuch */ })
        .then(function () { setTimeout(loop, pollMs); });
    }

    paintQr();
    loop();
  }

  // ---------------------------------------------------------------- Karte

  function initCard() {
    var page = byId('cardPage');
    var board = byId('board');
    if (!page || !board) return;

    var grid = APP.grid || parseInt(board.style.getPropertyValue('--grid'), 10) || 5;
    var status = page.dataset.status;
    var round = parseInt(page.dataset.round, 10) || 1;
    var card = [];
    var terms = {};
    var marks = [];
    var bingoBtn = byId('bingoBtn');

    function readCardFromDom() {
      card = [];
      Array.prototype.forEach.call(board.children, function (cell) {
        card.push(cell.classList.contains('cell-free') ? 0 : -1);
      });
    }

    function paintMarks() {
      var set = {};
      marks.forEach(function (m) { set[m] = true; });
      Array.prototype.forEach.call(board.children, function (cell) {
        if (cell.classList.contains('cell-free')) return;
        var idx = parseInt(cell.dataset.idx, 10);
        cell.classList.toggle('is-marked', !!set[idx]);
      });
      var bestEl = byId('bestNum');
      var countEl = byId('markCount');
      if (countEl) countEl.textContent = marks.length;
      if (bestEl && card.length) bestEl.textContent = bestLine(card, marks, grid);
    }

    function setStatus(next, label) {
      status = next;
      page.dataset.status = next;
      var badge = byId('statusBadge');
      if (badge) {
        badge.textContent = label;
        badge.className = 'badge badge-' + next;
      }
      var hint = byId('hint');
      if (hint) {
        if (next === 'lobby') hint.textContent = 'Warte auf den Start. Deine Karte siehst du schon – tippen geht gleich los.';
        else if (next === 'finished') hint.textContent = 'Das Spiel ist beendet. Danke fürs Mitspielen!';
        else hint.textContent = 'Tippe ein Feld an, sobald der Begriff gefallen ist.';
      }
      if (bingoBtn) bingoBtn.disabled = next !== 'running';
    }

    function syncCard(nextCard, nextTerms) {
      card = nextCard;
      terms = nextTerms;
      renderBoard(board, card, terms, grid);
      paintMarks();
    }

    on(board, 'click', function (event) {
      var cell = event.target.closest('.cell');
      if (!cell || cell.classList.contains('cell-free')) return;

      if (status !== 'running') {
        toast('Das Spiel läuft gerade nicht.');
        return;
      }

      var idx = parseInt(cell.dataset.idx, 10);
      var turnOn = !cell.classList.contains('is-marked');

      marks = marks.filter(function (m) { return m !== idx; });
      if (turnOn) marks.push(idx);
      paintMarks();

      requestJson('/api/mark', { code: APP.code, cell: idx, on: turnOn })
        .then(function (data) {
          marks = data.marks || marks;
          paintMarks();
        })
        .catch(function (err) {
          marks = marks.filter(function (m) { return m !== idx; });
          if (turnOn === false) marks.push(idx);
          paintMarks();
          toast(err.message, 'error');
        });
    });

    on(bingoBtn, 'click', function () {
      if (bingoBtn) bingoBtn.disabled = true;
      requestJson('/api/bingo', { code: APP.code })
        .then(function (data) {
          if (data.ok && data.new > 0) {
            toast('BINGO! Der Host sieht dich jetzt.', 'ok');
          } else {
            toast('Bingo war schon gewertet.', 'ok');
          }
        })
        .catch(function (err) {
          toast(err.message, 'error');
        })
        .then(function () {
          if (bingoBtn) bingoBtn.disabled = status !== 'running';
        });
    });

    var lastCardJson = '';
    var lastWinKey = '';
    function loop() {
      requestJson('/api/status?code=' + encodeURIComponent(APP.code))
        .then(function (data) {
          var game = data.game || {};
          if (game.status !== status) setStatus(game.status, { lobby: 'Warteraum', running: 'läuft', finished: 'beendet' }[game.status] || game.status);

          if (game.round !== round) {
            round = game.round;
            var rn = byId('roundNum');
            if (rn) rn.textContent = round;
            marks = [];
            toast('Neue Runde – neue Karte!', 'ok');
          }

          if (data.me) {
            var nextCard = data.me.card || [];
            var fingerprint = JSON.stringify(nextCard);
            if (fingerprint !== lastCardJson) {
              lastCardJson = fingerprint;
              syncCard(nextCard, data.terms || {});
            }
            marks = data.me.marks || [];
            paintMarks();
          }

          var wins = (data.wins || []).filter(function (w) { return w.round === round && w.name !== (data.me && data.me.name); });
          if (wins.length) {
            var last = wins[wins.length - 1];
            var key = last.name + last.round + last.type + last.index;
            if (key !== lastWinKey) {
              lastWinKey = key;
              toast(last.name + ' hat BINGO!', 'ok');
            }
          }
        })
        .catch(function () { /* still */ })
        .then(function () { setTimeout(loop, pollMs); });
    }

    readCardFromDom();
    setStatus(status, (byId('statusBadge') || {}).textContent || '');
    if (bingoBtn) bingoBtn.disabled = status !== 'running';
    loop();
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (APP.page === 'create') initCreate();
    else if (APP.page === 'host') initHost();
    else if (APP.page === 'card') initCard();
  });
})();
