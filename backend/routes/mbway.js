const express = require('express');
const router = express.Router();
const axios = require('axios');
const crypto = require('crypto');
const { v4: uuidv4 } = require('uuid');
const { giveItemsToPlayer } = require('../services/minecraft');
const { saveOrder, updateOrderStatus } = require('../services/database');

// Create MBWay Payment
router.post('/create-payment', async (req, res) => {
    try {
        const { items, username, uuid, total, phoneNumber } = req.body;
        
        if (!items || !username || !uuid || !total || !phoneNumber) {
            return res.status(400).json({ error: 'Missing required fields' });
        }
        
        // Validate Portuguese phone number
        const cleanPhone = phoneNumber.replace(/\s/g, '');
        if (!/^(9[1236]\d{7}|2\d{8})$/.test(cleanPhone)) {
            return res.status(400).json({ error: 'Invalid Portuguese phone number' });
        }
        
        const referenceId = uuidv4();
        
        // Call MBWay API (exemplo com EUPAGO)
        const response = await axios.post(
            `${process.env.MBWAY_API_URL}/mbway/create`,
            {
                api_key: process.env.MBWAY_API_KEY,
                phone: cleanPhone,
                value: total.toFixed(2),
                reference: referenceId,
                description: `Atlantic Anarchy - ${username}`
            }
        );
        
        if (response.data.success) {
            // Save order to database
            await saveOrder({
                orderId: referenceId,
                username,
                uuid,
                items: JSON.stringify(items),
                total,
                status: 'pending',
                paymentMethod: 'mbway',
                phoneNumber: cleanPhone
            });
            
            res.json({
                success: true,
                referenceId: referenceId,
                message: 'MBWay payment request sent to your phone. Please confirm on your MBWay app.',
                transactionId: response.data.transaction_id
            });
        } else {
            res.status(400).json({ 
                error: 'Failed to create MBWay payment',
                details: response.data.message 
            });
        }
        
    } catch (error) {
        console.error('MBWay Create Payment Error:', error);
        res.status(500).json({ 
            error: 'Failed to create MBWay payment', 
            details: error.message 
        });
    }
});

// Check MBWay Payment Status
router.get('/status/:referenceId', async (req, res) => {
    try {
        const { referenceId } = req.params;
        
        const response = await axios.get(
            `${process.env.MBWAY_API_URL}/mbway/status`,
            {
                params: {
                    api_key: process.env.MBWAY_API_KEY,
                    reference: referenceId
                }
            }
        );
        
        const status = response.data.status;
        
        if (status === 'success') {
            // Update order
            await updateOrderStatus(referenceId, 'completed');
            
            // Get order data
            const db = require('../services/database');
            const orderData = await db.getOrder(referenceId);
            const items = JSON.parse(orderData.items);
            
            // Give items to player
            const result = await giveItemsToPlayer(orderData.username, orderData.uuid, items);
            
            res.json({
                status: 'completed',
                delivered: result.success,
                message: result.success ? 
                    'Payment confirmed and items delivered!' : 
                    'Payment confirmed. Items will be delivered shortly.'
            });
        } else if (status === 'pending') {
            res.json({
                status: 'pending',
                message: 'Waiting for payment confirmation...'
            });
        } else if (status === 'failed' || status === 'cancelled') {
            await updateOrderStatus(referenceId, 'cancelled');
            res.json({
                status: 'failed',
                message: 'Payment was cancelled or failed'
            });
        } else {
            res.json({
                status: 'unknown',
                message: 'Payment status unknown'
            });
        }
        
    } catch (error) {
        console.error('MBWay Status Check Error:', error);
        res.status(500).json({ 
            error: 'Failed to check payment status', 
            details: error.message 
        });
    }
});

// MBWay Webhook (Callback from payment provider)
router.post('/webhook', async (req, res) => {
    try {
        const { reference, status, transaction_id } = req.body;
        
        // Verify webhook signature if provided
        // TODO: Implement signature verification based on your provider
        
        if (status === 'success') {
            await updateOrderStatus(reference, 'completed');
            
            // Get order and deliver items
            const db = require('../services/database');
            const orderData = await db.getOrder(reference);
            
            if (orderData) {
                const items = JSON.parse(orderData.items);
                await giveItemsToPlayer(orderData.username, orderData.uuid, items);
            }
        } else if (status === 'failed') {
            await updateOrderStatus(reference, 'cancelled');
        }
        
        res.sendStatus(200);
    } catch (error) {
        console.error('MBWay Webhook Error:', error);
        res.sendStatus(500);
    }
});

module.exports = router;
