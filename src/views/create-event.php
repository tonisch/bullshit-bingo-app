<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - <?php echo $config['app_name']; ?></title>
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
            <h1 class="display-4 fw-bold text-primary">Create New Game</h1>
        </header>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="createEventForm" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="eventName" class="form-label">Event Name</label>
                                <input type="text" class="form-control form-control-lg" id="eventName" required>
                                <div class="invalid-feedback">
                                    Please enter an event name.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Default Words</label>
                                <div class="d-flex justify-content-end mb-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllDefault">
                                        <i class="bi bi-check-all me-2"></i>Select All
                                    </button>
                                </div>
                                <div class="row g-3" id="defaultWords">
                                    <!-- Default words will be loaded here -->
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Custom Words</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="customWord" placeholder="Enter a custom word">
                                    <button class="btn btn-outline-primary" type="button" id="addCustomWord">
                                        <i class="bi bi-plus-lg"></i> Add
                                    </button>
                                </div>
                                <div class="row g-3" id="customWords">
                                    <!-- Custom words will be added here -->
                                </div>
                            </div>

                            <div class="alert alert-warning" id="wordCountWarning" style="display: none;">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                You need to select at least 24 words to create a valid bingo board.
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/" class="btn btn-outline-secondary btn-lg px-4">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg px-4" id="createEventBtn">
                                    <i class="bi bi-plus-circle me-2"></i>Create Event
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const defaultWords = [
                'Synergy', 'Leverage', 'Paradigm', 'Streamline', 'Optimize',
                'Innovation', 'Disrupt', 'Scalable', 'Agile', 'Framework',
                'Best Practice', 'ROI', 'KPI', 'Bandwidth', 'Touch Base',
                'Circle Back', 'Think Outside the Box', 'Low Hanging Fruit',
                'Win-Win', 'Game Changer', 'Core Competency', 'Action Item',
                'Takeaway', 'Deliverable', 'Stakeholder', 'Pain Point',
                'Value Add', 'Deep Dive', 'Moving Forward', 'At the End of the Day'
            ];

            const defaultWordsContainer = document.getElementById('defaultWords');
            const customWordsContainer = document.getElementById('customWords');
            const customWordInput = document.getElementById('customWord');
            const addCustomWordBtn = document.getElementById('addCustomWord');
            const wordCountWarning = document.getElementById('wordCountWarning');
            const createEventBtn = document.getElementById('createEventBtn');
            let selectedWords = new Set();

            // Load default words
            defaultWords.forEach(word => {
                const wordElement = createWordElement(word, 'default');
                defaultWordsContainer.appendChild(wordElement);
            });

            // Select all default words
            document.getElementById('selectAllDefault').addEventListener('click', function() {
                const checkboxes = defaultWordsContainer.querySelectorAll('.word-checkbox');
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                
                checkboxes.forEach(checkbox => {
                    if (allChecked) {
                        checkbox.checked = false;
                        selectedWords.delete(checkbox.value);
                    } else {
                        checkbox.checked = true;
                        selectedWords.add(checkbox.value);
                    }
                });
                
                updateWordCount();
            });

            // Add custom word
            addCustomWordBtn.addEventListener('click', function() {
                const word = customWordInput.value.trim();
                if (word) {
                    const wordElement = createWordElement(word, 'custom');
                    customWordsContainer.appendChild(wordElement);
                    customWordInput.value = '';
                    updateWordCount();
                }
            });

            // Handle form submission
            document.getElementById('createEventForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const eventName = document.getElementById('eventName').value.trim();
                const eventNameInput = document.getElementById('eventName');
                
                // Reset previous validation states
                eventNameInput.classList.remove('is-invalid');
                wordCountWarning.style.display = 'none';
                
                // Validate event name
                if (!eventName) {
                    eventNameInput.classList.add('is-invalid');
                    eventNameInput.focus();
                    return;
                }
                
                // Validate word count
                if (selectedWords.size < 24) {
                    wordCountWarning.style.display = 'block';
                    return;
                }

                try {
                    createEventBtn.disabled = true;
                    createEventBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating...';
                    
                    const response = await fetch('/api/events', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            name: eventName,
                            words: Array.from(selectedWords)
                        })
                    });

                    const data = await response.json();
                    if (response.ok) {
                        window.location.href = `/join-event?id=${data.eventId}`;
                    } else {
                        alert(data.error || 'Failed to create event');
                        createEventBtn.disabled = false;
                        createEventBtn.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Create Event';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to create event');
                    createEventBtn.disabled = false;
                    createEventBtn.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Create Event';
                }
            });

            function createWordElement(word, type) {
                const col = document.createElement('div');
                col.className = 'col-md-4 col-sm-6';
                
                const wordElement = document.createElement('div');
                wordElement.className = 'form-check';
                
                const input = document.createElement('input');
                input.type = 'checkbox';
                input.className = 'form-check-input word-checkbox';
                input.id = `word-${word}`;
                input.value = word;
                
                const label = document.createElement('label');
                label.className = 'form-check-label';
                label.htmlFor = `word-${word}`;
                label.textContent = word;
                
                if (type === 'custom') {
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-sm btn-outline-danger ms-2';
                    removeBtn.innerHTML = '<i class="bi bi-x"></i>';
                    removeBtn.onclick = function() {
                        selectedWords.delete(word);
                        col.remove();
                        updateWordCount();
                    };
                    label.appendChild(removeBtn);
                }
                
                input.addEventListener('change', function() {
                    if (this.checked) {
                        selectedWords.add(word);
                    } else {
                        selectedWords.delete(word);
                    }
                    updateWordCount();
                });
                
                wordElement.appendChild(input);
                wordElement.appendChild(label);
                col.appendChild(wordElement);
                
                return col;
            }

            function updateWordCount() {
                const count = selectedWords.size;
                wordCountWarning.style.display = count < 24 ? 'block' : 'none';
                createEventBtn.disabled = count < 24;
            }
        });
    </script>
</body>
</html> 