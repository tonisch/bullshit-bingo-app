<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Bullshit Bingo</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Create New Event</h1>
        
        <form id="createEventForm" class="form-container">
            <div class="form-group">
                <label for="eventName">Event Name</label>
                <input type="text" id="eventName" name="eventName" required 
                       placeholder="e.g., Team Meeting Bingo">
            </div>

            <div class="form-group">
                <label>Bingo Words</label>
                <div class="words-container">
                    <div class="default-words">
                        <h3>Default Words</h3>
                        <div class="word-list" id="defaultWords">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="custom-words">
                        <h3>Custom Words</h3>
                        <div class="word-input-group">
                            <input type="text" class="word-input" placeholder="Add custom word">
                            <button type="button" class="add-word-btn">+</button>
                        </div>
                        <div class="word-list" id="customWords"></div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="button primary">Create Event</button>
                <a href="/" class="button secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        // Default Bingo words
        const defaultWords = [
            "Synergy", "Leverage", "Bandwidth", "Circle back", "Touch base",
            "Think outside the box", "Low hanging fruit", "Win-win", "Game changer",
            "Take it offline", "Deep dive", "Moving forward", "Best practice",
            "Streamline", "Optimize", "Scalable", "Innovative", "Disruptive",
            "Agile", "Paradigm shift", "Core competency", "Value proposition",
            "Stakeholder", "Action item", "Deliverable"
        ];

        // Initialize default words
        const defaultWordsContainer = document.getElementById('defaultWords');
        defaultWords.forEach(word => {
            const wordElement = document.createElement('div');
            wordElement.className = 'word-item';
            wordElement.innerHTML = `
                <input type="checkbox" id="word_${word}" name="words[]" value="${word}">
                <label for="word_${word}">${word}</label>
            `;
            defaultWordsContainer.appendChild(wordElement);
        });

        // Handle custom word addition
        const customWordsContainer = document.getElementById('customWords');
        const wordInput = document.querySelector('.word-input');
        const addWordBtn = document.querySelector('.add-word-btn');

        addWordBtn.addEventListener('click', () => {
            const word = wordInput.value.trim();
            if (word) {
                const wordElement = document.createElement('div');
                wordElement.className = 'word-item';
                wordElement.innerHTML = `
                    <input type="checkbox" id="custom_${word}" name="words[]" value="${word}" checked>
                    <label for="custom_${word}">${word}</label>
                    <button type="button" class="remove-word-btn">×</button>
                `;
                customWordsContainer.appendChild(wordElement);
                wordInput.value = '';
            }
        });

        // Handle word removal
        customWordsContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-word-btn')) {
                e.target.parentElement.remove();
            }
        });

        // Form submission
        document.getElementById('createEventForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const selectedWords = Array.from(formData.getAll('words[]'));
            
            if (selectedWords.length < 25) {
                alert('Please select at least 25 words for the Bingo board.');
                return;
            }

            try {
                const response = await fetch('/api/events', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: formData.get('eventName'),
                        words: selectedWords
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    window.location.href = `/event/${data.eventId}`;
                } else {
                    alert('Error creating event. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error creating event. Please try again.');
            }
        });
    </script>
</body>
</html> 