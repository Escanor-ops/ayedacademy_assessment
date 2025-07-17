<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            direction: rtl;
            text-align: right;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .code {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            text-align: center;
            padding: 10px;
            margin: 20px 0;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .note {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>إعادة تعيين كلمة المرور</h2>
        <p>مرحباً،</p>
        <p>لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك. رمز التحقق الخاص بك هو:</p>
        
        <div class="code">{{ $code }}</div>
        
        <p>يرجى استخدام هذا الرمز لإعادة تعيين كلمة المرور الخاصة بك. سينتهي صلاحية هذا الرمز خلال 60 دقيقة.</p>
        
        <p class="note">إذا لم تقم بطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذا البريد الإلكتروني.</p>
        
        <p>مع تحياتنا،<br>Mo Sabry 👑</p>
    </div>
</body>
</html> 