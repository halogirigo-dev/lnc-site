<?php
// includes/email-functions.php — Shared HTML email senders

function _lnc_html_wrap(string $heading, string $body_html): string {
  return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;background:#f7f4ee;margin:0;padding:32px 16px;">
<div style="max-width:600px;margin:0 auto;background:#fff;border:1px solid #e0d8ce;">
  <div style="background:#1a2118;padding:28px 32px;">
    <p style="font-family:Arial,sans-serif;font-weight:900;font-size:18px;letter-spacing:.2em;text-transform:uppercase;color:#fff;margin:0;">LOMBOK NATURE CULTURE</p>
    <p style="font-family:Arial,sans-serif;font-size:12px;color:rgba(255,255,255,.45);margin:4px 0 0;">PT Lombok Nature Culture</p>
  </div>
  <div style="padding:32px;">
    <h2 style="font-family:Arial,sans-serif;font-size:20px;color:#1a2118;margin:0 0 20px;">' . $heading . '</h2>
    ' . $body_html . '
  </div>
  <div style="background:#f0ebe3;padding:16px 32px;border-top:1px solid #e0d8ce;font-family:Arial,sans-serif;font-size:11px;color:#8a7d6e;">
    ' . SITE_COMPANY . ' · ' . SITE_ADDRESS . ' · <a href="mailto:' . SITE_EMAIL . '" style="color:#2cb896;">' . SITE_EMAIL . '</a>
  </div>
</div></body></html>';
}

function _lnc_row(string $label, string $value): string {
  return '<tr><td style="padding:8px 12px;font-size:13px;color:#8a7d6e;border-bottom:1px solid #f0ebe3;white-space:nowrap;">' . $label . '</td>
          <td style="padding:8px 12px;font-size:13px;color:#1a2118;font-weight:600;border-bottom:1px solid #f0ebe3;">' . $value . '</td></tr>';
}

function _lnc_mail(string $to, string $subject, string $html): void {
  $headers  = "From: " . SITE_COMPANY . " <noreply@lomboknatureculture.com>\r\n";
  $headers .= "Reply-To: " . SITE_EMAIL . "\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "X-Mailer: PHP/" . phpversion();
  @mail($to, $subject, $html, $headers);
}

function lnc_send_deposit_confirmed(array $b): void {
  $ref        = htmlspecialchars($b['ref'] ?? '');
  $name       = htmlspecialchars($b['name'] ?? 'Guest');
  $pkg        = htmlspecialchars(($b['package_id'] ?? '') . ' — ' . ($b['package_title'] ?? ''));
  $deposit    = 'Rp ' . number_format($b['deposit_amount'] ?? 0, 0, ',', '.');
  $balance    = 'Rp ' . number_format($b['balance_amount'] ?? 0, 0, ',', '.');
  $inv_link   = SITE_URL . '/invoice.php?ref=' . urlencode($b['ref'] ?? '');
  $bal_link   = SITE_URL . '/payment-balance.php?ref=' . urlencode($b['ref'] ?? '');
  $wa_link    = 'https://wa.me/' . SITE_WA . '?text=' . urlencode("Hi LNC! My deposit is confirmed. Booking ref: {$b['ref']}.");

  $rows = _lnc_row('Booking Ref', $ref)
        . _lnc_row('Package',     $pkg)
        . _lnc_row('Guests',      (string)($b['guests'] ?? 1))
        . _lnc_row('Dates',       htmlspecialchars($b['dates'] ?? 'TBC'))
        . _lnc_row('Deposit Paid', '<span style="color:#2cb896;font-weight:700;">' . $deposit . ' ✓</span>')
        . _lnc_row('Balance Due',  $balance);

  $body = '<p style="font-size:14px;color:#3d3228;line-height:1.7;">Dear ' . $name . ',</p>
  <p style="font-size:14px;color:#3d3228;line-height:1.7;">Your deposit has been received — your booking is now <strong>confirmed</strong>. Our guide team will be in touch within 24 hours to finalise your itinerary.</p>
  <table style="width:100%;border-collapse:collapse;margin:20px 0;">' . $rows . '</table>
  <div style="margin:24px 0;display:flex;gap:12px;">
    <a href="' . $bal_link . '" style="display:inline-block;background:#2cb896;color:#fff;font-family:Arial,sans-serif;font-weight:700;font-size:12px;letter-spacing:.1em;text-transform:uppercase;padding:12px 24px;text-decoration:none;">Pay Balance →</a>
    <a href="' . $inv_link . '" style="display:inline-block;background:#1a2118;color:#fff;font-family:Arial,sans-serif;font-weight:700;font-size:12px;letter-spacing:.1em;text-transform:uppercase;padding:12px 24px;text-decoration:none;margin-left:12px;">View Invoice →</a>
  </div>
  <p style="font-size:13px;color:#8a7d6e;">Questions? <a href="' . $wa_link . '" style="color:#2cb896;">WhatsApp us anytime</a>.</p>';

  $html = _lnc_html_wrap('Deposit Confirmed — Your Journey is Booked ✓', $body);

  _lnc_mail($b['email'] ?? '', "Deposit Confirmed — {$ref} | Lombok Nature Culture", $html);
  lnc_send_admin_alert($b, 'deposit');
}

function lnc_send_balance_confirmed(array $b): void {
  $ref      = htmlspecialchars($b['ref'] ?? '');
  $name     = htmlspecialchars($b['name'] ?? 'Guest');
  $pkg      = htmlspecialchars(($b['package_id'] ?? '') . ' — ' . ($b['package_title'] ?? ''));
  $total    = 'Rp ' . number_format($b['total_amount'] ?? 0, 0, ',', '.');
  $inv_link = SITE_URL . '/invoice.php?ref=' . urlencode($b['ref'] ?? '');
  $wa_link  = 'https://wa.me/' . SITE_WA . '?text=' . urlencode("Hi LNC! My full payment is confirmed. Booking ref: {$b['ref']}.");

  $rows = _lnc_row('Booking Ref',  $ref)
        . _lnc_row('Package',      $pkg)
        . _lnc_row('Guests',       (string)($b['guests'] ?? 1))
        . _lnc_row('Dates',        htmlspecialchars($b['dates'] ?? 'TBC'))
        . _lnc_row('Total Paid',   '<span style="color:#2a7a52;font-weight:700;">' . $total . ' ✓ FULLY PAID</span>');

  $body = '<p style="font-size:14px;color:#3d3228;line-height:1.7;">Dear ' . $name . ',</p>
  <p style="font-size:14px;color:#3d3228;line-height:1.7;"><strong>🎉 Your journey is fully paid and confirmed.</strong> We are so excited to welcome you to Lombok!</p>
  <table style="width:100%;border-collapse:collapse;margin:20px 0;">' . $rows . '</table>
  <a href="' . $inv_link . '" style="display:inline-block;background:#2cb896;color:#fff;font-family:Arial,sans-serif;font-weight:700;font-size:12px;letter-spacing:.1em;text-transform:uppercase;padding:12px 24px;text-decoration:none;margin:16px 0;">View Receipt →</a>
  <p style="font-size:13px;color:#8a7d6e;">Your guide will meet you at Lombok International Airport (LOP). Any questions: <a href="' . $wa_link . '" style="color:#2cb896;">WhatsApp us</a>.</p>';

  $html = _lnc_html_wrap('🎉 Fully Paid — Your Journey is Confirmed!', $body);

  _lnc_mail($b['email'] ?? '', "Journey Confirmed — {$ref} | Lombok Nature Culture", $html);
  lnc_send_admin_alert($b, 'balance');
}

function lnc_send_admin_alert(array $b, string $payment_type): void {
  $ref     = $b['ref'] ?? 'N/A';
  $type_lc = $payment_type === 'deposit' ? 'Deposit' : 'Balance';
  $amount  = $payment_type === 'deposit'
    ? 'Rp ' . number_format($b['deposit_amount'] ?? 0, 0, ',', '.')
    : 'Rp ' . number_format($b['balance_amount'] ?? 0, 0, ',', '.');

  $rows = _lnc_row('Ref',      $ref)
        . _lnc_row('Guest',    htmlspecialchars($b['name'] ?? ''))
        . _lnc_row('Email',    htmlspecialchars($b['email'] ?? ''))
        . _lnc_row('Phone',    htmlspecialchars($b['phone'] ?? ''))
        . _lnc_row('Package',  htmlspecialchars(($b['package_id'] ?? '') . ' — ' . ($b['package_title'] ?? '')))
        . _lnc_row('Guests',   (string)($b['guests'] ?? 1))
        . _lnc_row('Dates',    htmlspecialchars($b['dates'] ?? 'TBC'))
        . _lnc_row('Payment',  '<strong style="color:#2cb896;">' . $type_lc . ' — ' . $amount . '</strong>');

  $wa_link  = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $b['phone'] ?? '') . '?text=' . urlencode("Hi {$b['name']}, your {$type_lc} payment of {$amount} has been received. Thank you! — LNC Team");
  $inv_link = SITE_URL . '/invoice.php?ref=' . urlencode($ref);

  $body = '<table style="width:100%;border-collapse:collapse;margin:20px 0;">' . $rows . '</table>
  <a href="' . $wa_link . '" style="display:inline-block;background:#25d366;color:#fff;font-weight:700;font-size:12px;padding:10px 20px;text-decoration:none;margin-right:10px;">WhatsApp Guest</a>
  <a href="' . $inv_link . '" style="display:inline-block;background:#1a2118;color:#fff;font-weight:700;font-size:12px;padding:10px 20px;text-decoration:none;">View Invoice</a>';

  $html = _lnc_html_wrap("[PAYMENT] {$type_lc} received — {$ref}", $body);

  _lnc_mail(SITE_EMAIL, "[PAID] {$type_lc} — {$ref} — " . ($b['name'] ?? ''), $html);
}
