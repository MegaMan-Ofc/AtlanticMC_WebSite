const Rcon = require('rcon-client').Rcon;

// Connect to Minecraft server via RCON
async function executeMinecraftCommand(command) {
    let rcon;
    try {
        rcon = await Rcon.connect({
            host: process.env.MC_SERVER_HOST || 'localhost',
            port: parseInt(process.env.MC_SERVER_PORT) || 25575,
            password: process.env.MC_RCON_PASSWORD
        });
        
        const response = await rcon.send(command);
        return { success: true, response };
    } catch (error) {
        console.error('RCON Error:', error);
        return { success: false, error: error.message };
    } finally {
        if (rcon) {
            await rcon.end();
        }
    }
}

// Give items to player based on purchase
async function giveItemsToPlayer(username, uuid, items) {
    try {
        const commands = [];
        
        for (const item of items) {
            switch (item.type) {
                case 'rank':
                    // Give rank using LuckPerms or your rank plugin
                    commands.push(`lp user ${username} parent set ${item.id}`);
                    // Give rank kit
                    commands.push(`give ${username} minecraft:diamond 64`);
                    break;
                    
                case 'rubis':
                    // Add rubis to player (assuming you have a currency plugin)
                    commands.push(`eco give ${username} ${item.amount || 0}`);
                    // Or custom command for your rubis system
                    // commands.push(`rubis add ${username} ${item.amount}`);
                    break;
                    
                case 'key':
                    // Give crate keys
                    const keyAmount = item.keyQuantity || 1;
                    commands.push(`crate givekey ${username} ${item.id} ${keyAmount}`);
                    break;
                    
                default:
                    console.warn(`Unknown item type: ${item.type}`);
            }
        }
        
        // Send notification to player if online
        commands.push(`tellraw ${username} {"text":"✨ Your purchase has been delivered! Thank you for supporting Atlantic Anarchy!","color":"gold"}`);
        
        // Execute all commands
        const results = [];
        for (const command of commands) {
            const result = await executeMinecraftCommand(command);
            results.push(result);
            
            // Small delay between commands
            await new Promise(resolve => setTimeout(resolve, 100));
        }
        
        // Check if all commands succeeded
        const allSuccess = results.every(r => r.success);
        
        if (allSuccess) {
            console.log(`✅ Items delivered to ${username}`);
            return { success: true, message: 'Items delivered successfully' };
        } else {
            console.error(`⚠️ Some items failed to deliver to ${username}`);
            return { success: false, message: 'Some items failed to deliver', results };
        }
        
    } catch (error) {
        console.error('Give Items Error:', error);
        return { success: false, error: error.message };
    }
}

// Check if player is online
async function isPlayerOnline(username) {
    const result = await executeMinecraftCommand(`list`);
    if (result.success) {
        return result.response.includes(username);
    }
    return false;
}

// Queue item delivery for offline player
async function queueItemDelivery(username, uuid, items) {
    // Save to database for delivery when player logs in
    const db = require('./database');
    await db.savePendingDelivery({
        username,
        uuid,
        items: JSON.stringify(items),
        createdAt: new Date()
    });
}

module.exports = {
    executeMinecraftCommand,
    giveItemsToPlayer,
    isPlayerOnline,
    queueItemDelivery
};
