<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Library & Resources';
$showSidebar = true;

$currentUser = getCurrentUser($pdo);

// Get filter type
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

// Build query
$query = "SELECT * FROM library_resources WHERE 1=1";
$params = [];

if ($type !== 'all') {
    $query .= " AND type = ?";
    $params[] = $type;
}

$query .= " ORDER BY 
    CASE type 
        WHEN 'ebook' THEN 1 
        WHEN 'article' THEN 2 
        WHEN 'video' THEN 3 
        WHEN 'guide' THEN 4 
        WHEN 'database' THEN 5 
    END, 
    title ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$resources = $stmt->fetchAll();

// Count resources by type
$typeCounts = [
    'ebook' => 0,
    'article' => 0,
    'video' => 0,
    'guide' => 0,
    'database' => 0
];

foreach ($resources as $res) {
    if (isset($typeCounts[$res['type']])) {
        $typeCounts[$res['type']]++;
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-book" style="color: #f59e0b;"></i> Library & Resources</h1>
            <p>Access e-books, research papers, study guides, and educational videos</p>
        </div>
        <button class="request-btn-header" id="requestBtn">
            <i class="fas fa-plus-circle"></i> Request Resource
        </button>
    </div>

    <!-- Category Tabs -->
    <div class="category-tabs">
        <a href="?type=all" class="cat-tab <?php echo $type == 'all' ? 'active' : ''; ?>">
            <i class="fas fa-globe"></i> All <span class="count"><?php echo count($resources); ?></span>
        </a>
        <a href="?type=ebook" class="cat-tab <?php echo $type == 'ebook' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> E-Books <span class="count"><?php echo $typeCounts['ebook']; ?></span>
        </a>
        <a href="?type=article" class="cat-tab <?php echo $type == 'article' ? 'active' : ''; ?>">
            <i class="fas fa-file-alt"></i> Articles <span class="count"><?php echo $typeCounts['article']; ?></span>
        </a>
        <a href="?type=video" class="cat-tab <?php echo $type == 'video' ? 'active' : ''; ?>">
            <i class="fas fa-video"></i> Videos <span class="count"><?php echo $typeCounts['video']; ?></span>
        </a>
        <a href="?type=guide" class="cat-tab <?php echo $type == 'guide' ? 'active' : ''; ?>">
            <i class="fas fa-graduation-cap"></i> Guides <span class="count"><?php echo $typeCounts['guide']; ?></span>
        </a>
        <a href="?type=database" class="cat-tab <?php echo $type == 'database' ? 'active' : ''; ?>">
            <i class="fas fa-database"></i> Databases <span class="count"><?php echo $typeCounts['database']; ?></span>
        </a>
    </div>

    <?php if (count($resources) == 0): ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No resources found</h3>
            <p>Try selecting a different category</p>
        </div>
    <?php else: ?>
        <!-- Resources Grid -->
        <div class="resources-grid">
            <?php foreach ($resources as $resource): 
                $typeIcons = [
                    'ebook' => 'fa-book',
                    'article' => 'fa-file-alt',
                    'video' => 'fa-video',
                    'guide' => 'fa-graduation-cap',
                    'database' => 'fa-database'
                ];
                $typeLabels = [
                    'ebook' => 'E-Book',
                    'article' => 'Article',
                    'video' => 'Video',
                    'guide' => 'Study Guide',
                    'database' => 'Database'
                ];
                $badgeColors = [
                    'ebook' => 'badge-ebook',
                    'article' => 'badge-article',
                    'video' => 'badge-video',
                    'guide' => 'badge-guide',
                    'database' => 'badge-database'
                ];
                $icon = $typeIcons[$resource['type']] ?? 'fa-file';
                $badgeClass = $badgeColors[$resource['type']] ?? 'badge-ebook';
                $typeLabel = $typeLabels[$resource['type']] ?? 'Resource';
            ?>
                <div class="resource-card">
                    <div class="resource-card-header">
                        <div class="resource-icon">
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <span class="resource-badge <?php echo $badgeClass; ?>"><?php echo $typeLabel; ?></span>
                    </div>
                    <div class="resource-card-body">
                        <h3><?php echo htmlspecialchars($resource['title']); ?></h3>
                        <p><?php echo htmlspecialchars($resource['description']); ?></p>
                        <?php if ($resource['author']): ?>
                            <div class="resource-author">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($resource['author']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="resource-card-footer">
                        <a href="<?php echo $resource['url']; ?>" class="resource-link" target="_blank">
                            <?php if ($resource['type'] == 'database'): ?>
                                <i class="fas fa-external-link-alt"></i> Access Database
                            <?php elseif ($resource['type'] == 'video'): ?>
                                <i class="fas fa-play"></i> Watch Now
                            <?php else: ?>
                                <i class="fas fa-book-open"></i> Read Now
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Request Resource Section -->
    <div class="request-section">
        <div class="request-text">
            <h3><i class="fas fa-plus-circle"></i> Can't find what you need?</h3>
            <p>Request a specific book, article, or resource and we'll add it to our library.</p>
        </div>
        <button class="request-btn" id="requestBtn2">
            <i class="fas fa-envelope"></i> Request Resource
        </button>
    </div>
</div>

<!-- Request Modal -->
<div id="requestModal" class="modal-overlay">
    <div class="modal-container modal-small">
        <div class="modal-header">
            <h3>Request a Resource</h3>
            <button class="modal-close" onclick="closeRequestModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Tell us what resource you need and we'll try to add it to the library.</p>
            <form id="requestForm" onsubmit="submitRequest(event)">
                <div class="form-group">
                    <label>Resource Title <span style="color: #dc2626;">*</span></label>
                    <input type="text" id="resourceTitle" class="form-input" required placeholder="e.g., Advanced Database Systems">
                </div>
                <div class="form-group">
                    <label>Author / Source (optional)</label>
                    <input type="text" id="resourceAuthor" class="form-input" placeholder="e.g., Elmasri & Navathe">
                </div>
                <div class="form-group">
                    <label>Resource Type</label>
                    <select id="resourceType" class="form-input">
                        <option value="ebook">E-Book</option>
                        <option value="article">Article</option>
                        <option value="video">Video Tutorial</option>
                        <option value="guide">Study Guide</option>
                        <option value="database">Research Database</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Additional Details (optional)</label>
                    <textarea id="resourceDesc" class="form-input" rows="3" placeholder="Any extra information about this resource..."></textarea>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}

.page-header-left h1 {
    font-size: 26px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.page-header-left p {
    color: var(--text-muted);
    font-size: 14px;
    margin: 0;
}

.request-btn-header {
    background: #f59e0b;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.request-btn-header:hover {
    background: #d97706;
    transform: translateY(-2px);
}

/* Category Tabs */
.category-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 24px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 12px;
}

.cat-tab {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 30px;
    text-decoration: none;
    color: var(--text-primary);
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
}

.cat-tab:hover {
    border-color: #f59e0b;
    color: #f59e0b;
}

.cat-tab.active {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
}

.cat-tab .count {
    background: rgba(0,0,0,0.08);
    padding: 1px 8px;
    border-radius: 20px;
    font-size: 11px;
}

.cat-tab.active .count {
    background: rgba(255,255,255,0.2);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--bg-secondary);
    border-radius: 20px;
    border: 1px solid var(--border-color);
}

.empty-state i {
    font-size: 56px;
    color: var(--text-muted);
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.resources-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.resource-card {
    background: var(--bg-secondary);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.2s;
    border: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
}

.resource-card:hover {
    transform: translateY(-3px);
    border-color: #f59e0b;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
}

.resource-card-header {
    padding: 16px 20px 0;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.resource-icon {
    width: 48px;
    height: 48px;
    background: rgba(245,158,11,0.08);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.resource-icon i {
    font-size: 22px;
    color: #f59e0b;
}

.resource-badge {
    padding: 3px 10px;
    border-radius: 30px;
    font-size: 10px;
    font-weight: 600;
}

.badge-ebook { background: #e0e7ff; color: #4f46e5; }
.badge-article { background: #fef3c7; color: #d97706; }
.badge-video { background: #fee2e2; color: #dc2626; }
.badge-guide { background: #d1fae5; color: #059669; }
.badge-database { background: #e0f2fe; color: #0284c7; }

.resource-card-body {
    padding: 14px 20px;
    flex: 1;
}

.resource-card-body h3 {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.resource-card-body p {
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.5;
    margin-bottom: 10px;
}

.resource-author {
    font-size: 12px;
    color: #f59e0b;
}

.resource-author i {
    margin-right: 4px;
}

.resource-card-footer {
    padding: 14px 20px;
    border-top: 1px solid var(--border-color);
}

.resource-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f59e0b;
    color: white;
    padding: 8px 18px;
    border-radius: 30px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    transition: background 0.2s;
}

.resource-link:hover {
    background: #d97706;
}

/* Request Section */
.request-section {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    border-radius: 16px;
    padding: 24px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    border: 1px solid #334155;
}

.request-text h3 {
    font-size: 18px;
    color: white;
    margin-bottom: 4px;
}

.request-text h3 i {
    margin-right: 8px;
    color: #f59e0b;
}

.request-text p {
    color: #94a3b8;
    margin: 0;
}

.request-btn {
    background: #f59e0b;
    border: none;
    padding: 10px 24px;
    border-radius: 30px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.request-btn:hover {
    background: #d97706;
    transform: translateY(-2px);
}

/* Modal */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-overlay.active {
    display: flex;
}

.modal-container {
    background: var(--bg-secondary);
    border-radius: 16px;
    width: 90%;
    max-width: 480px;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.modal-header {
    padding: 16px 20px;
    background: #f59e0b;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
}

.modal-header h3 {
    font-size: 17px;
    font-weight: 600;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 22px;
    cursor: pointer;
}

.modal-body {
    padding: 20px;
}

.modal-body p {
    margin-bottom: 16px;
    color: var(--text-muted);
    font-size: 14px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 4px;
    color: var(--text-primary);
}

.form-input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    background: var(--bg-primary);
    color: var(--text-primary);
    font-size: 14px;
}

.form-input:focus {
    outline: none;
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245,158,11,0.08);
}

.btn-submit {
    width: 100%;
    background: #f59e0b;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-submit:hover {
    background: #d97706;
}

@media (max-width: 768px) {
    .resources-grid {
        grid-template-columns: 1fr;
    }
    .category-tabs {
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 8px;
    }
    .request-section {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .request-btn-header {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
function openRequestModal() {
    document.getElementById('requestModal').classList.add('active');
}

function closeRequestModal() {
    document.getElementById('requestModal').classList.remove('active');
}

document.getElementById('requestBtn').addEventListener('click', openRequestModal);
document.getElementById('requestBtn2').addEventListener('click', openRequestModal);

document.getElementById('requestModal').addEventListener('click', function(e) {
    if (e.target === this) closeRequestModal();
});

function submitRequest(event) {
    event.preventDefault();
    const title = document.getElementById('resourceTitle').value;
    if (!title) {
        alert('Please enter a resource title');
        return;
    }
    alert('Thank you for your request!\n\n"' + title + '" has been submitted for review.\n\nWe will notify you once it\'s added to the library.');
    document.getElementById('requestForm').reset();
    closeRequestModal();
}
</script>

<?php include 'includes/footer.php'; ?>