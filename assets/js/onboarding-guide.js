/**
 * Onboarding Guide Script
 * Initialize Bootstrap Popovers for sidebar info icons
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize all popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => {
        return new bootstrap.Popover(popoverTriggerEl, {
            html: true,
            trigger: 'click',
            container: 'body',
            customClass: 'onboarding-popover'
        });
    });

    // Close popover when clicking outside
    document.addEventListener('click', function (e) {
        // If click is not on a popover trigger or popover content
        if (!e.target.closest('[data-bs-toggle="popover"]') && !e.target.closest('.popover')) {
            popoverList.forEach(popover => {
                popover.hide();
            });
        }
    });

    // Prevent popover info icon click from triggering parent link
    document.querySelectorAll('.info-icon').forEach(icon => {
        icon.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
        });
    });
});
