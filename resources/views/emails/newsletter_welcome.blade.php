{{-- resources/views/emails/newsletter_welcome.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Welcome to Deluxe Plus</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    /* ====== Base resets for email ====== */
    html, body { margin:0 !important; padding:0 !important; height:100% !important; width:100% !important; }
    * { -ms-text-size-adjust:100%; -webkit-text-size-adjust:100%; }
    table, td { mso-table-lspace:0pt; mso-table-rspace:0pt; }
    img { -ms-interpolation-mode:bicubic; border:0; outline:none; text-decoration:none; display:block; }
    a { text-decoration:none; }
    /* ====== Container ====== */
    .wrapper { width:100%; background:#f4f5f7; }
    .container { width:100%; max-width:600px; margin:0 auto; background:#ffffff; }
    .px { padding-left:24px; padding-right:24px; }
    .py { padding-top:24px; padding-bottom:24px; }
    .p { padding:24px; }
    .text { font-family: Arial, Helvetica, sans-serif; color:#111827; line-height:1.5; }
    .muted { color:#6b7280; }
    .center { text-align:center; }
    .h1 { font-size:24px; line-height:1.25; margin:0 0 8px 0; font-weight:700; color:#2563eb; }
    .h2 { font-size:18px; line-height:1.4; margin:0 0 8px 0; font-weight:700; color:#111827; }
    .btn-wrap { padding-top:20px; padding-bottom:8px; }
    /* Button (table-based for Outlook) */
    .btn a {
      background:#2563eb; color:#ffffff !important; font-weight:700; font-size:16px; line-height:1;
      padding:14px 22px; border-radius:8px; display:inline-block;
    }
    /* Footer */
    .footer { font-size:12px; color:#6b7280; }
    .divider { height:1px; background:#e5e7eb; line-height:1px; }
    /* Mobile tweaks */
    @media (max-width: 600px) {
      .p, .px, .py { padding:16px !important; }
      .h1 { font-size:22px !important; }
      .btn a { width:100% !important; text-align:center !important; }
    }
    /* (Optional) Dark mode hint */
    @media (prefers-color-scheme: dark) {
      .wrapper { background:#0b0f14 !important; }
      .container { background:#111827 !important; }
      .text { color:#e5e7eb !important; }
      .h1 { color:#60a5fa !important; }
      .muted, .footer { color:#9ca3af !important; }
      .divider { background:#1f2937 !important; }
    }
  </style>
  <!-- Hidden preheader: shows as preview text in inboxes -->
  <meta name="x-apple-disable-message-reformatting">
</head>
<body class="wrapper">
  <!--[if mso]>
  <center>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f4f5f7"><tr><td>
  <![endif]-->

  <!-- Preheader (hidden) -->
  <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#fff;">
    You’re in! Expect product updates, offers, and tips from Deluxe Plus.
  </div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
      <td align="center" style="padding:20px;">
        <table role="presentation" class="container" cellpadding="0" cellspacing="0" border="0">
          <!-- Header / Logo -->
          <tr>
            <td class="py px center">
              <img src="{{ $logoUrl ?? asset('storage/logo.jpeg') }}"
                   width="96" height="96" alt="Deluxe Plus"
                   style="width:96px; height:96px; border-radius:12px; margin:0 auto;">
            </td>
          </tr>

          <!-- Hero -->
          <tr>
            <td class="px">
              <h1 class="h1 text center">Thank You for Subscribing!</h1>
            </td>
          </tr>

          <!-- Body copy -->
          <tr>
            <td class="p text">
              <p>Hi {{ $name ?? 'there' }},</p>
              <p>You’ve successfully subscribed to our newsletter using <strong>{{ $email }}</strong>.</p>
              <p>We’ll keep you updated with our latest offers, new arrivals, and helpful tips for your practice.</p>

              <!-- CTA Button -->
              <div class="btn-wrap center">
                <!--[if mso]>
                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ $ctaUrl ?? route('products.index') }}"
                  style="height:44px;v-text-anchor:middle;width:220px;" arcsize="12%" strokecolor="#2563eb" fillcolor="#2563eb">
                  <w:anchorlock/>
                  <center style="color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:16px;font-weight:bold;">
                    Shop New Arrivals
                  </center>
                </v:roundrect>
                <![endif]-->
                <!--[if !mso]><!-- -->
                <span class="btn">
                  <a href="{{ $ctaUrl ?? route('products.index') }}" target="_blank">Shop New Arrivals</a>
                </span>
                <!--<![endif]-->
              </div>

              <!-- Secondary link (optional) -->
              <p class="center" style="margin:14px 0 0 0;">
                or visit <a href="{{ url('/') }}" style="color:#2563eb;">deluxeplus</a> to explore all products.
              </p>
            </td>
          </tr>

          <!-- Divider -->
          <tr><td class="px"><div class="divider"></div></td></tr>

          <!-- Footer -->
          <tr>
            <td class="p text footer">
              <p class="muted" style="margin:0 0 6px 0;">
                You’re receiving this because you subscribed to updates from Deluxe Plus Medical &amp; Dental Supplies.
              </p>
              <p class="muted" style="margin:0 0 6px 0;">
                If this wasn’t you or you’d rather not receive emails, you can
                <a href="{{ $unsubscribeUrl ?? '#' }}" style="color:#2563eb;">unsubscribe</a> at any time.
              </p>
              <p class="muted" style="margin:12px 0 0 0;">
                Deluxe Plus Medical &amp; Dental Supplies<br>
                Beirut, Lebanon
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

  <!--[if mso]>
  </td></tr></table>
  </center>
  <![endif]-->
</body>
</html>
