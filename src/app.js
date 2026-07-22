// Joguinho da Mila - PWA client (vanilla JS)
// Polls /api/* every 1s. Renders admin or client UI based on ?mila= or ?nome=.

const API_BASE = './api/api.php';
const POLL_MS = 1000;

let mode = null;
let adminToken = null;
let clientToken = localStorage.getItem('joguinho_client_token') || null;
let nickname = localStorage.getItem('joguinho_nickname') || '';
let roomId = parseInt(localStorage.getItem('joguinho_room_id') || '0', 10) || null;
let lastAdminState = null;
let lastClientState = null;
let selectedRoomId = null;
let savedFormState = null;
let pollTimer = null;

function $(sel) { return document.querySelector(sel); }

function el(tag, attrs, ...children) {
  const e = document.createElement(tag);
  if (attrs) {
    for (const [k, v] of Object.entries(attrs)) {
      if (v === null || v === undefined || v === false) continue;
      if (k === 'class') e.className = v;
      else if (k === 'onclick') e.onclick = v;
      else if (k === 'oninput') e.oninput = v;
      else if (k === 'onkeydown') e.onkeydown = v;
      else if (k === 'style' && typeof v === 'object') Object.assign(e.style, v);
      else e.setAttribute(k, v);
    }
  }
  for (const c of children) {
    if (c == null || c === false) continue;
    if (typeof c === 'string' || typeof c === 'number') e.appendChild(document.createTextNode(String(c)));
    else e.appendChild(c);
  }
  return e;
}

async function api(route, method = 'GET', body = null, token = null) {
  const url = new URL(API_BASE, window.location.href);
  url.searchParams.set('route', route);
  const opts = { method, headers: {} };
  if (method === 'GET' && token) {
    url.searchParams.set('token', token);
  }
  if (body) {
    if (token && method !== 'GET') body.token = token;
    opts.headers['Content-Type'] = 'application/json';
    opts.body = JSON.stringify(body);
  }
  let res;
  try {
    res = await fetch(url, opts);
  } catch (err) {
    throw { status: 0, error: 'network_error', message: err.message };
  }
  let data;
  try { data = await res.json(); } catch { data = {}; }
  if (!res.ok) throw { status: res.status, ...data };
  return data;
}

function startPolling(fn, interval = POLL_MS) {
  if (pollTimer) clearInterval(pollTimer);
  fn();
  pollTimer = setInterval(fn, interval);
}

function stopPolling() {
  if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
}

function renderHeartModal() {
  const heart = el('div', { class: 'heart' });
  heart.innerHTML = `
    <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
      <path d="M16 28.5s-13-8.5-13-19a8.5 8.5 0 0 1 13-7 8.5 8.5 0 0 1 13 7c0 10.5-13 19-13 19z" fill="#ffe4ee"/>
    </svg>
    <div class="ribbon">JOGUINHO DA MILA</div>
  `;
  return heart;
}

function renderError(msg) {
  stopPolling();
  const app = $('#app');
  app.innerHTML = '';
  app.appendChild(el('div', { class: 'modal-overlay' },
    el('div', { class: 'modal-card' },
      renderHeartModal(),
      el('h2', { class: 'modal-title' }, 'Erro'),
      el('p', { style: { marginBottom: '16px' } }, msg),
      el('button', { class: 'btn', onclick: () => location.reload() }, 'Recarregar')
    )
  ));
}

function detectRole() {
  const params = new URLSearchParams(window.location.search);
  const milaParam = params.get('mila');
  const nomeParam = params.get('nome');
  if (milaParam) {
    mode = 'admin-pending';
    adminToken = milaParam;
    localStorage.setItem('joguinho_admin_token', adminToken);
  } else {
    const stored = localStorage.getItem('joguinho_admin_token');
    if (stored) {
      adminToken = stored;
      mode = 'admin-pending';
    } else {
      mode = 'client';
      if (nomeParam) {
        nickname = nomeParam;
        localStorage.setItem('joguinho_nickname', nickname);
      }
    }
  }
}

function renderInitialModal() {
  const app = $('#app');
  app.innerHTML = '';
  const overlay = el('div', { class: 'modal-overlay' });
  const card = el('div', { class: 'modal-card' });
  card.appendChild(renderHeartModal());

  if (mode === 'admin-pending') {
    card.appendChild(el('h2', { class: 'modal-title' }, 'Validando admin...'));
    overlay.appendChild(card);
    app.appendChild(overlay);
    checkAdmin();
    return;
  }

  if (!nickname) {
    const title = el('h2', { class: 'modal-title' }, 'Qual é o seu nome?');
    const input = el('input', { class: 'modal-input', type: 'text', placeholder: 'Seu nome', maxlength: '30', autofocus: true });
    const error = el('div', { class: 'error' });
    const submit = () => {
      const name = input.value.trim();
      if (!name) { error.textContent = 'Digite um nome'; return; }
      nickname = name;
      localStorage.setItem('joguinho_nickname', nickname);
      error.textContent = '';
      tryJoin();
    };
    input.onkeydown = (ev) => { if (ev.key === 'Enter') submit(); };
    const btn = el('button', { class: 'btn', onclick: submit }, 'Entrar');
    card.appendChild(title);
    card.appendChild(input);
    card.appendChild(error);
    card.appendChild(btn);
  } else {
    const title = el('h2', { class: 'modal-title' }, 'Olá, ' + nickname + '!');
    const error = el('div', { class: 'error' });
    const tryJoin = async () => {
      try {
        await joinRoom();
      } catch (e) {
        error.textContent = 'Erro: ' + (e.error || 'desconhecido');
      }
    };
    const btn = el('button', { class: 'btn', onclick: tryJoin }, 'Entrar na sala');
    card.appendChild(title);
    card.appendChild(btn);
    card.appendChild(error);
  }

  overlay.appendChild(card);
  app.appendChild(overlay);
}

async function checkAdmin() {
  try {
    const res = await api('check-admin', 'GET', null, adminToken);
    if (res.valid) {
      mode = 'admin';
      renderAdmin();
    } else {
      renderAdminTokenInput();
    }
  } catch (e) {
    renderError('Erro ao validar token: ' + (e.error || 'desconhecido'));
  }
}

function renderAdminTokenInput() {
  const app = $('#app');
  app.innerHTML = '';
  const overlay = el('div', { class: 'modal-overlay' });
  const card = el('div', { class: 'modal-card' });
  card.appendChild(renderHeartModal());
  card.appendChild(el('h2', { class: 'modal-title' }, 'Token do admin'));
  const error = el('div', { class: 'error' });
  const input = el('input', { class: 'modal-input', type: 'text', placeholder: 'Cole o token aqui', autofocus: true });
  const submit = async () => {
    const t = input.value.trim();
    if (!t) { error.textContent = 'Digite o token'; return; }
    adminToken = t;
    try {
      const res = await api('check-admin', 'GET', null, adminToken);
      if (res.valid) {
        localStorage.setItem('joguinho_admin_token', adminToken);
        mode = 'admin';
        renderAdmin();
      } else {
        error.textContent = 'Token inválido';
      }
    } catch (e) {
      error.textContent = 'Erro: ' + (e.error || 'desconhecido');
    }
  };
  input.onkeydown = (ev) => { if (ev.key === 'Enter') submit(); };
  const btn = el('button', { class: 'btn', onclick: submit }, 'Entrar');
  card.appendChild(input);
  card.appendChild(error);
  card.appendChild(btn);
  overlay.appendChild(card);
  app.appendChild(overlay);
}

async function joinRoom() {
  const res = await api('join', 'POST', { nickname, token: clientToken }, null);
  clientToken = res.token;
  roomId = res.room_id;
  localStorage.setItem('joguinho_client_token', clientToken);
  localStorage.setItem('joguinho_room_id', String(roomId));
  lastClientState = res.state;
  mode = 'client';
  renderClient();
}

// ADMIN UI
function renderAdmin() {
  const app = $('#app');
  app.innerHTML = '';
  app.appendChild(el('div', { id: 'admin-root' }));
  startPolling(pollAdmin);
}

async function pollAdmin() {
  try {
    const res = await api('admin/state', 'GET', null, adminToken);
    lastAdminState = res;
    if (!selectedRoomId && res.rooms.length > 0) {
      selectedRoomId = res.rooms[res.rooms.length - 1].id;
    }
    renderAdminContent();
  } catch (e) {
    if (e.status === 401) {
      stopPolling();
      localStorage.removeItem('joguinho_admin_token');
      adminToken = null;
      renderAdminTokenInput();
    } else {
      const root = $('#admin-root');
      if (root) root.innerHTML = '<div class="main"><div class="card error">Erro: ' + (e.error || 'desconhecido') + '</div></div>';
    }
  }
}

function renderAdminContent() {
  const root = $('#admin-root');
  if (!root) return;

  // Preserva o estado do form de "aguardando" entre re-renders (o poll a 1s
  // destrói e recria os inputs, apagando o que a Mila digita). Limpa ao
  // sair de "aguardando" para não restaurar a palavra após um reset.
  if (selectedRoomId !== null && lastAdminState) {
    const cur = lastAdminState.rooms.find(r => r.id === selectedRoomId);
    const inAguardando = cur && cur.status === 'aguardando';
    if (!inAguardando) {
      savedFormState = null;
    } else {
      const oldWord = root.querySelector(`#word-input-${selectedRoomId}`);
      const oldAttempts = root.querySelector(`#attempts-input-${selectedRoomId}`);
      if (oldWord && oldAttempts) {
        const active = document.activeElement;
        savedFormState = {
          roomId: selectedRoomId,
          word: oldWord.value,
          attempts: oldAttempts.value,
          activeId: active && (active.id === oldWord.id || active.id === oldAttempts.id) ? active.id : null,
          selStart: active && 'selectionStart' in active ? active.selectionStart : null,
          selEnd: active && 'selectionEnd' in active ? active.selectionEnd : null,
        };
      }
    }
  }

  root.innerHTML = '';

  const tabs = el('div', { class: 'tabs' });
  for (const room of lastAdminState.rooms) {
    const tab = el('button', {
      class: 'tab' + (room.id === selectedRoomId ? ' active' : ''),
      onclick: () => { selectedRoomId = room.id; renderAdminContent(); },
    }, 'Sala ' + room.id);
    tabs.appendChild(tab);
  }
  tabs.appendChild(el('button', {
    class: 'tab tab-add',
    onclick: createRoom,
    title: 'Criar nova sala',
  }, '+'));
  root.appendChild(tabs);

  const main = el('div', { class: 'main' });
  const room = lastAdminState.rooms.find(r => r.id === selectedRoomId);
  if (room) {
    main.appendChild(renderAdminRoom(room));
  } else {
    main.appendChild(el('div', { class: 'card' }, 'Nenhuma sala. Clique em + para criar.'));
  }
  root.appendChild(main);

  // Restaura o form se for a mesma sala em "aguardando".
  if (savedFormState && savedFormState.roomId === selectedRoomId) {
    const newWord = root.querySelector(`#word-input-${selectedRoomId}`);
    const newAttempts = root.querySelector(`#attempts-input-${selectedRoomId}`);
    if (newWord && newAttempts) {
      newWord.value = savedFormState.word;
      newAttempts.value = savedFormState.attempts;
      if (savedFormState.activeId) {
        const toFocus = document.getElementById(savedFormState.activeId);
        if (toFocus) {
          toFocus.focus();
          if (savedFormState.selStart !== null) {
            try { toFocus.setSelectionRange(savedFormState.selStart, savedFormState.selEnd); } catch (e) {}
          }
        }
      }
    }
  }
}

function renderAdminRoom(room) {
  const container = el('div');

  const header = el('div', { class: 'card' });
  header.appendChild(el('span', { class: 'room-status ' + room.status }, room.status.replace('_', ' ')));

  if (room.status === 'aguardando') {
    const wordInput = el('input', { id: `word-input-${room.id}`, class: 'modal-input', type: 'text', placeholder: 'Palavra secreta (espaços e hífens ok)', value: room.word || '' });
    const attemptsInput = el('input', { id: `attempts-input-${room.id}`, class: 'modal-input', type: 'number', placeholder: 'Máx. tentativas', value: room.max_attempts || 6, min: '1', max: '26' });
    const error = el('div', { class: 'error' });
    const startBtn = el('button', {
      class: 'btn',
      onclick: async () => {
        const word = wordInput.value.trim();
        const attempts = parseInt(attemptsInput.value, 10);
        if (!word) { error.textContent = 'Digite a palavra secreta'; return; }
        if (!attempts || attempts < 1 || attempts > 26) { error.textContent = 'Tentativas inválidas (1-26)'; return; }
        try {
          await api('admin/action', 'POST', {
            token: adminToken,
            type: 'start_game',
            payload: { room: room.id, word, max_attempts: attempts },
          });
          error.textContent = '';
        } catch (e) {
          error.textContent = 'Erro: ' + (e.error || 'desconhecido');
        }
      },
    }, 'Iniciar jogo');
    header.appendChild(el('h3', { class: 'card-title' }, 'Configurar partida'));
    header.appendChild(wordInput);
    header.appendChild(attemptsInput);
    header.appendChild(error);
    header.appendChild(startBtn);
  } else if (room.status === 'em_jogo') {
    header.appendChild(el('h3', { class: 'card-title' }, 'Palavra: ' + (room.word || '?')));
    header.appendChild(el('div', { class: 'word-display' }, room.word_masked || ''));
    header.appendChild(el('div', { class: 'attempts' }, 'Erros: ' + room.wrong_guesses + ' / ' + room.max_attempts));
    if (room.turn_remaining_seconds !== null) {
      header.appendChild(el('div', { class: 'timer' + (room.turn_remaining_seconds <= 3 ? ' warn' : '') }, room.turn_remaining_seconds + 's'));
    }
    header.appendChild(el('button', {
      class: 'btn btn-danger',
      onclick: async () => {
        if (!confirm('Encerrar a partida?')) return;
        try {
          await api('admin/action', 'POST', { token: adminToken, type: 'end_game', payload: { room: room.id } });
        } catch (e) { alert('Erro: ' + (e.error || 'desconhecido')); }
      },
    }, 'Encerrar partida'));
  } else if (room.status === 'encerrada') {
    if (room.winner_nickname) {
      header.appendChild(el('div', { class: 'winner' }, 'Vencedor: ' + room.winner_nickname));
    } else {
      header.appendChild(el('div', { class: 'winner' }, 'Partida encerrada'));
    }
    if (room.word) header.appendChild(el('div', { class: 'word-display' }, room.word_masked || ''));
    header.appendChild(el('button', {
      class: 'btn',
      onclick: async () => {
        if (!confirm('Resetar a sala? Todos os clientes serão removidos.')) return;
        try {
          await api('admin/action', 'POST', { token: adminToken, type: 'reset_game', payload: { room: room.id } });
        } catch (e) { alert('Erro: ' + (e.error || 'desconhecido')); }
      },
    }, 'Resetar sala'));
  }
  container.appendChild(header);

  const playersCard = el('div', { class: 'card' });
  playersCard.appendChild(el('h3', { class: 'card-title' }, 'Clientes na sala (' + room.players.length + ')'));
  if (room.players.length === 0) {
    playersCard.appendChild(el('p', {}, 'Nenhum cliente aguardando.'));
  } else {
    const isLastRoom = room.id === lastAdminState.last_room_id;
    for (const player of room.players) {
      const row = el('div', { class: 'player-row' });
      const isCurrent = room.status === 'em_jogo' && player.position === room.current_player_idx;
      row.appendChild(el('span', {
        class: 'player-nickname' + (isCurrent ? ' player-current' : ''),
      }, (isCurrent ? '▶ ' : '') + player.nickname));
      const actions = el('div', { class: 'action-buttons' });
      const nextRoom = lastAdminState.rooms.find(r => r.id > room.id && r.status === 'aguardando');
      if (nextRoom) {
        actions.appendChild(el('button', {
          class: 'icon-btn',
          title: 'Mover para Sala ' + nextRoom.id,
          onclick: () => moveClient(player.token, nextRoom.id),
        }, '→'));
      } else {
        actions.appendChild(el('button', {
          class: 'icon-btn',
          disabled: 'true',
          title: 'Última sala (sem próxima)',
        }, '→'));
      }
      actions.appendChild(el('button', {
        class: 'icon-btn danger',
        title: 'Banir',
        onclick: () => banClient(player.token, player.nickname),
      }, '✕'));
      row.appendChild(actions);
      playersCard.appendChild(row);
    }
  }
  container.appendChild(playersCard);
  return container;
}

async function createRoom() {
  try {
    const res = await api('admin/action', 'POST', { token: adminToken, type: 'create_room' });
    selectedRoomId = res.room_id;
  } catch (e) {
    alert('Erro: ' + (e.error || 'desconhecido'));
  }
}

async function moveClient(clientToken, toRoom) {
  try {
    await api('admin/action', 'POST', { token: adminToken, type: 'move_client', payload: { client_token: clientToken, to_room: toRoom } });
  } catch (e) {
    alert('Erro: ' + (e.error || 'desconhecido'));
  }
}

async function banClient(clientToken, nickname) {
  if (!confirm('Banir ' + nickname + '?')) return;
  try {
    await api('admin/action', 'POST', { token: adminToken, type: 'ban', payload: { client_token: clientToken } });
  } catch (e) {
    alert('Erro: ' + (e.error || 'desconhecido'));
  }
}

// CLIENT UI
function renderClient() {
  const app = $('#app');
  app.innerHTML = '';
  app.appendChild(el('div', { id: 'client-root' }));
  startPolling(pollClient);
}

async function pollClient() {
  if (!roomId || !clientToken) return;
  try {
    const res = await api('state', 'GET', null, clientToken);
    lastClientState = res;
    renderClientContent();
  } catch (e) {
    if (e.status === 403 && e.error === 'banned') {
      stopPolling();
      localStorage.removeItem('joguinho_client_token');
      localStorage.removeItem('joguinho_room_id');
      clientToken = null;
      roomId = null;
      renderError('Você foi banida deste jogo.');
    } else if (e.status === 403 && e.error === 'not_in_room') {
      try {
        await joinRoom();
      } catch (e2) {
        renderError('Não foi possível entrar na sala: ' + (e2.error || 'desconhecido'));
      }
    } else {
      const root = $('#client-root');
      if (root) root.innerHTML = '<div class="main"><div class="card error">Erro: ' + (e.error || 'desconhecido') + '</div></div>';
    }
  }
}

function renderClientContent() {
  const root = $('#client-root');
  if (!root) return;
  root.innerHTML = '';
  const main = el('div', { class: 'main' });
  const s = lastClientState;
  if (!s) {
    main.appendChild(el('div', { class: 'card' }, 'Carregando...'));
    root.appendChild(main);
    return;
  }

  if (s.status === 'aguardando') {
    main.appendChild(el('div', { class: 'card' },
      el('h2', { class: 'card-title' }, 'Olá, ' + nickname + '!'),
      el('p', { style: { fontSize: '16px' } }, 'Aguardando Mila iniciar o jogo...')
    ));
  } else if (s.status === 'em_jogo') {
    main.appendChild(el('div', { class: 'card' },
      el('h2', { class: 'card-title' }, 'Sala ' + s.room_id),
      el('div', { class: 'word-display' }, s.word_masked || ''),
      el('div', { class: 'attempts' }, 'Erros: ' + s.wrong_guesses + ' / ' + s.max_attempts),
      s.turn_remaining_seconds !== null ? el('div', { class: 'timer' + (s.turn_remaining_seconds <= 3 ? ' warn' : '') }, s.turn_remaining_seconds + 's') : null
    ));
    if (s.your_turn) {
      const error = el('div', { class: 'error' });
      const input = el('input', { class: 'modal-input', type: 'text', maxlength: '1', placeholder: '?', style: { textAlign: 'center', fontSize: '24px', textTransform: 'uppercase', fontWeight: 'bold', marginBottom: '8px' } });
      const submit = async () => {
        const letter = input.value.trim().toLowerCase();
        if (!letter || letter.length !== 1) { error.textContent = 'Digite uma letra'; return; }
        try {
          await api('action', 'POST', { room: s.room_id, type: 'guess_letter', payload: { letter } }, clientToken);
          input.value = '';
          error.textContent = '';
        } catch (e) {
          if (e.error === 'not_your_turn') error.textContent = 'Não é mais sua vez';
          else if (e.error === 'already_guessed') error.textContent = 'Letra já tentada';
          else error.textContent = 'Erro: ' + (e.error || 'desconhecido');
        }
      };
      input.onkeydown = (ev) => { if (ev.key === 'Enter') submit(); };
      const card = el('div', { class: 'card' });
      card.appendChild(el('h3', { class: 'card-title' }, 'Sua vez! Digite uma letra:'));
      card.appendChild(input);
      card.appendChild(el('button', { class: 'btn', onclick: submit }, 'Enviar'));
      card.appendChild(error);
      main.appendChild(card);
    }
    if (s.guessed_letters && s.guessed_letters.length > 0) {
      main.appendChild(el('div', { class: 'card' },
        el('h3', { class: 'card-title' }, 'Letras tentadas'),
        el('div', { class: 'guessed-list' }, s.guessed_letters.join(' ').toUpperCase())
      ));
    }
  } else if (s.status === 'encerrada') {
    main.appendChild(el('div', { class: 'card' },
      s.winner_nickname ? el('div', { class: 'winner' }, 'Vencedor: ' + s.winner_nickname) : el('div', { class: 'winner' }, 'Partida encerrada'),
      el('div', { class: 'word-display' }, s.word_masked || '')
    ));
  }

  const playersCard = el('div', { class: 'card' });
  playersCard.appendChild(el('h3', { class: 'card-title' }, 'Jogadores'));
  for (const p of s.players) {
    const row = el('div', { class: 'player-row' });
    const name = el('span', {
      class: 'player-nickname' + (p.is_current ? ' player-current' : '') + (p.is_self ? ' player-self' : ''),
    }, (p.is_current ? '▶ ' : '') + p.nickname + (p.is_self ? ' (você)' : ''));
    row.appendChild(name);
    playersCard.appendChild(row);
  }
  main.appendChild(playersCard);

  root.appendChild(main);
}

// Service worker registration
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('./sw.js').catch(() => {});
  });
}

// Bootstrap
detectRole();
renderInitialModal();
