<div class="admin-panel">
    <div class="panel-header">
        <h2>Quiz Question Management</h2>
        <div class="panel-actions">
            <button class="btn btn-primary" onclick="togglePanel('add-quiz-panel')">
                <i class="btn-icon add-icon"></i>
                Add New Quiz Question
            </button>
        </div>
    </div>
    
    <!-- Add Quiz Question Panel -->
    <div id="add-quiz-panel" class="form-panel" style="display: none;">
        <div class="panel-header">
            <h3>Add New Quiz Question</h3>
            <button class="btn btn-icon btn-close" onclick="togglePanel('add-quiz-panel')">&times;</button>
        </div>
        
        <form method="post" action="" class="admin-form" id="add-quiz-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="flashcard_id">Flashcard <span class="required">*</span></label>
                <select id="flashcard_id" name="flashcard_id" required>
                    <option value="">Select a flashcard...</option>
                    <?php foreach ($flashcards as $flashcard): ?>
                        <option value="<?php echo $flashcard['id']; ?>">
                            <?php echo htmlspecialchars($flashcard['language_name']); ?> - 
                            <?php echo htmlspecialchars($flashcard['category_name']); ?> - 
                            <?php echo htmlspecialchars($flashcard['native_text']); ?> / 
                            <?php echo htmlspecialchars($flashcard['foreign_text']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="field-help">Choose the flashcard this quiz question is based on</div>
            </div>
            
            <div class="form-group">
                <label for="question">Question Text <span class="required">*</span></label>
                <input type="text" 
                       id="question" 
                       name="question" 
                       required 
                       maxlength="255" 
                       placeholder="e.g., What does 'Hello' mean in Spanish?">
                <div class="field-help">The question to ask the learner</div>
            </div>
            
            <div class="form-group">
                <label for="correct_answer">Correct Answer <span class="required">*</span></label>
                <input type="text" 
                       id="correct_answer" 
                       name="correct_answer" 
                       required 
                       maxlength="255" 
                       placeholder="e.g., Hola">
                <div class="field-help">The correct answer to the question</div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="wrong_answer1">Wrong Answer 1 <span class="required">*</span></label>
                    <input type="text" 
                           id="wrong_answer1" 
                           name="wrong_answer1" 
                           required 
                           maxlength="255" 
                           placeholder="e.g., Adiós">
                </div>
                
                <div class="form-group">
                    <label for="wrong_answer2">Wrong Answer 2 <span class="required">*</span></label>
                    <input type="text" 
                           id="wrong_answer2" 
                           name="wrong_answer2" 
                           required 
                           maxlength="255" 
                           placeholder="e.g., Gracias">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="wrong_answer3">Wrong Answer 3 <span class="required">*</span></label>
                    <input type="text" 
                           id="wrong_answer3" 
                           name="wrong_answer3" 
                           required 
                           maxlength="255" 
                           placeholder="e.g., Por favor">
                </div>
                
                <div class="form-group">
                    <!-- Placeholder for symmetry -->
                </div>
            </div>
            
            <div class="quiz-preview" id="quiz-preview" style="display: none;">
                <h4>Question Preview:</h4>
                <div class="preview-question">
                    <p id="preview-question-text"></p>
                    <div class="preview-answers">
                        <div class="preview-answer correct" id="preview-correct"></div>
                        <div class="preview-answer wrong" id="preview-wrong1"></div>
                        <div class="preview-answer wrong" id="preview-wrong2"></div>
                        <div class="preview-answer wrong" id="preview-wrong3"></div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="previewQuiz()">Preview Question</button>
                <button type="submit" name="add_quiz" class="btn btn-primary">
                    <i class="btn-icon save-icon"></i>
                    Add Quiz Question
                </button>
                <button type="button" class="btn btn-secondary" onclick="togglePanel('add-quiz-panel')">Cancel</button>
            </div>
        </form>
    </div>
    
    <!-- Filter Controls -->
    <div class="filter-controls">
        <div class="filter-group">
            <label for="language-filter">Filter by Language:</label>
            <select id="language-filter" onchange="filterByLanguage(this.value)">
                <option value="">All Languages</option>
                <?php foreach ($languages as $language): ?>
                    <option value="<?php echo $language['id']; ?>">
                        <?php echo htmlspecialchars($language['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="search-box">
            <input type="text" id="quiz-search" placeholder="Search quiz questions..." onkeyup="filterTable('quiz-table', this.value)">
            <i class="search-icon"></i>
        </div>
    </div>
    
    <!-- Quiz Questions Table -->
    <div class="table-container">
        <table class="admin-table" id="quiz-table">
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Flashcard</th>
                    <th>Category</th>
                    <th>Language</th>
                    <th>Correct Answer</th>
                    <th>Wrong Answers</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['quiz_questions'])): ?>
                    <?php foreach ($data['quiz_questions'] as $quiz): ?>
                        <tr data-language-name="<?php echo strtolower($quiz['language_name']); ?>">
                            <td>
                                <div class="question-text">
                                    <?php echo htmlspecialchars($quiz['question']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="flashcard-info">
                                    <div class="flashcard-texts">
                                        <strong><?php echo htmlspecialchars($quiz['native_text']); ?></strong>
                                        <div class="foreign-text"><?php echo htmlspecialchars($quiz['foreign_text']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="category-tag">
                                    <?php echo htmlspecialchars($quiz['category_name']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="language-tag">
                                    <?php echo htmlspecialchars($quiz['language_name']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="correct-answer">
                                    <span class="answer-badge correct">
                                        <?php echo htmlspecialchars($quiz['correct_answer']); ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="wrong-answers">
                                    <span class="answer-badge wrong"><?php echo htmlspecialchars($quiz['wrong_answer1']); ?></span>
                                    <span class="answer-badge wrong"><?php echo htmlspecialchars($quiz['wrong_answer2']); ?></span>
                                    <span class="answer-badge wrong"><?php echo htmlspecialchars($quiz['wrong_answer3']); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-secondary" onclick="editQuiz(<?php echo $quiz['id']; ?>)" title="Edit Quiz Question">
                                        <i class="btn-icon edit-icon"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="testQuiz(<?php echo $quiz['id']; ?>)" title="Test Question">
                                        <i class="btn-icon test-icon"></i>
                                    </button>
                                    <a href="?tab=quiz&action=delete&id=<?php echo $quiz['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this quiz question?')"
                                       title="Delete Quiz Question">
                                        <i class="btn-icon delete-icon"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="no-data">
                            <div class="empty-state">
                                <div class="empty-icon quiz-icon"></div>
                                <h3>No Quiz Questions Yet</h3>
                                <p>Quiz questions help test learners' knowledge. Create flashcards first, then add quiz questions based on them.</p>
                                <?php if (!empty($flashcards)): ?>
                                    <button class="btn btn-primary" onclick="togglePanel('add-quiz-panel')">
                                        <i class="btn-icon add-icon"></i>
                                        Add First Quiz Question
                                    </button>
                                <?php else: ?>
                                    <a href="?tab=flashcards" class="btn btn-primary">
                                        <i class="btn-icon flashcards-icon"></i>
                                        Create Flashcards First
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quiz Test Modal -->
<div id="quiz-test-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Test Quiz Question</h3>
            <button class="btn btn-icon btn-close" onclick="closeTestModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="quiz-test-content">
                <!-- Quiz test content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function editQuiz(quizId) {
    alert('Edit quiz functionality would go here for quiz ID: ' + quizId);
}

function testQuiz(quizId) {
    // Find the quiz data from the table
    const row = document.querySelector(`tr:has([onclick="testQuiz(${quizId})"])`);
    if (!row) return;
    
    const question = row.querySelector('.question-text').textContent;
    const correctAnswer = row.querySelector('.answer-badge.correct').textContent;
    const wrongAnswers = Array.from(row.querySelectorAll('.answer-badge.wrong')).map(el => el.textContent);
    
    // Create all answer options and shuffle them
    const allAnswers = [correctAnswer, ...wrongAnswers];
    const shuffledAnswers = allAnswers.sort(() => Math.random() - 0.5);
    
    // Create test interface
    const testContent = document.getElementById('quiz-test-content');
    testContent.innerHTML = `
        <div class="quiz-test">
            <h4>${question}</h4>
            <div class="quiz-options">
                ${shuffledAnswers.map((answer, index) => `
                    <button class="quiz-option" onclick="selectAnswer(this, '${answer}', '${correctAnswer}')">
                        ${answer}
                    </button>
                `).join('')}
            </div>
            <div id="quiz-result" style="display: none;"></div>
            <button class="btn btn-secondary" onclick="resetQuizTest()" style="display: none;" id="reset-btn">Try Again</button>
        </div>
    `;
    
    document.getElementById('quiz-test-modal').style.display = 'flex';
}

function selectAnswer(button, selectedAnswer, correctAnswer) {
    const options = document.querySelectorAll('.quiz-option');
    const result = document.getElementById('quiz-result');
    const resetBtn = document.getElementById('reset-btn');
    
    // Disable all options
    options.forEach(opt => opt.disabled = true);
    
    // Show correct and incorrect answers
    options.forEach(opt => {
        if (opt.textContent === correctAnswer) {
            opt.classList.add('correct');
        } else {
            opt.classList.add('wrong');
        }
    });
    
    // Show result
    if (selectedAnswer === correctAnswer) {
        result.innerHTML = '<div class="quiz-correct">✓ Correct!</div>';
        result.className = 'quiz-result correct';
    } else {
        result.innerHTML = '<div class="quiz-incorrect">✗ Incorrect. The correct answer is: ' + correctAnswer + '</div>';
        result.className = 'quiz-result incorrect';
    }
    
    result.style.display = 'block';
    resetBtn.style.display = 'inline-block';
}

function resetQuizTest() {
    const options = document.querySelectorAll('.quiz-option');
    const result = document.getElementById('quiz-result');
    const resetBtn = document.getElementById('reset-btn');
    
    options.forEach(opt => {
        opt.disabled = false;
        opt.classList.remove('correct', 'wrong');
    });
    
    result.style.display = 'none';
    resetBtn.style.display = 'none';
}

function closeTestModal() {
    document.getElementById('quiz-test-modal').style.display = 'none';
}

function previewQuiz() {
    const question = document.getElementById('question').value;
    const correct = document.getElementById('correct_answer').value;
    const wrong1 = document.getElementById('wrong_answer1').value;
    const wrong2 = document.getElementById('wrong_answer2').value;
    const wrong3 = document.getElementById('wrong_answer3').value;
    
    if (!question || !correct || !wrong1 || !wrong2 || !wrong3) {
        alert('Please fill in all fields before previewing');
        return;
    }
    
    const preview = document.getElementById('quiz-preview');
    document.getElementById('preview-question-text').textContent = question;
    document.getElementById('preview-correct').textContent = correct;
    document.getElementById('preview-wrong1').textContent = wrong1;
    document.getElementById('preview-wrong2').textContent = wrong2;
    document.getElementById('preview-wrong3').textContent = wrong3;
    
    preview.style.display = 'block';
}

function filterByLanguage(languageId) {
    // Note: This is a simplified filter - in a real implementation, 
    // you'd need to map language IDs properly
    const searchTerm = languageId ? document.querySelector(`#language-filter option[value="${languageId}"]`).textContent : '';
    filterTable('quiz-table', searchTerm);
}

function filterTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length - 1; j++) {
            if (cells[j].textContent.toLowerCase().indexOf(searchTerm.toLowerCase()) > -1) {
                found = true;
                break;
            }
        }
        
        row.style.display = found ? '' : 'none';
    }
}

// Auto-populate correct answer from flashcard selection
document.getElementById('flashcard_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const text = selectedOption.textContent;
        const parts = text.split(' - ');
        if (parts.length >= 4) {
            const flashcardTexts = parts[3].split(' / ');
            // You could auto-populate the correct answer here if desired
            // document.getElementById('correct_answer').value = flashcardTexts[1];
        }
    }
});

// Form validation
document.getElementById('add-quiz-form').addEventListener('submit', function(e) {
    const flashcardId = document.getElementById('flashcard_id').value;
    const question = document.getElementById('question').value.trim();
    const correct = document.getElementById('correct_answer').value.trim();
    const wrong1 = document.getElementById('wrong_answer1').value.trim();
    const wrong2 = document.getElementById('wrong_answer2').value.trim();
    const wrong3 = document.getElementById('wrong_answer3').value.trim();
    
    if (!flashcardId || !question || !correct || !wrong1 || !wrong2 || !wrong3) {
        e.preventDefault();
        alert('Please fill in all required fields');
        return false;
    }
    
    // Check for duplicate answers
    const answers = [correct, wrong1, wrong2, wrong3];
    const uniqueAnswers = [...new Set(answers)];
    if (uniqueAnswers.length !== answers.length) {
        e.preventDefault();
        alert('All answers must be different');
        return false;
    }
});

// Modal click outside to close
document.getElementById('quiz-test-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTestModal();
    }
});
</script>

<style>
.question-text {
    max-width: 250px;
    font-weight: 500;
}

.flashcard-info .flashcard-texts {
    max-width: 180px;
}

.flashcard-info .foreign-text {
    color: var(--primary-color);
    font-size: 0.875rem;
}

.answer-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    margin: 2px;
}

.answer-badge.correct {
    background-color: #e8f5e8;
    color: var(--success-color);
}

.answer-badge.wrong {
    background-color: #f8d7da;
    color: var(--error-color);
}

.wrong-answers {
    display: flex;
    flex-wrap: wrap;
    max-width: 200px;
}

.quiz-preview {
    background-color: var(--background-light);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
}

.preview-question {
    text-align: center;
}

.preview-answers {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 15px;
}

.preview-answer {
    padding: 10px;
    border-radius: 6px;
    font-weight: 500;
}

.quiz-test h4 {
    text-align: center;
    margin-bottom: 20px;
    color: var(--text-dark);
}

.quiz-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 20px;
}

.quiz-option {
    padding: 15px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    background-color: var(--background-white);
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.quiz-option:hover:not(:disabled) {
    border-color: var(--primary-color);
    background-color: var(--primary-light);
}

.quiz-option:disabled {
    cursor: not-allowed;
}

.quiz-option.correct {
    background-color: #e8f5e8;
    border-color: var(--success-color);
    color: var(--success-color);
}

.quiz-option.wrong {
    background-color: #f8d7da;
    border-color: var(--error-color);
    color: var(--error-color);
}

.quiz-result {
    text-align: center;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-weight: 600;
}

.quiz-result.correct {
    background-color: #e8f5e8;
    color: var(--success-color);
}

.quiz-result.incorrect {
    background-color: #f8d7da;
    color: var(--error-color);
}
</style>