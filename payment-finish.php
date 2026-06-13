<?php
require_once 'config.php';
$ref = trim($_GET['ref'] ?? '');
$page_title = 'Confirming Payment…';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($page_title) ?> — <?= SITE_NAME ?></title>
<link rel="icon" type="image/png" href="/uploads/logo-1777215811265.png">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css?v=<?= filemtime(__DIR__.'/assets/css/style.css') ?>">
<style>
  body { background:#f7f4ee; display:flex; align-items:center; justify-content:center; min-height:100vh; }
  .finish-wrap { text-align:center; max-width:480px; padding:48px 32px; }
  .spinner { width:56px; height:56px; border:3px solid rgba(44,184,150,.2); border-top-color:#2cb896; border-radius:50%; animation:spin .8s linear infinite; margin:0 auto 28px; }
  @keyframes spin { to { transform:rotate(360deg); } }
  .timeout-box { display:none; background:#fff; border:1px solid #e0d8ce; padding:28px 32px; margin-top:24px; }
</style>
</head>
<body>
<div class="finish-wrap">
  <div id="checking">
    <div class="spinner"></div>
    <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;letter-spacing:.18em;text-transform:uppercase;color:#2cb896;">Confirming Your Payment</p>
    <p style="font-family:'Museo',sans-serif;font-size:14px;color:#8a7d6e;margin-top:8px;line-height:1.7;">Please wait — we're verifying your payment with Midtrans.</p>
  </div>

  <div class="timeout-box" id="timeout-box">
    <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:20px;color:#1a2118;margin-bottom:8px;">Payment Processing</p>
    <p style="font-family:'Museo',sans-serif;font-size:14px;color:#8a7d6e;line-height:1.7;margin-bottom:24px;">
      Your payment is being processed. You'll receive a confirmation email within a few minutes.<br><br>
      Save your reference: <strong style="color:#1a2118;"><?= htmlspecialchars($ref) ?></strong>
    </p>
    <a href="https://wa.me/<?= SITE_WA ?>?text=<?= urlencode("Hi LNC, I just completed payment. My booking ref is {$ref}. Could you confirm it was received?") ?>"
       target="_blank" class="btn btn--primary" style="margin-bottom:12px;">
      💬 WhatsApp Us to Confirm
    </a><br>
    <a href="invoice.php?ref=<?= urlencode($ref) ?>" class="btn btn--outline btn--sm" style="margin-top:8px;">View Invoice →</a>
  </div>
</div>

<script>
(function() {
  var ref     = <?= json_encode($ref) ?>;
  var started = Date.now();
  var timeout = 30000;

  function check() {
    fetch('check-status.php?ref=' + encodeURIComponent(ref))
      .then(function(r) { return r.json(); })
      .then(function(data) {
        var paid = ['deposit_paid', 'balance_paid', 'confirmed'];
        if (paid.indexOf(data.status) !== -1) {
          window.location.href = 'thank-you.php?ref=' + encodeURIComponent(ref);
          return;
        }
        if (Date.now() - started >= timeout) {
          document.getElementById('checking').style.display = 'none';
          document.getElementById('timeout-box').style.display = 'block';
          return;
        }
        setTimeout(check, 2000);
      })
      .catch(function() {
        if (Date.now() - started >= timeout) {
          document.getElementById('checking').style.display = 'none';
          document.getElementById('timeout-box').style.display = 'block';
        } else {
          setTimeout(check, 2000);
        }
      });
  }

  setTimeout(check, 1500);
})();
</script>
</body>
</html>
