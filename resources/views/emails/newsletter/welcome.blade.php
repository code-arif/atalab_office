<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to The Dignity Draw Newsletter</title>
</head>

<body style="margin:0;padding:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f4f6f9;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
        style="background:#f4f6f9;padding:40px 20px;">
        <tr>
            <td align="center">

                <!-- MAIN CONTAINER -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                    style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.08);overflow:hidden;">

                    <!-- HEADER – Gradient -->
                    <tr>
                        <td
                            style="background:linear-gradient(135deg,#521aac 0%,#521aac 100%);padding:40px 30px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:28px;font-weight:700;letter-spacing:-0.5px;">
                                Welcome Aboard!
                            </h1>
                            <p style="margin:10px 0 0;color:#e6f0ff;font-size:16px;">
                                The Dignity Draw, Inc. Newsletter
                            </p>
                        </td>
                    </tr>

                    <!-- MAIN CONTENT -->
                    <tr>
                        <td style="padding:40px 30px;">

                            <h2 style="margin:0 0 20px;color:#521aac;font-size:24px;font-weight:700;">
                                Thank You for Joining Our Mission!
                            </h2>

                            <!-- EMAIL INFO BOX -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
                                style="background:#f8fafc;border-left:4px solid #521aac;border-radius:6px;margin:25px 0;">
                                <tr>
                                    <td style="padding:20px;">
                                        <p
                                            style="margin:0;color:#666;font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:.:0.5px;">
                                            Subscribed Email
                                        </p>
                                        <p style="margin:8px 0 0;color:#521aac;font-size:16px;font-weight:700;">
                                            {{ $email }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- DONATION CTA -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
                                style="margin:35px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="https://thedignitydraw.org/donate"
                                            style="display:inline-block;background:linear-gradient(135deg,#521aac 0%,#521aac 100%);color:#ffffff;padding:16px 40px;text-decoration:none;border-radius:8px;font-weight:700;font-size:16px;box-shadow:0 4px 12px rgba(30,83,164,.3);transition:all .3s;">
                                            Make a Donation Now
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:25px 0 0;color:#666;font-size:15px;line-height:1.6;">
                                Every contribution helps us **collect donations** and **distribute aid** to those who
                                need it most.
                                You’ll receive updates on impact stories, upcoming draws, and ways you can get involved.
                            </p>

                            <p style="margin:20px 0 0;color:#666;font-size:15px;line-height:1.6;">
                                Questions? <a href="mailto:support@thedignitydraw.org" style="color:#521aac;">Contact
                                    our team</a>.
                            </p>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background:#f8fafc;padding:30px;border-top:1px solid #e5e7eb;">
                            <p style="margin:0 0 15px;color:#999;font-size:13px;line-height:1.6;text-align:center;">
                                You are receiving this email because you subscribed to the newsletter of
                                <strong>The Dignity Draw, Inc.</strong>
                            </p>

                            <p style="margin:0;color:#999;font-size:12px;text-align:center;">
                                © {{ date('Y') }} The Dignity Draw, Inc. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
