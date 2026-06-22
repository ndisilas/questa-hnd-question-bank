<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Question Bank';
$showSidebar = true;

$currentUser = getCurrentUser($pdo);
$userDeptId = $currentUser['department_id'] ?? 0;
$userDeptName = '';

if ($userDeptId > 0) {
    $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $deptStmt->execute([$userDeptId]);
    $userDept = $deptStmt->fetch();
    $userDeptName = $userDept ? $userDept['name'] : 'Your Department';
}

// Get question papers for user's department
$papersStmt = $pdo->prepare("
    SELECT qp.*, c.name as course_name, c.code as course_code 
    FROM question_papers qp
    JOIN courses c ON qp.course_id = c.id
    WHERE c.department_id = ?
    ORDER BY qp.year DESC, qp.semester DESC
");
$papersStmt->execute([$userDeptId]);
$papers = $papersStmt->fetchAll();

// Get courses for user's department (for quiz subject selection)
$coursesStmt = $pdo->prepare("SELECT * FROM courses WHERE department_id = ? ORDER BY name");
$coursesStmt->execute([$userDeptId]);
$courses = $coursesStmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <!-- Department Banner -->
    <div class="dept-banner">
        <div class="dept-banner-icon">
            <i class="fas fa-building"></i>
        </div>
        <div class="dept-banner-text">
            <h3><?php echo htmlspecialchars($userDeptName); ?></h3>
            <p>You are viewing question papers and resources for your department only.</p>
        </div>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-database" style="color: #f59e0b;"></i> Question Bank</h1>
        <p>Search, download, and practice with past HND examination papers from your department</p>
    </div>

    <!-- Search and Filter Bar -->
    <div class="search-filter-bar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search by course name, topic, or course code...">
        </div>
        <div class="filter-group">
            <select id="yearFilter">
                <option value="">All Years</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
                <option value="2022">2022</option>
                <option value="2021">2021</option>
            </select>
            <select id="semesterFilter">
                <option value="">All Semesters</option>
                <option value="1">First Semester</option>
                <option value="2">Second Semester</option>
            </select>
            <button id="searchBtn" class="btn-primary"><i class="fas fa-search"></i> Search</button>
        </div>
    </div>

    <!-- Mode Tabs -->
    <div class="mode-tabs">
        <button class="mode-tab active" data-mode="pdf">
            <i class="fas fa-file-pdf"></i> Access Past Papers
        </button>
        <button class="mode-tab" data-mode="quiz">
            <i class="fas fa-puzzle-piece"></i> Practice Quizzes
        </button>
    </div>

    <!-- PDF MODE -->
    <div id="pdfMode" class="mode-content active">
        <?php if (count($papers) > 0): ?>
            <div class="papers-grid">
                <?php foreach ($papers as $paper): ?>
                    <div class="paper-card" data-year="<?php echo $paper['year']; ?>" data-semester="<?php echo $paper['semester']; ?>">
                        <div class="paper-icon"><i class="fas fa-file-pdf"></i></div>
                        <div class="paper-info">
                            <h4><?php echo htmlspecialchars($paper['title']); ?></h4>
                            <p><?php echo htmlspecialchars($paper['course_code']); ?> • <?php echo $paper['year']; ?> • Semester <?php echo $paper['semester']; ?></p>
                        </div>
                        <div class="paper-actions">
                            <button class="btn-view" onclick="viewPDF('<?php echo $paper['file_path']; ?>')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn-download" onclick="downloadPDF('<?php echo $paper['file_path']; ?>', '<?php echo htmlspecialchars($paper['title']); ?>')">
                                <i class="fas fa-download"></i> Download
                            </button>
                            <button class="btn-print" onclick="printPDF('<?php echo $paper['file_path']; ?>')">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>No Question Papers Available</h3>
                <p>No question papers have been uploaded for <?php echo htmlspecialchars($userDeptName); ?> department yet.</p>
                <p style="font-size: 13px; color: var(--text-muted);">Check back later or contact your lecturer.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- QUIZ MODE -->
    <div id="quizMode" class="mode-content">
        <div class="quiz-section">
            <h3><i class="fas fa-puzzle-piece"></i> Practice Quiz</h3>
            <p>Test your knowledge with interactive quizzes. Get instant feedback and explanations.</p>
            
            <div class="quiz-selector">
                <select id="quizSubject">
                    <option value="">Select Subject / Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>">
                            <?php echo htmlspecialchars($course['name']); ?> (<?php echo $course['code']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button id="startQuizBtn" class="btn-primary">
                    <i class="fas fa-play"></i> Start Quiz
                </button>
            </div>

            <?php if (count($courses) == 0): ?>
                <div class="empty-state" style="padding: 30px;">
                    <i class="fas fa-info-circle"></i>
                    <p>No courses have been added for your department yet.</p>
                </div>
            <?php endif; ?>

            <div class="feature-highlight">
                <div class="feature-icon-small"><i class="fas fa-pen-fancy"></i></div>
                <div class="feature-text">
                    <strong>📝 Try Before Answer</strong>
                    <p>Attempt questions first, then reveal the solution to check your understanding</p>
                </div>
            </div>

            <div class="feature-highlight">
                <div class="feature-icon-small"><i class="fas fa-lightbulb"></i></div>
                <div class="feature-text">
                    <strong>💡 Detailed Solutions</strong>
                    <p>Each question comes with a step-by-step explanation and model answer</p>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Chatbot -->
    <div class="ai-chatbot" id="aiChatbot">
        <div class="chatbot-toggle" onclick="toggleChatbot()">
            <i class="fas fa-robot"></i> AI Assistant
        </div>
        <div class="chatbot-window" id="chatbotWindow">
            <div class="chatbot-header">
                <i class="fas fa-robot"></i> Questa AI Assistant
                <button onclick="toggleChatbot()">✕</button>
            </div>
            <div class="chatbot-messages" id="chatMessages">
                <div class="message bot">Hello! I can help you find past questions, understand difficult topics, or guide you through exam preparation. What do you need help with?</div>
            </div>
            <div class="chatbot-input">
                <input type="text" id="chatInput" placeholder="Ask me something...">
                <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>

<style>
.dept-banner {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    border-radius: 16px;
    padding: 16px 24px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    border-left: 4px solid #f59e0b;
}

.dept-banner-icon {
    width: 44px;
    height: 44px;
    background: rgba(245, 158, 11, 0.15);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #f59e0b;
    font-size: 20px;
}

.dept-banner-text h3 {
    font-size: 16px;
    font-weight: 700;
    color: white;
    margin: 0 0 2px;
}

.dept-banner-text p {
    font-size: 13px;
    color: #94a3b8;
    margin: 0;
}

.page-header {
    margin-bottom: 24px;
}

.page-header h1 {
    font-size: 26px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.page-header p {
    color: var(--text-muted);
}

.search-filter-bar {
    background: var(--bg-secondary);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 24px;
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    border: 1px solid var(--border-color);
}

.search-box {
    flex: 2;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.search-box input {
    width: 100%;
    padding: 12px 16px 12px 42px;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.filter-group {
    flex: 1;
    display: flex;
    gap: 12px;
}

.filter-group select {
    flex: 1;
    padding: 12px;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.btn-primary {
    background: #f59e0b;
    color: white;
    padding: 10px 24px;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary:hover {
    background: #d97706;
    transform: translateY(-2px);
}

.mode-tabs {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 12px;
}

.mode-tab {
    padding: 10px 24px;
    background: transparent;
    border: none;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    color: var(--text-muted);
    border-radius: 30px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.mode-tab:hover {
    color: var(--text-primary);
}

.mode-tab.active {
    background: #f59e0b;
    color: white;
}

.mode-content {
    display: none;
}

.mode-content.active {
    display: block;
}

.papers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
}

.paper-card {
    background: var(--bg-secondary);
    border-radius: 16px;
    padding: 20px;
    display: flex;
    gap: 16px;
    border: 1px solid var(--border-color);
    transition: all 0.2s;
}

.paper-card:hover {
    transform: translateY(-3px);
    border-color: #f59e0b;
}

.paper-icon {
    width: 48px;
    height: 48px;
    background: #fee2e2;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.paper-icon i {
    font-size: 24px;
    color: #dc2626;
}

.paper-info {
    flex: 1;
}

.paper-info h4 {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.paper-info p {
    font-size: 12px;
    color: var(--text-muted);
}

.paper-actions {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.paper-actions button {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 4px;
    justify-content: center;
}

.btn-view {
    background: #e0e7ff;
    color: #4f46e5;
}
.btn-view:hover { background: #c7d2fe; }

.btn-download {
    background: #fef3c7;
    color: #d97706;
}
.btn-download:hover { background: #fde68a; }

.btn-print {
    background: #d1fae5;
    color: #059669;
}
.btn-print:hover { background: #a7f3d0; }

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

.quiz-section {
    background: var(--bg-secondary);
    border-radius: 20px;
    padding: 28px;
    border: 1px solid var(--border-color);
}

.quiz-section h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.quiz-section > p {
    color: var(--text-muted);
    margin-bottom: 24px;
}

.quiz-selector {
    display: flex;
    gap: 16px;
    margin-bottom: 32px;
}

.quiz-selector select {
    flex: 1;
    padding: 12px;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.feature-highlight {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: var(--bg-primary);
    border-radius: 12px;
    margin-bottom: 16px;
    border-left: 4px solid #f59e0b;
}

.feature-icon-small {
    width: 40px;
    height: 40px;
    background: #fef3c7;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.feature-icon-small i {
    font-size: 20px;
    color: #d97706;
}

.feature-text strong {
    display: block;
    font-size: 14px;
    margin-bottom: 4px;
    color: var(--text-primary);
}

.feature-text p {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
}

/* AI Chatbot */
.ai-chatbot {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1000;
}

.chatbot-toggle {
    background: #f59e0b;
    color: white;
    padding: 12px 20px;
    border-radius: 40px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
    transition: all 0.2s;
}

.chatbot-toggle:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(245, 158, 11, 0.4);
}

.chatbot-window {
    display: none;
    width: 320px;
    height: 420px;
    background: var(--bg-secondary);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    border: 1px solid var(--border-color);
    position: absolute;
    bottom: 60px;
    right: 0;
    flex-direction: column;
}

.chatbot-window.open {
    display: flex;
}

.chatbot-header {
    background: #f59e0b;
    color: white;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
}

.chatbot-header button {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    font-size: 18px;
}

.chatbot-messages {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: var(--bg-primary);
}

.message {
    max-width: 85%;
    padding: 10px 14px;
    border-radius: 16px;
    font-size: 13px;
    line-height: 1.4;
}

.message.bot {
    background: var(--bg-secondary);
    color: var(--text-primary);
    align-self: flex-start;
    border: 1px solid var(--border-color);
}

.message.user {
    background: #f59e0b;
    color: white;
    align-self: flex-end;
}

.chatbot-input {
    display: flex;
    padding: 12px;
    gap: 8px;
    border-top: 1px solid var(--border-color);
    background: var(--bg-secondary);
}

.chatbot-input input {
    flex: 1;
    padding: 10px;
    border: 1px solid var(--border-color);
    border-radius: 24px;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.chatbot-input button {
    background: #f59e0b;
    border: none;
    padding: 10px 16px;
    border-radius: 24px;
    color: white;
    cursor: pointer;
}

/* PDF Viewer Modal */
.pdf-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 1001;
    align-items: center;
    justify-content: center;
}

.pdf-modal-content {
    background: white;
    width: 90%;
    height: 90%;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.pdf-modal-header {
    padding: 16px;
    background: #1e293b;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pdf-modal-header button {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
}

.pdf-modal-body {
    flex: 1;
    overflow: auto;
}

.pdf-modal-body iframe {
    width: 100%;
    height: 100%;
    border: none;
}

@media (max-width: 768px) {
    .papers-grid {
        grid-template-columns: 1fr;
    }
    .paper-card {
        flex-direction: column;
    }
    .paper-actions {
        flex-direction: row;
    }
    .paper-actions button {
        flex: 1;
    }
    .search-filter-bar {
        flex-direction: column;
    }
    .filter-group {
        width: 100%;
    }
    .quiz-selector {
        flex-direction: column;
    }
    .chatbot-window {
        width: 280px;
        right: -20px;
    }
}
</style>

<script>
// Mode Tabs
document.querySelectorAll('.mode-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.mode-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const mode = this.dataset.mode;
        document.getElementById('pdfMode').classList.remove('active');
        document.getElementById('quizMode').classList.remove('active');
        
        if (mode === 'pdf') {
            document.getElementById('pdfMode').classList.add('active');
        } else {
            document.getElementById('quizMode').classList.add('active');
        }
    });
});

// Search and Filter
document.getElementById('searchBtn')?.addEventListener('click', function() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const yearFilter = document.getElementById('yearFilter').value;
    const semesterFilter = document.getElementById('semesterFilter').value;
    
    document.querySelectorAll('.paper-card').forEach(card => {
        let show = true;
        const title = card.querySelector('.paper-info h4')?.innerText.toLowerCase() || '';
        if (searchTerm && !title.includes(searchTerm)) show = false;
        if (yearFilter && card.dataset.year !== yearFilter) show = false;
        if (semesterFilter && card.dataset.semester !== semesterFilter) show = false;
        card.style.display = show ? 'flex' : 'none';
    });
});

// PDF Functions
function viewPDF(filePath) {
    let modal = document.getElementById('pdfModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'pdfModal';
        modal.className = 'pdf-modal';
        modal.innerHTML = `
            <div class="pdf-modal-content">
                <div class="pdf-modal-header">
                    <span>Document Viewer</span>
                    <button onclick="closePDFModal()">✕</button>
                </div>
                <div class="pdf-modal-body">
                    <iframe id="pdfFrame" src=""></iframe>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    document.getElementById('pdfFrame').src = filePath;
    modal.style.display = 'flex';
}

function closePDFModal() {
    document.getElementById('pdfModal').style.display = 'none';
}

function downloadPDF(filePath, fileName) {
    const link = document.createElement('a');
    link.href = filePath;
    link.download = fileName + '.pdf';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function printPDF(filePath) {
    window.open(filePath, '_blank');
}

// AI Chatbot
function toggleChatbot() {
    document.getElementById('chatbotWindow').classList.toggle('open');
}

function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    if (!message) return;
    
    const chatMessages = document.getElementById('chatMessages');
    const userDiv = document.createElement('div');
    userDiv.className = 'message user';
    userDiv.textContent = message;
    chatMessages.appendChild(userDiv);
    input.value = '';
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    setTimeout(() => {
        const botDiv = document.createElement('div');
        botDiv.className = 'message bot';
        let response = '';
        const lowerMsg = message.toLowerCase();
        if (lowerMsg.includes('past question') || lowerMsg.includes('paper')) {
            response = 'You can find past questions in "Access Past Papers". Use the search bar to filter by year or semester.';
        } else if (lowerMsg.includes('quiz')) {
            response = 'Go to "Practice Quizzes" mode. Select your subject from the dropdown to start.';
        } else if (lowerMsg.includes('download') || lowerMsg.includes('print')) {
            response = 'Each paper has View, Download, and Print buttons. You can save PDFs or print directly.';
        } else {
            response = 'I can help with: finding past questions, practicing quizzes, or printing study materials. What do you need?';
        }
        botDiv.textContent = response;
        chatMessages.appendChild(botDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }, 500);
}

document.getElementById('chatInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendMessage();
});

document.getElementById('startQuizBtn')?.addEventListener('click', function() {
    const subjectId = document.getElementById('quizSubject').value;
    if (!subjectId) {
        alert('Please select a subject/course first');
    } else {
        alert('Quiz feature will be available soon. You can practice questions for this subject.');
    }
});
</script>

<?php include 'includes/footer.php'; ?>