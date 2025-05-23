<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Event - Bullshit Bingo</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Join Event</h1>
        
        <div class="join-container">
            <div class="qr-section">
                <h2>Scan QR Code</h2>
                <div class="qr-code" id="qrCode">
                    <!-- QR Code will be generated here -->
                </div>
                <p class="qr-instruction">Scan this QR code with your phone to join the game</p>
            </div>

            <div class="join-form">
                <h2>Or Enter Code</h2>
                <form id="joinForm" class="form-container">
                    <div class="form-group">
                        <label for="eventCode">Event Code</label>
                        <input type="text" id="eventCode" name="eventCode" required 
                               placeholder="Enter the event code">
                    </div>
                    <div class="form-group">
                        <label for="playerName">Your Name</label>
                        <input type="text" id="playerName" name="playerName" required 
                               placeholder="Enter your name">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="button primary">Join Game</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Get event ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const eventId = urlParams.get('id');

        if (eventId) {
            // Generate QR code
            const qrCodeDiv = document.getElementById('qrCode');
            const joinUrl = `${window.location.origin}/join-event?id=${eventId}`;
            
            // Create QR code using Google Charts API
            const qrCodeUrl = `https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=${encodeURIComponent(joinUrl)}`;
            qrCodeDiv.innerHTML = `<img src="${qrCodeUrl}" alt="QR Code">`;
        }

        // Handle form submission
        document.getElementById('joinForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const eventCode = formData.get('eventCode');
            const playerName = formData.get('playerName');

            try {
                const response = await fetch('/api/players', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        eventId: eventCode,
                        name: playerName
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    window.location.href = `/game/${data.playerId}`;
                } else {
                    const error = await response.json();
                    alert(error.message || 'Error joining event. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error joining event. Please try again.');
            }
        });
    </script>
</body>
</html> 