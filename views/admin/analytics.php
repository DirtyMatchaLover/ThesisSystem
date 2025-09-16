<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<div class="analytics-container">
    <div class="analytics-header">
        <h1 class="analytics-title">📊 System Analytics & Reports</h1>
        <p class="analytics-subtitle">Comprehensive insights for research evaluation</p>
    </div>

    <!-- Key Performance Indicators -->
    <div class="kpi-grid">
        <div class="kpi-card efficiency">
            <div class="kpi-icon">⚡</div>
            <div class="kpi-content">
                <h3><?= number_format($metrics['avg_processing_days'] ?? 0, 1) ?></h3>
                <p>Average Processing Days</p>
                <small class="kpi-change <?= ($metrics['processing_improvement'] ?? 0) > 0 ? 'positive' : 'negative' ?>">
                    <?= ($metrics['processing_improvement'] ?? 0) > 0 ? '↓' : '↑' ?> 
                    <?= abs($metrics['processing_improvement'] ?? 0) ?>% vs manual
                </small>
            </div>
        </div>

        <div class="kpi-card quality">
            <div class="kpi-icon">💯</div>
            <div class="kpi-content">
                <h3><?= number_format($metrics['approval_rate'] ?? 0, 1) ?>%</h3>
                <p>Approval Rate</p>
                <small class="kpi-change positive">
                    ↑ <?= number_format($metrics['quality_improvement'] ?? 0, 1) ?>% improvement
                </small>
            </div>
        </div>

        <div class="kpi-card accessibility">
            <div class="kpi-icon">🌐</div>
            <div class="kpi-content">
                <h3><?= number_format($metrics['total_downloads'] ?? 0) ?></h3>
                <p>Total Downloads</p>
                <small class="kpi-change positive">
                    ↑ <?= number_format($metrics['access_increase'] ?? 0) ?>% more access
                </small>
            </div>
        </div>

        <div class="kpi-card satisfaction">
            <div class="kpi-icon">😊</div>
            <div class="kpi-content">
                <h3><?= number_format($metrics['user_satisfaction'] ?? 0, 1) ?>/5</h3>
                <p>User Satisfaction</p>
                <small class="kpi-change positive">
                    ⭐ <?= $metrics['total_responses'] ?? 0 ?> responses
                </small>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <!-- Submission Timeline Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>📈 Submission Timeline Analysis</h3>
                <p>Tracking submission efficiency over time</p>
            </div>
            <canvas id="timelineChart" width="400" height="200"></canvas>
        </div>

        <!-- Quality Metrics Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>🎯 Quality & Compliance Metrics</h3>
                <p>Format compliance and quality scores</p>
            </div>
            <canvas id="qualityChart" width="400" height="200"></canvas>
        </div>

        <!-- Access & Publication Stats -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>📚 Publication & Access Statistics</h3>
                <p>Research accessibility and usage patterns</p>
            </div>
            <canvas id="accessChart" width="400" height="200"></canvas>
        </div>

        <!-- User Activity Heatmap -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>🔥 User Activity Patterns</h3>
                <p>System usage by role and time</p>
            </div>
            <div id="activityHeatmap" class="heatmap-container"></div>
        </div>
    </div>

    <!-- Detailed Analytics Tables -->
    <div class="analytics-tables">
        <!-- Strand Performance Analysis -->
        <div class="table-card">
            <div class="table-header">
                <h3>🎓 Performance by Academic Strand</h3>
                <button class="btn btn-export" onclick="exportData('strand')">📊 Export Data</button>
            </div>
            <div class="table-container">
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Strand</th>
                            <th>Submissions</th>
                            <th>Approved</th>
                            <th>Avg. Processing Time</th>
                            <th>Quality Score</th>
                            <th>Downloads</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($strand_stats ?? [] as $strand): ?>
                        <tr>
                            <td>
                                <span class="strand-badge strand-<?= strtolower($strand['name']) ?>">
                                    <?= htmlspecialchars($strand['name']) ?>
                                </span>
                            </td>
                            <td><?= number_format($strand['submissions']) ?></td>
                            <td>
                                <span class="approval-rate <?= $strand['approval_rate'] > 80 ? 'high' : ($strand['approval_rate'] > 60 ? 'medium' : 'low') ?>">
                                    <?= number_format($strand['approval_rate'], 1) ?>%
                                </span>
                            </td>
                            <td><?= number_format($strand['avg_processing_days'], 1) ?> days</td>
                            <td>
                                <div class="quality-bar">
                                    <div class="quality-fill" style="width: <?= $strand['quality_score'] * 20 ?>%"></div>
                                    <span><?= number_format($strand['quality_score'], 1) ?>/5</span>
                                </div>
                            </td>
                            <td><?= number_format($strand['downloads']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User Feedback Summary -->
        <div class="table-card">
            <div class="table-header">
                <h3>💬 User Feedback Analysis</h3>
                <button class="btn btn-survey" onclick="showSurveyResults()">📋 View Full Survey</button>
            </div>
            <div class="feedback-summary">
                <div class="feedback-metrics">
                    <div class="feedback-metric">
                        <h4>Ease of Use</h4>
                        <div class="rating-display">
                            <span class="rating-score"><?= number_format($feedback['ease_of_use'] ?? 0, 1) ?></span>
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?= $i <= round($feedback['ease_of_use'] ?? 0) ? 'filled' : '' ?>">⭐</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <div class="feedback-metric">
                        <h4>System Speed</h4>
                        <div class="rating-display">
                            <span class="rating-score"><?= number_format($feedback['system_speed'] ?? 0, 1) ?></span>
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?= $i <= round($feedback['system_speed'] ?? 0) ? 'filled' : '' ?>">⭐</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <div class="feedback-metric">
                        <h4>Overall Satisfaction</h4>
                        <div class="rating-display">
                            <span class="rating-score"><?= number_format($feedback['overall_satisfaction'] ?? 0, 1) ?></span>
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?= $i <= round($feedback['overall_satisfaction'] ?? 0) ? 'filled' : '' ?>">⭐</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="recent-feedback">
                    <h4>Recent Comments</h4>
                    <div class="feedback-comments">
                        <?php foreach ($recent_feedback ?? [] as $comment): ?>
                        <div class="comment-item">
                            <div class="comment-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?= $i <= $comment['rating'] ? 'filled' : '' ?>">⭐</span>
                                <?php endfor; ?>
                            </div>
                            <div class="comment-text">"<?= htmlspecialchars($comment['comment']) ?>"</div>
                            <div class="comment-meta">
                                - <?= htmlspecialchars($comment['user_type']) ?>, <?= date('M j, Y', strtotime($comment['created_at'])) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Research Insights Section -->
    <div class="insights-section">
        <div class="insights-header">
            <h3>🔬 Research Insights & Findings</h3>
            <p>Key discoveries for your thesis research</p>
        </div>

        <div class="insights-grid">
            <div class="insight-card">
                <h4>📋 Submission Process Improvement</h4>
                <ul>
                    <li><strong><?= number_format($insights['time_reduction'] ?? 0) ?>% faster</strong> submission process vs manual</li>
                    <li><strong><?= number_format($insights['error_reduction'] ?? 0) ?>% fewer</strong> submission errors</li>
                    <li><strong><?= number_format($insights['completion_rate'] ?? 0) ?>%</strong> task completion rate</li>
                </ul>
            </div>

            <div class="insight-card">
                <h4>🎯 Quality & Organization Impact</h4>
                <ul>
                    <li><strong><?= number_format($insights['format_compliance'] ?? 0) ?>%</strong> format compliance rate</li>
                    <li><strong><?= number_format($insights['revision_reduction'] ?? 0) ?>% fewer</strong> revision requests</li>
                    <li><strong><?= number_format($insights['quality_improvement'] ?? 0) ?>%</strong> quality score improvement</li>
                </ul>
            </div>

            <div class="insight-card">
                <h4>🌐 Access & Publication Success</h4>
                <ul>
                    <li><strong><?= number_format($insights['access_increase'] ?? 0) ?>% increase</strong> in research access</li>
                    <li><strong><?= number_format($insights['search_efficiency'] ?? 0) ?>x faster</strong> research discovery</li>
                    <li><strong><?= number_format($insights['download_growth'] ?? 0) ?>%</strong> growth in downloads</li>
                </ul>
            </div>

            <div class="insight-card">
                <h4>😊 User Experience Success</h4>
                <ul>
                    <li><strong><?= number_format($insights['satisfaction_score'] ?? 0, 1) ?>/5</strong> average satisfaction rating</li>
                    <li><strong><?= number_format($insights['recommendation_rate'] ?? 0) ?>%</strong> would recommend system</li>
                    <li><strong><?= number_format($insights['adoption_rate'] ?? 0) ?>%</strong> user adoption rate</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Export and Reporting Tools -->
    <div class="export-section">
        <h3>📊 Export & Reporting Tools</h3>
        <div class="export-buttons">
            <button class="btn btn-primary" onclick="exportFullReport()">
                📄 Generate Complete Report
            </button>
            <button class="btn btn-secondary" onclick="exportChartData()">
                📊 Export Chart Data
            </button>
            <button class="btn btn-secondary" onclick="exportUserFeedback()">
                💬 Export User Feedback
            </button>
            <button class="btn btn-secondary" onclick="exportMetrics()">
                📈 Export Performance Metrics
            </button>
        </div>
    </div>
</div>

<style>
.analytics-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    background: #f8f9fa;
    min-height: 100vh;
}

.analytics-header {
    text-align: center;
    margin-bottom: 30px;
}

.analytics-title {
    font-size: 2.5rem;
    color: #d32f2f;
    margin-bottom: 10px;
}

.analytics-subtitle {
    color: #666;
    font-size: 1.1rem;
}

/* KPI Grid */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.kpi-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.kpi-icon {
    font-size: 3rem;
    width: 80px;
    text-align: center;
}

.kpi-content h3 {
    font-size: 2.5rem;
    margin: 0;
    color: #333;
}

.kpi-content p {
    margin: 5px 0;
    color: #666;
    font-weight: 600;
}

.kpi-change {
    font-size: 0.9rem;
    font-weight: 600;
}

.kpi-change.positive { color: #4caf50; }
.kpi-change.negative { color: #f44336; }

.kpi-card.efficiency { border-left: 5px solid #ff9800; }
.kpi-card.quality { border-left: 5px solid #4caf50; }
.kpi-card.accessibility { border-left: 5px solid #2196f3; }
.kpi-card.satisfaction { border-left: 5px solid #9c27b0; }

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.chart-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.chart-header {
    margin-bottom: 20px;
    text-align: center;
}

.chart-header h3 {
    color: #333;
    margin-bottom: 5px;
}

.chart-header p {
    color: #666;
    margin: 0;
    font-size: 0.9rem;
}

/* Analytics Tables */
.analytics-tables {
    display: grid;
    gap: 30px;
}

.table-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.table-header {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 2px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-header h3 {
    color: #333;
    margin: 0;
}

.table-container {
    overflow-x: auto;
}

.analytics-table {
    width: 100%;
    border-collapse: collapse;
}

.analytics-table th {
    background: #f5f5f5;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #555;
}

.analytics-table td {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.approval-rate.high { color: #4caf50; font-weight: 600; }
.approval-rate.medium { color: #ff9800; font-weight: 600; }
.approval-rate.low { color: #f44336; font-weight: 600; }

.quality-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
    background: #f0f0f0;
    border-radius: 10px;
    height: 20px;
    min-width: 100px;
}

.quality-fill {
    height: 100%;
    background: linear-gradient(90deg, #f44336, #ff9800, #4caf50);
    border-radius: 10px;
    transition: width 0.5s ease;
}

.quality-bar span {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.8rem;
    font-weight: 600;
    color: #333;
}

/* Feedback Section */
.feedback-summary {
    padding: 20px;
}

.feedback-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.feedback-metric {
    text-align: center;
}

.feedback-metric h4 {
    color: #333;
    margin-bottom: 10px;
}

.rating-display {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.rating-score {
    font-size: 2rem;
    font-weight: bold;
    color: #d32f2f;
}

.stars {
    display: flex;
    gap: 2px;
}

.star {
    font-size: 1.2rem;
    filter: grayscale(100%);
    transition: filter 0.3s ease;
}

.star.filled {
    filter: grayscale(0%);
}

.feedback-comments {
    max-height: 300px;
    overflow-y: auto;
}

.comment-item {
    padding: 15px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 10px;
    background: #fafafa;
}

.comment-rating {
    margin-bottom: 8px;
}

.comment-text {
    color: #333;
    font-style: italic;
    margin-bottom: 5px;
}

.comment-meta {
    color: #666;
    font-size: 0.9rem;
}

/* Insights Section */
.insights-section {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.insights-header {
    text-align: center;
    margin-bottom: 30px;
}

.insights-header h3 {
    color: #d32f2f;
    font-size: 1.8rem;
    margin-bottom: 8px;
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.insight-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border-left: 4px solid #d32f2f;
}

.insight-card h4 {
    color: #333;
    margin-bottom: 15px;
}

.insight-card ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.insight-card li {
    padding: 5px 0;
    color: #555;
}

.insight-card strong {
    color: #d32f2f;
}

/* Export Section */
.export-section {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.export-section h3 {
    color: #333;
    margin-bottom: 20px;
}

.export-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #d32f2f;
    color: white;
}

.btn-primary:hover {
    background: #b71c1c;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #f5f5f5;
    color: #333;
    border: 2px solid #e0e0e0;
}

.btn-secondary:hover {
    background: #e0e0e0;
    transform: translateY(-2px);
}

.btn-export {
    background: #4caf50;
    color: white;
}

.btn-export:hover {
    background: #388e3c;
}

.btn-survey {
    background: #2196f3;
    color: white;
}

.btn-survey:hover {
    background: #1976d2;
}

/* Responsive Design */
@media (max-width: 768px) {
    .analytics-container {
        padding: 15px;
    }
    
    .kpi-grid {
        grid-template-columns: 1fr;
    }
    
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-card {
        min-width: auto;
    }
    
    .feedback-metrics {
        grid-template-columns: 1fr;
    }
    
    .insights-grid {
        grid-template-columns: 1fr;
    }
    
    .export-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 250px;
        justify-content: center;
    }
}
</style>

<script>
// Sample data for demonstration - replace with actual data from your backend
const sampleData = {
    timeline: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        manual: [15, 12, 18, 14, 16, 13],
        digital: [3, 2, 4, 2, 3, 2]
    },
    quality: {
        labels: ['Format Compliance', 'Content Quality', 'Citation Accuracy', 'Overall Score'],
        before: [65, 70, 60, 65],
        after: [92, 85, 88, 90]
    },
    access: {
        labels: ['STEM', 'ABM', 'HUMSS', 'GAS'],
        downloads: [450, 320, 280, 150],
        views: [1200, 890, 650, 420]
    }
};

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

function initializeCharts() {
    // You would integrate with Chart.js or similar library here
    // For now, we'll create placeholder visualizations
    
    // Timeline Chart
    createTimelineChart();
    
    // Quality Chart  
    createQualityChart();
    
    // Access Chart
    createAccessChart();
    
    // Activity Heatmap
    createActivityHeatmap();
}

function createTimelineChart() {
    const canvas = document.getElementById('timelineChart');
    const ctx = canvas.getContext('2d');
    
    // Simple bar chart representation
    ctx.fillStyle = '#d32f2f';
    ctx.fillRect(50, 50, 100, 100);
    ctx.fillStyle = '#4caf50';
    ctx.fillRect(200, 100, 100, 50);
    
    ctx.fillStyle = '#333';
    ctx.font = '14px Arial';
    ctx.fillText('Manual Process', 30, 170);
    ctx.fillText('Digital System', 180, 170);
}

function createQualityChart() {
    const canvas = document.getElementById('qualityChart');
    const ctx = canvas.getContext('2d');
    
    // Simple visualization
    ctx.fillStyle = '#ff9800';
    ctx.fillRect(50, 50, 80, 100);
    ctx.fillStyle = '#4caf50';
    ctx.fillRect(150, 30, 80, 120);
    
    ctx.fillStyle = '#333';
    ctx.font = '14px Arial';
    ctx.fillText('Before System', 30, 170);
    ctx.fillText('After System', 130, 170);
}

function createAccessChart() {
    const canvas = document.getElementById('accessChart');
    const ctx = canvas.getContext('2d');
    
    // Simple pie chart representation
    ctx.beginPath();
    ctx.arc(150, 100, 70, 0, Math.PI);
    ctx.fillStyle = '#2196f3';
    ctx.fill();
    
    ctx.beginPath();
    ctx.arc(150, 100, 70, Math.PI, 2 * Math.PI);
    ctx.fillStyle = '#ff9800';
    ctx.fill();
}

function createActivityHeatmap() {
    const container = document.getElementById('activityHeatmap');
    
    // Create a simple grid heatmap
    const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    const hours = ['8AM', '10AM', '12PM', '2PM', '4PM', '6PM'];
    
    let heatmapHTML = '<div class="heatmap-grid">';
    
    // Add headers
    heatmapHTML += '<div class="heatmap-header"></div>';
    hours.forEach(hour => {
        heatmapHTML += `<div class="heatmap-header">${hour}</div>`;
    });
    
    // Add data rows
    days.forEach(day => {
        heatmapHTML += `<div class="heatmap-label">${day}</div>`;
        hours.forEach(() => {
            const intensity = Math.random();
            const opacity = intensity * 0.8 + 0.2;
            heatmapHTML += `<div class="heatmap-cell" style="background: rgba(211, 47, 47, ${opacity});"></div>`;
        });
    });
    
    heatmapHTML += '</div>';
    
    container.innerHTML = heatmapHTML;
}

// Export functions
function exportFullReport() {
    alert('Generating comprehensive analytics report...\nThis would create a PDF with all metrics and charts.');
}

function exportChartData() {
    alert('Exporting chart data as CSV...\nThis would download all chart data in spreadsheet format.');
}

function exportUserFeedback() {
    alert('Exporting user feedback...\nThis would compile all survey responses and comments.');
}

function exportMetrics() {
    alert('Exporting performance metrics...\nThis would provide detailed system performance data.');
}

function showSurveyResults() {
    alert('Opening detailed survey results...\nThis would show comprehensive user feedback analysis.');
}

function exportData(type) {
    alert(`Exporting ${type} performance data...\nThis would generate a detailed report for ${type} analysis.`);
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>