<?php
/* ==============================
   OR — On Rent Admin Panel
============================== */
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli("localhost","root","","or_onrent");

/* ACTIONS */
if (isset($_GET['approve'])) { $conn->query("UPDATE listings SET status='approved' WHERE id=".intval($_GET['approve'])); header("Location: admin_panel.php"); exit; }
if (isset($_GET['reject']))  { $conn->query("UPDATE listings SET status='rejected' WHERE id=".intval($_GET['reject']));  header("Location: admin_panel.php"); exit; }
if (isset($_GET['delete_listing'])) { $conn->query("DELETE FROM listings WHERE id=".intval($_GET['delete_listing']));    header("Location: admin_panel.php"); exit; }
if (isset($_GET['delete_user']))    { $conn->query("DELETE FROM owners WHERE id=".intval($_GET['delete_user']));         header("Location: admin_panel.php"); exit; }

$total_listings  = $conn->query("SELECT COUNT(*) c FROM listings")->fetch_assoc()['c'];
$pending_count   = $conn->query("SELECT COUNT(*) c FROM listings WHERE status='pending'")->fetch_assoc()['c'];
$total_owners    = $conn->query("SELECT COUNT(*) c FROM owners")->fetch_assoc()['c'];
$total_bookings  = $conn->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Admin Panel — OR On Rent</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
<style>
:root{--bg:#0a0a0f;--surface:#12121a;--card:#1a1a26;--border:#2a2a3a;--orange:#ff6b1a;--orange-glow:rgba(255,107,26,0.18);--amber:#ffb830;--text:#f0eee8;--muted:#8a8a9a;--white:#ffffff;--green:#2ecc71;--red:#e84444;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;display:flex;min-height:100vh;}
.sidebar{width:240px;min-height:100vh;background:var(--surface);border-right:1px solid var(--border);padding:28px 20px;display:flex;flex-direction:column;gap:6px;position:sticky;top:0;height:100vh;}
.sidebar-logo{font-family:'Bebas Neue',sans-serif;font-size:1.5rem;letter-spacing:2px;color:var(--white);margin-bottom:8px;}
.sidebar-logo span{color:var(--orange);}
.admin-badge{display:inline-block;padding:4px 10px;background:rgba(232,68,68,0.15);border:1px solid rgba(232,68,68,0.3);border-radius:20px;font-family:'Syne',sans-serif;font-size:0.62rem;font-weight:700;color:var(--red);letter-spacing:1px;text-transform:uppercase;margin-bottom:28px;}
.sidebar-label{font-family:'Syne',sans-serif;font-size:0.6rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);padding:0 8px;margin-top:14px;margin-bottom:4px;}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;color:var(--muted);font-family:'Syne',sans-serif;font-size:0.8rem;font-weight:600;cursor:pointer;transition:all .2s;text-decoration:none;border:none;background:none;width:100%;text-align:left;}
.nav-item:hover,.nav-item.active{background:var(--orange-glow);color:var(--orange);}
.nav-item .icon{font-size:1.1rem;}
.badge-count{margin-left:auto;background:var(--red);color:#fff;font-size:0.65rem;font-family:'Syne',sans-serif;font-weight:700;padding:2px 7px;border-radius:10px;}
.main-content{flex:1;padding:36px 40px;overflow-x:hidden;}
.page-title{font-family:'Bebas Neue',sans-serif;font-size:2.4rem;color:var(--white);letter-spacing:1px;margin-bottom:4px;}
.page-sub{font-size:0.83rem;color:var(--muted);margin-bottom:32px;}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:18px;margin-bottom:32px;}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:22px;position:relative;overflow:hidden;}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--orange),var(--amber));}
.stat-card-num{font-family:'Bebas Neue',sans-serif;font-size:2.5rem;color:var(--orange);line-height:1;}
.stat-card-label{font-size:0.75rem;color:var(--muted);margin-top:5px;font-family:'Syne',sans-serif;font-weight:600;letter-spacing:1px;text-transform:uppercase;}
.panel{display:none;}.panel.active{display:block;}
.table-wrap{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:28px;}
.table-head{padding:18px 22px;border-bottom:1px solid var(--border);}
.table-head-title{font-family:'Syne',sans-serif;font-weight:700;font-size:0.88rem;color:var(--white);}
table{width:100%;border-collapse:collapse;}
th{font-family:'Syne',sans-serif;font-size:0.65rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);padding:11px 18px;text-align:left;border-bottom:1px solid var(--border);}
td{padding:12px 18px;font-size:0.82rem;color:var(--text);border-bottom:1px solid rgba(42,42,58,0.5);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
tr:last-child td{border-bottom:none;}
.status-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-family:'Syne',sans-serif;font-size:0.65rem;font-weight:700;letter-spacing:0.5px;}
.status-badge.pending{background:rgba(255,184,48,0.12);color:var(--amber);border:1px solid rgba(255,184,48,0.3);}
.status-badge.approved{background:rgba(46,204,113,0.12);color:var(--green);border:1px solid rgba(46,204,113,0.3);}
.status-badge.rejected{background:rgba(232,68,68,0.12);color:var(--red);border:1px solid rgba(232,68,68,0.3);}
.action-btn{padding:4px 11px;border-radius:6px;font-family:'Syne',sans-serif;font-size:0.68rem;font-weight:700;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-block;margin-right:4px;border:none;}
.action-btn.approve{background:rgba(46,204,113,0.12);color:var(--green);border:1px solid rgba(46,204,113,0.3);}
.action-btn.approve:hover{background:var(--green);color:#fff;}
.action-btn.reject{background:rgba(255,184,48,0.1);color:var(--amber);border:1px solid rgba(255,184,48,0.3);}
.action-btn.reject:hover{background:var(--amber);color:#000;}
.action-btn.del{background:rgba(232,68,68,0.12);color:var(--red);border:1px solid rgba(232,68,68,0.3);}
.action-btn.del:hover{background:var(--red);color:#fff;}
@media(max-width:900px){.sidebar{display:none;}.main-content{padding:20px 14px;}}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo"><span>OR</span> ADMIN</div>
  <div class="admin-badge">⚙️ Admin Panel</div>

  <div class="sidebar-label">Management</div>
  <button class="nav-item active" onclick="showPanel('overview',this)"><span class="icon">📊</span> Overview</button>
  <button class="nav-item" onclick="showPanel('listings',this)"><span class="icon">📋</span> All Listings <span class="badge-count"><?= $pending_count ?></span></button>
  <button class="nav-item" onclick="showPanel('users',this)"><span class="icon">👥</span> All Owners</button>
  <button class="nav-item" onclick="showPanel('bookings',this)"><span class="icon">📅</span> All Bookings</button>

  <div class="sidebar-label">System</div>
  <a class="nav-item" href="index.html"><span class="icon">🌐</span> View Site</a>
  <a class="nav-item" href="admin_logout.php"><span class="icon">🚪</span> Logout</a>
</aside>

<div class="main-content">

  <!-- OVERVIEW -->
  <div class="panel active" id="panel-overview">
    <div class="page-title">Admin Dashboard</div>
    <p class="page-sub">Manage listings, owners, and bookings across the platform.</p>
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-card-num"><?= $total_listings ?></div><div class="stat-card-label">Total Listings</div></div>
      <div class="stat-card"><div class="stat-card-num"><?= $pending_count ?></div><div class="stat-card-label">Pending Approval</div></div>
      <div class="stat-card"><div class="stat-card-num"><?= $total_owners ?></div><div class="stat-card-label">Registered Owners</div></div>
      <div class="stat-card"><div class="stat-card-num"><?= $total_bookings ?></div><div class="stat-card-label">Total Bookings</div></div>
    </div>

    <!-- Pending table quick view -->
    <div class="table-wrap">
      <div class="table-head"><div class="table-head-title">⚠️ Listings Pending Approval</div></div>
      <table>
        <thead><tr><th>Type</th><th>Category</th><th>Owner</th><th>City</th><th>Price</th><th>Action</th></tr></thead>
        <tbody>
        <?php
        $pending = $conn->query("SELECT l.*,o.name as owner_name FROM listings l JOIN owners o ON l.owner_id=o.id WHERE l.status='pending' ORDER BY l.date_added DESC");
        if ($pending && $pending->num_rows > 0):
          while($row = $pending->fetch_assoc()):
        ?>
          <tr>
            <td><?= htmlspecialchars($row['type']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= htmlspecialchars($row['owner_name']) ?></td>
            <td><?= htmlspecialchars($row['city']) ?></td>
            <td>₹<?= htmlspecialchars($row['price']) ?></td>
            <td>
              <a class="action-btn approve" href="?approve=<?= $row['id'] ?>">Approve</a>
              <a class="action-btn reject" href="?reject=<?= $row['id'] ?>">Reject</a>
              <a class="action-btn del" href="?delete_listing=<?= $row['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px;">No pending listings 🎉</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ALL LISTINGS -->
  <div class="panel" id="panel-listings">
    <div class="page-title">All Listings</div>
    <p class="page-sub">Review, approve, reject, and manage all listings.</p>
    <div class="table-wrap">
      <div class="table-head"><div class="table-head-title">All Listings (<?= $total_listings ?>)</div></div>
      <table>
        <thead><tr><th>#</th><th>Type</th><th>Category</th><th>Owner</th><th>City</th><th>Price</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php
        $all = $conn->query("SELECT l.*,o.name as owner_name FROM listings l JOIN owners o ON l.owner_id=o.id ORDER BY l.date_added DESC");
        $i=1;
        if ($all && $all->num_rows > 0):
          while($row = $all->fetch_assoc()):
        ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['type']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= htmlspecialchars($row['owner_name']) ?></td>
            <td><?= htmlspecialchars($row['city']) ?></td>
            <td>₹<?= htmlspecialchars($row['price']) ?></td>
            <td><span class="status-badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
            <td>
              <?php if($row['status']==='pending'): ?>
                <a class="action-btn approve" href="?approve=<?= $row['id'] ?>">✓</a>
                <a class="action-btn reject" href="?reject=<?= $row['id'] ?>">✗</a>
              <?php endif; ?>
              <a class="action-btn del" href="?delete_listing=<?= $row['id'] ?>" onclick="return confirm('Delete?')">Del</a>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:24px;">No listings found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ALL OWNERS/USERS -->
  <div class="panel" id="panel-users">
    <div class="page-title">All Owners</div>
    <p class="page-sub">Registered service owners on the platform.</p>
    <div class="table-wrap">
      <div class="table-head"><div class="table-head-title">Owners (<?= $total_owners ?>)</div></div>
      <table>
        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Mobile</th><th>City</th><th>Area</th><th>Action</th></tr></thead>
        <tbody>
        <?php
        $owners = $conn->query("SELECT * FROM owners ORDER BY id DESC");
        $i=1;
        if ($owners && $owners->num_rows > 0):
          while($row = $owners->fetch_assoc()):
        ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['mobile']) ?></td>
            <td><?= htmlspecialchars($row['city']) ?></td>
            <td><?= htmlspecialchars($row['area']) ?></td>
            <td><a class="action-btn del" href="?delete_user=<?= $row['id'] ?>" onclick="return confirm('Delete this owner and all their listings?')">Delete</a></td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px;">No owners registered.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ALL BOOKINGS -->
  <div class="panel" id="panel-bookings">
    <div class="page-title">All Bookings</div>
    <p class="page-sub">All customer booking requests across the platform.</p>
    <div class="table-wrap">
      <div class="table-head"><div class="table-head-title">Bookings (<?= $total_bookings ?>)</div></div>
      <table>
        <thead><tr><th>#</th><th>Listing</th><th>Category</th><th>Booking Date</th><th>Payment</th></tr></thead>
        <tbody>
        <?php
        $bkgs = $conn->query("SELECT b.*,l.type,l.category FROM bookings b JOIN listings l ON b.listing_id=l.id ORDER BY b.booking_date DESC");
        $i=1;
        if ($bkgs && $bkgs->num_rows > 0):
          while($row = $bkgs->fetch_assoc()):
        ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['type']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= htmlspecialchars($row['booking_date']) ?></td>
            <td><span class="status-badge <?= $row['payment_status']==='paid'?'approved':'pending' ?>"><?= ucfirst($row['payment_status']) ?></span></td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px;">No bookings found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

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
