import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

const html = document.documentElement;
const themeToggle = document.querySelector('#themeToggle');

const isSoundEnabled = () => localStorage.getItem('soundEnabled') !== 'off';

let audioContext = null;
let lastUiSoundAt = 0;

function getAudioContext() {
    if (!window.AudioContext && !window.webkitAudioContext) {
        return null;
    }

    if (!audioContext) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }

    return audioContext;
}

function playUiSound() {
    if (!isSoundEnabled()) return;

    const context = getAudioContext();
    if (!context) return;

    if (context.state === 'suspended') {
        context.resume().catch(() => {});
    }

    const now = context.currentTime;
    const oscillator = context.createOscillator();
    const gain = context.createGain();

    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(760, now);
    oscillator.frequency.exponentialRampToValueAtTime(520, now + 0.08);

    gain.gain.setValueAtTime(0.0001, now);
    gain.gain.exponentialRampToValueAtTime(0.03, now + 0.01);
    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.09);

    oscillator.connect(gain);
    gain.connect(context.destination);
    oscillator.start(now);
    oscillator.stop(now + 0.1);
}

function triggerUiSound() {
    const now = Date.now();

    if (now - lastUiSoundAt < 80) {
        return;
    }

    lastUiSoundAt = now;
    playUiSound();
}

function getPreferredTheme() {
    const savedTheme = localStorage.getItem('theme');

    if (savedTheme === 'light' || savedTheme === 'dark') {
        return savedTheme;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function applyTheme(theme) {
    html.classList.toggle('dark', theme === 'dark');
    html.style.colorScheme = theme;

    if (themeToggle) {
        themeToggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
        themeToggle.setAttribute('title', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
    }

    window.dispatchEvent(new CustomEvent('cybershield:theme-change', { detail: { theme } }));
}

applyTheme(getPreferredTheme());
html.dataset.soundEnabled = isSoundEnabled() ? 'on' : 'off';

themeToggle?.addEventListener('click', () => {
    const nextTheme = html.classList.contains('dark') ? 'light' : 'dark';

    localStorage.setItem('theme', nextTheme);
    applyTheme(nextTheme);
});

document.addEventListener('click', (event) => {
    const target = event.target.closest('button, a[href], summary, [role="button"], [data-sound="click"]');

    if (!target || !isSoundEnabled() || target.closest('[data-sound="off"]')) {
        return;
    }

    triggerUiSound();
}, true);

document.addEventListener('submit', (event) => {
    if (!isSoundEnabled() || event.target.closest('[data-sound="off"]')) {
        return;
    }

    triggerUiSound();
}, true);

document.addEventListener('change', (event) => {
    const target = event.target.closest('input, select, textarea');

    if (!target || !isSoundEnabled() || target.closest('[data-sound="off"]')) {
        return;
    }

    triggerUiSound();
}, true);

const heroCanvas = document.querySelector('#hero3d');

if (heroCanvas) {
    import('three').then((THREE) => {
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({
            canvas: heroCanvas,
            alpha: true,
            antialias: true,
        });

        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.setSize(window.innerWidth, window.innerHeight);
        camera.position.z = 8;

        const globeMaterial = new THREE.MeshBasicMaterial({
            color: html.classList.contains('dark') ? 0x2dd4bf : 0x0f766e,
            wireframe: true,
            transparent: true,
            opacity: html.classList.contains('dark') ? 0.28 : 0.18,
        });

        const globe = new THREE.Mesh(new THREE.IcosahedronGeometry(2.5, 3), globeMaterial);
        globe.position.x = 3.1;
        globe.position.y = 0.1;
        scene.add(globe);

        const particlesGeometry = new THREE.BufferGeometry();
        const particleCount = 850;
        const positions = new Float32Array(particleCount * 3);

        for (let index = 0; index < positions.length; index += 1) {
            positions[index] = (Math.random() - 0.5) * 15;
        }

        particlesGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

        const particleMaterial = new THREE.PointsMaterial({
            color: html.classList.contains('dark') ? 0x67e8f9 : 0x0f766e,
            size: 0.016,
            transparent: true,
            opacity: html.classList.contains('dark') ? 0.5 : 0.3,
        });

        const particles = new THREE.Points(particlesGeometry, particleMaterial);
        scene.add(particles);

        function syncSceneTheme(event) {
            const theme = event.detail?.theme ?? getPreferredTheme();
            const isDark = theme === 'dark';

            globeMaterial.color.set(isDark ? 0x2dd4bf : 0x0f766e);
            globeMaterial.opacity = isDark ? 0.28 : 0.18;
            particleMaterial.color.set(isDark ? 0x67e8f9 : 0x0f766e);
            particleMaterial.opacity = isDark ? 0.5 : 0.3;
        }

        window.addEventListener('cybershield:theme-change', syncSceneTheme);

        function animate() {
            requestAnimationFrame(animate);

            globe.rotation.x += 0.0014;
            globe.rotation.y += 0.003;
            particles.rotation.y += 0.0005;

            renderer.render(scene, camera);
        }

        animate();

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    }).catch((error) => {
        console.warn('Hero scene could not be initialized.', error);
    });
}

const chartCanvas = document.querySelector('#securityChart');

if (chartCanvas) {
    import('chart.js/auto').then(({ default: Chart }) => {
        const isDark = () => html.classList.contains('dark');
        const chart = new Chart(chartCanvas, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    data: [72, 78, 81, 86, 88, 91, 92],
                    borderColor: isDark() ? '#2dd4bf' : '#0f766e',
                    backgroundColor: isDark() ? 'rgba(45, 212, 191, 0.14)' : 'rgba(15, 118, 110, 0.12)',
                    fill: true,
                    tension: 0.45,
                    pointRadius: 0,
                    borderWidth: 3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        displayColors: false,
                        backgroundColor: isDark() ? '#020617' : '#ffffff',
                        titleColor: isDark() ? '#e2e8f0' : '#0f172a',
                        bodyColor: isDark() ? '#cbd5e1' : '#334155',
                        borderColor: isDark() ? 'rgba(255,255,255,0.12)' : 'rgba(15,23,42,0.12)',
                        borderWidth: 1,
                    },
                },
                scales: {
                    x: {
                        ticks: { color: isDark() ? '#94a3b8' : '#64748b' },
                        grid: { color: isDark() ? 'rgba(148, 163, 184, 0.13)' : 'rgba(15, 23, 42, 0.08)' },
                    },
                    y: {
                        ticks: { color: isDark() ? '#94a3b8' : '#64748b' },
                        grid: { color: isDark() ? 'rgba(148, 163, 184, 0.13)' : 'rgba(15, 23, 42, 0.08)' },
                    },
                },
            },
        });

        window.addEventListener('cybershield:theme-change', () => {
            chart.data.datasets[0].borderColor = isDark() ? '#2dd4bf' : '#0f766e';
            chart.data.datasets[0].backgroundColor = isDark() ? 'rgba(45, 212, 191, 0.14)' : 'rgba(15, 118, 110, 0.12)';
            chart.options.plugins.tooltip.backgroundColor = isDark() ? '#020617' : '#ffffff';
            chart.options.plugins.tooltip.titleColor = isDark() ? '#e2e8f0' : '#0f172a';
            chart.options.plugins.tooltip.bodyColor = isDark() ? '#cbd5e1' : '#334155';
            chart.options.plugins.tooltip.borderColor = isDark() ? 'rgba(255,255,255,0.12)' : 'rgba(15,23,42,0.12)';
            chart.options.scales.x.ticks.color = isDark() ? '#94a3b8' : '#64748b';
            chart.options.scales.x.grid.color = isDark() ? 'rgba(148, 163, 184, 0.13)' : 'rgba(15, 23, 42, 0.08)';
            chart.options.scales.y.ticks.color = isDark() ? '#94a3b8' : '#64748b';
            chart.options.scales.y.grid.color = isDark() ? 'rgba(148, 163, 184, 0.13)' : 'rgba(15, 23, 42, 0.08)';
            chart.update();
        });
    }).catch((error) => {
        console.warn('Security chart could not be initialized.', error);
    });
}
