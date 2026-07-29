// Admin Panel Management
let currentConfig = null;

async function loadAdminConfig() {
    currentConfig = await loadConfig();
    if (!currentConfig) {
        showAlert('Failed to load configuration', 'error');
        return;
    }
    
    populateGeneralSettings();
    populateDiscordSettings();
    renderRanksList();
    renderRubisList();
    renderKeysList();
}

function populateGeneralSettings() {
    document.getElementById('server-name').value = currentConfig.server.name || '';
    document.getElementById('server-ip').value = currentConfig.server.ip || '';
    document.getElementById('payment-link').value = currentConfig.checkout.paymentLink || '';
}

function populateDiscordSettings() {
    document.getElementById('discord-client-id').value = currentConfig.discord.clientId || '';
    document.getElementById('discord-guild-id').value = currentConfig.discord.guildId || '';
    document.getElementById('discord-invite').value = currentConfig.discord.inviteLink || '';
}

function renderRanksList() {
    const container = document.getElementById('ranks-list');
    container.innerHTML = '';
    
    currentConfig.ranks.forEach((rank, index) => {
        const card = document.createElement('div');
        card.className = 'item-card';
        
        card.innerHTML = `
            <img src="${rank.image}" alt="${rank.name}" onerror="this.src='assets/placeholder.png'">
            <div>
                <h3>${rank.name}</h3>
                <p style="color: var(--text-gray);">${rank.price.toFixed(2)} EUR</p>
                <p style="color: var(--text-gray); font-size: 14px;">${rank.description || ''}</p>
            </div>
            <div class="item-actions">
                <button class="btn-edit" onclick="editRank(${index})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn-delete" onclick="deleteRank(${index})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        `;
        
        container.appendChild(card);
    });
}

function renderRubisList() {
    const container = document.getElementById('rubis-list');
    container.innerHTML = '';
    
    currentConfig.rubis.forEach((rubis, index) => {
        const card = document.createElement('div');
        card.className = 'item-card';
        
        card.innerHTML = `
            <img src="${rubis.image}" alt="${rubis.name}" onerror="this.src='assets/placeholder.png'">
            <div>
                <h3>${rubis.name}</h3>
                <p style="color: var(--text-gray);">${rubis.amount.toLocaleString()} Rubis - ${rubis.price.toFixed(2)} EUR</p>
                ${rubis.originalPrice ? `<p style="color: var(--success-green); font-size: 14px;">Original: ${rubis.originalPrice.toFixed(2)} EUR</p>` : ''}
            </div>
            <div class="item-actions">
                <button class="btn-edit" onclick="editRubis(${index})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn-delete" onclick="deleteRubis(${index})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        `;
        
        container.appendChild(card);
    });
}

function renderKeysList() {
    const container = document.getElementById('keys-list');
    container.innerHTML = '';
    
    currentConfig.keys.forEach((key, index) => {
        const card = document.createElement('div');
        card.className = 'item-card';
        
        card.innerHTML = `
            <img src="${key.image}" alt="${key.name}" onerror="this.src='assets/placeholder.png'">
            <div>
                <h3>${key.name}</h3>
                <p style="color: var(--text-gray);">${key.price.toFixed(2)} EUR</p>
                ${key.originalPrice ? `<p style="color: var(--success-green); font-size: 14px;">Original: ${key.originalPrice.toFixed(2)} EUR</p>` : ''}
                ${key.badge ? `<span style="color: ${key.badgeColor};">${key.badge}</span>` : ''}
            </div>
            <div class="item-actions">
                <button class="btn-edit" onclick="editKey(${index})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn-delete" onclick="deleteKey(${index})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        `;
        
        container.appendChild(card);
    });
}

// Tab Switching
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(`tab-${tabName}`).classList.add('active');
    
    // Mark button as active
    event.target.classList.add('active');
}

// Form Submissions
document.getElementById('form-general')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    currentConfig.server.name = document.getElementById('server-name').value;
    currentConfig.server.ip = document.getElementById('server-ip').value;
    currentConfig.checkout.paymentLink = document.getElementById('payment-link').value;
    
    await saveConfig();
});

document.getElementById('form-discord')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    currentConfig.discord.clientId = document.getElementById('discord-client-id').value;
    currentConfig.discord.guildId = document.getElementById('discord-guild-id').value;
    currentConfig.discord.inviteLink = document.getElementById('discord-invite').value;
    
    await saveConfig();
});

// Save Configuration
async function saveConfig() {
    try {
        // In a real implementation, this would save to a backend
        // For now, we'll download as JSON
        const blob = new Blob([JSON.stringify(currentConfig, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'store-config.json';
        a.click();
        URL.revokeObjectURL(url);
        
        showAlert('Configuration saved! Replace the config/store-config.json file with the downloaded file.', 'success');
    } catch (error) {
        showAlert('Failed to save configuration', 'error');
    }
}

// CRUD Operations
function editRank(index) {
    const rank = currentConfig.ranks[index];
    const newPrice = prompt('Enter new price:', rank.price);
    
    if (newPrice !== null) {
        currentConfig.ranks[index].price = parseFloat(newPrice);
        renderRanksList();
        showAlert('Rank updated! Remember to save changes.', 'success');
    }
}

function deleteRank(index) {
    if (confirm('Are you sure you want to delete this rank?')) {
        currentConfig.ranks.splice(index, 1);
        renderRanksList();
        showAlert('Rank deleted! Remember to save changes.', 'success');
    }
}

function editRubis(index) {
    const rubis = currentConfig.rubis[index];
    const newPrice = prompt('Enter new price:', rubis.price);
    
    if (newPrice !== null) {
        currentConfig.rubis[index].price = parseFloat(newPrice);
        renderRubisList();
        showAlert('Rubis package updated! Remember to save changes.', 'success');
    }
}

function deleteRubis(index) {
    if (confirm('Are you sure you want to delete this rubis package?')) {
        currentConfig.rubis.splice(index, 1);
        renderRubisList();
        showAlert('Rubis package deleted! Remember to save changes.', 'success');
    }
}

function editKey(index) {
    const key = currentConfig.keys[index];
    const newPrice = prompt('Enter new price:', key.price);
    
    if (newPrice !== null) {
        currentConfig.keys[index].price = parseFloat(newPrice);
        renderKeysList();
        showAlert('Key updated! Remember to save changes.', 'success');
    }
}

function deleteKey(index) {
    if (confirm('Are you sure you want to delete this key?')) {
        currentConfig.keys.splice(index, 1);
        renderKeysList();
        showAlert('Key deleted! Remember to save changes.', 'success');
    }
}

function addNewRank() {
    alert('To add a new rank, edit the config file directly and add it to the ranks array.');
}

function addNewRubis() {
    alert('To add a new rubis package, edit the config file directly and add it to the rubis array.');
}

function addNewKey() {
    alert('To add a new key, edit the config file directly and add it to the keys array.');
}

// Alert System
function showAlert(message, type) {
    const container = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
    `;
    
    container.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadAdminConfig();
});

// Export functions
window.switchTab = switchTab;
window.editRank = editRank;
window.deleteRank = deleteRank;
window.editRubis = editRubis;
window.deleteRubis = deleteRubis;
window.editKey = editKey;
window.deleteKey = deleteKey;
window.addNewRank = addNewRank;
window.addNewRubis = addNewRubis;
window.addNewKey = addNewKey;
