<?php
/**
 * Facebook CMS - UI Component
 * This file is included in admin_dashboard.php
 * Logic is handled in the parent file to prevent "headers already sent" errors.
 */
include('auth_check.php');
?>

<!-- Internal CSS for the CMS component -->
<style>
    .cms-wrapper {
        max-width: 700px;
        margin: 0 auto;
        padding: 10px;
    }

    .cms-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .cms-header i {
        font-size: 3rem;
        color: #1877f2; /* Facebook Blue */
        margin-bottom: 15px;
        display: inline-block;
    }

    .cms-header h3 {
        font-size: 1.5rem;
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .cms-header p {
        color: #64748b;
        font-size: 0.95rem;
    }

    /* Form Styling */
    .fb-form .form-group {
        margin-bottom: 20px;
    }

    .fb-form label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--primary-color);
        font-size: 0.9rem;
    }

    .fb-form .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-family: 'Inter', sans-serif;
        font-size: 1rem;
        transition: all 0.2s ease;
        background-color: #ffffff;
    }

    .fb-form .form-control:focus {
        outline: none;
        border-color: var(--accent-color);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .fb-form textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .hint-text {
        display: block;
        margin-top: 6px;
        font-size: 0.8rem;
        color: #94a3b8;
    }

    /* Submit Button */
    .btn-fb-post {
        background-color: #1877f2;
        color: white;
        border: none;
        padding: 15px 25px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1rem;
        width: 100%;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        transition: transform 0.1s, background-color 0.2s;
        margin-top: 10px;
    }

    .btn-fb-post:hover {
        background-color: #166fe5;
    }

    .btn-fb-post:active {
        transform: scale(0.98);
    }

    /* Alert Boxes */
    .cms-alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .alert-success {
        background-color: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .alert-error {
        background-color: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
        .cms-header i { font-size: 2.5rem; }
        .cms-header h3 { font-size: 1.25rem; }
    }
</style>

<div class="cms-wrapper">
    
    <!-- Header Section -->
    <div class="cms-header">
        <i class="fab fa-facebook-square"></i>
        <h3>Facebook Content Manager</h3>
        <p>Publish or schedule posts directly to your connected pages.</p>
    </div>

    <!-- Feedback Alerts -->
    <?php if (isset($message_status) && $message_status): ?>
        <div class="cms-alert alert-<?= $message_status['type'] ?>">
            <i class="fas <?= $message_status['type'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
            <span><?= $message_status['text'] ?></span>
        </div>
    <?php endif; ?>

    <!-- Post Creation Form -->
    <form class="fb-form" method="POST" enctype="multipart/form-data">
        
        <!-- Page Selection -->
        <div class="form-group">
            <label for="page_id"><i class="fas fa-layer-group"></i> 1. Select Facebook Page</label>
            <select name="page_id" id="page_id" class="form-control" required>
                <?php if (isset($_SESSION['pages']) && !empty($_SESSION['pages'])): ?>
                    <?php foreach ($_SESSION['pages'] as $p): ?>
                        <option value="<?= $p['id'] ?>|<?= $p['access_token'] ?>">
                            <?= htmlspecialchars($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option disabled>No pages found. Please reconnect Facebook.</option>
                <?php endif; ?>
            </select>
        </div>

        <!-- Message -->
        <div class="form-group">
            <label for="message"><i class="fas fa-quote-left"></i> 2. Post Caption</label>
            <textarea name="message" id="message" class="form-control" 
                      placeholder="Write your update here... e.g. New Bridal Gowns in stock!" required></textarea>
        </div>

        <!-- Image Upload -->
        <div class="form-group">
            <label for="image"><i class="fas fa-camera"></i> 3. Add Photo (Optional)</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <span class="hint-text">Supported formats: JPG, PNG, WEBP. Max size 5MB.</span>
        </div>

        <!-- Scheduling -->
        <div class="form-group">
            <label for="schedule_time"><i class="fas fa-calendar-alt"></i> 4. Schedule for Later</label>
            <input type="datetime-local" name="schedule_time" id="schedule_time" class="form-control">
            <span class="hint-text">Leave blank to post immediately. Schedules must be set at least 10 minutes in the future.</span>
        </div>

        <!-- Submit -->
        <button type="submit" name="publish_fb" class="btn-fb-post">
            <i class="fas fa-paper-plane"></i> Publish to Facebook
        </button>

    </form>
</div>