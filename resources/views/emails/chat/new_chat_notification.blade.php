<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নতুন চ্যাট মেসেজ</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
        }
        .info-box strong {
            color: #667eea;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
            <h1>🔔 নতুন চ্যাট মেসেজ পেয়েছেন</h1>
            <p>You have received a new chat message</p>
        </div>

        <div class="content">
            <p>একজন নতুন ভিজিটর আপনার সাথে চ্যাট শুরু করেছেন। নিচে বিস্তারিত তথ্য দেওয়া হলো:</p>

            <div class="info-box">
                <p><strong>নাম (Name):</strong> {{ $guestName }}</p>
                <p><strong>ইমেইল (Email):</strong> {{ $guestEmail }}</p>
                <p><strong>ফোন (Phone):</strong> {{ $guestPhone }}</p>
            </div>

            <h3>📩 মেসেজ:</h3>
            <div class="message-box">
                {{ $msg }}
            </div>

            <p>দ্রুত উত্তর দিতে নিচের বাটনে ক্লিক করুন:</p>

            <center>
                <a href="{{ $chatUrl }}" class="button">
                    💬 চ্যাটে যান (Go to Chat)
                </a>
            </center>
        </div>

        <div class="footer">
            <p>এই ইমেইল স্বয়ংক্রিয়ভাবে পাঠানো হয়েছে। অনুগ্রহ করে উত্তর দেবেন না।</p>
            <p>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
