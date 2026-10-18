(function () {
  var MOVE_URL = '/os/app/tictactoe/move';
  // The operator may have renamed her; the shell's own preferences endpoint is
  // the only place that knows. Until it answers, the markup's fallback stands.
  var aname = (document.querySelector('[data-aname]') || {}).textContent || 'Solomiia';
  fetch('/os/preferences', { headers: { 'Accept': 'application/json' } })
    .then(function (r) { return r.json(); })
    .then(function (d) {
      if (!d || !d.assistant_name) return;
      aname = d.assistant_name;
      Array.prototype.forEach.call(document.querySelectorAll('[data-aname]'), function (el) {
        el.textContent = aname;
      });
      document.title = 'Tic-tac-toe · ' + aname;
    })
    .catch(function () {});
  // gameId tags every opponent request: "New game" during a slow answer must
  // not let the old game's move (or its result) land on the fresh board.
  var board, over, thinking, gameId = 0;
  var grid = document.getElementById('grid');
  var statusEl = document.getElementById('status');

  function reset() {
    gameId++;
    board = ['', '', '', '', '', '', '', '', ''];
    over = false; thinking = false;
    setStatus('Your move — you are <b style="color:var(--x);margin:0 3px">X</b>', '');
    render();
  }

  function setStatus(html, cls) {
    statusEl.className = 'ttt__status' + (cls ? ' ' + cls : '');
    statusEl.innerHTML = html;
  }

  function render(winLine) {
    grid.className = 'ttt__grid' + (thinking ? ' thinking' : '');
    grid.innerHTML = '';
    for (var i = 0; i < 9; i++) {
      var v = board[i];
      var b = document.createElement('button');
      b.className = 'ttt__cell' + (v ? ' filled ' + v.toLowerCase() : '') +
        (winLine && winLine.indexOf(i) !== -1 ? ' win' : '');
      b.textContent = v;
      b.disabled = !!v || over || thinking;
      b.dataset.i = i;
      grid.appendChild(b);
    }
  }

  var LINES = [[0,1,2],[3,4,5],[6,7,8],[0,3,6],[1,4,7],[2,5,8],[0,4,8],[2,4,6]];
  function outcome(bd) {
    for (var k = 0; k < LINES.length; k++) {
      var l = LINES[k];
      if (bd[l[0]] && bd[l[0]] === bd[l[1]] && bd[l[1]] === bd[l[2]]) return { status: 'win', winner: bd[l[0]], line: l };
    }
    for (var i = 0; i < 9; i++) if (!bd[i]) return { status: 'continue' };
    return { status: 'draw' };
  }

  function finish(res) {
    over = true; thinking = false;
    if (res.status === 'draw') { setStatus("It's a draw.", 'draw'); render(); return; }
    if (res.winner === 'X') setStatus('You win! 🎉', 'win');
    else setStatus(escapeHtml(aname) + ' wins this one.', 'lose');
    render(res.line);
  }

  grid.addEventListener('click', function (e) {
    var cell = e.target.closest('.ttt__cell'); if (!cell) return;
    var i = +cell.dataset.i;
    if (over || thinking || board[i]) return;
    board[i] = 'X';
    render();
    var res = outcome(board);
    if (res.status !== 'continue') { finish(res); return; }
    opponentMove();
  });

  async function opponentMove() {
    var myGame = gameId;
    thinking = true;
    setStatus(escapeHtml(aname) + ' is thinking <span class="dots"><i></i><i></i><i></i></span>', '');
    render();
    var data;
    try {
      var r = await fetch(MOVE_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ board: board })
      });
      data = await r.json();
    } catch (err) { data = null; }

    if (myGame !== gameId) return;
    thinking = false;
    if (!data || typeof data.move !== 'number') {
      // Extreme fallback: pick a legal cell locally so the game never stalls.
      var free = []; for (var i = 0; i < 9; i++) if (!board[i]) free.push(i);
      if (!free.length) { finish(outcome(board)); return; }
      data = { move: free[0], say: '', result: null };
    }
    board[data.move] = 'O';
    var res = (data.result && data.result.status) ? data.result : outcome(board);
    if (res.status !== 'continue') {
      finish(res);
      if (data.say) statusEl.innerHTML += ' <span class="ttt__say">“' + escapeHtml(data.say) + '”</span>';
      return;
    }
    var line = 'Your move.';
    if (data.say) line += ' <span class="ttt__say">' + escapeHtml(aname) + ': “' + escapeHtml(data.say) + '”</span>';
    setStatus(line, '');
    render();
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  document.getElementById('new').addEventListener('click', reset);
  reset();
})();
