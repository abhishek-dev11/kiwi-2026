<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Enquiry</title>
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
                                <img src="https://kiwi.infosparkles.net/assets/dist/images/head-logo.png" alt="brand" style="margin: 0px auto; display:block;" />
                            </div>

                            <!-- Title -->
                            <h2 style="font-size:24px; text-align: center; font-weight:500; margin:0 0 25px 0; color:#222; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                New Contact Enquiry
                            </h2>

                            <!-- Message -->
                            <div style="font-size:16px; line-height:1.7; margin-bottom:20px; color:#555; text-align:left; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; width:100%;">
                                <p style="margin:0 0 12px 0;">You have received a new enquiry from the Contact Us form:</p>
                                <table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;">
                                    <tr>
                                        <td style="border-bottom:1px solid #eee; font-weight:600; width:120px;">Name</td>
                                        <td style="border-bottom:1px solid #eee;">{{ $name }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border-bottom:1px solid #eee; font-weight:600;">Email</td>
                                        <td style="border-bottom:1px solid #eee;"><a href="mailto:{{ $from_email }}">{{ $from_email }}</a></td>
                                    </tr>
                                    <tr>
                                        <td style="border-bottom:1px solid #eee; font-weight:600;">Phone</td>
                                        <td style="border-bottom:1px solid #eee;">{{ $phone }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:600; vertical-align:top;">Message</td>
                                        <td style="white-space:pre-line;">{{ $msg }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Footer -->
                            <div style="font-size:14px; line-height:1.6; color:#666; text-align:left; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; padding-top:20px; border-top:1px solid #eee; width:100%;">
                                <p style="margin:0; text-align:center; color:#6c757d;">— Kiwi Business Products Website</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
