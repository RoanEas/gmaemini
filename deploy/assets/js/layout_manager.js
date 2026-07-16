/**
 * Layout Manager
 * Detects device type and window width to enforce PC or Mobile specific layouts globally.
 */
document.addEventListener('DOMContentLoaded', () => {
    function enforceDeviceLayout() {
        const isTouch = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0);
        const width = window.innerWidth;
        const isMobileDevice = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        // Define Mobile as either a mobile user-agent, or a touch device with a narrow screen
        const isMobileMode = isMobileDevice || (isTouch && width <= 1024) || (width <= 768);

        if (isMobileMode) {
            document.body.classList.remove('pc-mode');
            document.body.classList.add('mobile-mode');
        } else {
            document.body.classList.remove('mobile-mode');
            document.body.classList.add('pc-mode');
        }
    }
    
    // Initial run
    enforceDeviceLayout();
    
    // Re-run on resize
    window.addEventListener('resize', enforceDeviceLayout);
});
