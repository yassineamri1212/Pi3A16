// File: assets/app.js

// --- Essential Imports ---
import './bootstrap.js';
import './styles/app.css';
import 'bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';
import $ from 'jquery';
// --- End Imports ---

// --- START: Main execution block (ONLY ONE document.ready) ---
$(document).ready(function () {
    console.log("JS Loaded and DOM Ready!");

    // ========================================================================
    // === START: Lively Public Header Code (Sticky & Mobile Toggle) ===
    // ========================================================================
    console.log("Setting up public header effects...");
    const $header = $('#main-header');
    const scrollThreshold = 50;
    if ($header.length) {
        console.log("Public Header element found, setting up scroll listener.");
        const handleScroll = () => {
            if ($(window).scrollTop() > scrollThreshold) {
                $header.addClass('header-scrolled');
            } else {
                $header.removeClass('header-scrolled');
            }
        };
        handleScroll(); // Run on page load
        $(window).on('scroll', handleScroll); // Run on scroll
    } else {
        console.log("Public Header element #main-header not found (OK if on admin page).");
    }

    // --- Mobile Menu Toggle & Cloning ---
    const $mobileToggleBtn = $('#mobile-menu-toggle');
    const $navbarCollapse = $('#main-navbar-collapse');
    const $mainNavUl = $('#main-navbar .main-nav-ul'); // Desktop nav list selector

    if ($mobileToggleBtn.length && $navbarCollapse.length && $mainNavUl.length) {
        console.log("Mobile nav elements found, setting up toggle.");
        if ($navbarCollapse.is(':empty')) {
            console.log("Cloning desktop nav for mobile menu.");
            const $clonedNav = $mainNavUl.clone()
                .removeClass('d-flex align-items-center gap-4') // Remove desktop layout classes
                .addClass('main-nav-ul-mobile'); // Add mobile layout class
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
        $(document).on('click', function(event) {
            if ($navbarCollapse.hasClass('show') &&
                $header.length > 0 && // Check if header exists
                !$header.is(event.target) && // Clicked outside header element itself
                $header.has(event.target).length === 0) // Clicked not within any element inside header
            {
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


    // ======================================================================
    // === START: Admin Sidebar & Dark Mode - EVENT DELEGATION ===
    // ======================================================================
    console.log("ADMIN DEBUG: Attempting to set up admin toggles using EVENT DELEGATION...");
    const $bodyForToggles = $('body'); // Use body for delegation

    // --- Sidebar Toggle ---
    $bodyForToggles.on('click', '#sidebar-toggle', function() {
        console.log("ADMIN DEBUG: >>> Sidebar toggle CLICK via DELEGATION! <<<");
        const $adminLayout = $('.admin-layout');
        const $sidebar = $('.admin-sidebar');
        if ($adminLayout.length && $sidebar.length) {
            $adminLayout.toggleClass('sidebar-collapsed');
            $sidebar.toggleClass('collapsed');
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
    console.log("ADMIN DEBUG: Delegated sidebar click listener attached to body.");

    // --- Dark Mode Toggle ---
    $bodyForToggles.on('click', '#dark-mode-toggle', function() {
        console.log("DEBUG: >>> Dark mode toggle CLICK via DELEGATION! <<<");
        const $htmlEl = $('html');
        const $darkModeBtn = $(this); // The button that was clicked
        const applyTheme = (theme) => {
            console.log(`DEBUG: Applying theme: ${theme}`);
            $htmlEl.attr('data-bs-theme', theme);
            const $icon = $darkModeBtn.find('i'); // Find icon within the clicked button
            if (theme === 'dark') {
                $icon.removeClass('fa-moon').addClass('fa-sun');
            } else {
                $icon.removeClass('fa-sun').addClass('fa-moon');
            }
        };
        const currentTheme = $htmlEl.attr('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
        localStorage.setItem('bsTheme', newTheme); // Save preference
        console.log(`DEBUG: Set theme preference to ${newTheme} in localStorage`);
    });
    console.log("DEBUG: Delegated dark mode click listener attached to body.");

    // --- Apply Initial Theme & Sidebar State on Load ---
    const $htmlElForInit = $('html');
    const $darkModeBtnForIcon = $('#dark-mode-toggle'); // Find the button for icon update
    const initialTheme = localStorage.getItem('bsTheme') || 'light'; // Default to light
    console.log(`DEBUG: Applying initial theme from localStorage: ${initialTheme}`);
    $htmlElForInit.attr('data-bs-theme', initialTheme);
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
    const $adminLayoutForInit = $('.admin-layout');
    const $sidebarForInit = $('.admin-sidebar');
    const initialStateSidebar = localStorage.getItem('sidebarState');
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


    // ======================================================================
    // === START: Chatbot MESSAGE SENDING Logic ===
    // (For the EMBEDDED chatbot on the homepage)
    // ======================================================================
    console.log("Setting up chatbot message sending logic...");

    // Find chat elements (these will only be found on the homepage now)
    const chatContainer = document.getElementById('chatbot-container'); // The main container
    const chatMessageContainer = document.getElementById('chatbot-messages');
    const chatInputField = document.getElementById('chatbot-input');
    const chatSendButton = document.getElementById('chatbot-send-btn');
    const chatApiUrl = '/api/chatbot/message'; // Backend endpoint

    // --- IMPORTANT: Only run chatbot JS if the main message container exists ---
    if (chatContainer && chatMessageContainer && chatInputField && chatSendButton) {
        console.log("Chatbot messaging elements found. Initializing.");

        // --- Function to add a message to the chat window ---
        const addChatMessage = (text, sender, isLoading = false, isError = false) => {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message', sender);
            if (isLoading) {
                messageDiv.classList.add('loading');
            }
            if (isError) {
                messageDiv.classList.add('error');
            }
            const paragraph = document.createElement('p');
            paragraph.textContent = text; // Use textContent for safety
            messageDiv.appendChild(paragraph);
            chatMessageContainer.appendChild(messageDiv);
            chatMessageContainer.scrollTop = chatMessageContainer.scrollHeight; // Scroll down
            return messageDiv;
        };

        // --- sendMessage function ---
        const sendChatMessage = async () => {
            const userMessageText = chatInputField.value.trim();
            if (userMessageText === '') {
                return;
            }
            addChatMessage(userMessageText, 'user');
            chatInputField.value = '';
            chatInputField.focus();
            const loadingIndicator = addChatMessage(" ", 'bot', true); // Add visual loading state
            chatInputField.disabled = true;
            chatSendButton.disabled = true;

            try {
                const response = await fetch(chatApiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: userMessageText })
                });

                if (loadingIndicator) { loadingIndicator.remove(); } // Remove loading state

                if (!response.ok) {
                    let errorMsg = `Error: ${response.status}`;
                    try {
                        const errorData = await response.json();
                        errorMsg = errorData.error || `Error: ${response.status}`;
                    } catch (jsonError) { /* Ignore */ }
                    console.error("Chatbot API Error:", errorMsg);
                    addChatMessage(`Sorry, an error occurred. (Code: ${response.status})`, 'bot', false, true);
                } else {
                    const data = await response.json();
                    if (data.reply) {
                        addChatMessage(data.reply, 'bot');
                    } else {
                        console.error("Chatbot API Response missing 'reply':", data);
                        addChatMessage("Sorry, I received an unexpected response.", 'bot', false, true);
                    }
                }
            } catch (error) {
                if (loadingIndicator) { loadingIndicator.remove(); }
                console.error('Error sending chat message:', error);
                addChatMessage(`Sorry, could not connect. Please check connection.`, 'bot', false, true);
            } finally {
                chatInputField.disabled = false;
                chatSendButton.disabled = false;
                chatInputField.focus();
            }
        }; // --- END sendChatMessage ---

        // --- Event Listeners ---
        chatSendButton.addEventListener('click', sendChatMessage);
        console.log("Chatbot Send button listener attached.");

        chatInputField.addEventListener('keypress', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendChatMessage();
            }
        });
        console.log("Chatbot Input listener attached.");

        chatMessageContainer.scrollTop = chatMessageContainer.scrollHeight; // Scroll down initially
        console.log("Chatbot message sending logic initialized.");

    } else {
        // Log if elements aren't found (expected on pages other than homepage)
        console.log("Chatbot elements not found on this page. Skipping message sending setup.");
    }
    // ======================================================================
    // === END: Chatbot MESSAGE SENDING Logic ===
    // ======================================================================


    console.log("Finished setting up ALL JS listeners in app.js.");

}); // --- END: Main execution block (ONLY ONE document.ready) ---