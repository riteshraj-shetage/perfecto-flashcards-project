<div class="admin-panel">
    <div class="panel-header">
        <h2>Flashcard Management</h2>
        <div class="panel-actions">
            <button class="btn btn-primary" onclick="togglePanel('add-flashcard-panel')">
                <i class="btn-icon add-icon"></i>
                Add New Flashcard
            </button>
        </div>
    </div>
    
    <!-- Add Flashcard Panel -->
    <div id="add-flashcard-panel" class="form-panel" style="display: none;">
        <div class="panel-header">
            <h3>Add New Flashcard</h3>
            <button class="btn btn-icon btn-close" onclick="togglePanel('add-flashcard-panel')">&times;</button>
        </div>
        
        <form method="post" action="" class="admin-form" id="add-flashcard-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Category <span class="required">*</span></label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select a category...</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['language_name']); ?> - <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="difficulty">Difficulty</label>
                    <select id="difficulty" name="difficulty">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="native_text">Native Text <span class="required">*</span></label>
                    <input type="text" 
                           id="native_text" 
                           name="native_text" 
                           required 
                           maxlength="255" 
                           placeholder="e.g., Hello">
                </div>
                
                <div class="form-group">
                    <label for="foreign_text">Foreign Text <span class="required">*</span></label>
                    <input type="text" 
                           id="foreign_text" 
                           name="foreign_text" 
                           required 
                           maxlength="255" 
                           placeholder="e.g., Hola">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="pronunciation">Pronunciation</label>
                    <input type="text" 
                           id="pronunciation" 
                           name="pronunciation" 
                           maxlength="255" 
                           placeholder="e.g., OH-lah">
                    <div class="field-help">Optional phonetic pronunciation guide</div>
                </div>
                
                <div class="form-group">
                    <label for="xp_value">XP Value</label>
                    <input type="number" 
                           id="xp_value" 
                           name="xp_value" 
                           min="1" 
                           max="100" 
                           value="10"
                           aria-describedby="xp-help">
                    <div id="xp-help" class="field-help">Experience points earned when learned (1-100)</div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="image_url">Image URL</label>
                    <input type="url" 
                           id="image_url" 
                           name="image_url" 
                           placeholder="https://example.com/image.jpg"
                           value="https://placehold.co/300x200">
                    <div class="field-help">Visual aid for the flashcard</div>
                </div>
                
                <div class="form-group">
                    <label for="display_order">Display Order</label>
                    <input type="number" 
                           id="display_order" 
                           name="display_order" 
                           min="0" 
                           max="999" 
                           value="0">
                    <div class="field-help">Order within the category (lower numbers first)</div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="add_flashcard" class="btn btn-primary">
                    <i class="btn-icon save-icon"></i>
                    Add Flashcard
                </button>
                <button type="button" class="btn btn-secondary" onclick="togglePanel('add-flashcard-panel')">Cancel</button>
            </div>
        </form>
    </div>
    
    <!-- Filter Controls -->
    <div class="filter-controls">
        <div class="filter-group">
            <label for="language-filter">Language:</label>
            <select id="language-filter" onchange="filterByLanguage(this.value)">
                <option value="">All Languages</option>
                <?php foreach ($languages as $language): ?>
                    <option value="<?php echo $language['id']; ?>">
                        <?php echo htmlspecialchars($language['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="difficulty-filter">Difficulty:</label>
            <select id="difficulty-filter" onchange="filterByDifficulty(this.value)">
                <option value="">All Difficulties</option>
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="advanced">Advanced</option>
            </select>
        </div>
        
        <div class="search-box">
            <input type="text" id="flashcard-search" placeholder="Search flashcards..." onkeyup="filterTable('flashcards-table', this.value)">
            <i class="search-icon"></i>
        </div>
    </div>
    
    <!-- Flashcards Table -->
    <div class="table-container">
        <table class="admin-table" id="flashcards-table">
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Texts</th>
                    <th>Category</th>
                    <th>Language</th>
                    <th>Difficulty</th>
                    <th>XP</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['flashcards'])): ?>
                    <?php foreach ($data['flashcards'] as $flashcard): ?>
                        <tr data-language-id="<?php 
                            // Get language ID from categories
                            $language_id = '';
                            foreach ($categories as $cat) {
                                if ($cat['id'] == $flashcard['category_id']) {
                                    $language_id = $cat['language_id'];
                                    break;
                                }
                            }
                            echo $language_id;
                        ?>" data-difficulty="<?php echo $flashcard['difficulty']; ?>">
                            <td>
                                <div class="flashcard-preview">
                                    <img src="<?php echo htmlspecialchars($flashcard['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($flashcard['native_text']); ?>"
                                         class="preview-image"
                                         onerror="this.src='https://placehold.co/60x40/E5E5E5/999999?text=No+Image'">
                                </div>
                            </td>
                            <td>
                                <div class="flashcard-texts">
                                    <div class="native-text">
                                        <strong><?php echo htmlspecialchars($flashcard['native_text']); ?></strong>
                                    </div>
                                    <div class="foreign-text">
                                        <?php echo htmlspecialchars($flashcard['foreign_text']); ?>
                                    </div>
                                    <?php if (!empty($flashcard['pronunciation'])): ?>
                                        <div class="pronunciation">
                                            <em><?php echo htmlspecialchars($flashcard['pronunciation']); ?></em>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="category-tag">
                                    <?php echo htmlspecialchars($flashcard['category_name']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="language-tag">
                                    <?php echo htmlspecialchars($flashcard['language_name']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="difficulty-badge <?php echo $flashcard['difficulty']; ?>">
                                    <?php echo ucfirst($flashcard['difficulty']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="xp-display">
                                    <span class="xp-amount"><?php echo $flashcard['xp_value']; ?></span>
                                    <span class="xp-label">XP</span>
                                </div>
                            </td>
                            <td>
                                <span class="order-badge"><?php echo $flashcard['display_order']; ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-secondary" onclick="editFlashcard(<?php echo $flashcard['id']; ?>)" title="Edit Flashcard">
                                        <i class="btn-icon edit-icon"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="previewFlashcard(<?php echo $flashcard['id']; ?>)" title="Preview">
                                        <i class="btn-icon preview-icon"></i>
                                    </button>
                                    <a href="?tab=flashcards&action=delete&id=<?php echo $flashcard['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this flashcard?')"
                                       title="Delete Flashcard">
                                        <i class="btn-icon delete-icon"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="no-data">
                            <div class="empty-state">
                                <div class="empty-icon flashcards-icon"></div>
                                <h3>No Flashcards Yet</h3>
                                <p>Flashcards are the core learning content. Create categories first, then add flashcards to them.</p>
                                <?php if (!empty($categories)): ?>
                                    <button class="btn btn-primary" onclick="togglePanel('add-flashcard-panel')">
                                        <i class="btn-icon add-icon"></i>
                                        Add First Flashcard
                                    </button>
                                <?php else: ?>
                                    <a href="?tab=categories" class="btn btn-primary">
                                        <i class="btn-icon categories-icon"></i>
                                        Create Categories First
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

<!-- Flashcard Preview Modal -->
<div id="flashcard-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Flashcard Preview</h3>
            <button class="btn btn-icon btn-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="flashcard-preview-large">
                <div class="flashcard-side front">
                    <img id="preview-image" src="" alt="">
                    <h2 id="preview-native"></h2>
                </div>
                <div class="flashcard-side back" style="display: none;">
                    <h2 id="preview-foreign"></h2>
                    <p id="preview-pronunciation"></p>
                </div>
            </div>
            <button class="btn btn-primary" onclick="flipPreviewCard()">Flip Card</button>
        </div>
    </div>
</div>

<script>
function editFlashcard(flashcardId) {
    alert('Edit flashcard functionality would go here for flashcard ID: ' + flashcardId);
}

function previewFlashcard(flashcardId) {
    // Find the flashcard data
    const row = document.querySelector(`tr:has([onclick="previewFlashcard(${flashcardId})"])`);
    if (!row) return;
    
    const texts = row.querySelector('.flashcard-texts');
    const image = row.querySelector('.preview-image');
    
    const nativeText = texts.querySelector('.native-text strong').textContent;
    const foreignText = texts.querySelector('.foreign-text').textContent;
    const pronunciationEl = texts.querySelector('.pronunciation em');
    const pronunciation = pronunciationEl ? pronunciationEl.textContent : '';
    
    // Update modal content
    document.getElementById('preview-image').src = image.src;
    document.getElementById('preview-native').textContent = nativeText;
    document.getElementById('preview-foreign').textContent = foreignText;
    document.getElementById('preview-pronunciation').textContent = pronunciation;
    document.getElementById('preview-pronunciation').style.display = pronunciation ? 'block' : 'none';
    
    // Show front side
    document.querySelector('.flashcard-side.front').style.display = 'block';
    document.querySelector('.flashcard-side.back').style.display = 'none';
    
    // Show modal
    document.getElementById('flashcard-modal').style.display = 'flex';
}

function flipPreviewCard() {
    const front = document.querySelector('.flashcard-side.front');
    const back = document.querySelector('.flashcard-side.back');
    
    if (front.style.display !== 'none') {
        front.style.display = 'none';
        back.style.display = 'block';
    } else {
        front.style.display = 'block';
        back.style.display = 'none';
    }
}

function closeModal() {
    document.getElementById('flashcard-modal').style.display = 'none';
}

function filterByLanguage(languageId) {
    const table = document.getElementById('flashcards-table');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const rowLanguageId = row.getAttribute('data-language-id');
        
        if (languageId === '' || rowLanguageId === languageId) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

function filterByDifficulty(difficulty) {
    const table = document.getElementById('flashcards-table');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const rowDifficulty = row.getAttribute('data-difficulty');
        
        if (difficulty === '' || rowDifficulty === difficulty) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
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

// Form validation
document.getElementById('add-flashcard-form').addEventListener('submit', function(e) {
    const categoryId = document.getElementById('category_id').value;
    const nativeText = document.getElementById('native_text').value.trim();
    const foreignText = document.getElementById('foreign_text').value.trim();
    const xpValue = parseInt(document.getElementById('xp_value').value);
    
    if (!categoryId || !nativeText || !foreignText) {
        e.preventDefault();
        alert('Please fill in all required fields');
        return false;
    }
    
    if (xpValue < 1 || xpValue > 100) {
        e.preventDefault();
        alert('XP value must be between 1 and 100');
        return false;
    }
});

// Image preview
document.getElementById('image_url').addEventListener('input', function() {
    const url = this.value;
    if (url) {
        let preview = document.getElementById('image-preview');
        if (!preview) {
            preview = document.createElement('img');
            preview.id = 'image-preview';
            preview.style.cssText = 'width: 100px; height: 60px; margin-top: 10px; border-radius: 4px; object-fit: cover; display: block;';
            this.parentNode.appendChild(preview);
        }
        preview.src = url;
        preview.onerror = function() {
            this.style.display = 'none';
        };
        preview.onload = function() {
            this.style.display = 'block';
        };
    }
});

// Modal click outside to close
document.getElementById('flashcard-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<style>
.flashcard-preview {
    width: 60px;
    height: 40px;
    border-radius: 4px;
    overflow: hidden;
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.flashcard-texts {
    max-width: 200px;
}

.native-text {
    margin-bottom: 4px;
}

.foreign-text {
    color: var(--primary-color);
    margin-bottom: 4px;
}

.pronunciation {
    font-size: 0.875rem;
    color: var(--text-medium);
}

.category-tag {
    background-color: var(--secondary-light);
    color: var(--secondary-color);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.difficulty-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.difficulty-badge.beginner {
    background-color: #e8f5e8;
    color: var(--success-color);
}

.difficulty-badge.intermediate {
    background-color: #fff3cd;
    color: var(--warning-color);
}

.difficulty-badge.advanced {
    background-color: #f8d7da;
    color: var(--error-color);
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background-color: var(--background-white);
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 8px 24px var(--shadow-color);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
}

.modal-header h3 {
    margin: 0;
}

.modal-body {
    padding: 30px;
    text-align: center;
}

.flashcard-preview-large {
    background-color: var(--background-light);
    border-radius: 12px;
    padding: 40px;
    margin-bottom: 20px;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.flashcard-side {
    text-align: center;
}

.flashcard-side img {
    max-width: 200px;
    max-height: 120px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.flashcard-side h2 {
    margin: 0 0 10px 0;
    color: var(--text-dark);
}

.flashcard-side p {
    margin: 0;
    color: var(--text-medium);
    font-style: italic;
}
</style>