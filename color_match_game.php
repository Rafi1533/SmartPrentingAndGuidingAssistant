<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Color Match Game</title>
    <link href="https://fonts.googleapis.com/css2?family=Bubblegum+Sans&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Bubblegum Sans', sans-serif;
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            color: #333;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://source.unsplash.com/random/1920x1080/?cartoon') no-repeat center center/cover;
            opacity: 0.2;
            z-index: -1;
        }

        .navbar {
            background: linear-gradient(90deg, #ff4081, #3f51b5);
            width: 100%;
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 10;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .navbar ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
        }

        .navbar li {
            margin: 0 20px;
        }

        .navbar a {
            color: #fff;
            text-decoration: none;
            font-size: 20px;
            text-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
            transition: transform 0.3s, text-shadow 0.3s;
        }

        .navbar a:hover {
            transform: scale(1.1);
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
        }

        h1 {
            color: #ff4081;
            margin: 80px 0 20px;
            font-size: 48px;
            text-shadow: 0 0 10px rgba(255, 64, 129, 0.8);
            animation: bounce 1.5s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .game-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .color-option {
            width: 100px;
            height: 100px;
            margin: 15px;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            animation: spin 2s infinite;
        }

        .color-option:hover {
            transform: scale(1.2);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }

        .color-option.disabled {
            pointer-events: none;
            opacity: 0.5;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            25% { transform: rotate(10deg); }
            75% { transform: rotate(-10deg); }
            100% { transform: rotate(0deg); }
        }

        .score-board {
            font-size: 24px;
            margin-top: 20px;
            color: #2196f3;
            text-shadow: 0 0 5px rgba(33, 150, 243, 0.6);
        }

        .timer {
            font-size: 28px;
            margin-top: 10px;
            color: #ff5722;
            text-shadow: 0 0 5px rgba(255, 87, 34, 0.6);
        }

        .message {
            font-size: 24px;
            margin-top: 20px;
            display: none;
            color: #00e676;
            text-shadow: 0 0 10px rgba(0, 230, 118, 0.8);
        }

        .target-color {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-top: 30px;
            margin-bottom: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.4);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .high-score {
            font-size: 24px;
            margin-top: 10px;
            color: #e91e63;
            text-shadow: 0 0 5px rgba(233, 30, 99, 0.6);
        }

        .final-score {
            font-size: 28px;
            margin-top: 30px;
            font-weight: bold;
            color: #00e676;
            text-shadow: 0 0 10px rgba(0, 230, 118, 0.8);
        }

        .comment {
            font-size: 26px;
            color: #ff9800;
            margin-top: 10px;
            text-shadow: 0 0 5px rgba(255, 152, 0, 0.6);
        }

        .start-button, .pause-button, .restart-button, .change-color-button {
            font-size: 20px;
            padding: 12px 24px;
            margin: 10px;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
            font-family: 'Bubblegum Sans', sans-serif;
        }

        .start-button {
            background-color: #4caf50;
            display: inline-block;
        }

        .start-button:hover {
            background-color: #388e3c;
            transform: scale(1.1);
        }

        .pause-button {
            background-color: #ff9800;
            display: none;
        }

        .pause-button:hover {
            background-color: #f57c00;
            transform: scale(1.1);
        }

        .restart-button {
            background-color: #2196f3;
            display: none;
        }

        .restart-button:hover {
            background-color: #1976d2;
            transform: scale(1.1);
        }

        .change-color-button {
            background-color: #ff4081;
            display: none;
        }

        .change-color-button:hover {
            background-color: #d81b60;
            transform: scale(1.1);
        }

        .leaderboard {
            margin-top: 40px;
            margin-bottom: 20px;
            width: 350px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }

        .leaderboard h3 {
            color: #ff4081;
            text-shadow: 0 0 10px rgba(255, 64, 129, 0.8);
            font-size: 28px;
        }

        .leaderboard ul {
            list-style: none;
            padding: 0;
        }

        .leaderboard li {
            font-size: 20px;
            margin: 10px 0;
            color: #333;
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
        }

        .username-form {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.4);
            display: none;
            z-index: 20;
            text-align: center;
        }

        .username-form input {
            padding: 12px;
            margin-bottom: 15px;
            border: none;
            border-radius: 10px;
            width: 200px;
            font-family: 'Bubblegum Sans', sans-serif;
            font-size: 18px;
        }

        .username-form button {
            background-color: #2196f3;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            cursor: pointer;
            font-family: 'Bubblegum Sans', sans-serif;
            font-size: 18px;
        }

        .username-form button:hover {
            background-color: #1976d2;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <ul>
            <li><a href="parent_dashboard.php">Home 🏠</a></li>
            <li><a href="specialchild.php">Special Child 🌟</a></li>
            <li><a href="childcare.php">Child Care 👶</a></li>
            <li><a href="parent_logout.php">Logout 🚪</a></li>
        </ul>
    </div>

    <div class="username-form" id="username-form" <?php echo !isset($_SESSION['game_username']) ? '' : 'style="display:none"'; ?>>
        <h3>Pick a Super Cool Name! 😎</h3>
        <input type="text" id="username" placeholder="Your Name" required>
        <button onclick="submitUsername()">Start Playing! 🚀</button>
    </div>

    <h1>Match the Colors, Superstar! 🌟</h1>

    <div class="score-board">
        Score: <span id="score">0</span> 🎮
    </div>

    <div class="timer">
        Time Left: <span id="time">30</span>s ⏰
    </div>

    <div class="target-color" id="target-color"></div>

    <div class="game-container" id="game-container">
        <!-- Color options will be dynamically inserted here -->
    </div>

    <div class="high-score" id="high-score">
        Your Best Score: <span id="high-score-value">0</span> 🏆
    </div>

    <div class="final-score" id="final-score">
        <!-- Final score will be shown here -->
    </div>

    <div class="comment" id="comment">
        <!-- Comment based on the score will be shown here -->
    </div>

    <div class="message" id="message">
        <span>Awesome Job! You Matched the Colors! 🎉</span>
    </div>

    <div>
        <button class="start-button" id="start-button">Start Game! 🚀</button>
        <button class="pause-button" id="pause-button">Pause Game ⏸</button>
        <button class="restart-button" id="restart-button">Restart Game 🔄</button>
        <button class="change-color-button" id="change-color-button">New Color! 🌈</button>
    </div>

    <div class="leaderboard">
        <h3>Top Superstars! 🌟</h3>
        <ul id="leaderboard-list"></ul>
    </div>

    <script>
        const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff', '#ff5722', '#e91e63'];
        const gameContainer = document.getElementById('game-container');
        const scoreElement = document.getElementById('score');
        const timeElement = document.getElementById('time');
        const targetColorElement = document.getElementById('target-color');
        const messageElement = document.getElementById('message');
        const highScoreElement = document.getElementById('high-score-value');
        const finalScoreElement = document.getElementById('final-score');
        const commentElement = document.getElementById('comment');
        const startButton = document.getElementById('start-button');
        const pauseButton = document.getElementById('pause-button');
        const restartButton = document.getElementById('restart-button');
        const changeColorButton = document.getElementById('change-color-button');
        const leaderboardList = document.getElementById('leaderboard-list');
        const usernameForm = document.getElementById('username-form');
        let score = 0;
        let highScore = 0;
        let timeLeft = 30;
        let currentTargetColor;
        let timerInterval;
        let isPaused = false;
        let isGameStarted = false;

        // Debug: Log colors array
        console.log('Colors array:', colors);

        // Check if username is set
        <?php if (isset($_SESSION['game_username'])): ?>
            console.log('Username found in session, showing start button');
            fetchData();
            startButton.style.display = 'inline-block';
            pauseButton.style.display = 'none';
            restartButton.style.display = 'none';
            changeColorButton.style.display = 'none';
        <?php else: ?>
            console.log('No username, showing form');
            usernameForm.style.display = 'block';
            startButton.style.display = 'none';
        <?php endif; ?>

        // Fetch high score and leaderboard
        function fetchData() {
            console.log('Fetching game data...');
            fetch('fetch_game_data.php')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error('Fetch error:', data.error);
                        alert('Error loading game data: ' + data.error);
                        return;
                    }
                    highScore = data.personal_high_score;
                    highScoreElement.textContent = highScore;
                    leaderboardList.innerHTML = '';
                    data.leaderboard.forEach(entry => {
                        const li = document.createElement('li');
                        li.textContent = `${entry.username}: ${entry.high_score} 🌟`;
                        leaderboardList.appendChild(li);
                    });
                    console.log('Leaderboard updated:', data.leaderboard);
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Failed to connect to server. Please check your connection.');
                });
        }

        // Submit username
        function submitUsername() {
            const username = document.getElementById('username').value.trim();
            if (username) {
                console.log('Submitting username:', username);
                fetch('submit_username.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `username=${encodeURIComponent(username)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Username submit error:', data.error);
                        alert(data.error);
                    } else {
                        usernameForm.style.display = 'none';
                        startButton.style.display = 'inline-block';
                        fetchData();
                    }
                })
                .catch(error => {
                    console.error('Username submit error:', error);
                    alert('Failed to save username. Try again!');
                });
            } else {
                alert('Please enter a super cool name! 😎');
            }
        }

        // Create color options
        function createColorOptions() {
            console.log('Creating color options...');
            gameContainer.innerHTML = ''; // Clear previous options
            const shuffledColors = shuffle([...colors]);
            shuffledColors.slice(0, 6).forEach(color => {
                const colorOption = document.createElement('div');
                colorOption.classList.add('color-option');
                colorOption.style.backgroundColor = color;
                colorOption.addEventListener('click', () => {
                    console.log('Color clicked:', color);
                    handleColorClick(color);
                });
                gameContainer.appendChild(colorOption);
            });
            console.log('Color options created:', shuffledColors.slice(0, 6));
        }

        // Shuffle colors
        function shuffle(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        // Handle color click
        function handleColorClick(selectedColor) {
            if (!isGameStarted || isPaused) return;
            if (selectedColor === currentTargetColor) {
                score += 10;
            } else {
                score = Math.max(0, score - 5);
            }

            if (score > highScore) {
                highScore = score;
                highScoreElement.textContent = highScore;
                updateHighScore();
            }

            scoreElement.textContent = score;
            createColorOptions();
            updateTargetColor();

            if (score >= 100) {
                clearInterval(timerInterval);
                showEndGame();
            }
        }

        // Update high score
        function updateHighScore() {
            console.log('Updating high score:', highScore);
            fetch('update_high_score.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `high_score=${highScore}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Update high score error:', data.error);
                    }
                })
                .catch(error => console.error('Update high score error:', error));
        }

        // Update target color
        function updateTargetColor() {
            currentTargetColor = colors[Math.floor(Math.random() * colors.length)];
            targetColorElement.style.backgroundColor = currentTargetColor;
            console.log('Target color updated:', currentTargetColor);
        }

        // Start timer
        function startTimer() {
            console.log('Starting timer...');
            if (timerInterval) clearInterval(timerInterval);
            timeLeft = 30;
            timeElement.textContent = timeLeft;
            timerInterval = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    showEndGame();
                } else {
                    timeLeft--;
                    timeElement.textContent = timeLeft;
                }
            }, 1000);
        }

        // Pause or resume game
        function togglePause() {
            if (!isGameStarted) return;
            isPaused = !isPaused;
            console.log('Game paused:', isPaused);
            pauseButton.textContent = isPaused ? 'Resume Game ▶' : 'Pause Game ⏸';
            if (isPaused) {
                clearInterval(timerInterval);
                document.querySelectorAll('.color-option').forEach(option => {
                    option.classList.add('disabled');
                });
            } else {
                startTimer();
                document.querySelectorAll('.color-option').forEach(option => {
                    option.classList.remove('disabled');
                });
            }
        }

        // Show end game
        function showEndGame() {
            isGameStarted = false;
            finalScoreElement.textContent = `Your Final Score: ${score} 🎉`;
            messageElement.style.display = 'block';
            startButton.style.display = 'inline-block';
            pauseButton.style.display = 'none';
            restartButton.style.display = 'none';
            changeColorButton.style.display = 'none';
            document.querySelectorAll('.color-option').forEach(option => {
                option.classList.add('disabled');
            });

            let comment;
            if (score >= 80) {
                comment = "Super Star! 🌟";
            } else if (score >= 50) {
                comment = "Great Job! 😊";
            } else if (score >= 20) {
                comment = "Keep Trying! 💪";
            } else {
                comment = "You Can Do It! 🚀";
            }
            commentElement.textContent = comment;
        }

        // Start game
        function startGame() {
            console.log('Starting game...');
            isGameStarted = true;
            isPaused = false;
            score = 0;
            timeLeft = 30;
            scoreElement.textContent = score;
            timeElement.textContent = timeLeft;
            messageElement.style.display = 'none';
            finalScoreElement.textContent = '';
            commentElement.textContent = '';
            startButton.style.display = 'none';
            pauseButton.style.display = 'inline-block';
            pauseButton.textContent = 'Pause Game ⏸';
            restartButton.style.display = 'inline-block';
            changeColorButton.style.display = 'inline-block';
            createColorOptions();
            updateTargetColor();
            startTimer();
        }

        // Restart game
        function restartGame() {
            console.log('Restarting game...');
            startGame();
        }

        // Change color
        function changeColor() {
            if (!isGameStarted || isPaused) return;
            score = Math.max(0, score - 5);
            scoreElement.textContent = score;
            createColorOptions();
            updateTargetColor();
        }

        // Event listeners
        startButton.addEventListener('click', startGame);
        pauseButton.addEventListener('click', togglePause);
        restartButton.addEventListener('click', restartGame);
        changeColorButton.addEventListener('click', changeColor);
    </script>
</body>
</html>