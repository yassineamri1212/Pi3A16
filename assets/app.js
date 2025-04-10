// File: assets/app.js

// --- Essential Imports ---
// Import bootstrap FIRST if needed for Stimulus/other setup
import './bootstrap.js';
// Import CSS entry point - THIS IS CRITICAL FOR STYLES
import './styles/app.css';
// Import Bootstrap's JavaScript components
import 'bootstrap';
// Import Font Awesome CSS (ensure the package is installed: yarn add @fortawesome/fontawesome-free)
import '@fortawesome/fontawesome-free/css/all.min.css';
// Import jQuery
import $ from 'jquery';
// --- End Imports ---


// --- Main execution block ---
$(document).ready(function () {
    console.log("JS Loaded and DOM Ready!");

    // ========================================================================
    // === START: Lively Public Header Code (Sticky & Mobile Toggle) ===
    // (This code remains as it was)
    // ========================================================================

    console.log("Setting up public header effects...");

    // --- Sticky Header on Scroll ---
    const $header = $('#main-header'); // Target the header by ID
    const scrollThreshold = 50;      // How many pixels to scroll down before sticking

    if ($header.length) { // Only run if the header element exists on the page
        console.log("Public Header element found, setting up scroll listener.");

        const handleScroll = () => {
            if ($(window).scrollTop() > scrollThreshold) {
                $header.addClass('header-scrolled');
            } else {
                $header.removeClass('header-scrolled');
            }
        };
        handleScroll(); // Run on page load
        $(window).on('scroll', handleScroll);

    } else {
        console.log("Public Header element #main-header not found (OK if on admin page).");
    }

    // --- Mobile Navigation Toggle ---
    const $mobileToggleBtn = $('#mobile-menu-toggle');
    const $navbarCollapse = $('#main-navbar-collapse');
    const $mainNavUl = $('#main-navbar .main-nav-ul');

    if ($mobileToggleBtn.length && $navbarCollapse.length && $mainNavUl.length) {
        console.log("Mobile nav elements found, setting up toggle.");
        if ($navbarCollapse.is(':empty')) {
            console.log("Cloning desktop nav for mobile menu.");
            const $clonedNav = $mainNavUl.clone()
                .removeClass('d-flex align-items-center gap-4') // Remove desktop classes
                .addClass('main-nav-ul-mobile');             // Add mobile specific class if needed
            $navbarCollapse.append($clonedNav);
        } else {
            console.log("Mobile collapse container already has content, not cloning.");
        }
        $mobileToggleBtn.on('click', function() {
            console.log("Mobile toggle button clicked.");
            $navbarCollapse.toggleClass('show');
            const isExpanded = $navbarCollapse.hasClass('show');
            $(this).attr('aria-expanded', isExpanded);
            const $icon = $(this).find('i');
            if (isExpanded) {
                $icon.removeClass('fa-bars').addClass('fa-times');
            } else {
                $icon.removeClass('fa-times').addClass('fa-bars');
            }
        });
        // Close mobile menu if clicking outside the header
        $(document).on('click', function(event) {
            // Check if the click target is outside the main header AND the navbar collapse is shown
            if ($navbarCollapse.hasClass('show') && $header.length > 0 && !$header.is(event.target) && $header.has(event.target).length === 0) {
                console.log("Clicked outside header, closing mobile menu.");
                $navbarCollapse.removeClass('show');
                $mobileToggleBtn.attr('aria-expanded', 'false');
                $mobileToggleBtn.find('i').removeClass('fa-times').addClass('fa-bars');
            }
        });

    } else {
        if (!$mobileToggleBtn.length) console.log("Mobile toggle button #mobile-menu-toggle not found.");
        if (!$navbarCollapse.length) console.log("Mobile collapse container #main-navbar-collapse not found.");
        if (!$mainNavUl.length) console.log("Main navigation list #main-navbar .main-nav-ul not found for cloning.");
    }
    console.log("Finished setting up public header effects.");

    // ======================================================================
    // === END: Lively Public Header Code ===
    // ======================================================================


    // --- REMOVE STARTING COMMENT MARKER HERE --- <--- FIX
    // ======================================================================
    // === START: Admin Sidebar & Dark Mode - EVENT DELEGATION ===
    // ======================================================================

    console.log("ADMIN DEBUG: Attempting to set up admin toggles using EVENT DELEGATION...");

    const $bodyForToggles = $('body'); // Target body once for delegation

    // --- Admin Sidebar Toggle (Only applies if #sidebar-toggle exists) ---
    $bodyForToggles.on('click', '#sidebar-toggle', function() {
        console.log("ADMIN DEBUG: >>> Sidebar toggle CLICK via DELEGATION! <<<");
        const $adminLayout = $('.admin-layout');
        const $sidebar = $('.admin-sidebar');
        if ($adminLayout.length && $sidebar.length) {
            $adminLayout.toggleClass('sidebar-collapsed'); // Toggles main layout class
            $sidebar.toggleClass('collapsed');           // Toggles sidebar specific class
            // Save state to localStorage
            if ($adminLayout.hasClass('sidebar-collapsed')) {
                localStorage.setItem('sidebarState', 'collapsed');
                console.log("ADMIN DEBUG: Saved sidebar state: collapsed");
            } else {
                localStorage.removeItem('sidebarState');
                console.log("ADMIN DEBUG: Removed sidebar state (expanded)");
            }
        } else {
            console.warn("ADMIN DEBUG: Sidebar/Layout element not found inside click handler!");
        }
    });
    // Only log attachment if the button might exist (don't need extra check here)
    console.log("ADMIN DEBUG: Delegated sidebar click listener attached to body.");


    // --- Dark Mode Toggle (Applies if #dark-mode-toggle exists in admin OR public) ---
    $bodyForToggles.on('click', '#dark-mode-toggle', function() {
        console.log("DEBUG: >>> Dark mode toggle CLICK via DELEGATION! <<<");
        const $htmlEl = $('html'); // Target the <html> element
        const $darkModeBtn = $(this); // The button that was clicked

        // Function to apply theme changes (HTML attribute and button icon)
        const applyTheme = (theme) => {
            console.log(`DEBUG: Applying theme: ${theme}`);
            $htmlEl.attr('data-bs-theme', theme); // Set Bootstrap theme attribute
            const $icon = $darkModeBtn.find('i'); // Find the icon within the clicked button
            if (theme === 'dark') {
                $icon.removeClass('fa-moon').addClass('fa-sun'); // Change icon to sun
            } else {
                $icon.removeClass('fa-sun').addClass('fa-moon'); // Change icon to moon
            }
        };

        // Determine the new theme based on the current one
        const currentTheme = $htmlEl.attr('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        // Apply the new theme and save it to localStorage
        applyTheme(newTheme);
        localStorage.setItem('bsTheme', newTheme); // Use 'bsTheme' consistently
        console.log(`DEBUG: Set theme preference to ${newTheme} in localStorage`);
    });
    // Only log attachment if the button might exist
    console.log("DEBUG: Delegated dark mode click listener attached to body.");

    // --- Apply Initial Theme on Page Load ---
    // This should run regardless of admin or public page
    const $htmlElForInit = $('html'); // Target <html> element again
    const $darkModeBtnForIcon = $('#dark-mode-toggle'); // Find the button to update its icon
    // Get theme from localStorage or default to 'light'
    const initialTheme = localStorage.getItem('bsTheme') || 'light';
    console.log(`DEBUG: Applying initial theme from localStorage: ${initialTheme}`);
    $htmlElForInit.attr('data-bs-theme', initialTheme); // Apply theme attribute immediately
    // Update the icon on the button if the button exists on the current page
    if ($darkModeBtnForIcon.length) {
        console.log("DEBUG: Dark mode button found, updating initial icon.");
        const $icon = $darkModeBtnForIcon.find('i');
        if (initialTheme === 'dark') {
            $icon.removeClass('fa-moon').addClass('fa-sun');
        } else {
            $icon.removeClass('fa-sun').addClass('fa-moon');
        }
    } else {
        console.log("DEBUG: Dark mode button not found on this page (OK).");
    }

    // --- Apply Initial Sidebar State on Page Load ---
    // This should only run if admin layout elements are present
    const $adminLayoutForInit = $('.admin-layout');
    const $sidebarForInit = $('.admin-sidebar');
    const initialStateSidebar = localStorage.getItem('sidebarState');
    // Check if we are potentially on an admin page AND state is 'collapsed'
    if (initialStateSidebar === 'collapsed' && $adminLayoutForInit.length && $sidebarForInit.length) {
        console.log("ADMIN DEBUG: Applying initial collapsed sidebar state from localStorage.");
        $adminLayoutForInit.addClass('sidebar-collapsed');
        $sidebarForInit.addClass('collapsed');
    } else if ($adminLayoutForInit.length) {
        console.log("ADMIN DEBUG: Initial sidebar state is expanded or not set.");
    }


    // ====================================================================
    // === END: Admin Sidebar & Dark Mode - EVENT DELEGATION ===
    // ====================================================================
    // --- REMOVE ENDING COMMENT MARKER HERE --- <--- FIX


    console.log("Finished setting up JS listeners.");

}); // End of document ready