<?php
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Stripe\Stripe;
use Stripe\Checkout\Session;

Stripe::setApiKey(STRIPE_SECRET_KEY);

$status = $_GET['status'] ?? '';
$orderId = $_GET['order_id'] ?? '';
$sessionId = $_GET['session_id'] ?? '';

$orderDetails = null;
$orderInfo = null;

// Si tenemos session_id, consultar el estado real a Stripe
if ($sessionId && $sessionId !== '{CHECKOUT_SESSION_ID}') {
    try {
        $session = Session::retrieve($sessionId);
        $orderDetails = $session;
        $status = ($session->payment_status === 'paid') ? 'success' : 'pending';
    } catch (Exception $e) {
        error_log('Error consultando sesión Stripe: ' . $e->getMessage());
    }
}

// Consultar información del pedido desde nuestra BD
if ($orderId) {
    $db = getDBConnection();
    if ($db) {
        try {
            $stmt = $db->prepare("SELECT * FROM orders WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $orderInfo = $stmt->fetch();
        } catch (Exception $e) {
            error_log('Error consultando BD: ' . $e->getMessage());
        }
    }
}

// Determinar qué mostrar según el estado
$icon = '🎉';
$title = 'Pago Exitoso';
$message = 'Tu pago ha sido procesado correctamente con Stripe. Recibirás un email de confirmación en breve.';
$cssClass = 'success';

if ($status === 'cancel') {
    $icon = '❌';
    $title = 'Pago Cancelado';
    $message = 'El pago ha sido cancelado. Puedes intentarlo nuevamente cuando lo desees.';
    $cssClass = 'error';
} elseif ($status === 'pending') {
    $icon = '⏳';
    $title = 'Pago Pendiente';
    $message = 'Tu pago está siendo procesado. Te notificaremos cuando se confirme.';
    $cssClass = 'pending';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - ShopHub</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .result-container {
            background: #0f172a;
            border-radius: 24px;
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            animation: slideIn 0.5s ease-out;
            border: 1px solid rgba(99, 102, 241, 0.3);
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .result-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            animation: bounce 1s ease-in-out;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .result-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: #f1f5f9;
        }
        
        .result-title.success { color: #10b981; }
        .result-title.error { color: #ef4444; }
        .result-title.pending { color: #f59e0b; }
        
        .result-message {
            font-size: 1.1rem;
            color: #94a3b8;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .order-details {
            background: rgba(30, 41, 59, 0.5);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: left;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }
        
        .order-details h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: #cbd5e1;
            font-size: 1.2rem;
        }
        
        .order-details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid rgba(99, 102, 241, 0.2);
            color: #94a3b8;
        }
        
        .order-details-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .order-details-row span {
            color: #f1f5f9;
            font-weight: 600;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .status-badge.success {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        
        .status-badge.error {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        
        .status-badge.pending {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }
        
        .back-btn {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(99, 102, 241, 0.5);
        }
        
        .support-link {
            margin-top: 2rem;
            color: #6366f1;
            font-size: 0.9rem;
        }
        
        .support-link a {
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
        }
        
        .support-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .result-container {
                padding: 2rem;
            }
            
            .result-icon {
                font-size: 4rem;
            }
            
            .result-title {
                font-size: 1.5rem;
            }
            
            .result-message {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="result-container">
        <div class="result-icon"><?php echo $icon; ?></div>
        <h1 class="result-title <?php echo $cssClass; ?>"><?php echo $title; ?></h1>
        <p class="result-message"><?php echo $message; ?></p>
        
        <?php if ($orderInfo || $orderDetails): ?>
        <div class="order-details">
            <h3>Detalles del Pedido</h3>
            
            <?php if ($orderInfo && isset($orderInfo['order_id'])): ?>
            <div class="order-details-row">
                <strong>Número de Orden:</strong>
                <span><?php echo htmlspecialchars($orderInfo['order_id']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($orderInfo && isset($orderInfo['total_amount'])): ?>
            <div class="order-details-row">
                <strong>Monto Total:</strong>
                <span>₲ <?php echo number_format($orderInfo['total_amount'], 0, ',', '.'); ?></span>
            </div>
            <?php elseif ($orderDetails && isset($orderDetails->amount_total)): ?>
            <div class="order-details-row">
                <strong>Monto Total:</strong>
                <span>₲ <?php echo number_format($orderDetails->amount_total, 0, ',', '.'); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($orderInfo && isset($orderInfo['customer_name'])): ?>
            <div class="order-details-row">
                <strong>Cliente:</strong>
                <span><?php echo htmlspecialchars($orderInfo['customer_name']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($orderInfo && isset($orderInfo['customer_email'])): ?>
            <div class="order-details-row">
                <strong>Email:</strong>
                <span><?php echo htmlspecialchars($orderInfo['customer_email']); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="order-details-row">
                <strong>Estado:</strong>
                <span class="status-badge <?php echo $cssClass; ?>">
                    <?php 
                    $statusText = $orderInfo['status'] ?? $status;
                    echo ucfirst($statusText); 
                    ?>
                </span>
            </div>
            
            <div class="order-details-row">
                <strong>Fecha:</strong>
                <span><?php echo date('d/m/Y H:i'); ?></span>
            </div>
            
            <?php if ($orderDetails && isset($orderDetails->payment_method_types)): ?>
            <div class="order-details-row">
                <strong>Método de Pago:</strong>
                <span><?php echo implode(', ', $orderDetails->payment_method_types); ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <a href="/" class="back-btn">🛍️ Volver a la Tienda</a>
        
        <div class="support-link">
            ¿Necesitas ayuda? <a href="mailto:contacto@shophub.com">Contáctanos</a>
        </div>
    </div>
</body>
</html>
