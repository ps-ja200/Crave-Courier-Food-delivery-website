<?php
// API endpoint for order tracking
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

include 'db_connect.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// API routes
switch ($method) {
    case 'GET':
        if (isset($path_parts[2]) && $path_parts[2] === 'orders') {
            if (isset($_GET['user_id'])) {
                getUserOrders($_GET['user_id']);
            } elseif (isset($_GET['order_id'])) {
                getOrderDetails($_GET['order_id']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required parameters']);
            }
        } elseif (isset($path_parts[2]) && $path_parts[2] === 'menu') {
            getMenuItems();
        } elseif (isset($path_parts[2]) && $path_parts[2] === 'categories') {
            getCategories();
        }
        break;
        
    case 'POST':
        if (isset($path_parts[2]) && $path_parts[2] === 'orders') {
            createOrder();
        } elseif (isset($path_parts[2]) && $path_parts[2] === 'cart') {
            updateCart();
        }
        break;
        
    case 'PUT':
        if (isset($path_parts[2]) && $path_parts[2] === 'orders') {
            updateOrderStatus();
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function getUserOrders($user_id) {
    global $conn;
    
    $sql = "SELECT o.*, 
                   COUNT(oi.id) as item_count,
                   GROUP_CONCAT(CONCAT(oi.item_name, ' x', oi.quantity) SEPARATOR ', ') as items_summary
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            WHERE o.user_id = ? 
            GROUP BY o.id 
            ORDER BY o.order_time DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'orders' => $orders]);
}

function getOrderDetails($order_id) {
    global $conn;
    
    // Get order information
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    
    if ($order_result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        return;
    }
    
    $order = $order_result->fetch_assoc();
    
    // Get order items
    $sql = "SELECT oi.*, mi.image_url 
            FROM order_items oi 
            LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id 
            WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items_result = $stmt->get_result();
    
    $items = [];
    while ($row = $items_result->fetch_assoc()) {
        $items[] = $row;
    }
    
    $order['items'] = $items;
    
    // Add tracking information
    $order['tracking'] = getOrderTracking($order['status'], $order['order_time']);
    
    echo json_encode(['status' => 'success', 'order' => $order]);
}

function getOrderTracking($status, $order_time) {
    $tracking_steps = [
        'pending' => ['Order Placed', 'We have received your order'],
        'confirmed' => ['Order Confirmed', 'Restaurant is preparing your food'],
        'preparing' => ['Food Being Prepared', 'Your delicious meal is being cooked'],
        'out_for_delivery' => ['Out for Delivery', 'Your order is on the way'],
        'delivered' => ['Delivered', 'Order delivered successfully'],
        'cancelled' => ['Cancelled', 'Order has been cancelled']
    ];
    
    $steps = [];
    $current_step = 0;
    
    foreach ($tracking_steps as $step_status => $step_info) {
        $is_completed = array_search($status, array_keys($tracking_steps)) >= array_search($step_status, array_keys($tracking_steps));
        $is_current = $step_status === $status;
        
        $steps[] = [
            'status' => $step_status,
            'title' => $step_info[0],
            'description' => $step_info[1],
            'completed' => $is_completed,
            'current' => $is_current,
            'timestamp' => $is_completed ? date('H:i', strtotime($order_time)) : null
        ];
    }
    
    return $steps;
}

function getMenuItems() {
    global $conn;
    
    $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;
    
    $sql = "SELECT mi.*, c.name as category_name 
            FROM menu_items mi 
            LEFT JOIN categories c ON mi.category_id = c.id 
            WHERE mi.is_available = TRUE";
    
    if ($category_id) {
        $sql .= " AND mi.category_id = ?";
    }
    
    $sql .= " ORDER BY c.display_order, mi.name";
    
    $stmt = $conn->prepare($sql);
    if ($category_id) {
        $stmt->bind_param("i", $category_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'items' => $items]);
}

function getCategories() {
    global $conn;
    
    $sql = "SELECT * FROM categories WHERE is_active = TRUE ORDER BY display_order";
    $result = $conn->query($sql);
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'categories' => $categories]);
}

function createOrder() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        return;
    }
    
    // Validate required fields
    $required_fields = ['user_id', 'customer_name', 'customer_phone', 'customer_email', 
                       'delivery_address', 'items', 'payment_method', 'total_amount'];
    
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            return;
        }
    }
    
    try {
        $conn->begin_transaction();
        
        // Generate order number
        $order_number = 'CC' . date('Ymd') . sprintf('%04d', rand(1, 9999));
        
        // Calculate delivery time (30 minutes from now)
        $estimated_delivery = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        
        // Insert order
        $sql = "INSERT INTO orders (user_id, order_number, customer_name, customer_phone, 
                customer_email, delivery_address, delivery_instructions, subtotal, 
                delivery_fee, tax_amount, total_amount, payment_method, estimated_delivery_time) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssdddss", 
            $input['user_id'],
            $order_number,
            $input['customer_name'],
            $input['customer_phone'],
            $input['customer_email'],
            $input['delivery_address'],
            $input['delivery_instructions'] ?? '',
            $input['subtotal'],
            $input['delivery_fee'] ?? 50,
            $input['tax_amount'],
            $input['total_amount'],
            $input['payment_method'],
            $estimated_delivery
        );
        
        $stmt->execute();
        $order_id = $conn->insert_id;
        
        // Insert order items
        foreach ($input['items'] as $item) {
            $sql = "INSERT INTO order_items (order_id, menu_item_id, item_name, item_price, quantity, special_instructions) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisdis", 
                $order_id,
                $item['menu_item_id'],
                $item['name'],
                $item['price'],
                $item['quantity'],
                $item['special_instructions'] ?? ''
            );
            $stmt->execute();
        }
        
        // Clear user's cart
        $sql = "DELETE FROM cart_items WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $input['user_id']);
        $stmt->execute();
        
        $conn->commit();
        
        echo json_encode([
            'status' => 'success', 
            'order_id' => $order_id,
            'order_number' => $order_number,
            'estimated_delivery' => $estimated_delivery
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create order: ' . $e->getMessage()]);
    }
}

function updateOrderStatus() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['order_id']) || !isset($input['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing order_id or status']);
        return;
    }
    
    $allowed_statuses = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
    
    if (!in_array($input['status'], $allowed_statuses)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status']);
        return;
    }
    
    $sql = "UPDATE orders SET status = ?";
    $params = [$input['status']];
    $types = "s";
    
    // If delivered, set actual delivery time
    if ($input['status'] === 'delivered') {
        $sql .= ", actual_delivery_time = NOW()";
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $input['order_id'];
    $types .= "i";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Order status updated']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update order status']);
    }
}

function updateCart() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['user_id']) || !isset($input['menu_item_id']) || !isset($input['quantity'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required parameters']);
        return;
    }
    
    if ($input['quantity'] <= 0) {
        // Remove item from cart
        $sql = "DELETE FROM cart_items WHERE user_id = ? AND menu_item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $input['user_id'], $input['menu_item_id']);
    } else {
        // Update or insert cart item
        $sql = "INSERT INTO cart_items (user_id, menu_item_id, quantity, special_instructions) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), special_instructions = VALUES(special_instructions)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiis", 
            $input['user_id'], 
            $input['menu_item_id'], 
            $input['quantity'], 
            $input['special_instructions'] ?? ''
        );
    }
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Cart updated']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update cart']);
    }
}
?>
