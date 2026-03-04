<?php
// =========================
// KEY TOOL PAGE (HMAC DAILY KEY)
// =========================

date_default_timezone_set('Asia/Ho_Chi_Minh');

// PHẢI GIỐNG y hệt KEY_SECRET trong sontds.py
$KEY_SECRET = 'DOI_SECRET_RAT_KHO_DOAN_CUA_BAN';

function hmac_hex($secret, $message) {
    return hash_hmac('sha256', $message, $secret);
}

function get_daily_key($secret, $dateStr) {
    $digest = hmac_hex($secret, $dateStr);
    return strtoupper(substr($digest, 0, 10));
}

function get_verify_signature($secret, $dateStr) {
    $payload = 'VERIFY::' . $dateStr;
    $digest = hmac_hex($secret, $payload);
    return substr($digest, 0, 24);
}

$dateParam = isset($_GET['d']) ? trim($_GET['d']) : '';
$sigParam = isset($_GET['s']) ? trim($_GET['s']) : '';
$today = date('Y-m-d');

$valid = false;
$errorMessage = '';
$dailyKey = '';

if ($dateParam === '' || $sigParam === '') {
    $errorMessage = 'Thiếu dữ liệu xác minh. Vui lòng vượt link để lấy key.';
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) {
    $errorMessage = 'Định dạng ngày không hợp lệ.';
} elseif ($dateParam !== $today) {
    $errorMessage = 'Link đã hết hạn hoặc chưa đến ngày hợp lệ.';
} else {
    $expected = get_verify_signature($KEY_SECRET, $dateParam);
    if (!hash_equals($expected, $sigParam)) {
        $errorMessage = 'Xác minh thất bại. Vui lòng vượt link lại.';
    } else {
        $valid = true;
        $dailyKey = get_daily_key($KEY_SECRET, $dateParam);
    }
}

$displayDate = date('d/m/Y');
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SONTDS Key Tool</title>
  <style>
    :root {
      --bg: #0b1020;
      --card: #111831;
      --text: #e6ecff;
      --muted: #a9b4d0;
      --ok: #1cc88a;
      --err: #ff6b6b;
      --btn: #4e73df;
      --btn-hover: #3b5dcc;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      min-height: 100vh;
      display: grid;
      place-items: center;
      font-family: Arial, sans-serif;
      background: radial-gradient(circle at top, #16213e 0%, var(--bg) 60%);
      color: var(--text);
      padding: 20px;
    }

    .card {
      width: 100%;
      max-width: 560px;
      background: linear-gradient(180deg, #141d3a 0%, var(--card) 100%);
      border: 1px solid #26345f;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 12px 30px rgba(0,0,0,.35);
    }

    h1 {
      margin: 0 0 8px;
      font-size: 28px;
    }

    .sub {
      color: var(--muted);
      margin-bottom: 20px;
    }

    .row {
      margin-bottom: 12px;
      color: var(--muted);
      font-size: 15px;
    }

    .key-box {
      margin-top: 14px;
      background: #0d1530;
      border: 1px dashed #3553a6;
      border-radius: 12px;
      padding: 16px;
      font-size: 28px;
      letter-spacing: 2px;
      text-align: center;
      font-weight: bold;
      color: #f8fbff;
      user-select: all;
    }

    .status {
      margin-top: 8px;
      font-weight: 700;
    }

    .ok { color: var(--ok); }
    .err { color: var(--err); }

    .btn {
      margin-top: 16px;
      width: 100%;
      border: 0;
      border-radius: 10px;
      padding: 12px 14px;
      background: var(--btn);
      color: #fff;
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
    }

    .btn:hover { background: var(--btn-hover); }

    .footer {
      margin-top: 14px;
      color: var(--muted);
      font-size: 13px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>SONTDS KEY TOOL</h1>
    <div class="sub">Trang cấp key theo ngày cho tool.</div>

    <div class="row">Ngày hôm nay: <strong><?php echo htmlspecialchars($displayDate); ?></strong></div>

    <?php if ($valid): ?>
      <div class="status ok">Vượt link thành công. Đây là key hôm nay:</div>
      <div id="dailyKey" class="key-box"><?php echo htmlspecialchars($dailyKey); ?></div>
      <button class="btn" onclick="copyKey()">Copy Key</button>
    <?php else: ?>
      <div class="status err"><?php echo htmlspecialchars($errorMessage); ?></div>
      <div class="footer">Bạn cần vượt đúng link rút gọn để nhận key.</div>
    <?php endif; ?>

    <div class="footer">Key tự đổi mỗi ngày lúc 00:00 (GMT+7).</div>
  </div>

  <script>
    function copyKey() {
      var keyEl = document.getElementById('dailyKey');
      if (!keyEl) return;
      var key = keyEl.textContent.trim();
      navigator.clipboard.writeText(key).then(function() {
        alert('Đã copy key: ' + key);
      }).catch(function() {
        alert('Không copy được tự động. Bạn hãy bôi đen và copy thủ công.');
      });
    }
  </script>
</body>
</html>
