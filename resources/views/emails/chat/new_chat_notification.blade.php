<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Chat Message</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4e5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #f4f4f4fd;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: #521aac;
            color: #fff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .info-box {
            background: #f8f9fae8;
            border-left: 4px solid #521aac;
            padding: 15px;
            margin: 20px 0;
        }
        .info-box strong {
            color: #521aac;
        }
        .message-box {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-style: italic;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #521aac;
            color: #fff !important;
            text-decoration: none;
            border-radius: 25px;
            margin-top: 20px;
            font-weight: bold;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>You have a new chat message</h1>
            <p>You have received a new chat message</p>
        </div>

        <div class="content">
            <p>A new visitor has started a chat with you. Details are below:</p>

            <div class="info-box">
                <p><strong>Name:</strong> {{ $guestName }}</p>
                <p><strong>Email:</strong> {{ $guestEmail }}</p>
                <p><strong>Phone:</strong> {{ $guestPhone }}</p>
            </div>

            <h3>Message:</h3>
            <div class="message-box">
                {{ $msg }}
            </div>

            <p>Click the button below to reply quickly:</p>

            <center>
                <a href="{{ $chatUrl }}" class="button">
                    Go to Chat
                </a>
            </center>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>
