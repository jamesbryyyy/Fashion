<?php
// We use $conn from your main dashboard file. 
// No need to connect again or include auth_check here.

// --- SOFT DELETE LOGIC ---
if(isset($_GET['delete'])){
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    // Update the flag instead of deleting the row
    mysqli_query($conn, "UPDATE packages SET is_deleted = 1 WHERE id='$id'");
    echo "<script>alert('Package moved to trash'); window.location='?page=package_management';</script>";
    exit();
}

// --- SAVE LOGIC ---
if(isset($_POST['save'])){
    $package_name = mysqli_real_escape_string($conn, $_POST['package_name']);
    $gown_id = $_POST['gown_id'];
    $makeup_artist_id = $_POST['makeup_artist_id'];
    $package_price = $_POST['package_price'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $image_name = basename($_FILES['image']['name']);
    $image_path = "../asset/" . $image_name;

    if(move_uploaded_file($_FILES['image']['tmp_name'], $image_path)){
        mysqli_query($conn,"INSERT INTO packages (package_name, gown_id, makeup_artist_id, package_price, description, image, is_deleted) 
                            VALUES ('$package_name', '$gown_id', '$makeup_artist_id', '$package_price', '$description', '$image_path', 0)");
        echo "<script>alert('Bundle Package Created!'); window.location='?page=package_management';</script>";
        exit();
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
    }
    
    h2, h3 { 
        color: var(--primary-blue); 
        font-weight: 600; 
        border-left: 5px solid var(--accent-blue);
        padding-left: 15px;
        margin-bottom: 25px;
    }

    .admin-card { 
        background: var(--white); 
        padding: 30px; 
        border-radius: 8px; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px; 
    }

    .form-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
        gap: 20px; 
    }

    .form-group { display: flex; flex-direction: column; }
    .form-group.full-width { grid-column: 1 / -1; }

    label { 
        font-size: 0.75rem; 
        color: #6b7280; 
        margin-bottom: 5px; 
        text-transform: uppercase; 
        font-weight: bold;
    }

    input, select, textarea { 
        background: var(--white); 
        border: 1px solid var(--border); 
        color: var(--text-dark); 
        padding: 10px; 
        border-radius: 5px; 
        font-family: inherit;
    }

    textarea { height: 80px; resize: none; }
    input:focus, select:focus, textarea:focus { border-color: var(--accent-blue); outline: none; }

    .btn-save { 
        background: var(--primary-blue); 
        color: white; 
        font-weight: bold; 
        padding: 12px 40px; 
        border: none; 
        border-radius: 5px; 
        cursor: pointer; 
        text-transform: uppercase;
        margin-top: 20px;
        transition: 0.3s;
    }
    .btn-save:hover { background: var(--accent-blue); }

    .table-container { 
        background: var(--white); 
        border-radius: 8px; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
        overflow: hidden;
    }

    table { width: 100%; border-collapse: collapse; }

    th { 
        background: var(--primary-blue); 
        color: white; 
        text-align: left; 
        padding: 15px; 
        font-size: 0.8rem; 
        text-transform: uppercase; 
    }

    td { 
        padding: 15px; 
        border-bottom: 1px solid var(--border); 
        font-size: 0.85rem; 
    }

    .package-img { 
        width: 60px; 
        height: 60px; 
        object-fit: cover; 
        border-radius: 6px; 
    }

    .price-tag { color: var(--primary-blue); font-weight: bold; font-size: 1rem; }
    
    .badge {
        background: #dbeafe;
        color: #1e40af;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        display: inline-block;
        margin-top: 4px;
    }

    .btn-delete {
        color: #ef4444;
        text-decoration: none;
        font-weight: bold;
    }
</style>

<div class="container">
    <h2>Package Management</h2>

    <!-- CREATE PACKAGE FORM -->
    <div class="admin-card">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>Package Name</label>
                    <input type="text" name="package_name" placeholder="e.g. Royal Wedding Bundle" required>
                </div>

                <div class="form-group">
                    <label>Select Gown</label>
                    <select name="gown_id" required>
                        <option value="">-- Choose Gown --</option>
                        <?php
                        // Only show gowns that are not deleted
                        $g = mysqli_query($conn,"SELECT id, name FROM gowns WHERE is_deleted = 0");
                        while($gr=mysqli_fetch_assoc($g)){
                            echo "<option value='".$gr['id']."'>".$gr['name']."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Select Makeup Artist</label>
                    <select name="makeup_artist_id" required>
                        <option value="">-- Choose Artist --</option>
                        <?php
                        // Only show artists that are not deleted
                        $m = mysqli_query($conn,"SELECT id, name FROM makeup_artists WHERE is_deleted = 0");
                        while($mr=mysqli_fetch_assoc($m)){
                            echo "<option value='".$mr['id']."'>".$mr['name']."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Package Price (₱)</label>
                    <input type="number" step="0.01" name="package_price" placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label>Package Cover Image</label>
                    <input type="file" name="image" required>
                </div>

                <div class="form-group full-width">
                    <label>Package Description</label>
                    <textarea name="description" placeholder="What's included in this bundle?"></textarea>
                </div>
            </div>
            <button type="submit" name="save" class="btn-save">Create Bundle</button>
        </form>
    </div>

    <!-- PACKAGE LIST -->
    <h3>Active Packages</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Package Details</th>
                    <th>Includes</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Only select packages where p.is_deleted = 0
                $q = mysqli_query($conn, "
                    SELECT p.*, g.name AS gname, m.name AS mname 
                    FROM packages p 
                    JOIN gowns g ON p.gown_id = g.id 
                    JOIN makeup_artists m ON p.makeup_artist_id = m.id 
                    WHERE p.is_deleted = 0
                    ORDER BY p.id DESC
                ");
                while($r = mysqli_fetch_assoc($q)){
                ?>
                <tr>
                    <td><img src="<?php echo $r['image']; ?>" class="package-img"></td>
                    <td>
                        <div style="font-weight:600; font-size:1rem;"><?php echo $r['package_name']; ?></div>
                        <div style="color:#666; font-size:0.75rem;"><?php echo $r['description']; ?></div>
                    </td>
                    <td>
                        <span class="badge">👗 <?php echo $r['gname']; ?></span><br>
                        <span class="badge">💄 <?php echo $r['mname']; ?></span>
                    </td>
                    <td class="price-tag">₱<?php echo number_format($r['package_price'], 2); ?></td>
                    <td>
                        <a href="?page=package_management&delete=<?php echo $r['id']; ?>" class="btn-delete" onclick="return confirm('Delete this package?')">Delete</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>