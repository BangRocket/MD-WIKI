/**
 * MD-WIKI Theme Switcher
 * 
 * Handles dark/light mode theme switching and persistence
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get theme toggle button
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    
    // Check for saved theme preference or respect OS preference
    const savedTheme = localStorage.getItem('md-wiki-theme');
    const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Apply theme based on saved preference or OS preference
    if (savedTheme === 'dark' || (!savedTheme && prefersDarkMode)) {
        enableDarkMode();
    } else {
        enableLightMode();
    }
    
    // Add click event listener to theme toggle button
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function() {
            if (document.documentElement.classList.contains('dark-mode')) {
                enableLightMode();
            } else {
                enableDarkMode();
            }
        });
    }
    
    /**
     * Enable dark mode
     */
    function enableDarkMode() {
        document.documentElement.classList.add('dark-mode');
        localStorage.setItem('md-wiki-theme', 'dark');
        
        // Update theme meta tag for mobile browsers
        updateThemeMetaTag('#121212');
    }
    
    /**
     * Enable light mode
     */
    function enableLightMode() {
        document.documentElement.classList.remove('dark-mode');
        localStorage.setItem('md-wiki-theme', 'light');
        
        // Update theme meta tag for mobile browsers
        updateThemeMetaTag('#f8f9fa');
    }
    
    /**
     * Update theme-color meta tag for mobile browsers
     * 
     * @param {string} color - Color value in hex format
     */
    function updateThemeMetaTag(color) {
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        
        if (!metaThemeColor) {
            metaThemeColor = document.createElement('meta');
            metaThemeColor.name = 'theme-color';
            document.head.appendChild(metaThemeColor);
        }
        
        metaThemeColor.content = color;
    }
});
