const express = require('express');
const router = express.Router();
const paypal = require('@paypal/checkout-server-sdk');
const { giveItemsToPlayer } = require('../services/minecraft');
const { saveOrder, updateOrderStatus } = require('../services/database');

// PayPal Environment
function environment() {
    const clientId = process.env.PAYPAL_CLIENT_ID;
    const clientSecret = process.env.PAYPAL_CLIENT_SECRET;
    
    if (process.env.PAYPAL_MODE === 'live') {
        return new paypal.core.LiveEnvironment(clientId, clientSecret);
    }
    return new paypal.core.SandboxEnvironment(clientId, clientSecret);
}

function client() {
    return new paypal.core.PayPalHttpClient(environment());
}

// Create PayPal Order
router.post('/create-order', async (req, res) => {
    try {
        const { items, username, uuid, total } = req.body;
        
        if (!items || !username || !uuid || !total) {
            return res.status(400).json({ error: 'Missing required fields' });
        }
        
        const request = new paypal.orders.OrdersCreateRequest();
        request.prefer("return=representation");
        request.requestBody({
            intent: 'CAPTURE',
            purchase_units: [{
                amount: {
                    currency_code: 'EUR',
                    value: total.toFixed(2),
                    breakdown: {
                        item_total: {
                            currency_code: 'EUR',
                            value: (total / 1.23).toFixed(2)
                        },
                        tax_total: {
                            currency_code: 'EUR',
                            value: (total - (total / 1.23)).toFixed(2)
                        }
                    }
                },
                items: items.map(item => ({
                    name: item.name,
                    unit_amount: {
                        currency_code: 'EUR',
                        value: item.price.toFixed(2)
                    },
                    quantity: item.quantity.toString()
                })),
                description: `Atlantic Anarchy Store - ${username}`,
                custom_id: JSON.stringify({ username, uuid })
            }],
            application_context: {
                brand_name: 'Atlantic Anarchy',
                landing_page: 'NO_PREFERENCE',
                user_action: 'PAY_NOW',
                return_url: `${process.env.FRONTEND_URL}/payment-success.html`,
                cancel_url: `${process.env.FRONTEND_URL}/payment-cancel.html`
            }
        });
        
        const order = await client().execute(request);
        
        // Save order to database
        await saveOrder({
            orderId: order.result.id,
            username,
            uuid,
            items: JSON.stringify(items),
            total,
            status: 'pending',
            paymentMethod: 'paypal'
        });
        
        res.json({
            orderId: order.result.id,
            approveUrl: order.result.links.find(link => link.rel === 'approve').href
        });
        
    } catch (error) {
        console.error('PayPal Create Order Error:', error);
        res.status(500).json({ error: 'Failed to create PayPal order', details: error.message });
    }
});

// Capture PayPal Order
router.post('/capture-order', async (req, res) => {
    try {
        const { orderId } = req.body;
        
        if (!orderId) {
            return res.status(400).json({ error: 'Order ID is required' });
        }
        
        const request = new paypal.orders.OrdersCaptureRequest(orderId);
        request.requestBody({});
        
        const capture = await client().execute(request);
        
        if (capture.result.status === 'COMPLETED') {
            // Get custom data
            const customData = JSON.parse(capture.result.purchase_units[0].custom_id);
            const { username, uuid } = customData;
            
            // Update order status
            await updateOrderStatus(orderId, 'completed');
            
            // Give items to player
            const orderData = await getOrderById(orderId);
            const items = JSON.parse(orderData.items);
            
            const result = await giveItemsToPlayer(username, uuid, items);
            
            if (result.success) {
                res.json({
                    success: true,
                    message: 'Payment completed and items delivered!',
                    orderId: orderId
                });
            } else {
                // Payment successful but item delivery failed - save for manual processing
                await updateOrderStatus(orderId, 'payment_completed_delivery_failed');
                res.json({
                    success: true,
                    warning: 'Payment completed but items could not be delivered automatically. Contact support.',
                    orderId: orderId
                });
            }
        } else {
            res.status(400).json({ error: 'Payment not completed', status: capture.result.status });
        }
        
    } catch (error) {
        console.error('PayPal Capture Order Error:', error);
        res.status(500).json({ error: 'Failed to capture PayPal order', details: error.message });
    }
});

// PayPal Webhook Handler
router.post('/webhook', async (req, res) => {
    try {
        // Verify webhook signature
        const webhookId = process.env.PAYPAL_WEBHOOK_ID;
        // TODO: Implement webhook signature verification
        
        const event = req.body;
        
        if (event.event_type === 'PAYMENT.CAPTURE.COMPLETED') {
            const orderId = event.resource.supplementary_data.related_ids.order_id;
            
            // Process order completion
            await updateOrderStatus(orderId, 'completed');
            
            // Additional processing if needed
        }
        
        res.sendStatus(200);
    } catch (error) {
        console.error('PayPal Webhook Error:', error);
        res.sendStatus(500);
    }
});

async function getOrderById(orderId) {
    const db = require('../services/database');
    return await db.getOrder(orderId);
}

module.exports = router;
