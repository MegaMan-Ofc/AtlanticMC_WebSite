// Login Page JavaScript
let selectedPlatform = 'java';
const usernameInput = document.getElementById('username-input');
const skinHeadImg = document.getElementById('skin-head');

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Check if already logged in
    const savedUser = getUserData();
    if (savedUser) {
        // Update header
        updateHeaderUser(savedUser.username, savedUser.platform);
    }
    
    // Set up input listeners
    usernameInput.addEventListener('input', handleUsernameChange);
});

function selectPlatform(platform) {
    selectedPlatform = platform;
    
    // Update button states
    document.querySelectorAll('.platform-btn').forEach(btn => {
        btn.classList.remove('selected');
    });
    document.getElementById(platform + 'Button').classList.add('selected');
    
    // Handle bedrock prefix
    if (platform === 'bedrock') {
        if (!usernameInput.value.startsWith('.')) {
            usernameInput.value = '.' + usernameInput.value;
        }
        usernameInput.placeholder = 'Enter your Bedrock username (starts with .)';
    } else {
        if (usernameInput.value.startsWith('.')) {
            usernameInput.value = usernameInput.value.substring(1);
        }
        usernameInput.placeholder = 'Enter your Java username';
        
        // Update skin preview for Java
        updateSkinPreview(usernameInput.value);
    }
}

function handleUsernameChange() {
    if (selectedPlatform === 'bedrock') {
        // Ensure bedrock usernames start with dot
        if (!usernameInput.value.startsWith('.')) {
            usernameInput.value = '.' + usernameInput.value;
        }
    } else {
        // Java Edition - update skin preview
        updateSkinPreview(usernameInput.value);
    }
}

function updateSkinPreview(username) {
    // Sanitize username before using in URL
    username = sanitizeUsername(username);
    
    // Remove leading dot if exists
    let cleanUsername = username.startsWith('.') ? username.substring(1) : username;
    
    if (cleanUsername.length > 2) {
        skinHeadImg.src = `https://mc-heads.net/avatar/${cleanUsername}/150`;
    } else {
        skinHeadImg.src = 'https://mc-heads.net/avatar/steve/150';
    }
}

function sanitizeUsername(username) {
    // Remove any HTML tags and special characters to prevent XSS
    // Only allow alphanumeric characters, underscore, and dot (for bedrock)
    return username.replace(/[^a-zA-Z0-9_.]/g, '');
}

function validateUsername(username, platform) {
    // Validate username format
    if (platform === 'bedrock') {
        // Bedrock: must start with dot, then alphanumeric and underscore
        return /^\.[a-zA-Z0-9_]{2,15}$/.test(username);
    } else {
        // Java: alphanumeric and underscore only, 3-16 characters
        return /^[a-zA-Z0-9_]{3,16}$/.test(username);
    }
}

function handleLogin(event) {
    event.preventDefault();
    
    let username = usernameInput.value.trim();
    
    if (!username) {
        alert('Please enter your username');
        return false;
    }
    
    // Sanitize username to prevent XSS attacks
    username = sanitizeUsername(username);
    
    // Validate username
    if (selectedPlatform === 'bedrock' && !username.startsWith('.')) {
        alert('Bedrock usernames must start with a dot (.)');
        return false;
    }
    
    if (!validateUsername(username, selectedPlatform)) {
        if (selectedPlatform === 'bedrock') {
            alert('Invalid Bedrock username. Must start with "." followed by 2-15 alphanumeric characters or underscores.');
        } else {
            alert('Invalid Java username. Only letters, numbers, and underscores allowed (3-16 characters).');
        }
        return false;
    }
    
    if (username.length < 3) {
        alert('Username must be at least 3 characters');
        return false;
    }
    
    // Save user data
    const userData = {
        username: username,
        platform: selectedPlatform,
        loginTime: Date.now()
    };
    
    localStorage.setItem('atlantic_user', JSON.stringify(userData));
    
    // Update header
    updateHeaderUser(username, selectedPlatform);
    
    // Show success message
    showLoginSuccess(username);
    
    // Redirect to home after 1.5 seconds
    setTimeout(() => {
        window.location.href = 'index.html';
    }, 1500);
    
    return false;
}

function showLoginSuccess(username) {
    const button = document.querySelector('.login-form button');
    const originalHTML = button.innerHTML;
    
    button.innerHTML = '<i class="fa fa-check"></i> LOGGED IN!';
    button.style.background = 'linear-gradient(135deg, #00E676, #00C853)';
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.style.background = '';
        button.disabled = false;
    }, 1500);
}

function updateHeaderUser(username, platform) {
    // Sanitize username before displaying (defense in depth)
    username = sanitizeUsername(username);
    
    // Update username in header (use textContent to prevent XSS)
    const headerUsername = document.getElementById('header-username');
    if (headerUsername) {
        headerUsername.textContent = username;
    }
    
    // Update avatar in header
    const headerAvatar = document.getElementById('header-avatar');
    if (headerAvatar) {
        let cleanUsername = username.startsWith('.') ? username.substring(1) : username;
        // Sanitize again before using in URL
        cleanUsername = sanitizeUsername(cleanUsername);
        headerAvatar.src = `https://mc-heads.net/avatar/${cleanUsername}/40`;
    }
    
    // Update all pages user info
    updateAllPagesUserInfo();
}

function updateAllPagesUserInfo() {
    // This function will be called from other pages via main.js
    const userData = getUserData();
    if (!userData) return;
    
    // Sanitize username from storage (defense in depth)
    const sanitizedUsername = sanitizeUsername(userData.username);
    
    const usernameElements = document.querySelectorAll('#username, #header-username');
    usernameElements.forEach(el => {
        // Always use textContent to prevent XSS, never innerHTML
        if (el) el.textContent = sanitizedUsername;
    });
    
    const avatarElements = document.querySelectorAll('#user-avatar, #header-avatar');
    avatarElements.forEach(el => {
        if (el) {
            let cleanUsername = sanitizedUsername.startsWith('.') ? sanitizedUsername.substring(1) : sanitizedUsername;
            // Sanitize again before using in URL
            cleanUsername = sanitizeUsername(cleanUsername);
            // Get the size from the current src or default to 40
            let size = 40;
            if (el.src.includes('/')) {
                const parts = el.src.split('/');
                size = parseInt(parts[parts.length - 1]) || 40;
            }
            el.src = `https://mc-heads.net/avatar/${cleanUsername}/${size}`;
        }
    });
}

function getUserData() {
    const data = localStorage.getItem('atlantic_user');
    return data ? JSON.parse(data) : null;
}

function logout() {
    localStorage.removeItem('atlantic_user');
    window.location.reload();
}

// Export functions for use in other pages
window.getUserData = getUserData;
window.updateAllPagesUserInfo = updateAllPagesUserInfo;
window.logout = logout;
