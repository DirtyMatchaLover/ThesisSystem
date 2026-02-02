<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<div class="upload-thesis-container">
    <div class="upload-header">
        <div class="header-icon"></div>
        <h1 class="upload-title">Upload Your Thesis</h1>
        <p class="upload-subtitle">Share your research with the academic community</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <span class="alert-icon">️</span>
            <div class="alert-content">
                <strong>Error!</strong>
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-success">
            <span class="alert-icon"></span>
            <div class="alert-content">
                <strong>Success!</strong>
                <p><?= htmlspecialchars($_SESSION['flash_message']) ?></p>
            </div>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="<?= route('thesis/create') ?>" class="upload-form" id="thesisForm">
        <!-- CSRF Protection -->
        <?php csrf_field(); ?>

        <!-- Progress Indicator -->
        <div class="form-progress">
            <div class="progress-step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Basic Info</div>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Details</div>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Upload</div>
            </div>
        </div>

        <!-- Step 1: Basic Information -->
        <div class="form-step active" data-step="1">
            <h2 class="step-title"> Basic Information</h2>

            <div class="form-group">
                <label for="title" class="form-label">
                    Thesis Title <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-control"
                    placeholder="Enter your thesis title..."
                    value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                    required
                    maxlength="500"
                >
                <div class="form-hint">
                    <span class="hint-icon"></span>
                    <span class="char-counter"><span id="titleCounter">0</span>/500 characters</span>
                </div>
            </div>

            <div class="form-group">
                <label for="author" class="form-label">
                    Author(s) <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="author"
                    name="author"
                    class="form-control"
                    placeholder="Enter author name(s)..."
                    value="<?= htmlspecialchars($_POST['author'] ?? current_user()['name'] ?? '') ?>"
                    required
                >
                <div class="form-hint">
                    <span class="hint-icon"></span>
                    <span>Use commas to separate multiple authors</span>
                </div>
            </div>

            <div class="form-group">
                <label for="adviser" class="form-label">
                    Research Adviser <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="adviser"
                    name="adviser"
                    class="form-control"
                    placeholder="Enter adviser's full name..."
                    value="<?= htmlspecialchars($_POST['adviser'] ?? '') ?>"
                    required
                >
                <div class="form-hint">
                    <span class="hint-icon">‍</span>
                    <span>Name of your faculty adviser for this thesis</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-next" onclick="nextStep(1)">
                    Next: Details <span class="btn-icon">→</span>
                </button>
            </div>
        </div>

        <!-- Step 2: Thesis Details -->
        <div class="form-step" data-step="2">
            <h2 class="step-title"> Thesis Details</h2>

            <div class="form-group">
                <label for="abstract" class="form-label">
                    Abstract <span class="required">*</span>
                </label>
                <textarea
                    id="abstract"
                    name="abstract"
                    class="form-control form-textarea"
                    placeholder="Provide a brief summary of your research (minimum 100 characters)..."
                    rows="8"
                    required
                    minlength="100"
                ><?= htmlspecialchars($_POST['abstract'] ?? '') ?></textarea>
                <div class="form-hint">
                    <span class="hint-icon"></span>
                    <span class="char-counter"><span id="abstractCounter">0</span> characters (min. 100)</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="strand" class="form-label">Academic Strand</label>
                    <select id="strand" name="strand" class="form-control">
                        <option value="">Select Strand</option>
                        <option value="STEM">STEM - Science, Technology, Engineering, Mathematics</option>
                        <option value="HUMSS">HUMSS - Humanities and Social Sciences</option>
                        <option value="ABM">ABM - Accountancy, Business and Management</option>
                        <option value="TVL-HE">TVL-HE - Home Economics</option>
                        <option value="TVL-ICT">TVL-ICT - Information and Communications Technology</option>
                        <option value="ADT">ADT - Arts and Design Track</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="academic_year" class="form-label">Academic Year</label>
                    <input
                        type="text"
                        id="academic_year"
                        name="academic_year"
                        class="form-control"
                        placeholder="e.g., 2024-2025"
                        value="<?= date('Y') . '-' . (date('Y') + 1) ?>"
                    >
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-back" onclick="prevStep(2)">
                    <span class="btn-icon">←</span> Back
                </button>
                <button type="button" class="btn btn-next" onclick="nextStep(2)">
                    Next: Upload File <span class="btn-icon">→</span>
                </button>
            </div>
        </div>

        <!-- Step 3: File Upload -->
        <div class="form-step" data-step="3">
            <h2 class="step-title"> Upload Your Thesis</h2>

            <div class="upload-zone" id="dropZone">
                <input
                    type="file"
                    id="file"
                    name="file"
                    class="file-input"
                    accept="application/pdf"
                    required
                >
                <div class="upload-content">
                    <div class="upload-icon"></div>
                    <h3 class="upload-text">Drag & Drop your PDF here</h3>
                    <p class="upload-subtext">or</p>
                    <label for="file" class="btn btn-upload">
                        Browse Files
                    </label>
                    <div class="upload-requirements">
                        <div class="requirement-item">
                            <span class="req-icon"></span>
                            <span>PDF format only</span>
                        </div>
                        <div class="requirement-item">
                            <span class="req-icon"></span>
                            <span>Maximum size: 10MB</span>
                        </div>
                    </div>
                </div>
                <div class="file-preview" id="filePreview" style="display: none;">
                    <div class="preview-icon"></div>
                    <div class="preview-info">
                        <div class="preview-name" id="fileName"></div>
                        <div class="preview-size" id="fileSize"></div>
                        <div class="preview-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" id="progressFill"></div>
                            </div>
                            <span class="progress-text" id="progressText">Ready to upload</span>
                        </div>
                    </div>
                    <button type="button" class="btn-remove" onclick="removeFile()"></button>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-back" onclick="prevStep(3)">
                    <span class="btn-icon">←</span> Back
                </button>
                <button type="submit" class="btn btn-submit" id="submitBtn">
                    <span class="btn-icon"></span> Submit Thesis
                </button>
            </div>
        </div>
    </form>
</div>

<style>
/* Upload Thesis Styles */
.upload-thesis-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
}

.upload-header {
    text-align: center;
    margin-bottom: 40px;
}

.header-icon {
    font-size: 64px;
    margin-bottom: 20px;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.upload-title {
    color: #d32f2f;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.upload-subtitle {
    color: #666;
    font-size: 1.1rem;
}

/* Alerts */
.alert {
    display: flex;
    align-items: flex-start;
    padding: 16px;
    border-radius: 12px;
    margin-bottom: 30px;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-error {
    background: #ffebee;
    border-left: 4px solid #d32f2f;
}

.alert-success {
    background: #e8f5e9;
    border-left: 4px solid #4caf50;
}

.alert-icon {
    font-size: 24px;
    margin-right: 12px;
}

.alert-content strong {
    display: block;
    margin-bottom: 4px;
    color: #333;
}

.alert-content p {
    margin: 0;
    color: #666;
}

/* Form Progress */
.form-progress {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding: 0 20px;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    flex: 1;
}

.step-number {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #e0e0e0;
    color: #999;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.progress-step.active .step-number {
    background: #d32f2f;
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
}

.progress-step.completed .step-number {
    background: #4caf50;
    color: white;
}

.step-label {
    font-size: 0.9rem;
    color: #666;
    font-weight: 500;
}

.progress-step.active .step-label {
    color: #d32f2f;
    font-weight: 700;
}

.progress-line {
    flex: 1;
    height: 3px;
    background: #e0e0e0;
    margin: 0 8px;
    margin-bottom: 30px;
}

/* Upload Form */
.upload-form {
    background: white;
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.form-step {
    display: none;
}

.form-step.active {
    display: block;
    animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.step-title {
    color: #333;
    font-size: 1.8rem;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.form-group {
    margin-bottom: 25px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 1rem;
}

.required {
    color: #d32f2f;
}

.form-control {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #d32f2f;
    box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 150px;
}

.form-hint {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 6px;
    font-size: 0.9rem;
    color: #666;
}

.hint-icon {
    font-size: 1rem;
}

.char-counter {
    margin-left: auto;
    font-weight: 500;
}

/* Upload Zone */
.upload-zone {
    border: 3px dashed #d0d0d0;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    transition: all 0.3s ease;
    background: #fafafa;
    position: relative;
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-zone.drag-over {
    border-color: #d32f2f;
    background: #ffebee;
    transform: scale(1.02);
}

.file-input {
    display: none;
}

.upload-content {
    width: 100%;
}

.upload-icon {
    font-size: 64px;
    margin-bottom: 20px;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.upload-text {
    color: #333;
    font-size: 1.4rem;
    margin-bottom: 10px;
}

.upload-subtext {
    color: #999;
    margin: 15px 0;
}

.btn-upload {
    display: inline-block;
    padding: 12px 32px;
    background: #d32f2f;
    color: white;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
}

.btn-upload:hover {
    background: #b71c1c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
}

.upload-requirements {
    margin-top: 25px;
    display: flex;
    justify-content: center;
    gap: 30px;
}

.requirement-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-size: 0.9rem;
}

.req-icon {
    color: #4caf50;
    font-weight: 700;
}

/* File Preview */
.file-preview {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: white;
    border-radius: 12px;
    border: 2px solid #e0e0e0;
}

.preview-icon {
    font-size: 48px;
}

.preview-info {
    flex: 1;
}

.preview-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
    font-size: 1.1rem;
}

.preview-size {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 12px;
}

.preview-progress {
    margin-top: 10px;
}

.progress-bar {
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 6px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #d32f2f, #f44336);
    border-radius: 4px;
    transition: width 0.3s ease;
    width: 0%;
}

.progress-text {
    font-size: 0.85rem;
    color: #666;
}

.btn-remove {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: #ffebee;
    color: #d32f2f;
    cursor: pointer;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.btn-remove:hover {
    background: #d32f2f;
    color: white;
    transform: rotate(90deg);
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #f0f0f0;
}

.btn {
    padding: 14px 28px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-back {
    background: #f5f5f5;
    color: #666;
}

.btn-back:hover {
    background: #e0e0e0;
}

.btn-next, .btn-submit {
    background: #d32f2f;
    color: white;
}

.btn-next:hover, .btn-submit:hover {
    background: #b71c1c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
}

.btn-submit {
    background: linear-gradient(135deg, #d32f2f, #f44336);
}

.btn-icon {
    font-size: 1.1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .upload-form {
        padding: 25px;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .form-progress {
        padding: 0;
    }

    .step-label {
        font-size: 0.75rem;
    }

    .upload-zone {
        padding: 25px;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Character counter for title
const titleInput = document.getElementById('title');
const titleCounter = document.getElementById('titleCounter');

if (titleInput && titleCounter) {
    titleInput.addEventListener('input', function() {
        titleCounter.textContent = this.value.length;
    });
    titleCounter.textContent = titleInput.value.length;
}

// Character counter for abstract
const abstractInput = document.getElementById('abstract');
const abstractCounter = document.getElementById('abstractCounter');

if (abstractInput && abstractCounter) {
    abstractInput.addEventListener('input', function() {
        abstractCounter.textContent = this.value.length;
    });
    abstractCounter.textContent = abstractInput.value.length;
}

// Multi-step form navigation
let currentStep = 1;

function nextStep(step) {
    // Validate current step
    const currentStepElement = document.querySelector(`.form-step[data-step="${step}"]`);
    const inputs = currentStepElement.querySelectorAll('input[required], textarea[required]');

    let isValid = true;
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = '#d32f2f';
            setTimeout(() => {
                input.style.borderColor = '';
            }, 2000);
        }
    });

    if (!isValid) {
        alert('Please fill in all required fields');
        return;
    }

    // Move to next step
    currentStep = step + 1;
    updateSteps();
}

function prevStep(step) {
    currentStep = step - 1;
    updateSteps();
}

function updateSteps() {
    // Update form steps
    document.querySelectorAll('.form-step').forEach(step => {
        step.classList.remove('active');
    });
    document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.add('active');

    // Update progress indicators
    document.querySelectorAll('.progress-step').forEach(step => {
        const stepNum = parseInt(step.getAttribute('data-step'));
        step.classList.remove('active', 'completed');

        if (stepNum === currentStep) {
            step.classList.add('active');
        } else if (stepNum < currentStep) {
            step.classList.add('completed');
        }
    });
}

// File upload handling
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('file');
const filePreview = document.getElementById('filePreview');
const uploadContent = document.querySelector('.upload-content');

// Drag and drop
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('drag-over');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('drag-over');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');

    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        displayFilePreview(files[0]);
    }
});

// File input change
fileInput.addEventListener('change', function() {
    if (this.files.length > 0) {
        displayFilePreview(this.files[0]);
    }
});

function displayFilePreview(file) {
    // Validate file type
    if (file.type !== 'application/pdf') {
        alert('Please select a PDF file');
        fileInput.value = '';
        return;
    }

    // Validate file size (10MB)
    const maxSize = 10 * 1024 * 1024;
    if (file.size > maxSize) {
        alert('File size must be less than 10MB');
        fileInput.value = '';
        return;
    }

    // Show preview
    uploadContent.style.display = 'none';
    filePreview.style.display = 'flex';

    // Update preview info
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = formatFileSize(file.size);

    // Animate progress bar
    const progressFill = document.getElementById('progressFill');
    setTimeout(() => {
        progressFill.style.width = '100%';
    }, 100);
}

function removeFile() {
    fileInput.value = '';
    filePreview.style.display = 'none';
    uploadContent.style.display = 'block';
    document.getElementById('progressFill').style.width = '0%';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Form submission
document.getElementById('thesisForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="btn-icon">⏳</span> Uploading...';
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
