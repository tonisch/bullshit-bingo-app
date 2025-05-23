<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bullshit Bingo Game</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <div class="game-header">
            <h1 id="eventName">Loading...</h1>
            <div class="player-info">
                <span id="playerName"></span>
                <button id="leaveGame" class="button secondary">Leave Game</button>
            </div>
        </div>

        <div class="game-container">
            <div class="bingo-board" id="bingoBoard">
                <!-- Board will be generated here -->
            </div>

            <div class="game-sidebar">
                <div class="round-info">
                    <h2>Round <span id="currentRound">1</span></h2>
                    <div class="timer" id="roundTimer">00:00</div>
                </div>

                <div class="winners-list">
                    <h3>Winners</h3>
                    <ul id="winnersList">
                        <!-- Winners will be listed here -->
                    </ul>
                </div>

                <div class="game-controls">
                    <button id="newRound" class="button primary">Start New Round</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let playerId = null;
        let eventId = null;
        let currentRound = 1;
        let boardWords = [];
        let markedWords = new Set();

        // Initialize game
        async function initGame() {
            const pathParts = window.location.pathname.split('/');
            playerId = pathParts[pathParts.length - 1];

            try {
                // Get game data
                const response = await fetch(`/api/game/${playerId}`);
                if (!response.ok) throw new Error('Failed to load game data');
                
                const data = await response.json();
                eventId = data.eventId;
                
                // Update UI
                document.getElementById('eventName').textContent = data.eventName;
                document.getElementById('playerName').textContent = data.playerName;
                
                // Generate board
                boardWords = data.words;
                generateBoard();
                
                // Start WebSocket connection for real-time updates
                initWebSocket();
                
            } catch (error) {
                console.error('Error:', error);
                alert('Error loading game. Please try again.');
            }
        }

        // Generate Bingo board
        function generateBoard() {
            const board = document.getElementById('bingoBoard');
            board.innerHTML = '';
            
            // Create 5x5 grid
            for (let i = 0; i < 5; i++) {
                for (let j = 0; j < 5; j++) {
                    const cell = document.createElement('div');
                    cell.className = 'bingo-cell';
                    if (i === 2 && j === 2) {
                        cell.className += ' free-space';
                        cell.textContent = 'FREE';
                    } else {
                        const wordIndex = i * 5 + j;
                        if (wordIndex < boardWords.length) {
                            cell.textContent = boardWords[wordIndex];
                            cell.dataset.word = boardWords[wordIndex];
                            
                            // Add click handler
                            cell.addEventListener('click', () => toggleWord(cell));
                            
                            // Check if word was previously marked
                            if (markedWords.has(boardWords[wordIndex])) {
                                cell.classList.add('marked');
                            }
                        }
                    }
                    board.appendChild(cell);
                }
            }
        }

        // Toggle word marking
        function toggleWord(cell) {
            const word = cell.dataset.word;
            if (markedWords.has(word)) {
                markedWords.delete(word);
                cell.classList.remove('marked');
            } else {
                markedWords.add(word);
                cell.classList.add('marked');
                checkWin();
            }
            
            // Save state
            saveBoardState();
        }

        // Check for win
        function checkWin() {
            // Check rows
            for (let i = 0; i < 5; i++) {
                if (checkLine(i * 5, 1)) return true;
            }
            
            // Check columns
            for (let i = 0; i < 5; i++) {
                if (checkLine(i, 5)) return true;
            }
            
            // Check diagonals
            if (checkLine(0, 6)) return true;
            if (checkLine(4, 4)) return true;
            
            return false;
        }

        // Check a line (row, column, or diagonal)
        function checkLine(start, step) {
            for (let i = 0; i < 5; i++) {
                const index = start + (i * step);
                const cell = document.querySelector(`.bingo-cell:nth-child(${index + 1})`);
                if (!cell.classList.contains('marked') && !cell.classList.contains('free-space')) {
                    return false;
                }
            }
            return true;
        }

        // Save board state
        async function saveBoardState() {
            try {
                await fetch(`/api/game/${playerId}/board`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        markedWords: Array.from(markedWords)
                    })
                });
            } catch (error) {
                console.error('Error saving board state:', error);
            }
        }

        // Initialize WebSocket connection
        function initWebSocket() {
            const ws = new WebSocket(`ws://${window.location.host}/ws/game/${eventId}`);
            
            ws.onmessage = (event) => {
                const data = JSON.parse(event.data);
                
                switch (data.type) {
                    case 'winner':
                        addWinner(data.playerName);
                        break;
                    case 'new_round':
                        startNewRound(data.roundNumber);
                        break;
                }
            };
            
            ws.onclose = () => {
                console.log('WebSocket connection closed');
                // Try to reconnect after 5 seconds
                setTimeout(initWebSocket, 5000);
            };
        }

        // Add winner to list
        function addWinner(playerName) {
            const list = document.getElementById('winnersList');
            const item = document.createElement('li');
            item.textContent = `${playerName} - Round ${currentRound}`;
            list.appendChild(item);
        }

        // Start new round
        function startNewRound(roundNumber) {
            currentRound = roundNumber;
            document.getElementById('currentRound').textContent = roundNumber;
            markedWords.clear();
            generateBoard();
        }

        // Leave game
        document.getElementById('leaveGame').addEventListener('click', () => {
            if (confirm('Are you sure you want to leave the game?')) {
                window.location.href = '/';
            }
        });

        // Start new round (host only)
        document.getElementById('newRound').addEventListener('click', async () => {
            try {
                const response = await fetch(`/api/game/${eventId}/round`, {
                    method: 'POST'
                });
                
                if (!response.ok) {
                    throw new Error('Failed to start new round');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error starting new round. Please try again.');
            }
        });

        // Initialize game when page loads
        initGame();
    </script>
</body>
</html> 