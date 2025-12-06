<!-- plans.php - Choose Plan Page -->
<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$plans = [
    [
        'name' => 'Basic',
        'price' => 9.99,
        'features' => ['HD Quality', '1 Device', 'Cancel Anytime'],
        'popular' => false
    ],
    [
        'name' => 'Standard',
        'price' => 14.99,
        'features' => ['Full HD Quality', '2 Devices', 'Download Content', 'Cancel Anytime'],
        'popular' => true
    ],
    [
        'name' => 'Premium',
        'price' => 19.99,
        'features' => ['4K + HDR Quality', '4 Devices', 'Download Content', 'Early Access', 'Cancel Anytime'],
        'popular' => false
    ]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['selected_plan'] = $_POST['plan'];
    $_SESSION['selected_price'] = $_POST['price'];
    header("Location: checkout.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Plan - StreamFlix</title>
    <link rel="stylesheet" href="./styles/plans.css">
</head>
<body>
    <div class="header">
        <h1>Choose Your Plan</h1>
        <p>Select the perfect plan for your entertainment needs</p>
    </div>
    
    <div class="user-info">
        Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
    </div>
    
    <div class="plans-container">
        <?php foreach ($plans as $plan): ?>
            <div class="plan-card <?php echo $plan['popular'] ? 'popular' : ''; ?>">
                <?php if ($plan['popular']): ?>
                    <div class="popular-badge">MOST POPULAR</div>
                <?php endif; ?>
                
                <div class="plan-name"><?php echo $plan['name']; ?></div>
                <div class="plan-price">
                    $<?php echo number_format($plan['price'], 2); ?>
                    <span>/month</span>
                </div>
                
                <ul class="plan-features">
                    <?php foreach ($plan['features'] as $feature): ?>
                        <li><?php echo $feature; ?></li>
                    <?php endforeach; ?>
                </ul>
                
                <form method="POST" action="">
                    <input type="hidden" name="plan" value="<?php echo $plan['name']; ?>">
                    <input type="hidden" name="price" value="<?php echo $plan['price']; ?>">
                    <button type="submit" class="select-btn">Select <?php echo $plan['name']; ?></button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>