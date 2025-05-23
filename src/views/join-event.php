<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Event - <?php echo $config['app_name']; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <header class="text-center mb-5">
            <h1 class="display-4 fw-bold text-primary">Join Game</h1>
        </header>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <h2 class="card-title h3 mb-3">Scan QR Code to Join</h2>
                        <div class="qr-code bg-white p-3 rounded-3 d-inline-block">
                            <div id="qrcode"></div>
                        </div>
                        <p class="text-muted mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Scan this QR code with your phone to join the game
                        </p>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title h4 mb-4">Or Join Manually</h3>
                        <form id="joinForm" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="eventCode" class="form-label">Event Code</label>
                                <input type="text" class="form-control form-control-lg" id="eventCode" required>
                                <div class="invalid-feedback">
                                    Please enter the event code.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="playerName" class="form-label">Your Name</label>
                                <input type="text" class="form-control form-control-lg" id="playerName" required>
                                <div class="invalid-feedback">
                                    Please enter your name.
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/" class="btn btn-outline-secondary btn-lg px-4">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg px-4">
                                    <i class="bi bi-people me-2"></i>Join Game
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- QR Code Generator -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get event ID from URL
            const urlParams = new URLSearchParams(window.location.search);
            const eventId = urlParams.get('id');

            if (eventId) {
                // Generate QR code
                const qrCodeUrl = `${window.location.origin}/join-event?id=${eventId}`;
                QRCode.toCanvas(document.getElementById('qrcode'), qrCodeUrl, {
                    width: 200,
                    margin: 1,
                    color: {
                        dark: '#00eb00',
                        light: '#ffffff'
                    }
                });
            }

            // Handle form submission
            document.getElementById('joinForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const eventCode = document.getElementById('eventCode').value;
                const playerName = document.getElementById('playerName').value;

                if (!eventCode || !playerName) {
                    return;
                }

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

                    const data = await response.json();
                    if (response.ok) {
                        window.location.href = `/game?id=${data.id}`;
                    } else {
                        alert(data.error || 'Failed to join event');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to join event');
                }
            });
        });
    </script>
</body>
</html> 