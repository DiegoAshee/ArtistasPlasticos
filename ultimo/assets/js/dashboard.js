document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');
    const userMenu = document.getElementById('userMenu');
    const userDropdown = document.getElementById('userDropdown');
    const mainContent = document.querySelector('.main-content');
    const overlay = document.getElementById('overlay');
    
    // Function to toggle sidebar
    function toggleSidebar() {
        // Desktop: collapse to icons-only (no overlay)
        if (window.innerWidth > 1024) {
            sidebar.classList.toggle('collapsed');
            mainContent?.classList.toggle('collapsed');
        } else {
            // Mobile: slide in/out with overlay
            sidebar.classList.toggle('show');
            if (overlay) {
                overlay.classList.toggle('show');
                document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
            }
        }

        // Toggle menu icon with animation
        const menuIcon = menuToggle?.querySelector('i');
        if (menuIcon) {
            const opened = window.innerWidth > 1024 ? !sidebar.classList.contains('collapsed') : sidebar.classList.contains('show');
            if (opened) {
                menuIcon.classList.remove('fa-bars');
                menuIcon.classList.add('fa-times');
                menuToggle.style.transform = 'rotate(90deg)';
            } else {
                menuIcon.classList.remove('fa-times');
                menuIcon.classList.add('fa-bars');
                menuToggle.style.transform = 'rotate(0deg)';
            }
        }
    }
    
    // Toggle user dropdown
    function toggleUserDropdown(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('show');
        
        // Add ripple effect
        createRipple(e, userMenu);
    }
    
    // Close all dropdowns
    function closeAllDropdowns() {
        if (userDropdown && userDropdown.classList.contains('show')) {
            userDropdown.classList.remove('show');
        }
    }
    
    // Create ripple effect
    function createRipple(event, element) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        `;
        
        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }
    
    // Add ripple animation CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Event Listeners
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSidebar();
            createRipple(e, this);
        });
    }
    
    if (userMenu) {
        userMenu.addEventListener('click', toggleUserDropdown);
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        // Close user dropdown if click is outside
        if (userDropdown && userDropdown.classList.contains('show') && 
            !userMenu.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
        
        // Close sidebar when clicking outside on mobile
        if (window.innerWidth <= 1024 && 
            sidebar.classList.contains('show') && 
            !sidebar.contains(e.target) && 
            e.target !== menuToggle &&
            !menuToggle.contains(e.target)) {
            toggleSidebar();
        }
    });
    
    // Close sidebar when clicking on overlay (mobile)
    overlay?.addEventListener('click', () => {
        if (window.innerWidth <= 1024) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
            const menuIcon = menuToggle?.querySelector('i');
            if (menuIcon) {
                menuIcon.classList.remove('fa-times');
                menuIcon.classList.add('fa-bars');
                menuToggle.style.transform = 'rotate(0deg)';
            }
        }
    });
    
    // Close sidebar when clicking overlay
    if (overlay) {
        overlay.addEventListener('click', function() {
            toggleSidebar();
        });
    }
    
    // Close menu when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (window.innerWidth <= 1024 && sidebar.classList.contains('show')) {
                toggleSidebar();
            }
            closeAllDropdowns();
        }
    });
    
    // Handle window resize
    function handleResize() {
        if (window.innerWidth > 1024) {
            // Clear mobile-only states
            sidebar.classList.remove('show');
            overlay?.classList.remove('show');
            document.body.style.overflow = '';
            // Keep current desktop collapsed state as is
        } else {
            // Mobile default: hidden; remove desktop collapsed styles
            sidebar.classList.remove('collapsed');
            mainContent?.classList.remove('collapsed');
            sidebar.classList.remove('show');
            overlay?.classList.remove('show');
            document.body.style.overflow = '';
            // Ensure icon reset
            const menuIcon = menuToggle?.querySelector('i');
            if (menuIcon) {
                menuIcon.classList.remove('fa-times');
                menuIcon.classList.add('fa-bars');
                menuToggle.style.transform = 'rotate(0deg)';
            }
        }
    }
    
    // Initialize
    window.addEventListener('resize', handleResize);
    handleResize(); // Run once on load
    
    // Add smooth transitions after page load
    setTimeout(() => {
        document.body.style.transition = 'all 0.3s ease';
    }, 100);
    
    // Add loaded class to body to prevent FOUC
    document.body.classList.add('loaded');
    
    // Load dashboard data
    loadSociosStats();
    loadRecentActivities();
    
    // Auto-refresh data every 5 minutes
    setInterval(() => {
        loadSociosStats();
        loadRecentActivities();
    }, 300000);
});

// Function to load member statistics
async function loadSociosStats() {
    try {
        // Show loading state
        showLoadingState();
        
        // In a real environment, this would make a call to your API
        // const response = await fetch('/api/socios/estadisticas');
        // const data = await response.json();
        
        // Example data (replace with real API call)
        const data = {
            total: 125,
            alDia: 98,
            enDeuda: 27,
            pendientesHoy: 8,
            cambios: {
                total: 12,
                alDia: 5,
                enDeuda: -5,
                pendientesHoy: 3
            }
        };
        
        // Simulate API delay
        await new Promise(resolve => setTimeout(resolve, 800));
        
        // Update interface with animation
        updateStatsWithAnimation(data);
        
    } catch (error) {
        console.error('Error loading member statistics:', error);
        showErrorState('Error al cargar estadísticas');
    }
}

// Function to show loading state
function showLoadingState() {
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.classList.add('loading');
    });
}

// Function to update stats with animation
function updateStatsWithAnimation(data) {
    // Remove loading state
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.classList.remove('loading');
    });
    
    // Animate counter updates
    animateCounter('totalSocios', data.total);
    animateCounter('sociosAlDia', data.alDia);
    animateCounter('sociosEnDeuda', data.enDeuda);
    animateCounter('pendientesHoy', data.pendientesHoy);
    
    // Update change indicators
    updateChangeIndicator('totalSocios', data.cambios.total, '+12% desde el mes pasado');
    updateChangeIndicator('sociosAlDia', data.cambios.alDia, '78% del total');
    updateChangeIndicator('sociosEnDeuda', data.cambios.enDeuda, '-5 desde ayer');
    updateChangeIndicator('pendientesHoy', data.cambios.pendientesHoy, 'Requiere atención');
}

// Function to animate counters
function animateCounter(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const startValue = parseInt(element.textContent) || 0;
    const duration = 1000;
    const startTime = performance.now();
    
    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const currentValue = Math.round(startValue + (targetValue - startValue) * easeOut);
        
        element.textContent = currentValue;
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    }
    
    requestAnimationFrame(updateCounter);
}

// Function to update change indicators
function updateChangeIndicator(statId, change, text) {
    const statElement = document.querySelector(`#${statId}`);
    if (!statElement) return; // Exit if element not found
    
    const card = statElement.closest('.card');
    if (!card) return; // Exit if no parent card found
    
    const changeElement = card.querySelector('.card-change');
    
    if (changeElement) {
        changeElement.className = 'card-change';
        if (change > 0) {
            changeElement.classList.add('positive');
            changeElement.innerHTML = `<i class="fas fa-arrow-up"></i> ${text}`;
        } else if (change < 0) {
            changeElement.classList.add('negative');
            changeElement.innerHTML = `<i class="fas fa-arrow-down"></i> ${text}`;
        } else {
            changeElement.innerHTML = `<i class="fas fa-minus"></i> ${text}`;
        }
    }
}

// Function to load recent activities
async function loadRecentActivities() {
    try {
        // In a real environment, this would make a call to your API
        // const response = await fetch('/api/actividades/recientes');
        // const activities = await response.json();
        
        // Example data (replace with real API call)
        const activities = [
            { 
                id: 1, 
                tipo: 'pago', 
                mensaje: 'Juan Pérez realizó un pago de Bs. 15',
                fecha: new Date(Date.now() - 2 * 60 * 60 * 1000),
                icono: 'money-bill-wave',
                color: 'success'
            },
            { 
                id: 2, 
                tipo: 'nuevo_socio', 
                mensaje: 'Nuevo socio registrado: María González',
                fecha: new Date(Date.now() - 4 * 60 * 60 * 1000),
                icono: 'user-plus',
                color: 'primary'
            },
            { 
                id: 3, 
                tipo: 'recordatorio', 
                mensaje: 'Recordatorio: 5 socios con pagos pendientes',
                fecha: new Date(Date.now() - 6 * 60 * 60 * 1000),
                icono: 'bell',
                color: 'warning'
            },
            { 
                id: 4, 
                tipo: 'actualizacion', 
                mensaje: 'Se actualizó la información de contacto de Carlos Rojas',
                fecha: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000),
                icono: 'edit',
                color: 'primary'
            },
            { 
                id: 5, 
                tipo: 'pago', 
                mensaje: 'Ana Martínez realizó un pago de Bs. 15',
                fecha: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000),
                icono: 'money-bill-wave',
                color: 'success'
            }
        ];
        
        // Update interface
        const activityList = document.getElementById('activityList');
        if (activityList) {
            // Clear current activities with fade out
            activityList.style.opacity = '0';
            
            setTimeout(() => {
                activityList.innerHTML = activities.map(activity => `
                    <div class="activity-item" style="opacity: 0; transform: translateY(20px);">
                        <div class="activity-icon">
                            <i class="fas fa-${activity.icono}"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-text">${activity.mensaje}</div>
                            <div class="activity-time">${formatTimeAgo(activity.fecha)}</div>
                        </div>
                    </div>
                `).join('');
                
                // Fade in with stagger
                activityList.style.opacity = '1';
                const items = activityList.querySelectorAll('.activity-item');
                items.forEach((item, index) => {
                    setTimeout(() => {
                        item.style.transition = 'all 0.3s ease';
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            }, 300);
        }
        
    } catch (error) {
        console.error('Error loading recent activities:', error);
        showErrorState('Error al cargar actividades recientes');
    }
}

// Function to show error state
function showErrorState(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--accent-error);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
    `;
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-triangle"></i>
        <span style="margin-left: 8px;">${message}</span>
    `;
    
    document.body.appendChild(errorDiv);
    
    setTimeout(() => {
        errorDiv.style.animation = 'slideOutRight 0.3s ease forwards';
        setTimeout(() => errorDiv.remove(), 300);
    }, 3000);
}

// Function to format time ago
function formatTimeAgo(date) {
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
        return 'Hace menos de un minuto';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `Hace ${minutes} minuto${minutes > 1 ? 's' : ''}`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `Hace ${hours} hora${hours > 1 ? 's' : ''}`;
    } else {
        const days = Math.floor(diffInSeconds / 86400);
        return `Hace ${days} día${days > 1 ? 's' : ''}`;
    }
}

// Function to format dates
function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('es-ES', options);
}

// Navigation item click handler
document.addEventListener('click', function(e) {
    if (e.target.closest('.nav-item')) {
        // Remove active class from all nav items
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Add active class to clicked item
        e.target.closest('.nav-item').classList.add('active');
        
        // Close sidebar on mobile after navigation
        if (window.innerWidth <= 1024) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.remove('show');
            overlay?.classList.remove('show');
            document.body.style.overflow = '';
        }
    }
});

// Logout function
function logout() {
    if (confirm('¿Está seguro de que desea cerrar sesión?')) {
        // Show loading
        const loadingDiv = document.createElement('div');
        loadingDiv.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        `;
        loadingDiv.innerHTML = `
            <div style="text-align: center;">
                <div class="loading" style="width: 40px; height: 40px; border-width: 4px;"></div>
                <p style="margin-top: 16px; color: var(--text); font-weight: 500;">Cerrando sesión...</p>
            </div>
        `;
        
        document.body.appendChild(loadingDiv);
        
        // Simulate logout process
        setTimeout(() => {
            window.location.href = '/login';
        }, 1500);
    }
}

// Add animations CSS
const additionalStyle = document.createElement('style');
additionalStyle.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
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
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(additionalStyle);