<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
</head>
<body style="margin:0; padding:0; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; color:#333;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding:30px 15px;">
                <!-- Container -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600"
                    style="background:#ffffff; border: 1px solid #e9ecef; border-radius:12px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                    <tr>
                        <td align="center" style="padding:30px 20px;">
                            <!-- Logo -->
                            <div style="margin-bottom: 40px;">
                                <img src="https://kiwi.infosparkles.net/assets/dist/images/head-logo.png" alt="brand"  style="margin: 0px auto; display:block;" />
                            </div>

                            <!-- Title -->
                            <h2
                                style="font-size:24px; text-align: center; font-weight:500; margin:0 0 25px 0; color:#222; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                Welcome to Kiwi Business Products
                            </h2>

                            <!-- Message -->
                            <div
                                style="font-size:16px; line-height:1.7; margin-bottom:45px; color:#555; text-align:left; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                <p style="margin:0 0 12px 0;">Hi <strong>{{ $name }}</strong>,</p>
                                <p style="margin:0 0 12px 0;">You're now part of Kiwi Business Products.</p>
                               
                            </div>

                            <!-- Button -->
                            <a href="{{ route('user.login') }}"
                                style="display:inline-block; background:#D4AF37; color:#000; padding:14px 28px; border-radius:6px; font-size:15px; font-weight:600; text-decoration:none; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; margin-bottom:45px;">
                                Click to Login
                            </a>

                             
                            <!-- Footer -->
                            <div
                                style="font-size:14px; line-height:1.6; color:#666; text-align:left; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; padding-top:20px; border-top:1px solid #eee;">
                                <p style="margin:0; text-align:center; color:#6c757d;">— Team Kiwi Business Products</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>