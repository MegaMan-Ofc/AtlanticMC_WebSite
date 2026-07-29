const mysql = require('mysql2/promise');

// Database connection pool
const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME || 'atlantic_store',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Initialize database tables
async function initDatabase() {
    const connection = await pool.getConnection();
    
    try {
        // Orders table
        await connection.query(`
            CREATE TABLE IF NOT EXISTS orders (
                id INT PRIMARY KEY AUTO_INCREMENT,
                orderId VARCHAR(255) UNIQUE NOT NULL,
                username VARCHAR(255) NOT NULL,
                uuid VARCHAR(255) NOT NULL,
                items TEXT NOT NULL,
                total DECIMAL(10, 2) NOT NULL,
                status VARCHAR(50) NOT NULL,
                paymentMethod VARCHAR(50) NOT NULL,
                phoneNumber VARCHAR(20),
                createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        `);
        
        // Pending deliveries table
        await connection.query(`
            CREATE TABLE IF NOT EXISTS pending_deliveries (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(255) NOT NULL,
                uuid VARCHAR(255) NOT NULL,
                items TEXT NOT NULL,
                orderId VARCHAR(255),
                delivered BOOLEAN DEFAULT FALSE,
                createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                deliveredAt TIMESTAMP NULL
            )
        `);
        
        // Transactions log table
        await connection.query(`
            CREATE TABLE IF NOT EXISTS transactions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                orderId VARCHAR(255) NOT NULL,
                transactionId VARCHAR(255),
                paymentMethod VARCHAR(50) NOT NULL,
                amount DECIMAL(10, 2) NOT NULL,
                status VARCHAR(50) NOT NULL,
                createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);
        
        console.log('✅ Database tables initialized');
    } catch (error) {
        console.error('Database init error:', error);
    } finally {
        connection.release();
    }
}

// Save order
async function saveOrder(orderData) {
    const { orderId, username, uuid, items, total, status, paymentMethod, phoneNumber } = orderData;
    
    const [result] = await pool.query(
        `INSERT INTO orders (orderId, username, uuid, items, total, status, paymentMethod, phoneNumber) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
        [orderId, username, uuid, items, total, status, paymentMethod, phoneNumber || null]
    );
    
    return result;
}

// Get order by ID
async function getOrder(orderId) {
    const [rows] = await pool.query(
        'SELECT * FROM orders WHERE orderId = ?',
        [orderId]
    );
    
    return rows[0];
}

// Update order status
async function updateOrderStatus(orderId, status) {
    const [result] = await pool.query(
        'UPDATE orders SET status = ?, updatedAt = NOW() WHERE orderId = ?',
        [status, orderId]
    );
    
    return result;
}

// Save pending delivery
async function savePendingDelivery(deliveryData) {
    const { username, uuid, items, orderId } = deliveryData;
    
    const [result] = await pool.query(
        `INSERT INTO pending_deliveries (username, uuid, items, orderId) 
         VALUES (?, ?, ?, ?)`,
        [username, uuid, items, orderId || null]
    );
    
    return result;
}

// Get pending deliveries for player
async function getPendingDeliveries(username) {
    const [rows] = await pool.query(
        'SELECT * FROM pending_deliveries WHERE username = ? AND delivered = FALSE',
        [username]
    );
    
    return rows;
}

// Mark delivery as completed
async function markDeliveryCompleted(deliveryId) {
    const [result] = await pool.query(
        'UPDATE pending_deliveries SET delivered = TRUE, deliveredAt = NOW() WHERE id = ?',
        [deliveryId]
    );
    
    return result;
}

// Get orders by username
async function getOrdersByUsername(username) {
    const [rows] = await pool.query(
        'SELECT * FROM orders WHERE username = ? ORDER BY createdAt DESC',
        [username]
    );
    
    return rows;
}

// Initialize database on startup
initDatabase();

module.exports = {
    pool,
    saveOrder,
    getOrder,
    updateOrderStatus,
    savePendingDelivery,
    getPendingDeliveries,
    markDeliveryCompleted,
    getOrdersByUsername
};
