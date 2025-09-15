<div class="admin-panel">
    <div class="panel-header">
        <h2>Category Management</h2>
        <div class="panel-actions">
            <button class="btn btn-primary" onclick="togglePanel('add-category-panel')">
                <i class="btn-icon add-icon"></i>
                Add New Category
            </button>
        </div>
    </div>
    
    <!-- Add Category Panel -->
    <div id="add-category-panel" class="form-panel" style="display: none;">
        <div class="panel-header">
            <h3>Add New Category</h3>
            <button class="btn btn-icon btn-close" onclick="togglePanel('add-category-panel')">&times;</button>
        </div>
        
        <form method="post" action="" class="admin-form" id="add-category-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="language_id">Language <span class="required">*</span></label>
                    <select id="language_id" name="language_id" required>
                        <option value="">Select a language...</option>
                        <?php foreach ($languages as $language): ?>
                            <option value="<?php echo $language['id']; ?>">
                                <?php echo htmlspecialchars($language['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="name">Category Name <span class="required">*</span></label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           required 
                           maxlength="100" 
                           placeholder="e.g., Greetings, Food, Travel">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="slug">URL Slug <span class="required">*</span></label>
                    <input type="text" 
                           id="slug" 
                           name="slug" 
                           required 
                           maxlength="100" 
                           pattern="[a-z0-9-]+" 
                           placeholder="e.g., greetings, food, travel"
                           aria-describedby="slug-help">
                    <div id="slug-help" class="field-help">Lowercase letters, numbers, and hyphens only</div>
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
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" 
                          name="description" 
                          rows="3" 
                          maxlength="500" 
                          placeholder="Brief description of what this category covers..."></textarea>
                <div class="field-help">Optional description for learners</div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="add_category" class="btn btn-primary">
                    <i class="btn-icon save-icon"></i>
                    Add Category
                </button>
                <button type="button" class="btn btn-secondary" onclick="togglePanel('add-category-panel')">Cancel</button>
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
            <input type="text" id="category-search" placeholder="Search categories..." onkeyup="filterTable('categories-table', this.value)">
            <i class="search-icon"></i>
        </div>
    </div>
    
    <!-- Categories Table -->
    <div class="table-container">
        <table class="admin-table" id="categories-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Language</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Order</th>
                    <th>Flashcards</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['categories'])): ?>
                    <?php foreach ($data['categories'] as $category): ?>
                        <tr data-language-id="<?php echo $category['language_id']; ?>">
                            <td>
                                <div class="category-info">
                                    <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                    <span class="category-id">#<?php echo $category['id']; ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="language-tag">
                                    <?php echo htmlspecialchars($category['language_name']); ?>
                                </span>
                            </td>
                            <td>
                                <code class="slug-display"><?php echo htmlspecialchars($category['slug']); ?></code>
                            </td>
                            <td>
                                <div class="description-cell">
                                    <?php if (!empty($category['description'])): ?>
                                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                                    <?php else: ?>
                                        <span class="no-description">No description</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="order-badge"><?php echo $category['display_order']; ?></span>
                            </td>
                            <td>
                                <div class="flashcard-count">
                                    <?php
                                    // Count flashcards for this category
                                    $flashcard_count = 0;
                                    foreach ($flashcards as $flashcard) {
                                        if ($flashcard['category_id'] == $category['id']) {
                                            $flashcard_count++;
                                        }
                                    }
                                    ?>
                                    <span class="count-number"><?php echo $flashcard_count; ?></span>
                                    <span class="count-label">flashcards</span>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-secondary" onclick="editCategory(<?php echo $category['id']; ?>)" title="Edit Category">
                                        <i class="btn-icon edit-icon"></i>
                                    </button>
                                    <a href="?tab=flashcards&filter=category:<?php echo $category['id']; ?>" 
                                       class="btn btn-sm btn-primary" 
                                       title="View Flashcards">
                                        <i class="btn-icon flashcards-icon"></i>
                                    </a>
                                    <a href="?tab=categories&action=delete&id=<?php echo $category['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this category? This will also delete all its flashcards.')"
                                       title="Delete Category">
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
                                <div class="empty-icon categories-icon"></div>
                                <h3>No Categories Yet</h3>
                                <p>Categories help organize flashcards by topic. Start by adding your first category.</p>
                                <button class="btn btn-primary" onclick="togglePanel('add-category-panel')">
                                    <i class="btn-icon add-icon"></i>
                                    Add First Category
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editCategory(categoryId) {
    // Implement category editing functionality
    alert('Edit category functionality would go here for category ID: ' + categoryId);
}

function filterByLanguage(languageId) {
    const table = document.getElementById('categories-table');
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

function filterTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length - 1; j++) { // Skip actions column
            if (cells[j].textContent.toLowerCase().indexOf(searchTerm.toLowerCase()) > -1) {
                found = true;
                break;
            }
        }
        
        row.style.display = found ? '' : 'none';
    }
}

// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const slugField = document.getElementById('slug');
    
    if (!slugField.value || slugField.dataset.autoGenerated === 'true') {
        const slug = name
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
            .replace(/\s+/g, '-') // Replace spaces with hyphens
            .replace(/-+/g, '-') // Replace multiple hyphens with single
            .replace(/^-|-$/g, ''); // Remove leading/trailing hyphens
        
        if (slug) {
            slugField.value = slug;
            slugField.dataset.autoGenerated = 'true';
        }
    }
});

// Clear auto-generated flag when user manually edits slug
document.getElementById('slug').addEventListener('input', function() {
    this.dataset.autoGenerated = 'false';
    // Ensure slug follows the pattern
    this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
});

// Form validation
document.getElementById('add-category-form').addEventListener('submit', function(e) {
    const languageId = document.getElementById('language_id').value;
    const name = document.getElementById('name').value.trim();
    const slug = document.getElementById('slug').value.trim();
    
    if (!languageId || !name || !slug) {
        e.preventDefault();
        alert('Please fill in all required fields');
        return false;
    }
    
    if (!/^[a-z0-9-]+$/.test(slug)) {
        e.preventDefault();
        alert('Slug must contain only lowercase letters, numbers, and hyphens');
        return false;
    }
    
    // Update the slug field to ensure it's clean
    document.getElementById('slug').value = slug.toLowerCase();
});

// Character counter for description
const descriptionField = document.getElementById('description');
if (descriptionField) {
    const counter = document.createElement('div');
    counter.className = 'char-counter';
    counter.style.cssText = 'text-align: right; font-size: 0.875rem; color: var(--text-medium); margin-top: 4px;';
    descriptionField.parentNode.appendChild(counter);
    
    function updateCounter() {
        const remaining = 500 - descriptionField.value.length;
        counter.textContent = `${remaining} characters remaining`;
        counter.style.color = remaining < 50 ? 'var(--error-color)' : 'var(--text-medium)';
    }
    
    descriptionField.addEventListener('input', updateCounter);
    updateCounter();
}
</script>