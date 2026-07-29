// Advanced Visual Effects for Atlantic Store

// Cursor Trail Effect
class CursorTrail {
    constructor() {
        this.particles = [];
        this.mouse = { x: 0, y: 0 };
        this.init();
    }
    
    init() {
        document.addEventListener('mousemove', (e) => {
            this.mouse.x = e.clientX;
            this.mouse.y = e.clientY;
            this.addParticle(e.clientX, e.clientY);
        });
        
        this.animate();
    }
    
    addParticle(x, y) {
        const particle = document.createElement('div');
        particle.className = 'cursor-particle';
        particle.style.left = x + 'px';
        particle.style.top = y + 'px';
        document.body.appendChild(particle);
        
        setTimeout(() => particle.remove(), 1000);
    }
    
    animate() {
        requestAnimationFrame(() => this.animate());
    }
}

// Card 3D Tilt Effect
function init3DTilt() {
    const cards = document.querySelectorAll('.rank-card, .rubis-card, .key-card, .category-card');
    
    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 10;
            const rotateY = (centerX - x) / 10;
            
            card.style.transform = `
                perspective(1000px) 
                rotateX(${rotateX}deg) 
                rotateY(${rotateY}deg) 
                scale(1.05)
            `;
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });
}

// Ripple Effect on Click
function initRippleEffect() {
    const buttons = document.querySelectorAll('button, .btn-add-to-cart');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.className = 'ripple';
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
}

// Scroll Reveal Animation
function initScrollReveal() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.category-card, .rank-card, .rubis-card, .key-card').forEach(el => {
        observer.observe(el);
    });
}

// Glitch Text Effect
function initGlitchEffect() {
    const glitchElements = document.querySelectorAll('.rank-name, .rubis-amount, .key-name');
    
    glitchElements.forEach(el => {
        el.addEventListener('mouseenter', () => {
            const original = el.textContent;
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
            let iterations = 0;
            
            const interval = setInterval(() => {
                el.textContent = original
                    .split('')
                    .map((char, index) => {
                        if (index < iterations) {
                            return original[index];
                        }
                        return chars[Math.floor(Math.random() * chars.length)];
                    })
                    .join('');
                
                if (iterations >= original.length) {
                    clearInterval(interval);
                }
                
                iterations += 1/3;
            }, 30);
        });
    });
}

// Sparkles Effect on Hover
function initSparkles() {
    const sparkleElements = document.querySelectorAll('.rank-card img, .rubis-card img, .key-card img');
    
    sparkleElements.forEach(el => {
        el.addEventListener('mouseenter', function() {
            for (let i = 0; i < 8; i++) {
                setTimeout(() => {
                    const sparkle = document.createElement('div');
                    sparkle.className = 'sparkle';
                    sparkle.style.left = (Math.random() * 100) + '%';
                    sparkle.style.top = (Math.random() * 100) + '%';
                    this.parentElement.appendChild(sparkle);
                    
                    setTimeout(() => sparkle.remove(), 1000);
                }, i * 50);
            }
        });
    });
}

// Initialize all effects
document.addEventListener('DOMContentLoaded', () => {
    new CursorTrail();
    init3DTilt();
    initRippleEffect();
    initScrollReveal();
    initGlitchEffect();
    initSparkles();
    
    console.log('🎨 Visual effects initialized!');
});

// CSS for effects
const effectsCSS = `
.cursor-particle {
    position: fixed;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.8) 0%, transparent 70%);
    pointer-events: none;
    z-index: 99999;
    animation: fade-out 1s ease-out forwards;
}

@keyframes fade-out {
    to {
        opacity: 0;
        transform: scale(2);
    }
}

.ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    transform: scale(0);
    animation: ripple-animation 0.6s ease-out;
    pointer-events: none;
}

@keyframes ripple-animation {
    to {
        transform: scale(2);
        opacity: 0;
    }
}

.revealed {
    animation: revealCard 0.6s ease-out forwards;
}

@keyframes revealCard {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.sparkle {
    position: absolute;
    width: 8px;
    height: 8px;
    background: radial-gradient(circle, #fff 0%, #fbbf24 100%);
    border-radius: 50%;
    box-shadow: 0 0 20px #fbbf24;
    pointer-events: none;
    animation: sparkle-animation 1s ease-out forwards;
    z-index: 10;
}

@keyframes sparkle-animation {
    0% {
        opacity: 1;
        transform: scale(0) translateY(0);
    }
    50% {
        opacity: 1;
        transform: scale(1) translateY(-20px);
    }
    100% {
        opacity: 0;
        transform: scale(0) translateY(-40px);
    }
}

/* Smooth transitions */
* {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Glow on focus */
button:focus, input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}
`;

// Inject CSS
const style = document.createElement('style');
style.textContent = effectsCSS;
document.head.appendChild(style);
