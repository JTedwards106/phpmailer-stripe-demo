<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    
    // Recipients
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress(SMTP_USERNAME);  // Send to yourself for testing
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from StreamFlix';
    $mail->Body = '<h1>Success!</h1><p>Your Gmail SMTP configuration is working correctly.</p>';
    
    $mail->send();
    echo '✓ Email sent successfully! Check your Gmail inbox.';
    
} catch (Exception $e) {
    echo "✗ Email failed: {$mail->ErrorInfo}";
}
?>
```

Visit: `http://localhost/phpmailer-stripe-demo/test_gmail.php`

---

## **Common Gmail Issues & Solutions**

### **"Invalid credentials" error:**
- Make sure you're using the **App Password**, not your regular Gmail password
- Remove any spaces from the app password
- Double-check that 2FA is enabled first

### **"Could not authenticate" error:**
- Verify `SMTP_USERNAME` is your complete Gmail address (including @gmail.com)
- Make sure the app password is correct (try generating a new one)

### **Can't find "App passwords" option:**
- 2FA must be enabled first
- Try this direct link: https://myaccount.google.com/apppasswords

### **Email sent but not received:**
- Check your Gmail spam folder
- Make sure you're sending to a valid email address

---

## **Security Note**

⚠️ **Never share or commit your App Password to GitHub or public repositories!**

Consider adding this to `.gitignore`:
```
config.php
vendor/