// File: assets/app.js

// Import base styles, Bootstrap JS, and Font Awesome CSS
import './styles/app.css';
import 'bootstrap'; // Imports Bootstrap's JS (and Popper.js dependency)
import '@fortawesome/fontawesome-free/css/all.min.css';
import $ from 'jquery'; // Import jQuery
import 'bootstrap/dist/css/bootstrap.min.css'; // Import Bootstrap CSS

// Wait for the DOM to be ready before running jQuery code
$(document).ready(function () {
    console.log("JS Loaded and DOM Ready!"); // General confirmation

    // ========================================================================
    // === START: Lively Public Header Code (Sticky & Mobile Toggle) ===
    // ========================================================================

    console.log("Setting up header effects...");

    // --- Sticky Header on Scroll ---
    const $header = $('#main-header'); // Target the header by ID
    const scrollThreshold = 50;      // How many pixels to scroll down before sticking

    if ($header.length) { // Only run if the header element exists on the page
        console.log("Public Header element found, setting up scroll listener.");

        const handleScroll = () => {
            if ($(window).scrollTop() > scrollThreshold) {
                // Add class if scrolled past threshold
                $header.addClass('header-scrolled');
            } else {
                // Remove class if scrolled back to top
                $header.removeClass('header-scrolled');
            }
        };

        // Run on page load in case it's already scrolled
        handleScroll();

        // Attach the function to the window's scroll event
        $(window).on('scroll', handleScroll);

    } else {
        console.log("Public Header element #main-header not found (OK if on admin page).");
    }


    // --- Mobile Navigation Toggle ---
    const $mobileToggleBtn = $('#mobile-menu-toggle');        // The hamburger button
    const $navbarCollapse = $('#main-navbar-collapse');     // The container for mobile links
    const $mainNavUl = $('#main-navbar .main-nav-ul');     // The original UL in desktop nav

    // Check if all necessary elements exist for the public header mobile nav
    if ($mobileToggleBtn.length && $navbarCollapse.length && $mainNavUl.length) {
        console.log("Mobile nav elements found, setting up toggle.");

        // Clone navigation items ONCE to avoid duplication if clicked multiple times
        if ($navbarCollapse.is(':empty')) { // Only clone if the container has no children yet
            console.log("Cloning desktop nav for mobile menu.");
            const $clonedNav = $mainNavUl
                .clone() // Create a copy of the UL
                .removeClass('d-flex align-items-center gap-4') // Remove desktop layout classes
                .addClass('main-nav-ul-mobile'); // Add the specific class for mobile styling
            $navbarCollapse.append($clonedNav); // Add the cloned list to the mobile container
        }

        // Add click listener to the mobile toggle button
        $mobileToggleBtn.on('click', function() {
            console.log("Mobile toggle button clicked.");
            // Toggle the .show class on the mobile container to trigger CSS animations
            $navbarCollapse.toggleClass('show');

            // Toggle ARIA attribute for accessibility
            const isExpanded = $navbarCollapse.hasClass('show');
            $(this).attr('aria-expanded', isExpanded);

            // Optional: Change hamburger icon to X icon and back
            const $icon = $(this).find('i'); // Find the <i> tag within the button
            if (isExpanded) {
                $icon.removeClass('fa-bars').addClass('fa-times'); // Change to X
            } else {
                $icon.removeClass('fa-times').addClass('fa-bars'); // Change back to bars
            }
        });

        // Optional: Close mobile menu if user clicks outside the header area
        $(document).on('click', function(event) {
            // Check if the mobile menu is open AND if the click was *not* inside the header
            // Ensure $header variable is accessible here or re-select if needed
            if ($navbarCollapse.hasClass('show') && !$header.is(event.target) && $header.has(event.target).length === 0) {
                console.log("Clicked outside header, closing mobile menu.");
                $navbarCollapse.removeClass('show'); // Close menu
                $mobileToggleBtn.attr('aria-expanded', 'false'); // Reset ARIA
                $mobileToggleBtn.find('i').removeClass('fa-times').addClass('fa-bars'); // Reset icon
            }
        });

    } else {
        // Log warnings if elements are missing - helps debugging!
        // These warnings are normal if you are on an admin page that doesn't have these elements
        if (!$mobileToggleBtn.length) console.log("Mobile toggle button #mobile-menu-toggle not found (OK if on admin page).");
        if (!$navbarCollapse.length) console.log("Mobile collapse container #main-navbar-collapse not found (OK if on admin page).");
        if (!$mainNavUl.length) console.log("Main navigation list #main-navbar .main-nav-ul not found for cloning (OK if on admin page).");
    }

    // ======================================================================
    // === END: Lively Public Header Code ===
    // ======================================================================


    // ======================================================================
    // === START: Your Existing Admin Sidebar & Dark Mode Code ===
    // ======================================================================

    console.log("Setting up admin sidebar/darkmode listeners...");

    // --- Admin Sidebar Toggle Functionality ---
    const $sidebar = $('.admin-sidebar'); // Uses class selector
    const $toggleBtn = $('#sidebar-toggle'); // Uses ID selector

    // Check if ADMIN sidebar elements exist on the current page
    if ($toggleBtn.length > 0 && $sidebar.length > 0) {
        console.log("Admin Sidebar toggle elements found.");

        // Check initial state from localStorage (optional)
        if (localStorage.getItem('sidebarState') === 'collapsed') {
            console.log("Applying collapsed state from localStorage.");
            $sidebar.addClass('collapsed');
        }

        // Add click listener
        $toggleBtn.on('click', function () {
            console.log("Admin Sidebar toggle clicked.");
            $sidebar.toggleClass('collapsed');

            // Save state on toggle (optional)
            if ($sidebar.hasClass('collapsed')) {
                localStorage.setItem('sidebarState', 'collapsed');
                console.log("Saved admin sidebar state: collapsed");
            } else {
                localStorage.removeItem('sidebarState');
                console.log("Removed admin sidebar state (expanded)");
            }
        });

    } else {
        // Log warnings only if relevant elements are missing on a page where they SHOULD exist
        // These warnings are normal if you are on a public page without these admin elements
        if ($toggleBtn.length === 0) {
            console.log('Admin Sidebar toggle button (#sidebar-toggle) not found (OK if on public page).');
        }
        // Sidebar might exist on public pages if we reuse the class, check both button AND sidebar
        if ($sidebar.length === 0) {
            console.log('Admin Sidebar element (.admin-sidebar) not found (OK if on public page).');
        } else if ($toggleBtn.length === 0 && $sidebar.length > 0){
            console.log('Found sidebar element but no toggle button (maybe public page?).');
        }
    }

    // --- Dark Mode Toggle Functionality ---
    // This uses an ID, so it should work on any page where the button exists
    const $darkModeBtn = $('#dark-mode-toggle'); // Uses ID selector
    const $body = $('body'); // Target the body element

    if ($darkModeBtn.length > 0) {
        console.log("Dark mode toggle button found.");

        // Apply initial state based on localStorage
        if (localStorage.getItem('darkMode') === 'enabled') {
            console.log("Applying dark mode from localStorage.");
            $body.addClass('dark-mode');
            // Optional: Update Bootstrap theme attribute if using BS5.3+ dark mode integration
            $('html').attr('data-bs-theme', 'dark');
        } else {
            $('html').attr('data-bs-theme', 'light'); // Ensure light mode if not enabled
        }

        // Add click listener
        $darkModeBtn.on('click', function () {
            console.log("Dark mode toggle clicked.");
            $body.toggleClass('dark-mode');

            // Persist preference in localStorage
            if ($body.hasClass('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                console.log("Saved dark mode state: enabled");
                $('html').attr('data-bs-theme', 'dark'); // Update Bootstrap theme attribute
            } else {
                localStorage.removeItem('darkMode');
                console.log("Removed dark mode state (light)");
                $('html').attr('data-bs-theme', 'light'); // Update Bootstrap theme attribute
            }
        });
    } else {
        console.log('Dark mode toggle button (#dark-mode-toggle) not found.');
    }

    // ====================================================================
    // === END: Your Existing Admin Sidebar & Dark Mode Code ===
    // ====================================================================


    // --- Initialize Bootstrap components (like tooltips, dropdowns) if needed ---
    // Example: Enable tooltips everywhere
    // const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    // tooltipTriggerList.map(function (tooltipTriggerEl) {
    //   return new bootstrap.Tooltip(tooltipTriggerEl)
    // })

    console.log("Finished setting up JS listeners.");

}); // End of document ready