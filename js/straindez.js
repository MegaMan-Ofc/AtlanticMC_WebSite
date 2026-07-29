/* ===============================================
   ATLANTIC STORE - STRAINDEZ JAVASCRIPT
   Funcionalidades específicas do design Straindez
   =============================================== */

// Initialize Straindez features
document.addEventListener('DOMContentLoaded', () => {
    initCopyServerIP();
    initDiscordMemberCount();
    initMinecraftPlayerCount();
    initClickableSections();
    initScrollAnimations();
    loadUserData();
});

// Copy Server IP to Clipboard
function initCopyServerIP() {
    const copyElement = document.getElementById('copy');
    
    if (copyElement) {
        copyElement.addEventListener('click', () => {
            const serverIP = '185.83.154.12:25591';
            
            // Copy to clipboard
            navigator.clipboard.writeText(serverIP).then(() => {
                // Show success notification
                showCopyNotification();
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        });
    }
}

// Show copy notification
function showCopyNotification() {
    const notification = document.createElement('div');
    notification.className = 'copy-notification';
    notification.innerHTML = '<i class="fa fa-check"></i> IP Copied to Clipboard!';
    
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        background: 'linear-gradient(135deg, #00E676, #00C853)',
        color: 'white',
        padding: '16px 24px',
        borderRadius: '12px',
        boxShadow: '0 10px 30px rgba(0, 230, 118, 0.4)',
        zIndex: '99999',
        fontWeight: '600',
        fontSize: '14px',
        display: 'flex',
        alignItems: 'center',
        gap: '10px',
        animation: 'slideInRight 0.3s ease'
    });
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 2500);
}

// Fetch Discord Member Count
async function initDiscordMemberCount() {
    const discordElements = document.querySelectorAll('#dPlayers');
    
    try {
        // Replace with your actual Discord Widget API endpoint
        // Example: https://discord.com/api/guilds/YOUR_GUILD_ID/widget.json
        const guildId = 'YOUR_GUILD_ID'; // Get from store config
        
        // For now, using a static count (replace with actual API call)
        const memberCount = '12,954';
        
        discordElements.forEach(el => {
            el.textContent = memberCount;
        });
        
        // Uncomment when you have the actual Guild ID:
        /*
        const response = await fetch(`https://discord.com/api/guilds/${guildId}/widget.json`);
        if (response.ok) {
            const data = await response.json();
            const count = data.presence_count || data.members?.length || 0;
            discordElements.forEach(el => {
                el.textContent = count.toLocaleString();
            });
        }
        */
    } catch (error) {
        console.error('Error fetching Discord members:', error);
    }
}

// Fetch Minecraft Server Player Count
async function initMinecraftPlayerCount() {
    const playerElements = document.querySelectorAll('#mcPlayers');
    
    try {
        // Replace with your actual Minecraft server status API
        // Example using mcsrvstat.us API
        const serverIP = '185.83.154.12:25591';
        
        const response = await fetch(`https://api.mcsrvstat.us/2/${serverIP}`);
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.online) {
                const playerCount = data.players?.online || 0;
                playerElements.forEach(el => {
                    el.textContent = playerCount;
                });
            } else {
                playerElements.forEach(el => {
                    el.textContent = '0';
                });
            }
        }
    } catch (error) {
        console.error('Error fetching player count:', error);
        // Fallback to random count for demo
        const demoCount = Math.floor(Math.random() * 150) + 50;
        playerElements.forEach(el => {
            el.textContent = demoCount;
        });
    }
}

// Initialize clickable sections with data-sdz-section-href
function initClickableSections() {
    const clickableSections = document.querySelectorAll('[data-sdz-section-href]');
    
    clickableSections.forEach(section => {
        const href = section.getAttribute('data-sdz-section-href');
        
        if (href) {
            section.style.cursor = 'pointer';
            
            section.addEventListener('click', (e) => {
                // Prevent if clicking on a link inside
                if (e.target.tagName === 'A') return;
                
                window.location.href = href;
            });
        }
    });
}

// Scroll animations
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all category cards
    const cards = document.querySelectorAll('.category-card, .sdz-plus-card, .sdz-rank-card');
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
}

// Add CSS animations
const styleSheet = document.createElement('style');
styleSheet.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    .copy-notification i {
        font-size: 18px;
    }
`;
document.head.appendChild(styleSheet);

// Export functions
window.straindez = {
    initCopyServerIP,
    initDiscordMemberCount,
    initMinecraftPlayerCount,
    initClickableSections,
    initScrollAnimations
};

// Load user data from localStorage
function loadUserData() {
    const userData = localStorage.getItem('atlantic_user');
    
    if (userData) {
        try {
            const user = JSON.parse(userData);
            updateUserDisplay(user.username, user.platform);
        } catch (error) {
            console.error('Error loading user data:', error);
        }
    }
}

// Update user display in header
function updateUserDisplay(username, platform) {
    // Update username
    const usernameElements = document.querySelectorAll('#username, #header-username');
    usernameElements.forEach(el => {
        if (el) el.textContent = username;
    });
    
    // Update avatar (remove dot for bedrock to get skin)
    const cleanUsername = username.startsWith('.') ? username.substring(1) : username;
    
    const avatarElements = document.querySelectorAll('#user-avatar, #header-avatar');
    avatarElements.forEach(el => {
        if (el) {
            el.src = `https://mc-heads.net/avatar/${cleanUsername}`;
            el.onerror = function() {
                this.src = 'https://mc-heads.net/avatar/steve';
            };
        }
    });
    
    // Change "Logged in as Guest" to actual username
    const userCard = document.querySelector('.user-card');
    if (userCard) {
        // Change onclick to go to login page (to logout or change user)
        userCard.onclick = function() {
            if (confirm(`Logged in as ${username}. Want to logout or change user?`)) {
                location.href = 'login.html';
            }
        };
    }
}

// Logout function
function logout() {
    localStorage.removeItem('atlantic_user');
    location.reload();
}

// Export logout
window.logout = logout;
