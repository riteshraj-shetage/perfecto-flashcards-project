<div class="admin-panel">
    <div class="panel-header">
        <h2>Language Management</h2>
        <div class="panel-actions">
            <button class="btn btn-primary" onclick="togglePanel('add-language-panel')">
                <i class="btn-icon add-icon"></i>
                Add New Language
            </button>
        </div>
    </div>
    
    <!-- Add Language Panel -->
    <div id="add-language-panel" class="form-panel" style="display: none;">
        <div class="panel-header">
            <h3>Add New Language</h3>
            <button class="btn btn-icon btn-close" onclick="togglePanel('add-language-panel')">&times;</button>
        </div>
        
        <form method="post" action="" class="admin-form" id="add-language-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Language Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required maxlength="100" placeholder="e.g., Spanish">
                </div>
                
                <div class="form-group">
                    <label for="code">Language Code <span class="required">*</span></label>
                    <input type="text" 
                           id="code" 
                           name="code" 
                           required 
                           maxlength="10" 
                           pattern="[a-z]{2,10}" 
                           placeholder="e.g., es, fr, de"
                           aria-describedby="code-help">
                    <div id="code-help" class="field-help">2-10 lowercase letters (ISO language code)</div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="flag_url">Flag URL</label>
                    <input type="url" 
                           id="flag_url" 
                           name="flag_url" 
                           placeholder="https://example.com/flag.png"
                           value="https://placehold.co/150x150/58CC02/FFFFFF?text=">
                    <div class="field-help">URL to the language flag image</div>
                </div>
                
                <div class="form-group">
                    <label for="display_order">Display Order</label>
                    <input type="number" 
                           id="display_order" 
                           name="display_order" 
                           min="0" 
                           max="999" 
                           value="0"
                           aria-describedby="order-help">
                    <div id="order-help" class="field-help">Lower numbers appear first</div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="add_language" class="btn btn-primary">
                    <i class="btn-icon save-icon"></i>
                    Add Language
                </button>
                <button type="button" class="btn btn-secondary" onclick="togglePanel('add-language-panel')">Cancel</button>
            </div>
        </form>
    </div>
    
    <!-- Languages Grid -->
    <div class="content-grid">
        <?php if (!empty($data['languages'])): ?>
            <?php foreach ($data['languages'] as $language): ?>
                <div class="content-card language-card">
                    <div class="card-header">
                        <div class="card-flag">
                            <img src="<?php echo htmlspecialchars($language['flag_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($language['name']); ?> flag"
                                 onerror="this.src='https://placehold.co/60x60/58CC02/FFFFFF?text=<?php echo substr($language['code'], 0, 2); ?>'">
                        </div>
                        <div class="card-info">
                            <h3><?php echo htmlspecialchars($language['name']); ?></h3>
                            <p class="language-code"><?php echo htmlspecialchars($language['code']); ?></p>
                        </div>
                        <div class="card-actions">
                            <button class="btn btn-sm btn-secondary" onclick="editLanguage(<?php echo $language['id']; ?>)" title="Edit Language">
                                <i class="btn-icon edit-icon"></i>
                            </button>
                            <a href="?tab=languages&action=delete&id=<?php echo $language['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this language? This will also delete all its categories and flashcards.')"
                               title="Delete Language">
                                <i class="btn-icon delete-icon"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-stats">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo number_format($language['phrase_count']); ?></span>
                            <span class="stat-label">Phrases</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $language['display_order']; ?></span>
                            <span class="stat-label">Order</span>
                        </div>
                    </div>
                    
                    <div class="card-actions-full">
                        <a href="?tab=categories&filter=<?php echo $language['id']; ?>" class="btn btn-sm btn-outline">
                            <i class="btn-icon categories-icon"></i>
                            View Categories
                        </a>
                        <a href="?tab=flashcards&filter=<?php echo $language['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="btn-icon flashcards-icon"></i>
                            View Flashcards
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon languages-icon"></div>
                <h3>No Languages Yet</h3>
                <p>Start by adding your first language to begin creating content.</p>
                <button class="btn btn-primary" onclick="togglePanel('add-language-panel')">
                    <i class="btn-icon add-icon"></i>
                    Add First Language
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function editLanguage(languageId) {
    // Implement language editing functionality
    alert('Edit language functionality would go here for language ID: ' + languageId);
}

// Auto-generate language code from name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value.toLowerCase();
    const codeField = document.getElementById('code');
    
    if (!codeField.value || codeField.dataset.autoGenerated === 'true') {
        let code = '';
        
        // Simple language code generation
        const languageCodes = {
            'spanish': 'es',
            'french': 'fr',
            'german': 'de',
            'italian': 'it',
            'portuguese': 'pt',
            'dutch': 'nl',
            'russian': 'ru',
            'chinese': 'zh',
            'japanese': 'ja',
            'korean': 'ko',
            'arabic': 'ar',
            'hindi': 'hi'
        };
        
        code = languageCodes[name] || name.substring(0, 2);
        
        if (code && code.length >= 2) {
            codeField.value = code;
            codeField.dataset.autoGenerated = 'true';
        }
    }
});

// Clear auto-generated flag when user manually edits code
document.getElementById('code').addEventListener('input', function() {
    this.dataset.autoGenerated = 'false';
});

// Auto-update flag URL placeholder
document.getElementById('code').addEventListener('input', function() {
    const flagField = document.getElementById('flag_url');
    const code = this.value.toUpperCase();
    
    if (flagField.value === flagField.defaultValue || !flagField.value) {
        flagField.value = `https://placehold.co/150x150/58CC02/FFFFFF?text=${code}`;
    }
});

// Form validation
document.getElementById('add-language-form').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const code = document.getElementById('code').value.trim().toLowerCase();
    
    if (!name || !code) {
        e.preventDefault();
        alert('Please fill in all required fields');
        return false;
    }
    
    if (!/^[a-z]{2,10}$/.test(code)) {
        e.preventDefault();
        alert('Language code must be 2-10 lowercase letters');
        return false;
    }
    
    // Update the code field to ensure it's lowercase
    document.getElementById('code').value = code;
});

// Preview flag image
document.getElementById('flag_url').addEventListener('input', function() {
    const url = this.value;
    if (url) {
        // Create a small preview
        let preview = document.getElementById('flag-preview');
        if (!preview) {
            preview = document.createElement('img');
            preview.id = 'flag-preview';
            preview.style.cssText = 'width: 30px; height: 30px; margin-left: 10px; border-radius: 4px; object-fit: cover;';
            this.parentNode.appendChild(preview);
        }
        preview.src = url;
        preview.onerror = function() {
            this.style.display = 'none';
        };
        preview.onload = function() {
            this.style.display = 'inline-block';
        };
    }
});
</script>