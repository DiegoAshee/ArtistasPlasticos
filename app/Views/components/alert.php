<?php
/**
 * Modern Alert Component
 * 
 * @param string $type        Type of alert (success, error, warning, info)
 * @param string $message     Message to display
 * @param bool   $dismissible Whether the alert can be dismissed
 * @param string $class       Additional CSS classes
 */
$type = $type ?? 'info';
$dismissible = $dismissible ?? true;
$class = $class ?? '';

// Map types to icons, colors and titles
$typeConfig = [
    'success' => [
        'icon' => 'check-circle',
        'title' => '¡Éxito!',
        'bg' => 'bg-emerald-50',
        'text' => 'text-emerald-800',
        'border' => 'border-emerald-200',
        'iconColor' => 'text-emerald-600'
    ],
    'error' => [
        'icon' => 'exclamation-triangle',
        'title' => 'Error',
        'bg' => 'bg-red-50',
        'text' => 'text-red-800',
        'border' => 'border-red-200',
        'iconColor' => 'text-red-600'
    ],
    'warning' => [
        'icon' => 'exclamation-circle',
        'title' => 'Advertencia',
        'bg' => 'bg-amber-50',
        'text' => 'text-amber-800',
        'border' => 'border-amber-200',
        'iconColor' => 'text-amber-600'
    ],
    'info' => [
        'icon' => 'info-circle',
        'title' => 'Información',
        'bg' => 'bg-blue-50',
        'text' => 'text-blue-800',
        'border' => 'border-blue-200',
        'iconColor' => 'text-blue-600'
    ]
];

// Default to info if type is invalid
$config = $typeConfig[$type] ?? $typeConfig['info'];
?>

<div class="alert-notification fixed top-6 right-6 z-50 max-w-md w-full transform transition-all duration-300 ease-in-out" 
     x-data="{ 
         show: true,
         init() {
             // Auto-hide after 5 seconds
             setTimeout(() => { 
                 this.hide();
             }, 5000);
             
             // Pause on hover
             this.$el.addEventListener('mouseenter', () => {
                 clearTimeout(this.timeout);
             });
             
             this.$el.addEventListener('mouseleave', () => {
                 this.timeout = setTimeout(() => {
                     this.hide();
                 }, 2000);
             });
         },
         hide() {
             this.show = false;
             // Remove from DOM after animation
             setTimeout(() => {
                 this.$el.remove();
             }, 300);
         }
     }" 
     x-show="show"
     x-transition:enter="transform ease-out duration-300 transition"
     x-transition:enter-start="translate-x-full opacity-0"
     x-transition:enter-end="translate-x-0 opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-x-0"
     x-transition:leave-end="opacity-0 translate-x-full">
    <div class="relative rounded-lg p-4 shadow-lg <?= $config['bg'] ?> <?= $config['border'] ?> border">
        <div class="flex items-start">
            <!-- Icon -->
            <div class="flex-shrink-0 pt-0.5">
                <div class="h-6 w-6 rounded-full <?= $config['bg'] ?> flex items-center justify-center">
                    <i class="fas fa-<?= $config['icon'] ?> <?= $config['iconColor'] ?> text-lg"></i>
                </div>
            </div>
            
            <!-- Content -->
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-semibold <?= $config['text'] ?>">
                    <?= $config['title'] ?>
                </h3>
                <div class="mt-1 text-sm <?= $config['text'] ?> opacity-90">
                    <?= htmlspecialchars($message) ?>
                </div>
            </div>
            
            <!-- Close Button -->
            <?php if ($dismissible): ?>
                <div class="ml-4 flex-shrink-0">
                    <button @click="hide()" class="inline-flex rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Progress Bar -->
        <div class="mt-2 h-1 bg-black bg-opacity-10 rounded-full overflow-hidden">
            <div class="h-full <?= $config['iconColor'] ?> bg-opacity-50 rounded-full progress-bar" 
                 x-data="{}" 
                 x-init="setTimeout(() => {
                     $el.style.transition = 'width 4.8s linear';
                     $el.style.width = '0%';
                 }, 100);">
            </div>
        </div>
    </div>
</div>

<style>
.alert-notification {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.progress-bar {
    width: 100%;
    transition: width 0.1s linear;
}
</style>
