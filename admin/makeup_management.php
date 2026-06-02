<?php
// We use $conn from your main dashboard file. 
// No need to connect again or include auth_check here.

// --- SOFT DELETE LOGIC ---
if(isset($_GET['delete'])){
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    // Update the flag instead of deleting the row
    mysqli_query($conn, "UPDATE makeup_artists SET is_deleted = 1 WHERE id='$id'");
    echo "<script>alert('Artist removed from active list'); window.location='?page=makeup_management';</script>";
    exit();
}

// --- SAVE LOGIC ---
if(isset($_POST['save'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $specialty = mysqli_real_escape_string($conn, $_POST['specialty']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $price = $_POST['price'];

    $image_name = basename($_FILES['image']['name']);
    $image_path = "../asset/" . $image_name;

    if(move_uploaded_file($_FILES['image']['tmp_name'], $image_path)){
        mysqli_query($conn,"INSERT INTO makeup_artists (name, specialty, contact, price, image, is_deleted) 
                            VALUES ('$name', '$specialty', '$contact', '$price', '$image_path', 0)");
        echo "<script>alert('Artist Profile Saved'); window.location='?page=makeup_management';</script>";
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
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 20px; 
    }

    .form-group { display: flex; flex-direction: column; }

    label { 
        font-size: 0.75rem; 
        color: #6b7280; 
        margin-bottom: 5px; 
        text-transform: uppercase; 
        font-weight: bold;
    }

    input { 
        background: var(--white); 
        border: 1px solid var(--border); 
        color: var(--text-dark); 
        padding: 10px; 
        border-radius: 5px; 
    }

    input:focus { border-color: var(--accent-blue); outline: none; }

    .btn-save { 
        background: var(--primary-blue); 
        color: white; 
        font-weight: bold; 
        padding: 12px 30px; 
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
        font-size: 0.9rem; 
    }

    .artist-img { 
        width: 50px; 
        height: 50px; 
        object-fit: cover; 
        border-radius: 50%; 
        border: 2px solid var(--accent-blue); 
    }

    .price-tag { color: var(--primary-blue); font-weight: bold; }
    
    .btn-delete {
        color: #ef4444;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: bold;
    }
    .btn-delete:hover { text-decoration: underline; }
</style>

<div class="container">
    <h2>Makeup Artist Management</h2>

    <!-- ADD ARTIST FORM -->
    <div class="admin-card">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>Artist Name</label>
                    <input type="text" name="name" placeholder="Full Name" required>
                </div>
                <div class="form-group">
                    <label>Specialty</label>
                    <input type="text" name="specialty" placeholder="e.g. Bridal, Editorial">
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact" placeholder="0912 345 6789">
                </div>
                <div class="form-group">
                    <label>Service Price (₱)</label>
                    <input type="number" step="0.01" name="price" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Profile Picture</label>
                    <input type="file" name="image" required>
                </div>
            </div>
            <button type="submit" name="save" class="btn-save">Register Artist</button>
        </form>
    </div>

    <!-- ARTIST LIST -->
    <h3>Available Artists</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Specialty</th>
                    <th>Contact</th>
                    <th>Rate</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Filter to only show artists where is_deleted is 0
                $q = mysqli_query($conn, "SELECT * FROM makeup_artists WHERE is_deleted = 0 ORDER BY id DESC");
                while($r = mysqli_fetch_assoc($q)){
                ?>
                <tr>
                    <td><img src="<?php echo $r['image']; ?>" class="artist-img"></td>
                    <td style="font-weight:600;"><?php echo $r['name']; ?></td>
                    <td><?php echo $r['specialty']; ?></td>
                    <td><?php echo $r['contact']; ?></td>
                    <td class="price-tag">₱<?php echo number_format($r['price'], 2); ?></td>
                    <td>
                        <a href="?page=makeup_management&delete=<?php echo $r['id']; ?>" class="btn-delete" onclick="return confirm('Remove this artist?')">Remove</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>