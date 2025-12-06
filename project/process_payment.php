<?php
// Prevent any output before JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once 'config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['stripeToken'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

try {
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    
    // Create Stripe charge
    $charge = \Stripe\Charge::create([
        'amount' => $_SESSION['selected_price'] * 100,
        'currency' => 'usd',
        'description' => $_SESSION['selected_plan'] . ' Plan Subscription',
        'source' => $_POST['stripeToken'],
        'metadata' => [
            'user_id' => $_SESSION['user_id'],
            'plan' => $_SESSION['selected_plan']
        ]
    ]);
    
    // Save subscription to database
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, plan_name, plan_price, stripe_payment_id, status) VALUES (?, ?, ?, ?, 'active')");
    $stmt->bind_param("isds", $_SESSION['user_id'], $_SESSION['selected_plan'], $_SESSION['selected_price'], $charge->id);
    $stmt->execute();
    $subscription_id = $stmt->insert_id;
    $stmt->close();
    $conn->close();
    
    // Store data for email
    $email_data = [
        'user_name' => $_SESSION['user_name'],
        'user_email' => $_SESSION['user_email'],
        'plan' => $_SESSION['selected_plan'],
        'price' => $_SESSION['selected_price'],
        'charge_id' => $charge->id,
        'subscription_id' => $subscription_id
    ];
    
    $_SESSION['subscription_id'] = $subscription_id;
    $_SESSION['payment_id'] = $charge->id;
    
    // Send success response IMMEDIATELY
    echo json_encode(['success' => true]);
    
    // Flush output buffers to send response to browser
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } else {
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
    }
    
    // NOW send the email (after response is sent)
    try {
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email_data['user_email'], $email_data['user_name']);
        
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to StreamFlix - Subscription Confirmed!';
        $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h1 style='color: #FF4B2B;'>Welcome to StreamFlix!</h1>
                    <p>Hi " . htmlspecialchars($email_data['user_name']) . ",</p>
                    <p>Thank you for subscribing to StreamFlix! Your payment has been processed successfully.</p>
                    
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>
                        <h2 style='color: #333; margin-top: 0;'>Subscription Details</h2>
                        <p><strong>Plan:</strong> " . htmlspecialchars($email_data['plan']) . "</p>
                        <p><strong>Price:</strong> $" . number_format($email_data['price'], 2) . "/month</p>
                        <p><strong>Transaction ID:</strong> " . $email_data['charge_id'] . "</p>
                        <p><strong>Subscription ID:</strong> #" . $email_data['subscription_id'] . "</p>
                    </div>
                    
                    <p>Your subscription is now active and you can start enjoying unlimited entertainment!</p>
                    
                    <p>If you have any questions, feel free to contact our support team.</p>
                    
                    <p>Happy streaming!<br>The StreamFlix Team</p>
                </div>
            </body>
            </html>
        ";
        
        $mail->send();
    } catch (Exception $e) {
        // Log email error but don't affect payment success
        error_log("Email sending failed: " . $mail->ErrorInfo);
    }
    
} catch (\Stripe\Exception\CardException $e) {
    echo json_encode(['success' => false, 'error' => $e->getError()->message]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Payment processing failed. Please try again.']);
}
?>