<!-- checkout.php - Payment Page -->
<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['selected_plan'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/checkout.css">
    <title>Checkout - StreamFlix</title>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Complete Your Purchase</h1>
        </div>
        
        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="summary-row">
                <span>Plan:</span>
                <span><?php echo htmlspecialchars($_SESSION['selected_plan']); ?></span>
            </div>
            <div class="summary-row">
                <span>Customer:</span>
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            </div>
            <div class="summary-row">
                <span>Email:</span>
                <span><?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
            </div>
            <div class="summary-row">
                <span>Total:</span>
                <span>$<?php echo number_format($_SESSION['selected_price'], 2); ?>/month</span>
            </div>
        </div>
        
        <div class="payment-form">
            <form id="payment-form">
                <div id="card-element"></div>
                <div id="card-errors" role="alert"></div>
                <button type="submit" class="submit-btn" id="submit-button">
                    Subscribe Now - $<?php echo number_format($_SESSION['selected_price'], 2); ?>
                </button>
            </form>
            
            <div class="loading" id="loading">
                <p>Processing payment...</p>
            </div>
        </div>
    </div>
    
    <script>
        const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');
        
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        const loading = document.getElementById('loading');
        
        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
        
        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            submitButton.disabled = true;
            loading.classList.add('active');
            
            const {token, error} = await stripe.createToken(cardElement);
            
            if (error) {
                document.getElementById('card-errors').textContent = error.message;
                submitButton.disabled = false;
                loading.classList.remove('active');
            } else {
                // Send token to server
                const formData = new FormData();
                formData.append('stripeToken', token.id);
                
                fetch('process_payment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text().then(text => {
                        console.log('Server response:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            console.error('Response text:', text);
                            throw new Error('Invalid JSON response from server');
                        }
                    });
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = 'success.php';
                    } else {
                        document.getElementById('card-errors').textContent = data.error || 'Payment failed. Please try again.';
                        submitButton.disabled = false;
                        loading.classList.remove('active');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('card-errors').textContent = 'An error occurred. Please try again.';
                    submitButton.disabled = false;
                    loading.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>