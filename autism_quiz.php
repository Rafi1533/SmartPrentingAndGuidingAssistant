<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autism Screening Questionnaire</title>
    <style>
       /* Base Styles */
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
    margin: 0;
    padding: 0;
    text-align: center;
    color: #333;
}

/* Navbar Styles */
.navbar {
    background: linear-gradient(90deg, #ff416c, #ff4b2b, #6a11cb, #2575fc);
    color: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    border-radius: 0 0 20px 20px;
}

.navbar ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
}

.navbar ul li {
    margin: 0 20px;
}

.navbar ul li a {
    color: white;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    padding: 5px 10px;
    border-radius: 5px;
    transition: all 0.3s ease-in-out;
}

.navbar ul li a:hover {
    background: rgba(255,255,255,0.2);
    color: #fff;
    transform: scale(1.1);
}

/* Make container slightly transparent with frosted glass effect */
.container {
    background: rgba(255, 255, 255, 0.14);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 30px;
    max-width: 850px;
    margin: 40px auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    transition: all 0.3s ease-in-out;
}

/* Tabs */
.tabs {
    display: flex;
    justify-content: space-around;
    margin-bottom: 25px;
}

.tab {
    padding: 12px 25px;
    cursor: pointer;
    background: #f0f0f0;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease-in-out;
}

.tab:hover {
    background: #ff6b81;
    color: white;
    transform: scale(1.1) translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.tab.active {
    background: #3498db;
    color: white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Questionnaire */
.questionnaire {
    display: none;
}

.questionnaire.active {
    display: block;
}

/* Question Box */
.question-box {
    background: rgba(255, 255, 255, 0.95);
    padding: 20px;
    border-radius: 12px;
    border-left: 5px solid #3498db;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    text-align: left;
    transition: all 0.3s ease-in-out;
}

.question-box:hover {
    transform: scale(1.02) translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

/* Question */
.question {
    font-size: 17px;
    margin-bottom: 12px;
    font-weight: 600;
    color: #2c3e50;
}

/* Options */
.options {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    align-items: center;
}

.options label {
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 8px;
    transition: all 0.2s ease-in-out;
}

.options input[type="radio"]:checked + label,
.options input[type="checkbox"]:checked + label {
    background: #3498db;
    color: white;
}

/* Button */
button {
    background: linear-gradient(135deg, #ff416c, #ff4b2b);
    color: white;
    padding: 14px;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
    font-size: 16px;
    margin-top: 15px;
    width: 100%;
}

button:hover {
    background: linear-gradient(135deg, #ff6b81, #ff3a3a);
    transform: scale(1.05) translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

/* Result */
.result {
    margin-top: 25px;
    font-size: 19px;
    font-weight: 700;
    color: #ffffffff;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
    background-color: rgba(0, 0, 0, 0.73);
    border-radius: 30px;
}

/* Smooth transitions for the whole container */
.container, .question-box, .tab, button {
    will-change: transform, box-shadow, background;
}
#bg-video {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: -2;
}

/* Dark Overlay for readability */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4); /* adjust darkness */
    z-index: -1;
}

/* Keep content above video */
.navbar, .container {
    position: relative;
    z-index: 1;
}
    </style>
</head>
<body>
    <video autoplay muted loop id="bg-video">
        <source src="parenthome.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <!-- Optional Dark Overlay -->
    <div class="overlay"></div>

    <div class="navbar">
        <span>Autism Screening Questionnaire</span>
        <ul>
            <li><a href="parent_dashboard.php">Home</a></li>
            <li><a href="specialchild.php">Special Child Home</a></li>
            <li><a href="results.php">Your Results</a></li>
            <li><a href="parent_logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <h2>Autism Screening Questionnaire</h2>
        
        <div class="tabs">
            <div class="tab active" data-section="infant">Infants (6–12 Months)</div>
            <div class="tab" data-section="toddler">Toddlers (12–24 Months)</div>
            <div class="tab" data-section="preschool">Preschoolers (2–5 Years)</div>
            <div class="tab" data-section="children">Children (6–12 Years)</div>
        </div>

        <div class="questionnaire active" id="infant">
            <h3>For Infants (6–12 Months)</h3>
            <div class="question-box">
                <p class="question">Does your baby make eye contact with you during feeding or play?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="infant-q1" value="yes"></label>
                    <label>No <input type="radio" name="infant-q1" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your baby smile back when you smile at them?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="infant-q2" value="yes"></label>
                    <label>No <input type="radio" name="infant-q2" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your baby respond to their name when called?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="infant-q3" value="yes"></label>
                    <label>No <input type="radio" name="infant-q3" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your baby follow objects or people with their eyes?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="infant-q4" value="yes"></label>
                    <label>No <input type="radio" name="infant-q4" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your baby show interest in new faces?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="infant-q5" value="yes"></label>
                    <label>No <input type="radio" name="infant-q5" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your baby babble or make cooing sounds?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="infant-q6" value="yes"></label>
                    <label>No <input type="radio" name="infant-q6" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your baby reach for objects or try to grab things?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="infant-q7" value="yes"></label>
                    <label>No <input type="radio" name="infant-q7" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your baby show interest in social games like peek-a-boo?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="infant-q8" value="yes"></label>
                    <label>No <input type="radio" name="infant-q8" value="no"></label>
                </div>
            </div>
            <button onclick="submitQuiz('infant')">Submit</button>
            <div id="result-infant" class="result"></div>
        </div>

        <div class="questionnaire" id="toddler">
            <h3>For Toddlers (12–24 Months)</h3>
            <div class="question-box">
                <p class="question">Does your child point to objects or people to show interest?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="toddler-q1" value="yes"></label>
                    <label>No <input type="radio" name="toddler-q1" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child try to imitate sounds, gestures, or facial expressions?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="toddler-q2" value="yes"></label>
                    <label>No <input type="radio" name="toddler-q2" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child engage in pretend play (e.g., pretending to feed a doll)?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="toddler-q3" value="yes"></label>
                    <label>No <input type="radio" name="toddler-q3" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child bring objects to show you (not just for help)?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="toddler-q4" value="yes"></label>
                    <label>No <input type="radio" name="toddler-q4" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child have at least a few meaningful words?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="toddler-q5" value="yes"></label>
                    <label>No <input type="radio" name="toddler-q5" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child show joint attention (looking where you point)?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="toddler-q6" value="yes"></label>
                    <label>No <input type="radio" name="toddler-q6" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child walk on tiptoes frequently?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="toddler-q7" value="yes"></label>
                    <label>No <input type="radio" name="toddler-q7" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child have repetitive behaviors (e.g., hand flapping, lining up objects)?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="toddler-q8" value="yes"></label>
                    <label>No <input type="radio" name="toddler-q8" value="no"></label>
                </div>
            </div>
            <button onclick="submitQuiz('toddler')">Submit</button>
            <div id="result-toddler" class="result"></div>
        </div>

        <div class="questionnaire" id="preschool">
            <h3>For Preschoolers (2–5 Years)</h3>
            <div class="question-box">
                <p class="question">Does your child respond when you call their name?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="preschool-q1" value="yes"></label>
                    <label>No <input type="radio" name="preschool-q1" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child engage in back-and-forth conversations?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="preschool-q2" value="yes"></label>
                    <label>No <input type="radio" name="preschool-q2" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child prefer playing alone rather than with others?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="preschool-q3" value="yes"></label>
                    <label>No <input type="radio" name="preschool-q3" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child have difficulty understanding or using gestures (e.g., nodding, shaking head)?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="preschool-q4" value="yes"></label>
                    <label>No <input type="radio" name="preschool-q4" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child have trouble making friends or showing interest in peers?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="preschool-q5" value="yes"></label>
                    <label>No <input type="radio" name="preschool-q5" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child get upset over minor changes in routine?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="preschool-q6" value="yes"></label>
                    <label>No <input type="radio" name="preschool-q6" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child repeat words or phrases (echolalia)?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="preschool-q7" value="yes"></label>
                    <label>No <input type="radio" name="preschool-q7" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child show strong reactions to certain sounds, lights, or textures?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="preschool-q8" value="yes"></label>
                    <label>No <input type="radio" name="preschool-q8" value="no"></label>
                </div>
            </div>
            <button onclick="submitQuiz('preschool')">Submit</button>
            <div id="result-preschool" class="result"></div>
        </div>

        <div class="questionnaire" id="children">
            <h3>For Children (6–12 Years)</h3>
            <div class="question-box">
                <p class="question">Does your child struggle with understanding social cues, like facial expressions or tone of voice?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="children-q1" value="yes"></label>
                    <label>No <input type="radio" name="children-q1" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child have difficulty understanding jokes or sarcasm?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="children-q2" value="yes"></label>
                    <label>No <input type="radio" name="children-q2" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child avoid eye contact or seem uninterested in conversations?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="children-q3" value="yes"></label>
                    <label>No <input type="radio" name="children-q3" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child get intensely focused on specific topics or hobbies?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="children-q4" value="yes"></label>
                    <label>No <input type="radio" name="children-q4" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child have trouble adapting to new situations?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="children-q5" value="yes"></label>
                    <label>No <input type="radio" name="children-q5" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child dislike certain textures, sounds, or lights?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="children-q6" value="yes"></label>
                    <label>No <input type="radio" name="children-q6" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child display repetitive movements (rocking, hand flapping, spinning)?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="children-q7" value="yes"></label>
                    <label>No <input type="radio" name="children-q7" value="no"></label>
                </div>
            </div>
            <div class="question-box">
                <p class="question">Does your child struggle with changes in daily routine?</p>
                <div class="options">
                    <label>Yes <input type="radio" name="children-q8" value="yes"></label>
                    <label>No <input type="radio" name="children-q8" value="no"></label>
                </div>
            </div>
            <button onclick="submitQuiz('children')">Submit</button>
            <div id="result-children" class="result"></div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const sectionId = tab.getAttribute('data-section');
                showSection(sectionId);
            });
        });

        function showSection(sectionId) {
            document.querySelectorAll('.questionnaire').forEach(q => {
                q.classList.remove('active');
                q.querySelectorAll('input[type="radio"]').forEach(input => input.checked = false);
                q.querySelector('.result').innerHTML = '';
            });
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));

            document.getElementById(sectionId).classList.add('active');
            document.querySelector(`.tab[data-section="${sectionId}"]`).classList.add('active');
        }

        function submitQuiz(section) {
            let redFlags = 0;
            const questions = document.querySelectorAll(`#${section} .question-box input[type="radio"]:checked`);

            if (questions.length !== 8) {
                alert("Please answer all questions.");
                return;
            }

            questions.forEach((input, index) => {
                const value = input.value;
                if (section === 'infant') {
                    // Scoring: Yes = 1 for Q1, Q2, Q3, Q4, Q6, Q8; No = 0 for Q5, Q7
                    if ([0, 1, 2, 3, 5, 7].includes(index) && value === 'yes') {
                        redFlags++;
                    } else if ([4, 6].includes(index) && value === 'no') {
                        redFlags++;
                    }
                } else if (section === 'toddler') {
                    if (index < 6 && value === 'no') redFlags++;
                    else if (index >= 6 && value === 'yes') redFlags++;
                } else if (section === 'preschool' || section === 'children') {
                    if (value === 'yes') redFlags++;
                }
            });

            let riskLevel;
            if (section === 'infant') {
                if (redFlags >= 6) riskLevel = 'High level of engagement and developmental signs';
                else if (redFlags >= 3) riskLevel = 'Moderate level of engagement, possible developmental monitoring';
                else riskLevel = 'Low engagement, further observation or consultation with a pediatrician might be needed';
            } else if (section === 'toddler') {
                if (redFlags <= 2) riskLevel = 'Low risk';
                else if (redFlags <= 4) riskLevel = 'Moderate risk (monitor + discuss with pediatrician)';
                else riskLevel = 'High risk (refer for autism assessment)';
            } else if (section === 'preschool') {
                if (redFlags <= 3) riskLevel = 'Low risk';
                else if (redFlags <= 5) riskLevel = 'Moderate risk (further screening recommended)';
                else riskLevel = 'High risk (refer for diagnostic evaluation)';
            } else if (section === 'children') {
                if (redFlags <= 3) riskLevel = 'Minimal signs (likely typical development)';
                else if (redFlags <= 5) riskLevel = 'Subthreshold (consider social communication assessment)';
                else riskLevel = 'High likelihood of autism (refer for full evaluation)';
            }

            document.getElementById(`result-${section}`).innerHTML = `Score: ${redFlags}<br>Risk Level: ${riskLevel}`;

            fetch('save_result.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `section=${section}&red_flags=${redFlags}&risk_level=${encodeURIComponent(riskLevel)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Result saved successfully!');
                } else {
                    alert('Error saving result.');
                }
            });
        }
    </script>

</body>
</html>