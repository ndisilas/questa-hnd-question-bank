<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'News & Updates';
$showSidebar = true;

$currentUser = getCurrentUser($pdo);

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "SELECT * FROM news WHERE 1=1";
$params = [];

if ($category !== 'all') {
    $query .= " AND category = ?";
    $params[] = $category;
}

if ($search !== '') {
    $query .= " AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY featured DESC, created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$allNews = $stmt->fetchAll();

// Separate featured and regular news
$featuredNews = [];
$regularNews = [];

foreach ($allNews as $news) {
    if ($news['featured'] == 1) {
        $featuredNews[] = $news;
    } else {
        $regularNews[] = $news;
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-newspaper" style="color: #f59e0b;"></i> News & Updates</h1>
            <p>Stay informed with the latest announcements from HIPTEX</p>
        </div>
        <button class="notif-btn" id="enableNewsNotifications">
            <i class="fas fa-bell"></i> Enable Notifications
        </button>
    </div>

    <!-- Category Filters -->
    <div class="category-filters">
        <a href="?category=all" class="filter-btn <?php echo $category == 'all' ? 'active' : ''; ?>">
            <i class="fas fa-globe"></i> All News
        </a>
        <a href="?category=academic" class="filter-btn <?php echo $category == 'academic' ? 'active' : ''; ?>">
            <i class="fas fa-graduation-cap"></i> Academic
        </a>
        <a href="?category=event" class="filter-btn <?php echo $category == 'event' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> Events
        </a>
        <a href="?category=announcement" class="filter-btn <?php echo $category == 'announcement' ? 'active' : ''; ?>">
            <i class="fas fa-bullhorn"></i> Announcements
        </a>
        <a href="?category=deadline" class="filter-btn <?php echo $category == 'deadline' ? 'active' : ''; ?>">
            <i class="fas fa-clock"></i> Deadlines
        </a>
        <a href="?category=achievement" class="filter-btn <?php echo $category == 'achievement' ? 'active' : ''; ?>">
            <i class="fas fa-trophy"></i> Achievements
        </a>
    </div>

    <!-- Search Bar -->
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <form method="GET" action="">
            <input type="hidden" name="category" value="<?php echo $category; ?>">
            <input type="text" name="search" placeholder="Search news, announcements, events..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
        <?php if ($search): ?>
            <a href="?category=<?php echo $category; ?>" class="clear-search"><i class="fas fa-times"></i> Clear</a>
        <?php endif; ?>
    </div>

    <?php if (count($allNews) == 0): ?>
        <div class="empty-state">
            <i class="fas fa-newspaper"></i>
            <h3>No news found</h3>
            <p>Try adjusting your search or filter</p>
        </div>
    <?php else: ?>

    <!-- Featured News Banner -->
    <?php if (!empty($featuredNews) && $category == 'all' && !$search): ?>
        <?php $featured = $featuredNews[0]; ?>
        <div class="featured-news" onclick="openNewsModal(<?php echo $featured['id']; ?>)">
            <div class="featured-badge"><i class="fas fa-star"></i> Featured</div>
            <h2><?php echo htmlspecialchars($featured['title']); ?></h2>
            <p><?php echo htmlspecialchars($featured['excerpt'] ?: substr($featured['content'], 0, 150)); ?>...</p>
            <div class="featured-meta">
                <span><i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($featured['created_at'])); ?></span>
                <span><i class="fas fa-tag"></i> <?php echo ucfirst($featured['category']); ?></span>
                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($featured['author_name']); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- News Grid -->
    <div class="news-grid">
        <?php foreach ($regularNews as $news): ?>
            <div class="news-card" onclick="openNewsModal(<?php echo $news['id']; ?>)">
                <div class="news-category category-<?php echo $news['category']; ?>">
                    <?php
                    $catIcons = [
                        'academic' => '📚',
                        'event' => '🎉',
                        'announcement' => '📢',
                        'deadline' => '⏰',
                        'achievement' => '🏆'
                    ];
                    echo $catIcons[$news['category']] . ' ' . ucfirst($news['category']);
                    ?>
                </div>
                <h3><?php echo htmlspecialchars($news['title']); ?></h3>
                <p class="news-excerpt"><?php echo htmlspecialchars($news['excerpt'] ?: substr($news['content'], 0, 100)); ?>...</p>
                <div class="news-meta">
                    <span class="date"><i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($news['created_at'])); ?></span>
                    <span class="read-time"><i class="fas fa-clock"></i> <?php echo ceil(strlen($news['content']) / 1000); ?> min read</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>

<!-- News Modal -->
<div id="newsModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="modalTitle">News</h3>
            <button class="modal-close" onclick="closeNewsModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalContent">
            <div class="loading">Loading...</div>
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
}

.notif-btn {
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

.notif-btn:hover {
    background: #d97706;
    transform: translateY(-2px);
}

.category-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
}

.filter-btn {
    padding: 8px 20px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 30px;
    text-decoration: none;
    color: var(--text-primary);
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.filter-btn:hover {
    border-color: #f59e0b;
    color: #f59e0b;
}

.filter-btn.active {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
}

.search-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 50px;
    padding: 4px 4px 4px 20px;
    margin-bottom: 32px;
}

.search-bar i {
    color: var(--text-muted);
}

.search-bar form {
    flex: 1;
    display: flex;
}

.search-bar input {
    flex: 1;
    padding: 12px 0;
    border: none;
    background: none;
    font-size: 14px;
    outline: none;
    color: var(--text-primary);
}

.search-bar button {
    background: #f59e0b;
    border: none;
    padding: 8px 24px;
    border-radius: 40px;
    color: white;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}

.search-bar button:hover {
    background: #d97706;
}

.clear-search {
    color: #ef4444;
    text-decoration: none;
    font-size: 13px;
    padding: 0 12px;
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

/* Featured News */
.featured-news {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    border-radius: 20px;
    padding: 32px;
    margin-bottom: 32px;
    cursor: pointer;
    transition: transform 0.2s;
    border: 1px solid #334155;
}

.featured-news:hover {
    transform: translateY(-3px);
}

.featured-badge {
    display: inline-block;
    background: #f59e0b;
    color: #1e293b;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 16px;
}

.featured-news h2 {
    font-size: 24px;
    color: white;
    margin-bottom: 12px;
}

.featured-news p {
    color: #94a3b8;
    margin-bottom: 16px;
    line-height: 1.6;
}

.featured-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    font-size: 13px;
    color: #64748b;
}

.featured-meta i {
    margin-right: 6px;
}

/* News Grid */
.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 24px;
}

.news-card {
    background: var(--bg-secondary);
    border-radius: 16px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid var(--border-color);
}

.news-card:hover {
    transform: translateY(-3px);
    border-color: #f59e0b;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
}

.news-category {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 12px;
}

.category-academic { background: #e0e7ff; color: #4f46e5; }
.category-event { background: #fef3c7; color: #d97706; }
.category-announcement { background: #e0f2fe; color: #0284c7; }
.category-deadline { background: #fee2e2; color: #dc2626; }
.category-achievement { background: #d1fae5; color: #059669; }

.news-card h3 {
    font-size: 17px;
    font-weight: 700;
    margin-bottom: 12px;
    line-height: 1.4;
    color: var(--text-primary);
}

.news-excerpt {
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.5;
    margin-bottom: 16px;
}

.news-meta {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: var(--text-muted);
    border-top: 1px solid var(--border-color);
    padding-top: 12px;
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
    border-radius: 20px;
    width: 90%;
    max-width: 700px;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.modal-header {
    padding: 20px 24px;
    background: #f59e0b;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
}

.modal-header h3 {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
}

.modal-body {
    padding: 24px;
}

.modal-body .news-category {
    margin-bottom: 16px;
}

.modal-body h2 {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 16px;
    color: var(--text-primary);
}

.modal-body .news-date {
    color: var(--text-muted);
    font-size: 13px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
}

.modal-body .news-content {
    line-height: 1.8;
    color: var(--text-primary);
}

.modal-body .news-content p {
    margin-bottom: 16px;
}

/* Responsive */
@media (max-width: 768px) {
    .news-grid {
        grid-template-columns: 1fr;
    }
    .featured-news {
        padding: 20px;
    }
    .featured-news h2 {
        font-size: 18px;
    }
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .category-filters {
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 8px;
    }
    .search-bar form {
        flex: 1;
    }
    .filter-btn {
        font-size: 13px;
        padding: 6px 16px;
    }
}
</style>

<script>
// ========== NEWS DATA ==========
const newsData = <?php
$newsArray = [];
foreach ($allNews as $news) {
    $newsArray[$news['id']] = [
        'title' => $news['title'],
        'content' => $news['content'],
        'category' => $news['category'],
        'author' => $news['author_name'],
        'date' => date('F j, Y', strtotime($news['created_at']))
    ];
}
echo json_encode($newsArray);
?>;

// ========== MODAL FUNCTIONS ==========
function openNewsModal(newsId) {
    const news = newsData[newsId];
    if (!news) return;
    
    const modal = document.getElementById('newsModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalContent');
    
    modalTitle.textContent = news.title;
    
    const catIcons = {
        'academic': '📚',
        'event': '🎉',
        'announcement': '📢',
        'deadline': '⏰',
        'achievement': '🏆'
    };
    
    modalBody.innerHTML = `
        <div class="news-category category-${news.category}">
            ${catIcons[news.category]} ${news.category.charAt(0).toUpperCase() + news.category.slice(1)}
        </div>
        <h2>${escapeHtml(news.title)}</h2>
        <div class="news-date">
            <i class="fas fa-calendar-alt"></i> ${news.date} 
            <span style="margin-left: 16px;"><i class="fas fa-user"></i> ${escapeHtml(news.author || 'Administrator')}</span>
        </div>
        <div class="news-content">
            ${escapeHtml(news.content).replace(/\n/g, '<br>')}
        </div>
    `;
    
    modal.classList.add('active');
}

function closeNewsModal() {
    document.getElementById('newsModal').classList.remove('active');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('newsModal').addEventListener('click', function(e) {
    if (e.target === this) closeNewsModal();
});

// ========== NOTIFICATION SYSTEM ==========
let newsNotificationsEnabled = false;

document.getElementById('enableNewsNotifications').addEventListener('click', function() {
    if ('Notification' in window) {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                newsNotificationsEnabled = true;
                this.innerHTML = '<i class="fas fa-check"></i> Notifications On';
                this.style.background = '#10b981';
                showNewsNotification('🔔 Notifications Enabled', 'You will now receive alerts for new news!');
            } else {
                alert('Please allow notifications to get news alerts.');
            }
        });
    } else {
        alert('Your browser does not support notifications.');
    }
});

function showNewsNotification(title, body) {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, { body: body, icon: 'https://via.placeholder.com/64' });
    }
}

// ========== CHECK FOR NEW NEWS ON PAGE LOAD ==========
document.addEventListener('DOMContentLoaded', function() {
    const lastChecked = localStorage.getItem('lastNewsCheck');
    const today = new Date().toISOString().split('T')[0];
    
    if (lastChecked !== today && Notification.permission === 'granted') {
        // Check if there are any news from today
        const todayNews = <?php
            $today = date('Y-m-d');
            $todayNews = [];
            foreach ($allNews as $news) {
                if (date('Y-m-d', strtotime($news['created_at'])) === $today) {
                    $todayNews[] = $news['title'];
                }
            }
            echo json_encode($todayNews);
        ?>;
        
        if (todayNews.length > 0) {
            showNewsNotification('📰 New News Today!', todayNews.length + ' new article(s) posted today.');
        }
        localStorage.setItem('lastNewsCheck', today);
    }
});
</script>

<?php include 'includes/footer.php'; ?>