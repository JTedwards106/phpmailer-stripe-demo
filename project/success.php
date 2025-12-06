<!-- success.php - Confirmation Page -->
<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['subscription_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/success.css">
    <title>Success - StreamFlix</title>
</head>
<body>
    <div class="container">
        <div class="success-icon">✓</div>
        <h1>Payment Successful!</h1>
        <p>Thank you for subscribing to StreamFlix</p>
        
        <div class="details">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
            <p><strong>Plan:</strong> <?php echo htmlspecialchars($_SESSION['selected_plan']); ?></p>
            <p><strong>Amount:</strong> $<?php echo number_format($_SESSION['selected_price'], 2); ?>/month</p>
            <p><strong>Subscription ID:</strong> #<?php echo $_SESSION['subscription_id']; ?></p>
            <p><strong>Payment ID:</strong> <?php echo htmlspecialchars($_SESSION['payment_id']); ?></p>
        </div>
        
        <p>A confirmation email has been sent to your email address.</p>
        
        <a href="#" class="btn">Start Watching Now</a>
    </div>
</body>
</html>