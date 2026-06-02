<?php
/**
 * DATABASE MAINTENANCE COMPONENT (Design Only)
 */
if (!isset($conn)) {
    $conn = new mysqli("localhost", "root", "", "fashion");
}

$message = "";

// Keep only RESTORE logic here because it shows a message on the same page
if (isset($_POST['restore'])) {
    $file = $_FILES['sql_file']['tmp_name'];
    if (empty($file)) {
        $message = "<div class='alert alert-error'><i class='fas fa-exclamation-circle'></i> Please select a valid .sql file.</div>";
    } else {
        $sqlQueries = file_get_contents($file);
        $queries = explode(';', $sqlQueries);
        
        $success = true;

        // FIX: Disable foreign key checks before running the queries
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                if (!$conn->query($query)) { 
                    $success = false; 
                }
            }
        }

        // FIX: Re-enable them after finishing
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");

        $message = $success 
            ? "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Database Restored Successfully!</div>" 
            : "<div class='alert alert-error'><i class='fas fa-times-circle'></i> Error during restore. Some tables couldn't be updated.</div>";
    }
}
?>

<style>
    .maintenance-wrapper { max-width: 900px; margin: 0 auto; }
    .maintenance-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; margin-top: 20px; }
    .data-card { background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; }
    .card-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 20px; }
    .bg-soft-indigo { background: #eef2ff; color: #6366f1; }
    .bg-soft-rose { background: #fff1f2; color: #f43f5e; }
    .data-card h3 { font-size: 1.1rem; color: #1e293b; margin-bottom: 10px; }
    .data-card p { color: #64748b; font-size: 0.9rem; line-height: 1.5; margin-bottom: 25px; flex-grow: 1; }
    .btn-full { width: 100%; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; }
    .btn-primary { background: #6366f1; color: white; }
    .btn-secondary { background: white; border: 1px solid #e2e8f0; color: #1e293b; }
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
</style>

<div class="maintenance-wrapper">
    <?php echo $message; ?>

    <div class="maintenance-grid">
        <!-- BACKUP CARD -->
        <div class="data-card">
            <div class="card-icon bg-soft-indigo">
                <i class="fas fa-download"></i>
            </div>
            <h3>Backup System</h3>
            <p>Download a complete SQL file containing all gown data and user records.</p>
            
            <!-- ACTION POINTING TO THE SEPARATE FILE -->
            <form action="download_backup.php" method="POST">
                <button type="submit" name="backup" class="btn-full btn-primary">
                    <i class="fas fa-file-export"></i> Export .SQL File
                </button>
            </form>
        </div>

        <!-- RESTORE CARD -->
        <div class="data-card">
            <div class="card-icon bg-soft-rose">
                <i class="fas fa-upload"></i>
            </div>
            <h3>Restore System</h3>
            <p>Upload a .sql file to restore the database. This will overwrite current data.</p>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="sql_file" accept=".sql" required style="margin-bottom:15px; font-size:0.8rem;">
                <button type="submit" name="restore" class="btn-full btn-secondary">
                    <i class="fas fa-undo"></i> Run Restoration
                </button>
            </form>
        </div>
    </div>
</div>