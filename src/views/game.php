<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game - <?php echo $config['app_name']; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Game Board -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="h2 mb-0">Game Board</h1>
                            <div class="d-flex gap-2">
                                <button id="newRoundBtn" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-repeat me-2"></i>New Round
                                </button>
                                <a href="/" class="btn btn-outline-secondary">
                                    <i class="bi bi-house me-2"></i>Home
                                </a>
                            </div>
                        </div>
                        <div id="gameBoard" class="game-board"></div>
                    </div>
                </div>
            </div>

            <!-- Player List -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Players</h2>
                    </div>
                    <div class="card-body">
                        <div id="playerList" class="list-group list-group-flush"></div>
                    </div>
                </div>

                <!-- Chat -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Chat</h2>
                    </div>
                    <div class="card-body">
                        <div id="chatMessages" class="chat-messages mb-3"></div>
                        <form id="chatForm" class="d-flex gap-2">
                            <input type="text" id="messageInput" class="form-control" placeholder="Type a message...">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Winner Modal -->
    <div class="modal fade" id="winnerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h3 class="modal-title">We Have a Winner!</h3>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-trophy text-warning display-1 mb-3"></i>
                    <h4 id="winnerName" class="mb-3"></h4>
                    <p class="text-muted">Congratulations on winning this round!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Continue Playing</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const gameBoard = document.getElementById('gameBoard');
            const playerList = document.getElementById('playerList');
            const chatMessages = document.getElementById('chatMessages');
            const chatForm = document.getElementById('chatForm');
            const messageInput = document.getElementById('messageInput');
            const newRoundBtn = document.getElementById('newRoundBtn');
            const winnerModal = new bootstrap.Modal(document.getElementById('winnerModal'));

            let ws;
            let gameState = null;
            let playerId = null;

            // Get player ID from URL
            const urlParams = new URLSearchParams(window.location.search);
            playerId = urlParams.get('id');

            if (!playerId) {
                window.location.href = '/';
                return;
            }

            // Initialize WebSocket connection
            function initWebSocket() {
                ws = new WebSocket('<?php echo $config['websocket_url']; ?>');

                ws.onopen = () => {
                    console.log('WebSocket connected');
                    ws.send(JSON.stringify({
                        type: 'join',
                        playerId: playerId
                    }));
                };

                ws.onmessage = (event) => {
                    const data = JSON.parse(event.data);
                    handleWebSocketMessage(data);
                };

                ws.onclose = () => {
                    console.log('WebSocket disconnected');
                    setTimeout(initWebSocket, 1000);
                };
            }

            // Handle WebSocket messages
            function handleWebSocketMessage(data) {
                switch (data.type) {
                    case 'gameState':
                        updateGameState(data.state);
                        break;
                    case 'chat':
                        addChatMessage(data.message);
                        break;
                    case 'winner':
                        showWinner(data.playerName);
                        break;
                }
            }

            // Update game state
            function updateGameState(state) {
                gameState = state;
                renderGameBoard();
                updatePlayerList();
            }

            // Render game board
            function renderGameBoard() {
                if (!gameState) return;

                const board = gameState.board;
                const markedWords = gameState.markedWords[playerId] || [];

                gameBoard.innerHTML = '';
                gameBoard.style.gridTemplateColumns = `repeat(${board.length}, 1fr)`;

                board.forEach((row, rowIndex) => {
                    row.forEach((word, colIndex) => {
                        const cell = document.createElement('div');
                        cell.className = 'game-cell';
                        if (markedWords.includes(word)) {
                            cell.classList.add('marked');
                        }
                        cell.textContent = word;
                        cell.onclick = () => markWord(word);
                        gameBoard.appendChild(cell);
                    });
                });
            }

            // Update player list
            function updatePlayerList() {
                if (!gameState) return;

                playerList.innerHTML = '';
                gameState.players.forEach(player => {
                    const item = document.createElement('div');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center';
                    item.innerHTML = `
                        <span>${player.name}</span>
                        <span class="badge bg-primary rounded-pill">${gameState.markedWords[player.id]?.length || 0}</span>
                    `;
                    playerList.appendChild(item);
                });
            }

            // Add chat message
            function addChatMessage(message) {
                const messageElement = document.createElement('div');
                messageElement.className = 'chat-message mb-2';
                messageElement.innerHTML = `
                    <strong>${message.playerName}:</strong>
                    <span>${message.text}</span>
                `;
                chatMessages.appendChild(messageElement);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // Show winner
            function showWinner(playerName) {
                document.getElementById('winnerName').textContent = playerName;
                winnerModal.show();
            }

            // Mark word
            function markWord(word) {
                if (!gameState) return;

                ws.send(JSON.stringify({
                    type: 'markWord',
                    playerId: playerId,
                    word: word
                }));
            }

            // Send chat message
            chatForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const message = messageInput.value.trim();
                if (message) {
                    ws.send(JSON.stringify({
                        type: 'chat',
                        playerId: playerId,
                        message: message
                    }));
                    messageInput.value = '';
                }
            });

            // Start new round
            newRoundBtn.addEventListener('click', () => {
                ws.send(JSON.stringify({
                    type: 'newRound',
                    playerId: playerId
                }));
            });

            // Initialize WebSocket
            initWebSocket();
        });
    </script>
</body>
</html> 