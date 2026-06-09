/**
 * Table of Content Generator - Administrative Control Suite JavaScript Engine Core
 */
document.addEventListener('DOMContentLoaded', function() {
    // Instantiate core WordPress color validation inputs
    const colorFields = document.querySelectorAll('.toc-color-field');
    if (colorFields.length && typeof jQuery.fn.wpColorPicker === 'function') {
        jQuery(colorFields).wpColorPicker();
    }
});