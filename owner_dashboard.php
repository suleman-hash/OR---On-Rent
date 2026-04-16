<?php
/* ==============================
   OR — On Rent Owner Dashboard
============================== */
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: owner_login.php");
    exit;
}

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "or_onrent";
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

$owner_id = $_SESSION['owner_id'];
$owner_name = $_SESSION['owner_name'];

/* ADD LISTING */
$add_msg = "";
if (isset($_POST['add_listing'])) {
    $category     = $conn->real_escape_string($_POST['category']);
    $type         = $conn->real_escape_string($_POST['type']);
    $price        = $conn->real_escape_string($_POST['price']);
    $pricing_type = $conn->real_escape_string($_POST['pricing_type']);
    $description  = $conn->real_escape_string($_POST['description']);
    $city         = $conn->real_escape_string($_POST['city']);
    $area         = $conn->real_escape_string($_POST['area']);
    $driver       = isset($_POST['driver_included']) ? 1 : 0;
    $image        = "";

    if (!empty($_FILES['image']['name'])) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = $upload_dir . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    $sql = "INSERT INTO listings (owner_id,category,type,price,pricing_type,description,image,city,area,driver_included,status,date_added)
            VALUES ('$owner_id','$category','$type','$price','$pricing_type','$description','$image','$city','$area','$driver','pending',NOW())";
    if ($conn->query($sql) === TRUE) {
        $add_msg = "success";
    } else {
        $add_msg = "error";
    }
}

/* DELETE LISTING */
if (isset($_GET['delete'])) {
    $lid = intval($_GET['delete']);
    $conn->query("DELETE FROM listings WHERE id=$lid AND owner_id=$owner_id");
    header("Location: owner_dashboard.php");
    exit;
}

/* FETCH LISTINGS */
$listings = $conn->query("SELECT * FROM listings WHERE owner_id=$owner_id ORDER BY date_added DESC");
$bookings = $conn->query("SELECT b.*, l.type, l.category FROM bookings b JOIN listings l ON b.listing_id=l.id WHERE l.owner_id=$owner_id ORDER BY b.booking_date DESC LIMIT 10");
$total_listings = $conn->query("SELECT COUNT(*) as c FROM listings WHERE owner_id=$owner_id")->fetch_assoc()['c'];
$pending_count  = $conn->query("SELECT COUNT(*) as c FROM listings WHERE owner_id=$owner_id AND status='pending'")->fetch_assoc()['c'];
$booking_count  = $conn->query("SELECT COUNT(*) as c FROM bookings b JOIN listings l ON b.listing_id=l.id WHERE l.owner_id=$owner_id")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Owner Dashboard — OR On Rent</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
<style>
:root{--bg:#0a0a0f;--surface:#12121a;--card:#1a1a26;--border:#2a2a3a;--orange:#ff6b1a;--orange-glow:rgba(255,107,26,0.18);--amber:#ffb830;--text:#f0eee8;--muted:#8a8a9a;--white:#ffffff;--green:#2ecc71;--red:#e84444;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;display:flex;min-height:100vh;}

/* SIDEBAR */
.sidebar{width:240px;min-height:100vh;background:var(--surface);border-right:1px solid var(--border);padding:28px 20px;display:flex;flex-direction:column;gap:8px;position:sticky;top:0;height:100vh;overflow-y:auto;}
.sidebar-logo{font-family:'Bebas Neue',sans-serif;font-size:1.6rem;letter-spacing:2px;color:var(--white);margin-bottom:32px;padding-bottom:20px;border-bottom:1px solid var(--border);}
.sidebar-logo span{color:var(--orange);}
.sidebar-label{font-family:'Syne',sans-serif;font-size:0.62rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);padding:0 8px;margin-top:16px;margin-bottom:4px;}
.nav-item{display:flex;align-items:center;gap:10px;padding:11px 12px;border-radius:8px;color:var(--muted);font-family:'Syne',sans-serif;font-size:0.82rem;font-weight:600;letter-spacing:0.5px;cursor:pointer;transition:all .2s;text-decoration:none;border:none;background:none;width:100%;text-align:left;}
.nav-item:hover,.nav-item.active{background:var(--orange-glow);color:var(--orange);}
.nav-item .icon{font-size:1.1rem;}
.sidebar-footer{margin-top:auto;padding-top:20px;border-top:1px solid var(--border);}
.owner-pill{display:flex;align-items:center;gap:10px;padding:12px;}
.owner-avatar{width:38px;height:38px;border-radius:50%;background:var(--orange-glow);border:1px solid rgba(255,107,26,0.3);display:flex;align-items:center;justify-content:center;font-size:1.1rem;}
.owner-name{font-family:'Syne',sans-serif;font-size:0.82rem;font-weight:700;color:var(--white);}
.owner-role{font-size:0.7rem;color:var(--muted);}

/* MAIN */
.main-content{flex:1;padding:36px 40px;overflow-x:hidden;}
.page-header{margin-bottom:36px;}
.page-title{font-family:'Bebas Neue',sans-serif;font-size:2.5rem;color:var(--white);letter-spacing:1px;}
.page-sub{font-size:0.85rem;color:var(--muted);margin-top:4px;}

/* PANELS */
.panel{display:none;}
.panel.active{display:block;}

/* STATS GRID */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:36px;}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:24px;position:relative;overflow:hidden;}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--orange),var(--amber));}
.stat-card-num{font-family:'Bebas Neue',sans-serif;font-size:2.8rem;color:var(--orange);line-height:1;}
.stat-card-label{font-size:0.78rem;color:var(--muted);margin-top:6px;font-family:'Syne',sans-serif;font-weight:600;letter-spacing:1px;text-transform:uppercase;}

/* TABLE */
.table-wrap{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:28px;}
.table-head{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.table-head-title{font-family:'Syne',sans-serif;font-weight:700;font-size:0.9rem;color:var(--white);}
table{width:100%;border-collapse:collapse;}
th{font-family:'Syne',sans-serif;font-size:0.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);padding:12px 20px;text-align:left;border-bottom:1px solid var(--border);}
td{padding:14px 20px;font-size:0.84rem;color:var(--text);border-bottom:1px solid rgba(42,42,58,0.5);}
tr:last-child td{border-bottom:none;}
tr:hover td{background:rgba(255,107,26,0.03);}
.status-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-family:'Syne',sans-serif;font-size:0.68rem;font-weight:700;letter-spacing:0.5px;}
.status-badge.pending{background:rgba(255,184,48,0.12);color:var(--amber);border:1px solid rgba(255,184,48,0.3);}
.status-badge.approved{background:rgba(46,204,113,0.12);color:var(--green);border:1px solid rgba(46,204,113,0.3);}
.status-badge.rejected{background:rgba(232,68,68,0.12);color:var(--red);border:1px solid rgba(232,68,68,0.3);}
.action-btn{padding:5px 12px;border-radius:6px;font-family:'Syne',sans-serif;font-size:0.72rem;font-weight:700;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-block;margin-right:6px;border:none;}
.action-btn.del{background:rgba(232,68,68,0.12);color:var(--red);border:1px solid rgba(232,68,68,0.3);}
.action-btn.del:hover{background:var(--red);color:#fff;}

/* ADD LISTING FORM */
.form-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:32px;}
.form-card-title{font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:var(--white);letter-spacing:1px;margin-bottom:6px;}
.form-card-sub{font-size:0.8rem;color:var(--muted);margin-bottom:28px;}
.form-group{margin-bottom:20px;}
.form-label{display:block;font-family:'Syne',sans-serif;font-size:0.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);margin-bottom:8px;}
.form-input{width:100%;padding:12px 16px;background:var(--surface);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:0.9rem;outline:none;transition:border-color .25s;}
.form-input::placeholder{color:var(--muted);}
.form-input:focus{border-color:var(--orange);}
.form-select{appearance:none;cursor:pointer;}
.form-textarea{resize:vertical;min-height:100px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.form-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;}
.checkbox-group{display:flex;align-items:center;gap:10px;background:var(--surface);border:1px solid var(--border);padding:12px 16px;border-radius:8px;cursor:pointer;}
.checkbox-group input{accent-color:var(--orange);width:16px;height:16px;}
.checkbox-label{font-family:'Syne',sans-serif;font-size:0.82rem;font-weight:600;color:var(--text);}
.submit-btn{padding:14px 36px;background:var(--orange);color:#fff;border:none;border-radius:8px;font-family:'Syne',sans-serif;font-weight:700;font-size:0.9rem;letter-spacing:1px;cursor:pointer;transition:all .25s;box-shadow:0 4px 20px rgba(255,107,26,0.3);}
.submit-btn:hover{background:#e05a10;transform:translateY(-1px);}

/* ALERT */
.alert{padding:13px 18px;border-radius:8px;font-size:0.82rem;font-family:'Syne',sans-serif;font-weight:600;margin-bottom:24px;display:flex;align-items:center;gap:8px;}
.alert.success{background:rgba(46,204,113,0.12);border:1px solid rgba(46,204,113,0.3);color:var(--green);}
.alert.error{background:rgba(232,68,68,0.12);border:1px solid rgba(232,68,68,0.3);color:var(--red);}

@media(max-width:900px){.sidebar{display:none;}.main-content{padding:24px 16px;}.form-row,.form-row-3{grid-template-columns:1fr;}}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo"><span>OR</span> OWNER</div>

  <div class="sidebar-label">Main</div>
  <button class="nav-item active" onclick="showPanel('overview',this)"><span class="icon">📊</span> Overview</button>
  <button class="nav-item" onclick="showPanel('listings',this)"><span class="icon">📋</span> My Listings</button>
  <button class="nav-item" onclick="showPanel('add',this)"><span class="icon">➕</span> Add Listing</button>
  <button class="nav-item" onclick="showPanel('bookings',this)"><span class="icon">📅</span> Bookings</button>

  <div class="sidebar-label">Account</div>
  <button class="nav-item" onclick="showPanel('profile',this)"><span class="icon">👤</span> My Profile</button>
  <a class="nav-item" href="index.html"><span class="icon">🌐</span> View Site</a>
  <a class="nav-item" href="owner_logout.php"><span class="icon">🚪</span> Logout</a>

  <div class="sidebar-footer">
    <div class="owner-pill">
      <div class="owner-avatar">👷</div>
      <div>
        <div class="owner-name"><?= htmlspecialchars($owner_name) ?></div>
        <div class="owner-role">Service Owner</div>
      </div>
    </div>
  </div>
</aside>

<!-- MAIN CONTENT -->
<div class="main-content">

  <!-- ══ OVERVIEW ══ -->
  <div class="panel active" id="panel-overview">
    <div class="page-header">
      <div class="page-title">Dashboard Overview</div>
      <div class="page-sub">Welcome back, <?= htmlspecialchars($owner_name) ?>! Here's a summary of your account.</div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-card-num"><?= $total_listings ?></div>
        <div class="stat-card-label">Total Listings</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-num"><?= $pending_count ?></div>
        <div class="stat-card-label">Pending Approval</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-num"><?= $booking_count ?></div>
        <div class="stat-card-label">Total Bookings</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-num">₹0</div>
        <div class="stat-card-label">Total Earnings</div>
      </div>
    </div>

    <div class="table-wrap">
      <div class="table-head">
        <span class="table-head-title">Recent Listings</span>
        <button class="action-btn" onclick="showPanel('add',null)" style="background:var(--orange-glow);color:var(--orange);border:1px solid rgba(255,107,26,0.3);">+ Add New</button>
      </div>
      <table>
        <thead><tr><th>Type</th><th>Category</th><th>Price</th><th>City</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php
        $conn->query("SELECT * FROM listings WHERE owner_id=$owner_id ORDER BY date_added DESC LIMIT 5")->data_seek(0);
        $recent = $conn->query("SELECT * FROM listings WHERE owner_id=$owner_id ORDER BY date_added DESC LIMIT 5");
        if ($recent && $recent->num_rows > 0):
          while($row = $recent->fetch_assoc()):
        ?>
          <tr>
            <td><?= htmlspecialchars($row['type']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td>₹<?= htmlspecialchars($row['price']) ?></td>
            <td><?= htmlspecialchars($row['city']) ?></td>
            <td><span class="status-badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
            <td><a class="action-btn del" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this listing?')">Delete</a></td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px;">No listings yet. <a href="#" onclick="showPanel('add',null)" style="color:var(--orange);">Add your first listing →</a></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ══ LISTINGS ══ -->
  <div class="panel" id="panel-listings">
    <div class="page-header">
      <div class="page-title">My Listings</div>
      <div class="page-sub">All your listed services and vehicles.</div>
    </div>
    <div class="table-wrap">
      <div class="table-head">
        <span class="table-head-title">All Listings (<?= $total_listings ?>)</span>
      </div>
      <table>
        <thead><tr><th>#</th><th>Type</th><th>Category</th><th>Pricing Type</th><th>Price</th><th>City/Area</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php
        $listings->data_seek(0);
        if ($listings->num_rows > 0):
          $i = 1;
          while($row = $listings->fetch_assoc()):
        ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['type']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= htmlspecialchars($row['pricing_type']) ?></td>
            <td>₹<?= htmlspecialchars($row['price']) ?></td>
            <td><?= htmlspecialchars($row['city']) ?> / <?= htmlspecialchars($row['area']) ?></td>
            <td><span class="status-badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
            <td><a class="action-btn del" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:30px;">No listings found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ══ ADD LISTING ══ -->
  <div class="panel" id="panel-add">
    <div class="page-header">
      <div class="page-title">Add New Listing</div>
      <div class="page-sub">Fill in the details of your service or vehicle to list it on OR.</div>
    </div>

    <?php if ($add_msg === 'success'): ?>
      <div class="alert success">✅ Listing submitted successfully! It will be visible after admin approval.</div>
    <?php elseif ($add_msg === 'error'): ?>
      <div class="alert error">⚠️ Error submitting listing. Please try again.</div>
    <?php endif; ?>

    <div class="form-card">
      <div class="form-card-title">Service Details</div>
      <p class="form-card-sub">Complete all fields for faster approval.</p>

      <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Category</label>
            <select class="form-input form-select" name="category" required>
              <option value="">Select Category</option>
              <option>Labour &amp; Services</option>
              <option>Vehicles &amp; Services</option>
              <option>Marriage &amp; Services</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Service / Vehicle Type</label>
            <input class="form-input" type="text" name="type" placeholder="e.g. Electrician, Honda City, Caterer" required/>
          </div>
        </div>

        <div class="form-row-3">
          <div class="form-group">
            <label class="form-label">Price (₹)</label>
            <input class="form-input" type="number" name="price" placeholder="0" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Pricing Type</label>
            <select class="form-input form-select" name="pricing_type" required>
              <option value="">Select</option>
              <option>Half Day</option>
              <option>Full Day</option>
              <option>Hourly</option>
              <option>Per KM / Distance</option>
              <option>Per Plate</option>
              <option>Per Event</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Upload Image</label>
            <input class="form-input" type="file" name="image" accept="image/*" style="padding:9px;"/>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">City</label>
            <select class="form-input form-select" name="city" required>
              <option value="">Select City</option>
              <option>Pune</option><option>Mumbai</option>
              <option>Nashik</option><option>Nagpur</option><option>Aurangabad</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Area / Pincode</label>
            <input class="form-input" type="text" name="area" placeholder="e.g. Pimpri, 411017" required/>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-input form-textarea" name="description" placeholder="Describe your service, experience, availability..." required></textarea>
        </div>

        <div class="form-group">
          <label class="checkbox-group">
            <input type="checkbox" name="driver_included"/>
            <span class="checkbox-label">Driver / Operator Included in Price</span>
          </label>
        </div>

        <button class="submit-btn" type="submit" name="add_listing">Submit Listing →</button>
      </form>
    </div>
  </div>

  <!-- ══ BOOKINGS ══ -->
  <div class="panel" id="panel-bookings">
    <div class="page-header">
      <div class="page-title">Bookings</div>
      <div class="page-sub">Bookings received for your listings.</div>
    </div>
    <div class="table-wrap">
      <div class="table-head"><span class="table-head-title">Recent Bookings</span></div>
      <table>
        <thead><tr><th>#</th><th>Service</th><th>Category</th><th>Date</th><th>Payment</th></tr></thead>
        <tbody>
        <?php
        if ($bookings && $bookings->num_rows > 0):
          $i = 1;
          while($row = $bookings->fetch_assoc()):
        ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['type']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= htmlspecialchars($row['booking_date']) ?></td>
            <td><span class="status-badge <?= $row['payment_status'] === 'paid' ? 'approved' : 'pending' ?>"><?= ucfirst($row['payment_status']) ?></span></td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:30px;">No bookings yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ══ PROFILE ══ -->
  <div class="panel" id="panel-profile">
    <div class="page-header">
      <div class="page-title">My Profile</div>
      <div class="page-sub">Your owner account details.</div>
    </div>
    <div class="form-card" style="max-width:500px;">
      <?php
      $owner = $conn->query("SELECT * FROM owners WHERE id=$owner_id")->fetch_assoc();
      ?>
      <div style="display:flex;flex-direction:column;gap:16px;">
        <?php foreach(['name'=>'Full Name','email'=>'Email','mobile'=>'Mobile','city'=>'City','area'=>'Area'] as $k=>$label): ?>
        <div>
          <div class="form-label"><?= $label ?></div>
          <div style="padding:12px 16px;background:var(--surface);border:1px solid var(--border);border-radius:8px;font-size:0.9rem;">
            <?= htmlspecialchars($owner[$k] ?? '—') ?>
          </div>
        </div>
        <?php endforeach; ?>
        <a href="owner_login.php" style="display:inline-block;padding:12px 28px;background:rgba(232,68,68,0.12);border:1px solid rgba(232,68,68,0.3);color:var(--red);font-family:'Syne',sans-serif;font-weight:700;font-size:0.82rem;letter-spacing:1px;border-radius:8px;text-decoration:none;text-align:center;">Logout</a>
      </div>
    </div>
  </div>

</div><!-- /main-content -->

<script>
function showPanel(id, btn) {
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
  document.getElementById('panel-' + id).classList.add('active');
  if (btn) {
    document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  }
}
</script>
</body>
</html>
