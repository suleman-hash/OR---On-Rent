<?php
/* OR — Admin Login */
session_start();
if (isset($_SESSION['admin_id'])) { header("Location: admin_panel.php"); exit; }

$conn = new mysqli("localhost","root","","or_onrent");
$error = "";

if (isset($_POST['login'])) {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];
    $row = $conn->query("SELECT * FROM admins WHERE username='$username'")->fetch_assoc();
    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['admin_id']   = $row['id'];
        $_SESSION['admin_name'] = $row['username'];
        header("Location: admin_panel.php"); exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Admin Login — OR On Rent</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet"/>
<style>
:root{--bg:#0a0a0f;--card:#1a1a26;--border:#2a2a3a;--orange:#ff6b1a;--text:#f0eee8;--muted:#8a8a9a;--red:#e84444;}
*{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;}
.box{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:40px;width:100%;max-width:420px;}
.logo{font-family:'Bebas Neue',sans-serif;font-size:2rem;letter-spacing:2px;color:#fff;margin-bottom:6px;text-align:center;}
.logo span{color:var(--orange);}
.sub{text-align:center;font-size:0.8rem;color:var(--muted);margin-bottom:28px;}
.lbl{font-family:'Syne',sans-serif;font-size:0.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);display:block;margin-bottom:7px;}
.inp{width:100%;padding:12px 16px;background:#12121a;border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:0.9rem;outline:none;margin-bottom:16px;}
.inp:focus{border-color:var(--orange);}
.btn{width:100%;padding:14px;background:var(--orange);color:#fff;border:none;border-radius:8px;font-family:'Syne',sans-serif;font-weight:700;font-size:0.9rem;letter-spacing:1px;cursor:pointer;}
.btn:hover{background:#e05a10;}
.err{background:rgba(232,68,68,0.1);border:1px solid rgba(232,68,68,0.3);color:var(--red);padding:10px 14px;border-radius:8px;font-size:0.8rem;margin-bottom:18px;}
</style>
</head>
<body>
<div class="box">
  <div class="logo"><span>OR</span> ADMIN</div>
  <div class="sub">Admin Access Only</div>
  <?php if ($error): ?><div class="err">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <label class="lbl">Username</label>
    <input class="inp" type="text" name="username" placeholder="admin" required/>
    <label class="lbl">Password</label>
    <input class="inp" type="password" name="password" placeholder="••••••••" required/>
    <button class="btn" type="submit" name="login">Login to Admin →</button>
  </form>
</div>
</body>
</html>
