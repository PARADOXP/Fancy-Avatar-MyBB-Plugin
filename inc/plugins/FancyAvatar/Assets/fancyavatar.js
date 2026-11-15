/**
 * Fancy Avatar JavaScript
 * 
 * Client-side functionality for the Fancy Avatar plugin.
 * 
 * @package FancyAvatar
 * @version 1.0.0
 */

var FancyAvatar = {
    /**
     * Initialize FancyAvatar functionality
     */
    init: function() {
        // Placeholder for any client-side functionality
        // The plugin primarily works server-side via PHP hooks
    },
    
    /**
     * Get a DiceBear avatar URL for a given seed
     * 
     * @param {string} seed The seed for avatar generation
     * @param {string} style The DiceBear style
     * @param {number} size The avatar size in pixels
     * @returns {string} The avatar URL
     */
    getUrl: function(seed, style, size) {
        style = style || 'adventurer';
        size = size || 128;
        
        return 'https://api.dicebear.com/7.x/' + encodeURIComponent(style) + '/svg?seed=' + encodeURIComponent(seed) + '&size=' + size;
    },
    
    /**
     * Preview a DiceBear avatar style
     * 
     * @param {string} style The DiceBear style to preview
     * @param {HTMLElement} container The container element to show preview in
     */
    preview: function(style, container) {
        if (!container) return;
        
        var url = this.getUrl('preview', style, 64);
        container.innerHTML = '<img src="' + url + '" alt="Preview" width="64" height="64" style="border-radius: 50%;" />';
    }
};

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        FancyAvatar.init();
    });
} else {
    FancyAvatar.init();
}
