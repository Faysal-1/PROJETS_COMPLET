<?php
/**
 * Order Processing Backend
 * Handles order submissions from the frontend
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

try {
    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Données invalides');
    }
    
    // Validate customer data
    if (!isset($data['customer']) || !isset($data['order'])) {
        throw new Exception('Données manquantes');
    }
    
    $customer = $data['customer'];
    $order = $data['order'];
    
    // Validate required fields
    $requiredFields = ['firstName', 'lastName', 'email', 'phone'];
    foreach ($requiredFields as $field) {
        if (empty($customer[$field])) {
            throw new Exception("Champ requis manquant: $field");
        }
    }
    
    // Validate email
    if (!filter_var($customer['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email invalide');
    }
    
    // Validate phone (9 digits)
    if (!preg_match('/^[0-9]{9}$/', $customer['phone'])) {
        throw new Exception('Numéro de téléphone invalide');
    }
    
    // Validate order items
    if (empty($order['items']) || !is_array($order['items'])) {
        throw new Exception('Aucun produit dans la commande');
    }
    
    // Generate order ID
    $orderId = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Prepare order data for storage
    $orderData = [
        'order_id' => $orderId,
        'customer' => $customer,
        'items' => $order['items'],
        'total' => $order['total'],
        'date' => date('Y-m-d H:i:s'),
        'status' => 'pending'
    ];
    
    // Save to file (JSON format)
    saveOrderToFile($orderData);
    
    // Send email notification (optional)
    sendEmailNotification($orderData);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Commande enregistrée avec succès',
        'order_id' => $orderId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Save order to file
 */
function saveOrderToFile($orderData) {
    $ordersDir = __DIR__ . '/orders';
    
    // Create orders directory if it doesn't exist
    if (!file_exists($ordersDir)) {
        mkdir($ordersDir, 0755, true);
    }
    
    // Generate filename
    $filename = $ordersDir . '/' . $orderData['order_id'] . '.json';
    
    // Save order as JSON
    file_put_contents($filename, json_encode($orderData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // Also append to orders log
    $logFile = $ordersDir . '/orders_log.txt';
    $logEntry = sprintf(
        "[%s] Order %s - Customer: %s %s - Total: %s DH\n",
        date('Y-m-d H:i:s'),
        $orderData['order_id'],
        $orderData['customer']['firstName'],
        $orderData['customer']['lastName'],
        number_format($orderData['total'], 2)
    );
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * Send email notification
 */
function sendEmailNotification($orderData) {
    // Email configuration
    $to = 'votre-email@example.com'; // Change this to your email
    $subject = 'Nouvelle commande - ' . $orderData['order_id'];
    
    // Build email body
    $message = "NOUVELLE COMMANDE\n\n";
    $message .= "Numéro de commande: " . $orderData['order_id'] . "\n";
    $message .= "Date: " . $orderData['date'] . "\n\n";
    
    $message .= "INFORMATIONS CLIENT:\n";
    $message .= "Nom: " . $orderData['customer']['firstName'] . " " . $orderData['customer']['lastName'] . "\n";
    $message .= "Email: " . $orderData['customer']['email'] . "\n";
    $message .= "Téléphone: +212" . $orderData['customer']['phone'] . "\n";
    
    if (!empty($orderData['customer']['address'])) {
        $message .= "Adresse: " . $orderData['customer']['address'] . "\n";
    }
    
    $message .= "\nPRODUITS COMMANDÉS:\n";
    foreach ($orderData['items'] as $item) {
        $message .= sprintf(
            "- %s (x%d) - %s DH x %d = %s DH\n",
            $item['productName'],
            $item['quantity'],
            number_format($item['price'], 2),
            $item['quantity'],
            number_format($item['subtotal'], 2)
        );
    }
    
    $message .= "\n" . str_repeat('-', 50) . "\n";
    $message .= "TOTAL: " . number_format($orderData['total'], 2) . " DH\n";
    $message .= str_repeat('-', 50) . "\n";
    
    // Email headers
    $headers = "From: noreply@votre-site.com\r\n";
    $headers .= "Reply-To: " . $orderData['customer']['email'] . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Send email (commented out for testing - uncomment in production)
    // mail($to, $subject, $message, $headers);
}
?>
