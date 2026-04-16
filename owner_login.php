<?php
/* ==============================
   OR — On Rent Owner Login System
============================== */

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "or_onrent";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

$error_msg = "";
$success_msg = "";

/* ---------- REGISTRATION ---------- */
if (isset($_POST['register'])) {
    $name    = $conn->real_escape_string(trim($_POST['name']));
    $email   = $conn->real_escape_string(trim($_POST['email']));
    $mobile  = $conn->real_escape_string(trim($_POST['mobile']));
    $city    = $conn->real_escape_string(trim($_POST['city']));
    $area    = $conn->real_escape_string(trim($_POST['area']));
    $pass    = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->query("SELECT id FROM owners WHERE email='$email'");
    if ($check->num_rows > 0) {
        $error_msg = "Email already registered. Please login.";
    } else {
        $sql = "INSERT INTO owners (name, email, mobile, password, city, area)
                VALUES ('$name','$email','$mobile','$pass','$city','$area')";
        if ($conn->query($sql) === TRUE) {
            $success_msg = "Registration successful! Please login.";
        } else {
            $error_msg = "Error: " . $conn->error;
        }
    }
}

/* ---------- LOGIN ---------- */
if (isset($_POST['login'])) {
    $email = $conn->real_escape_string(trim($_POST['email']));
    $pass  = $_POST['password'];

    $result = $conn->query("SELECT * FROM owners WHERE email='$email'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['owner_id']   = $row['id'];
            $_SESSION['owner_name'] = $row['name'];
            header("Location: owner_dashboard.php");
            exit;
        } else {
            $error_msg = "Invalid password. Please try again.";
        }
    } else {
        $error_msg = "No account found with this email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Owner Login — OR On Rent</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
<style>
:root {
  --bg:#0a0a0f; --surface:#12121a; --card:#1a1a26; --border:#2a2a3a;
  --orange:#ff6b1a; --orange-glow:rgba(255,107,26,0.18); --amber:#ffb830;
  --text:#f0eee8; --muted:#8a8a9a; --white:#ffffff; --red:#e84444;
  --green:#2ecc71;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;flex-direction:column;}

/* NAV */
nav{background:rgba(10,10,15,0.95);border-bottom:1px solid var(--border);padding:16px 40px;display:flex;align-items:center;justify-content:space-between;}
.nav-logo{font-family:'Bebas Neue',sans-serif;font-size:1.8rem;letter-spacing:2px;color:var(--white);text-decoration:none;}
.nav-logo span{color:var(--orange);}
.nav-back{font-family:'Syne',sans-serif;font-size:0.78rem;font-weight:700;letter-spacing:1px;color:var(--muted);text-decoration:none;transition:color .2s;}
.nav-back:hover{color:var(--orange);}

/* MAIN */
main{flex:1;display:flex;align-items:center;justify-content:center;padding:60px 20px;position:relative;overflow:hidden;}
main::before{content:'';position:absolute;top:-200px;right:-200px;width:600px;height:600px;background:radial-gradient(circle,rgba(255,107,26,0.12) 0%,transparent 65%);pointer-events:none;}
main::after{content:'';position:absolute;bottom:-150px;left:-150px;width:500px;height:500px;background:radial-gradient(circle,rgba(255,184,48,0.06) 0%,transparent 65%);pointer-events:none;}

/* TABS CONTAINER */
.auth-container{width:100%;max-width:480px;position:relative;z-index:1;}
.auth-header{text-align:center;margin-bottom:40px;}
.auth-logo{font-family:'Bebas Neue',sans-serif;font-size:3rem;letter-spacing:3px;color:var(--white);margin-bottom:8px;}
.auth-logo span{color:var(--orange);}
.auth-tagline{font-size:0.82rem;color:var(--muted);letter-spacing:1px;}

/* TABS */
.tab-switch{display:flex;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:4px;margin-bottom:32px;}
.tab-btn{flex:1;padding:11px;background:none;border:none;color:var(--muted);font-family:'Syne',sans-serif;font-size:0.82rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;cursor:pointer;border-radius:7px;transition:all .25s;}
.tab-btn.active{background:var(--orange);color:#fff;box-shadow:0 2px 12px rgba(255,107,26,0.35);}

/* CARD */
.auth-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:36px;}
.form-title{font-family:'Bebas Neue',sans-serif;font-size:1.8rem;letter-spacing:1px;color:var(--white);margin-bottom:6px;}
.form-sub{font-size:0.8rem;color:var(--muted);margin-bottom:28px;}

/* ALERT */
.alert{padding:12px 16px;border-radius:8px;font-size:0.82rem;font-family:'Syne',sans-serif;font-weight:600;margin-bottom:20px;display:flex;align-items:center;gap:8px;}
.alert.error{background:rgba(232,68,68,0.12);border:1px solid rgba(232,68,68,0.3);color:#e84444;}
.alert.success{background:rgba(46,204,113,0.12);border:1px solid rgba(46,204,113,0.3);color:var(--green);}

/* FORM */
.form-group{margin-bottom:18px;}
.form-label{display:block;font-family:'Syne',sans-serif;font-size:0.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);margin-bottom:8px;}
.form-input{width:100%;padding:12px 16px;background:var(--surface);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:0.9rem;outline:none;transition:border-color .25s;}
.form-input::placeholder{color:var(--muted);}
.form-input:focus{border-color:var(--orange);}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-select{appearance:none;cursor:pointer;}
.submit-btn{width:100%;padding:14px;background:var(--orange);color:#fff;border:none;border-radius:8px;font-family:'Syne',sans-serif;font-weight:700;font-size:0.9rem;letter-spacing:1px;cursor:pointer;transition:all .25s;margin-top:6px;box-shadow:0 4px 20px rgba(255,107,26,0.3);}
.submit-btn:hover{background:#e05a10;transform:translateY(-1px);box-shadow:0 6px 28px rgba(255,107,26,0.4);}

/* HIDDEN */
.form-panel{display:none;}
.form-panel.active{display:block;}

/* FOOTER */
footer{background:var(--surface);border-top:1px solid var(--border);padding:20px 40px;text-align:center;}
.footer-copy{font-size:0.75rem;color:var(--muted);}
.footer-copy span{color:var(--orange);}

@media(max-width:500px){.form-row{grid-template-columns:1fr;}.auth-card{padding:28px 20px;}}
</style>
</head>
<body>

<nav>
  <a href="index.html" class="nav-logo"><span>OR</span> ON RENT</a>
  <a href="index.html" class="nav-back">← Back to Home</a>
</nav>

<main>
  <div class="auth-container">
    <div class="auth-header">
      <div class="auth-logo"><span>OR</span> OWNER PORTAL</div>
      <p class="auth-tagline">Manage your listings, bookings &amp; earnings</p>
    </div>

    <!-- TABS -->
    <div class="tab-switch">
      <button class="tab-btn <?= !isset($_POST['register']) ? 'active' : '' ?>" onclick="switchTab('login')">Login</button>
      <button class="tab-btn <?= isset($_POST['register']) ? 'active' : '' ?>" onclick="switchTab('register')">Register</button>
    </div>

    <!-- ALERTS -->
    <?php if ($error_msg): ?>
      <div class="alert error">⚠️ <?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>
    <?php if ($success_msg): ?>
      <div class="alert success">✅ <?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>

    <!-- LOGIN PANEL -->
    <div class="auth-card">
      <div class="form-panel <?= !isset($_POST['register']) ? 'active' : '' ?>" id="loginPanel">
        <div class="form-title">Welcome Back</div>
        <p class="form-sub">Sign in to manage your OR listings &amp; bookings.</p>
        <form method="POST" action="">
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input class="form-input" type="email" name="email" placeholder="owner@email.com" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input class="form-input" type="password" name="password" placeholder="Enter your password" required/>
          </div>
          <button class="submit-btn" type="submit" name="login">Login to Dashboard →</button>
        </form>
      </div>

      <!-- REGISTER PANEL -->
      <div class="form-panel <?= isset($_POST['register']) ? 'active' : '' ?>" id="registerPanel">
        <div class="form-title">Create Account</div>
        <p class="form-sub">Register as a service owner and start listing today.</p>
        <form method="POST" action="">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Full Name</label>
              <input class="form-input" type="text" name="name" placeholder="Your full name" required/>
            </div>
            <div class="form-group">
              <label class="form-label">Mobile Number</label>
              <input class="form-input" type="tel" name="mobile" placeholder="+91 XXXXXXXXXX" required/>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input class="form-input" type="email" name="email" placeholder="your@email.com" required/>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">City</label>
              <select class="form-input form-select" name="city" required>
                <option value="">Select City</option>
                <option>Pune</option><option>Mumbai</option>
                <option>Nashik</option><option>Nagpur</option>
                <option>Aurangabad</option><option>Kolhapur</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Area / Pincode</label>
              <input class="form-input" type="text" name="area" placeholder="Area or PIN code" required/>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input class="form-input" type="password" name="password" placeholder="Create a strong password" required/>
          </div>
          <button class="submit-btn" type="submit" name="register">Create Account →</button>
        </form>
      </div>
    </div>
  </div>
</main>

<footer>
  <p class="footer-copy">© 2025 <span>OR — On Rent</span>. All rights reserved.</p>
</footer>

<script>
function switchTab(tab) {
  document.querySelectorAll('.tab-btn').forEach((b,i) => b.classList.toggle('active', (tab==='login'?i===0:i===1)));
  document.getElementById('loginPanel').classList.toggle('active', tab === 'login');
  document.getElementById('registerPanel').classList.toggle('active', tab === 'register');
}
<?php if (isset($_POST['register'])): ?>
switchTab('register');
<?php endif; ?>
</script>
</body>
</html>
