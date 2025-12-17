/**
 * Claim Confirmation - Confetti Animation
 * Lightweight confetti system with randomized physics
 * Generates particles with random CSS custom properties
 */

const CLEANUP_BUFFER_SECONDS = 1;

const CONFIG = {
    emojis: ['❤️', '💕', '🌈', '🎁', '✨', '💝', '🎉'],
    dotColors: [
        { bg: '#E8614D', shadow: 'rgba(232, 97, 77, 0.5)' },   // coral
        { bg: '#D44B3B', shadow: 'rgba(212, 75, 59, 0.5)' },   // coral-dark
        { bg: '#F5C840', shadow: 'rgba(245, 200, 64, 0.5)' },  // sunny
        { bg: '#fde047', shadow: 'rgba(253, 224, 71, 0.4)' },  // sunny-light
        { bg: '#3DB9A0', shadow: 'rgba(61, 185, 160, 0.5)' },  // teal
        { bg: '#2EADA0', shadow: 'rgba(46, 173, 160, 0.5)' },  // teal-dark
        { bg: '#FF85A1', shadow: 'rgba(255, 133, 161, 0.5)' }, // pink
        { bg: '#ffffff', shadow: 'rgba(0, 0, 0, 0.15)' },      // white
    ],
    // Burst 1: Emojis
    burst1: {
        count: 28,
        delayStart: 0.15,
        delaySpread: 0.6,
        durationMin: 2.4,
        durationMax: 3.2,
    },
    // Burst 2: Dots (after pause)
    burst2: {
        count: 36,
        delayStart: 3.8,
        delaySpread: 0.55,
        durationMin: 2.0,
        durationMax: 2.8,
    },
};

function random(min, max) {
    return Math.random() * (max - min) + min;
}

function randomInt(min, max) {
    return Math.floor(random(min, max + 1));
}

function pick(arr) {
    return arr[randomInt(0, arr.length - 1)];
}

function createParticle(isLeft, isEmoji, burstConfig, index) {
    const el = document.createElement('span');
    el.className = `confetti-particle ${isEmoji ? 'confetti-emoji' : 'confetti-dot'}`;

    // Starting position: side of screen, roughly middle height
    const startX = isLeft ? -3 : 103;
    const startY = random(40, 60);

    // Flight angle: 15-75 degrees from vertical
    const angleDeg = random(20, 70);
    const angleRad = (angleDeg * Math.PI) / 180;

    // How far horizontally and vertically to travel
    const horizontalDistance = random(35, 75); // vw
    const peakHeight = random(25, 50);          // vh upward from start

    // Calculate positions based on side
    const direction = isLeft ? 1 : -1;
    const peakX = direction * horizontalDistance * Math.sin(angleRad) * 0.6;
    const peakY = -peakHeight; // negative = upward
    const endX = direction * horizontalDistance;
    const endY = random(40, 70); // fall below start

    // Rotation: emojis rotate less, dots more
    const rotation = isEmoji
        ? random(-180, 180) * (Math.random() > 0.5 ? 1 : -1)
        : random(300, 600) * (Math.random() > 0.5 ? 1 : -1);

    // Timing with natural variation
    const duration = random(burstConfig.durationMin, burstConfig.durationMax);
    const delay = burstConfig.delayStart + random(0, burstConfig.delaySpread) + (index * 0.02);

    // Set CSS custom properties
    el.style.setProperty('--start-x', startX);
    el.style.setProperty('--start-y', startY);
    el.style.setProperty('--peak-x', peakX);
    el.style.setProperty('--peak-y', peakY);
    el.style.setProperty('--end-x', endX);
    el.style.setProperty('--end-y', endY);
    el.style.setProperty('--rotation', rotation);
    el.style.setProperty('--duration', `${duration}s`);
    el.style.setProperty('--delay', `${delay}s`);

    if (isEmoji) {
        el.textContent = pick(CONFIG.emojis);
    } else {
        // Random size: 6-14px
        const size = pick([6, 8, 10, 12, 14]);
        const color = pick(CONFIG.dotColors);
        el.style.width = `${size}px`;
        el.style.height = `${size}px`;
        el.style.backgroundColor = color.bg;
        el.style.setProperty('--shadow-color', color.shadow);
    }

    return el;
}

function initConfetti() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    try {
        const container = document.createElement('div');
        container.className = 'confetti-container';
        container.setAttribute('aria-hidden', 'true');

        for (let i = 0; i < CONFIG.burst1.count; i++) {
            const isLeft = i % 2 === 0;
            container.appendChild(createParticle(isLeft, true, CONFIG.burst1, i));
        }

        for (let i = 0; i < CONFIG.burst2.count; i++) {
            const isLeft = i % 2 === 0;
            container.appendChild(createParticle(isLeft, false, CONFIG.burst2, i));
        }

        document.body.appendChild(container);

        const maxDuration = CONFIG.burst2.delayStart + CONFIG.burst2.delaySpread +
                            CONFIG.burst2.durationMax + CLEANUP_BUFFER_SECONDS;
        setTimeout(() => container.remove(), maxDuration * 1000);
    } catch {
        // Non-critical visual enhancement - fail silently
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initConfetti);
} else {
    initConfetti();
}
