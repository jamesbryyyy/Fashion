<?php
// CONNECTION BRIDGE
if (!isset($conn)) {
    include('../db.php');
    if (isset($con) && !isset($conn)) { $conn = $con; }
}

// Configuration: Change this to match your routing key in index.php
$page_key = "admin_trash"; 
$message = "";

// --- ACTION LOGIC (RESTORE & PURGE) ---
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['type'])) {
    $id = $_GET['id'];
    $type = $_GET['type'];
    $action = $_GET['action'];

    // Map types to tables
    $tables = [
        'gown' => ['table' => 'gowns', 'name' => 'Gown'],
        'artist' => ['table' => 'makeup_artists', 'name' => 'Artist Profile'],
        'package' => ['table' => 'packages', 'name' => 'Package']
    ];

    if (array_key_exists($type, $tables)) {
        $table = $tables[$type]['table'];
        $label = $tables[$type]['name'];

        if ($action == 'restore') {
            $stmt = $conn->prepare("UPDATE $table SET is_deleted = 0, deleted_at = NULL WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "<script>alert('✨ $label restored successfully!'); window.location='?page=$page_key';</script>";
            }
        } 
        elseif ($action == 'purge') {
            $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "<script>alert('🗑️ $label permanently deleted.'); window.location='?page=$page_key';</script>";
            }
        }
    }
}

// --- FETCH DELETED DATA ---
$deleted_gowns = mysqli_query($conn, "SELECT id, name, deleted_at FROM gowns WHERE is_deleted = 1 ORDER BY deleted_at DESC");
$deleted_artists = mysqli_query($conn, "SELECT id, name, deleted_at FROM makeup_artists WHERE is_deleted = 1 ORDER BY deleted_at DESC");
$deleted_packages = mysqli_query($conn, "SELECT id, package_name as name, deleted_at FROM packages WHERE is_deleted = 1 ORDER BY deleted_at DESC");
?>

<style>
    :root { 
        --primary-blue: #1e3a8a; 
        --accent-blue: #3b82f6;  
        --bg-light: #f3f4f6;    
        --white: #ffffff;
        --text-dark: #1f2937;
        --border: #e5e7eb;
        --danger: #ef4444;
        --success: #10b981;
    }
    
    .trash-container { font-family: 'Inter', sans-serif; padding: 20px; }
    .trash-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    
    h3 { color: var(--primary-blue); font-weight: 600; border-left: 5px solid var(--accent-blue); padding-left: 15px; margin: 30px 0 15px 0; }

    .table-container { background: var(--white); border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 20px; border: 1px solid var(--border); }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f8fafc; color: #64748b; text-align: left; padding: 12px 15px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--border); }
    td { padding: 12px 15px; border-bottom: 1px solid var(--border); font-size: 0.9rem; color: var(--text-dark); }
    tr:last-child td { border-bottom: none; }
    tr:hover { background-color: #f9fafb; }
    
    .btn { padding: 6px 12px; border-radius: 4px; font-weight: 600; text-decoration: none; font-size: 0.75rem; transition: 0.2s; display: inline-block; }
    .btn-restore { border: 1px solid var(--success); color: var(--success); margin-right: 5px; }
    .btn-restore:hover { background: var(--success); color: white; }
    
    .btn-purge { border: 1px solid var(--danger); color: var(--danger); }
    .btn-purge:hover { background: var(--danger); color: white; }

    .btn-back { background: var(--primary-blue); color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; }

    .timestamp-tag { font-size: 0.8rem; color: #6b7280; }
    .empty-msg { text-align: center; color: #9ca3af; padding: 40px; font-style: italic; }
</style>

<div class="trash-container">
    <div class="trash-header">
        <div>
            <h2 style="color: var(--primary-blue); margin: 0;">System Restoration Vault</h2>
            <p style="color: #6b7280; font-size: 0.9rem; margin-top: 5px;">Manage recently deleted items</p>
        </div>
        <a href="?page=admin_dashboard" class="btn-back">← Back to Dashboard</a>
    </div>

    <!-- GOWNS TRASH -->
    <h3>👗 Deleted Gowns</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Date Deleted</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($deleted_gowns) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($deleted_gowns)): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo htmlspecialchars($r["name"]); ?></td>
                        <td class="timestamp-tag"><?php echo $r["deleted_at"] ? date('M d, Y - h:i A', strtotime($r["deleted_at"])) : '---'; ?></td>
                        <td style="text-align:right;">
                            <a href="?page=<?php echo $page_key; ?>&action=restore&type=gown&id=<?php echo $r['id']; ?>" class="btn btn-restore">Restore</a>
                            <a href="?page=<?php echo $page_key; ?>&action=purge&type=gown&id=<?php echo $r['id']; ?>" class="btn btn-purge" onclick="return confirm('Permanently delete this gown? This cannot be undone.')">Purge</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="empty-msg">No deleted gowns found in trash.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ARTISTS TRASH -->
    <h3>💄 Deleted Makeup Artists</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Artist Name</th>
                    <th>Date Deleted</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($deleted_artists) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($deleted_artists)): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo htmlspecialchars($r["name"]); ?></td>
                        <td class="timestamp-tag"><?php echo $r["deleted_at"] ? date('M d, Y - h:i A', strtotime($r["deleted_at"])) : '---'; ?></td>
                        <td style="text-align:right;">
                            <a href="?page=<?php echo $page_key; ?>&action=restore&type=artist&id=<?php echo $r['id']; ?>" class="btn btn-restore">Restore</a>
                            <a href="?page=<?php echo $page_key; ?>&action=purge&type=artist&id=<?php echo $r['id']; ?>" class="btn btn-purge" onclick="return confirm('Permanently delete this artist?')">Purge</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="empty-msg">No deleted artists found in trash.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PACKAGES TRASH -->
    <h3>📦 Deleted Packages</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Package Name</th>
                    <th>Date Deleted</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($deleted_packages) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($deleted_packages)): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo htmlspecialchars($r["name"]); ?></td>
                        <td class="timestamp-tag"><?php echo $r["deleted_at"] ? date('M d, Y - h:i A', strtotime($r["deleted_at"])) : '---'; ?></td>
                        <td style="text-align:right;">
                            <a href="?page=<?php echo $page_key; ?>&action=restore&type=package&id=<?php echo $r['id']; ?>" class="btn btn-restore">Restore</a>
                            <a href="?page=<?php echo $page_key; ?>&action=purge&type=package&id=<?php echo $r['id']; ?>" class="btn btn-purge" onclick="return confirm('Permanently delete this package?')">Purge</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="empty-msg">No deleted packages found in trash.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>