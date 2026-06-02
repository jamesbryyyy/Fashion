<?php
// Note: No need to include db.php or auth_check.php if this file is called via the main dashboard index.

// --- DELETE GOWN LOGIC (Soft Delete) ---
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    mysqli_query($conn, "UPDATE gowns SET is_deleted = 1, deleted_at = NOW() WHERE id = '$delete_id'");
    echo "<script>alert('🗑️ Gown moved to trash'); window.location='?page=gowns';</script>";
    exit();
}

// --- ADD GOWN LOGIC ---
if (isset($_POST["btnsubmit"])) {
    $gown_code = mysqli_real_escape_string($conn, $_POST["gown_code"]);
    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $description = mysqli_real_escape_string($conn, $_POST["description"]);
    $category = mysqli_real_escape_string($conn, $_POST["category"]);
    $color = mysqli_real_escape_string($conn, $_POST["color"]);
    $size = mysqli_real_escape_string($conn, $_POST["size"]);
    $base_price = $_POST["base_price"];

    $image_name = basename($_FILES["image"]["name"]);
    $image_path = "../asset/" . $image_name;
    move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);

    mysqli_query($conn, "INSERT INTO gowns (name, description, image, base_price, category, created_at, is_deleted) 
                        VALUES ('$name', '$description', '$image_path', '$base_price', '$category', NOW(), 0)");
    
    $gown_id = mysqli_insert_id($conn);

    mysqli_query($conn, "INSERT INTO gown_items (gown_id, size, sku, color, status, created_at) 
                        VALUES ('$gown_id', '$size', '$gown_code', '$color', 'available', NOW())");

    echo "<script>alert('✅ Gown Added Successfully'); window.location='?page=gowns';</script>";
}

// --- UPDATE STATUS LOGIC ---
if(isset($_POST["btnupdate"])){
    $item_id = $_POST["item_id"];
    $status = $_POST["status"];
    $date_from = $_POST["date_from"];
    $date_to = $_POST["date_to"];

    if($status == "available"){
        mysqli_query($conn, "UPDATE gown_items SET status='available', date_from=NULL, date_to=NULL WHERE id='$item_id'");
    } else {
        if(empty($date_from) || empty($date_to)){
            echo "<script>alert('Please select Date Range for non-available status');</script>";
        } else {
            mysqli_query($conn, "UPDATE gown_items SET status='$status', date_from='$date_from', date_to='$date_to' WHERE id='$item_id'");
            echo "<script>alert('Status Updated'); window.location='?page=gowns';</script>";
        }
    }
}
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
    }
    
    h3 { color: var(--primary-blue); font-weight: 600; border-left: 5px solid var(--accent-blue); padding-left: 15px; margin-bottom: 25px; }

    .admin-card { background: var(--white); padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-bottom: 40px; }
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
    .form-group { display: flex; flex-direction: column; }
    label { font-size: 0.75rem; color: #6b7280; margin-bottom: 5px; text-transform: uppercase; font-weight: bold; }
    input, select { background: var(--white); border: 1px solid var(--border); padding: 10px; border-radius: 5px; }
    
    .btn-add { grid-column: 1 / -1; background: var(--primary-blue); color: white; font-weight: bold; padding: 12px; border: none; border-radius: 5px; cursor: pointer; text-transform: uppercase; margin-top: 10px; }
    .btn-add:hover { background: var(--accent-blue); }

    .table-container { background: var(--white); border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; }
    table { width: 100%; border-collapse: collapse; }
    th { background: var(--primary-blue); color: white; text-align: left; padding: 15px; font-size: 0.8rem; text-transform: uppercase; }
    td { padding: 15px; border-bottom: 1px solid var(--border); font-size: 0.9rem; }
    tr:hover { background-color: #f9fafb; }
    
    .gown-img { width: 50px; height: 70px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border); }
    .status-select { padding: 6px; font-size: 0.8rem; border: 1px solid var(--accent-blue); border-radius: 4px; }
    
    .btn-update { background: var(--white); border: 1px solid var(--primary-blue); color: var(--primary-blue); padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: 600; }
    .btn-update:hover { background: var(--primary-blue); color: white; }
    
    .btn-delete { background: #fee2e2; border: 1px solid var(--danger); color: var(--danger); padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: 600; text-decoration: none; font-size: 0.8rem; }
    .btn-delete:hover { background: var(--danger); color: white; }

    .sku-tag { font-size: 0.75rem; background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 99px; display: inline-block; }
    .actions-cell { display: flex; gap: 10px; align-items: center; }
</style>

<div class="container">
    <h3>Add New Inventory Item</h3>
    <div class="admin-card">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>SKU / Gown Code</label>
                    <input type="text" name="gown_code" required>
                </div>
                <div class="form-group">
                    <label>Gown Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option>Bridal</option>
                        <option>Evening Wear</option>
                        <option>Debut</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Size</label>
                    <input type="text" name="size">
                </div>
                <div class="form-group">
                    <label>Color</label>
                    <input type="text" name="color">
                </div>
                <div class="form-group">
                    <label>Base Price (₱)</label>
                    <input type="number" name="base_price" step="0.01">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Description</label>
                    <input type="text" name="description">
                </div>
                <div class="form-group">
                    <label>Upload Photo</label>
                    <input type="file" name="image" required>
                </div>
                <button type="submit" name="btnsubmit" class="btn-add">Add Gown to System</button>
            </div>
        </form>
    </div>

    <h3>Current Gown Inventory</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product Details</th>
                    <th>Category</th>
                    <th>Size/Color</th>
                    <th>Rental Price</th>
                    <th>Status</th>
                    <th>Dates</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = mysqli_query($conn, "SELECT g.id AS gown_id, g.name, g.description, g.image, g.base_price, g.category,
                                                gi.id AS item_id, gi.size, gi.sku, gi.color, gi.status, gi.date_from, gi.date_to
                                         FROM gowns g
                                         LEFT JOIN gown_items gi ON g.id = gi.gown_id
                                         WHERE g.is_deleted = 0
                                         ORDER BY g.id DESC");

                while ($r = mysqli_fetch_array($q)) {
                ?>
                <tr>
                    <form method="POST">
                        <td><img src="<?php echo $r["image"]; ?>" class="gown-img"></td>
                        <td>
                            <div style="font-weight: 600;"><?php echo $r["name"]; ?></div>
                            <span class="sku-tag"><?php echo $r["sku"]; ?></span>
                        </td>
                        <td><?php echo $r["category"]; ?></td>
                        <td><?php echo $r["size"]; ?> / <?php echo $r["color"]; ?></td>
                        <td style="font-weight: bold; color: var(--primary-blue);">
                            ₱<?php echo number_format($r["base_price"], 2); ?>
                        </td>
                        <td>
                            <input type="hidden" name="item_id" value="<?php echo $r["item_id"]; ?>">
                            <select name="status" class="status-select">
                                <option value="available" <?php if($r["status"]=="available") echo "selected"; ?>>Available</option>
                                <option value="cleaning" <?php if($r["status"]=="cleaning") echo "selected"; ?>>Cleaning</option>
                                <option value="maintenance" <?php if($r["status"]=="maintenance") echo "selected"; ?>>Maintenance</option>
                                <option value="rented" <?php if($r["status"]=="rented") echo "selected"; ?>>Rented</option>
                            </select>
                        </td>
                        <td>
                            <div style="display:flex; gap:3px; flex-direction:column">
                                <input type="date" name="date_from" value="<?php echo $r["date_from"]; ?>" style="padding:2px; font-size:0.7rem;">
                                <input type="date" name="date_to" value="<?php echo $r["date_to"]; ?>" style="padding:2px; font-size:0.7rem;">
                            </div>
                        </td>
                        <td>
                            <div class="actions-cell">
                                <button type="submit" name="btnupdate" class="btn-update">Update</button>
                                <a href="?page=gowns&delete_id=<?php echo $r['gown_id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this?')">
                                   Delete
                                </a>
                            </div>
                        </td>
                    </form>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>