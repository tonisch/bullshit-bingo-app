<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['app_name']; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <header class="text-center mb-5">
            <h1 class="display-4 fw-bold text-primary"><?php echo $config['app_name']; ?></h1>
        </header>

        <main>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <h2 class="card-title h3 mb-3">Welcome to Bullshit Bingo!</h2>
                            <p class="card-text text-muted">Create a new game or join an existing one to start playing.</p>
                        </div>
                    </div>

                    <div class="d-grid gap-3 d-sm-flex justify-content-sm-center mb-5">
                        <a href="/create-event" class="btn btn-primary btn-lg px-4 gap-3">
                            <i class="bi bi-plus-circle me-2"></i>Create New Game
                        </a>
                        <a href="/join-event" class="btn btn-success btn-lg px-4 gap-3">
                            <i class="bi bi-people me-2"></i>Join Game
                        </a>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title h4 mb-4">How to Play</h3>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-center text-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 mb-3">
                                            <i class="bi bi-1-circle-fill text-primary fs-1"></i>
                                        </div>
                                        <p class="mb-0">Create a new game or join an existing one using the game code</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-center text-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 mb-3">
                                            <i class="bi bi-2-circle-fill text-primary fs-1"></i>
                                        </div>
                                        <p class="mb-0">Mark words on your board as they are mentioned</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-center text-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 mb-3">
                                            <i class="bi bi-3-circle-fill text-primary fs-1"></i>
                                        </div>
                                        <p class="mb-0">Get five in a row to win!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="text-center mt-5">
            <p class="text-muted">&copy; <?php echo date('Y'); ?> <?php echo $config['app_name']; ?></p>
        </footer>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 