require('dotenv').config();
const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const axios = require('axios');
const crypto = require('crypto');
const { v4: uuidv4 } = require('uuid');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Import routes
const paypalRoutes = require('./routes/paypal');
const mbwayRoutes = require('./routes/mbway');
const minecraftRoutes = require('./routes/minecraft');
const orderRoutes = require('./routes/orders');

// Use routes
app.use('/api/paypal', paypalRoutes);
app.use('/api/mbway', mbwayRoutes);
app.use('/api/minecraft', minecraftRoutes);
app.use('/api/orders', orderRoutes);

// Health check
app.get('/health', (req, res) => {
    res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// Error handler
app.use((err, req, res, next) => {
    console.error('Error:', err);
    res.status(500).json({
        error: 'Internal Server Error',
        message: err.message
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 Atlantic Store Backend running on port ${PORT}`);
    console.log(`📦 Environment: ${process.env.NODE_ENV}`);
    console.log(`💰 PayPal Mode: ${process.env.PAYPAL_MODE}`);
});

module.exports = app;
