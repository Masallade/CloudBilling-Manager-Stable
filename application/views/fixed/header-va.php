<link rel="stylesheet" type="text/css" href="<?= assets_url() ?>app-assets/<?= LTR ?>/core/menu/menu-types/vertical-menu-modern.css">
<!-- Font Awesome CSS -->
<style>
    /* Comprehensive Font Awesome icon fixes */
    .fa,
    .fas,
    .far,
    .fab,
    .fal,
    .fad {
        font-family: "Font Awesome 5 Free" !important;
        font-weight: 900 !important;
        font-style: normal !important;
        font-variant: normal !important;
        text-rendering: auto !important;
        line-height: 1 !important;
        -webkit-font-smoothing: antialiased !important;
        -moz-osx-font-smoothing: grayscale !important;
        display: inline-block !important;
    }

    /* Ensure all Font Awesome icons are visible */
    i.fa,
    i.fas,
    i.far,
    i.fab,
    i.fal,
    i.fad {
        display: inline-block !important;
        font-style: normal !important;
        font-variant: normal !important;
        text-rendering: auto !important;
        line-height: 1 !important;
        font-family: "Font Awesome 5 Free" !important;
        font-weight: 900 !important;
        -webkit-font-smoothing: antialiased !important;
        -moz-osx-font-smoothing: grayscale !important;
        width: auto !important;
        height: auto !important;
    }

    /* Force all menu icons to display properly */
    .navigation i,
    .menu-content i,
    .nav-item i,
    .dropdown-menu i,
    .main-menu i {
        font-family: "Font Awesome 5 Free" !important;
        font-weight: 900 !important;
        display: inline-block !important;
        font-style: normal !important;
        text-rendering: auto !important;
        line-height: 1 !important;
        width: 20px !important;
        text-align: center !important;
        margin-right: 8px !important;
        font-size: 16px !important;
    }

    /* Specific fixes for menu items */
    .menu-content i {
        width: 16px !important;
        margin-right: 6px !important;
        font-size: 14px !important;
    }

    /* Ensure proper icon rendering in dropdowns */
    .dropdown-menu i {
        width: 16px !important;
        text-align: center !important;
        margin-right: 6px !important;
        font-size: 14px !important;
    }

    /* Additional icon fixes for compatibility */
    .fa.fa-pie-chart:before {
        content: "\f200";
    }

    .fa.fa-dollar:before {
        content: "\f155";
    }

    .fa.fa-money:before {
        content: "\f0d6";
    }

    .fa.fa-clock-o:before {
        content: "\f017";
    }

    .fa.fa-check-circle:before {
        content: "\f058";
    }

    .fa.fa-print:before {
        content: "\f02f";
    }

    .fa.fa-user:before {
        content: "\f007";
    }

    .fa.fa-reply:before {
        content: "\f112";
    }

    .fa.fa-check-square:before {
        content: "\f14a";
    }

    .fa.fa-undo:before {
        content: "\f0e2";
    }

    .fa.fa-truck:before {
        content: "\f0d1";
    }

    .fa.fa-file-text:before {
        content: "\f15c";
    }

    .fa.fa-eye:before {
        content: "\f06e";
    }

    .fa.fa-exchange:before {
        content: "\f0ec";
    }

    .fa.fa-tags:before {
        content: "\f02c";
    }

    .fa.fa-building:before {
        content: "\f1ad";
    }

    .fa.fa-balance-scale:before {
        content: "\f24e";
    }

    .fa.fa-pencil:before {
        content: "\f040";
    }

    .fa.fa-barcode:before {
        content: "\f02a";
    }

    .fa.fa-users:before {
        content: "\f0c0";
    }

    .fa.fa-user-plus:before {
        content: "\f234";
    }

    .fa.fa-list:before {
        content: "\f03a";
    }

    .fa.fa-file-text-o:before {
        content: "\f15c";
    }

    .fa.fa-history:before {
        content: "\f1da";
    }

    .fa.fa-briefcase:before {
        content: "\f0b1";
    }

    .fa.fa-tag:before {
        content: "\f02b";
    }

    /* Feather Icon to Font Awesome 5 mappings for compatibility */
    .ft-user:before {
        content: "\f007";
    }

    .ft-users:before {
        content: "\f0c0";
    }

    .ft-unlock:before {
        content: "\f09c";
    }

    .ft-calendar:before {
        content: "\f073";
    }

    .ft-sun:before {
        content: "\f185";
    }

    .ft-credit-card:before {
        content: "\f09d";
    }

    .ft-folder:before {
        content: "\f07b";
    }

    .ft-edit:before {
        content: "\f044";
    }

    .ft-book:before {
        content: "\f02d";
    }

    .ft-list:before {
        content: "\f03a";
    }

    .ft-clipboard:before {
        content: "\f328";
    }

    .ft-file-text:before {
        content: "\f15c";
    }

    .ft-eye:before {
        content: "\f06e";
    }

    .ft-plus:before {
        content: "\f067";
    }

    .ft-arrow-right:before {
        content: "\f061";
    }

    .ft-repeat:before {
        content: "\f01e";
    }

    .ft-trending-up:before {
        content: "\f3c5";
    }

    .ft-trending-down:before {
        content: "\f3c6";
    }

    .ft-percent:before {
        content: "\f295";
    }

    .ft-download:before {
        content: "\f019";
    }

    .ft-database:before {
        content: "\f1c0";
    }

    .ft-package:before {
        content: "\f4e6";
    }

    .ft-file:before {
        content: "\f15b";
    }

    .ft-trash-2:before {
        content: "\f2ed";
    }

    .ft-edit-3:before {
        content: "\f044";
    }

    .ft-settings:before {
        content: "\f013";
    }

    .ft-globe:before {
        content: "\f0ac";
    }

    .ft-home:before {
        content: "\f015";
    }

    .ft-layout:before {
        content: "\f0c8";
    }

    .ft-zap:before {
        content: "\f0e7";
    }

    .ft-shopping-cart:before {
        content: "\f07a";
    }

    .ft-power:before {
        content: "\f011";
    }

    .ft-menu:before {
        content: "\f0c9";
    }

    .ft-briefcase:before {
        content: "\f0b1";
    }

    .ft-plus-circle:before {
        content: "\f055";
    }

    .ft-check-square:before {
        content: "\f14a";
    }

    /* Additional responsive fixes */
    @media (max-width: 768px) {

        .navigation i,
        .menu-content i,
        .nav-item i,
        .dropdown-menu i,
        .main-menu i {
            font-size: 14px !important;
            width: 18px !important;
        }

        .menu-content i {
            font-size: 12px !important;
            width: 14px !important;
        }
    }

    /* ============================================
       SIDEBAR HOVER EXPAND - Mimics menu-expanded state
       When hovering over .main-menu, apply all expanded styles
       while keeping body in menu-collapsed state
       ============================================ */
    
    /* Ensure sidebar is collapsed by default - override expanded class */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu {
        width: 60px !important;
        z-index: 999999 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        height: 100vh !important;
        display: flex !important;
        flex-direction: column !important;
    }
    
    /* Ensure collapsed menu content also takes full height and fills available space */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu-content {
        height: 100vh !important;
        max-height: 100vh !important;
        min-height: 100vh !important;
        flex: 1 1 auto !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }
    
    /* Ensure navigation list in collapsed menu takes full available height and spreads items evenly */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu-content .navigation {
        min-height: 100% !important;
        height: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
    }
    
    /* Ensure navigation list items work properly with flexbox in collapsed state */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu-content .navigation > li {
        flex: 0 0 auto !important;
    }
    
    /* Ensure menu is positioned at the top when expanded */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu,
    .main-menu.menu-fixed {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        height: 100vh !important;
        display: flex !important;
        flex-direction: column !important;
    }
    
    /* Ensure expanded menu content also takes full height and fills available space */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu-content {
        flex: 1 1 auto !important;
        min-height: 0 !important;
    }
    
    /* Ensure navigation list in expanded menu spreads items evenly across height */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu-content .navigation {
        height: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
    }
    
    /* Ensure navigation list items work properly with flexbox in expanded state */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu-content .navigation > li {
        flex: 0 0 auto !important;
    }
    
    /* Fix Perfect Scrollbar calculation - ensure main-menu-content has proper height */
    .main-menu-content.ps-container.ps-theme-light.ps-active-y {
        height: 100% !important;
        max-height: 100vh !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        position: relative !important;
    }
    
    /* Ensure the navigation list inside doesn't cause excessive scrolling */
    .main-menu-content .navigation.navigation-main {
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }
    
    /* Override height for collapsed and expanded states to use flexbox */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu-content .navigation.navigation-main,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu-content .navigation.navigation-main {
        height: 100% !important;
        max-height: 100% !important;
    }
    
    /* Hide Perfect Scrollbar - remove scrollbar completely */
    .main-menu-content .ps-scrollbar-y-rail,
    .main-menu-content .ps-scrollbar-y,
    .main-menu-content .ps-scrollbar-x-rail,
    .main-menu-content .ps-scrollbar-x {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
    
    /* Hide native browser scrollbar for sidebar */
    .main-menu-content {
        scrollbar-width: none !important; /* Firefox */
        -ms-overflow-style: none !important; /* IE and Edge */
    }
    
    /* Hide native browser scrollbar for WebKit browsers (Chrome, Safari, etc.) */
    .main-menu-content::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    
    /* Ensure content is still scrollable but without visible scrollbar */
    .main-menu-content.ps-container.ps-theme-light.ps-active-y {
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }
    
    /* Prevent hidden/collapsed submenus from affecting scroll calculation */
    /* This applies to ALL submenu items: nav-item, menu-item, and any nested submenus */
    .main-menu-content .navigation li:not(.open) > .menu-content,
    .main-menu-content .navigation li.has-sub:not(.open) > .menu-content,
    .main-menu-content .navigation li.nav-item.has-sub:not(.open) > .menu-content,
    .main-menu-content .navigation li.menu-item.has-sub:not(.open) > .menu-content,
    .main-menu-content .menu-content li.has-sub:not(.open) > .menu-content,
    .main-menu-content .menu-content li.menu-item.has-sub:not(.open) > .menu-content {
        display: none !important;
        height: 0 !important;
        overflow: hidden !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }

    /* Ensure all sidebar children inherit high z-index */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu * {
        position: relative;
        z-index: inherit;
    }

    /* Ensure app-content doesn't cover the sidebar */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .app-content.content {
        z-index: 1 !important;
        position: relative !important;
    }

    /* Ensure navbar doesn't cover the sidebar */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .header-navbar {
        z-index: 999998 !important;
    }

    /* Ensure all other elements have lower z-index than sidebar */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .content-wrapper,
    body.vertical-layout.vertical-menu-modern.menu-collapsed .content-body,
    body.vertical-layout.vertical-menu-modern.menu-collapsed .card,
    body.vertical-layout.vertical-menu-modern.menu-collapsed .navbar-container {
        z-index: 1 !important;
        position: relative !important;
    }

    /* Ensure main-menu-content also has high z-index */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu-content {
        z-index: inherit !important;
    }

    /* Show menu text when expanded - HIGH PRIORITY */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation > li > a > span,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.nav-item > a > span,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.menu-item > a > span {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        width: auto !important;
        height: auto !important;
    }

    /* Ensure menu has proper width when expanded */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu {
        width: 260px !important;
    }

    /* Force show all navigation spans - override any external CSS */
    .main-menu.menu-expanded .navigation > li > a > span,
    .main-menu.menu-expanded .navigation li > a > span,
    .main-menu.expanded .navigation > li > a > span,
    .main-menu.expanded .navigation li > a > span,
    body.menu-expanded .main-menu .navigation > li > a > span,
    body.menu-expanded .main-menu .navigation li > a > span,
    body.menu-expanded .main-menu.expanded .navigation > li > a > span,
    body.menu-expanded .main-menu.expanded .navigation li > a > span {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        width: auto !important;
        height: auto !important;
        max-width: none !important;
    }

    /* Ensure navigation links have proper layout */
    body.menu-expanded .main-menu .navigation > li > a,
    body.menu-expanded .main-menu.expanded .navigation > li > a {
        display: flex !important;
        align-items: center !important;
        white-space: nowrap !important;
    }

    /* Override any width restrictions on spans */
    body.menu-expanded .main-menu .navigation > li > a > span {
        max-width: 100% !important;
        overflow: visible !important;
        text-overflow: clip !important;
    }

    /* Hide menu text by default when collapsed */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu .navigation > li > a > span {
        display: none !important;
    }

    /* Hide navigation header text by default */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu .navigation > li.navigation-header span {
        display: none !important;
    }

    /* Hide submenu arrows by default when collapsed */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu .navigation li.has-sub > a:not(.mm-next):after {
        display: none !important;
    }
    
    /* Show and style submenu arrows when menu is expanded */
    /* This applies to ALL submenu items: nav-item, menu-item, and any nested submenus */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub > a:not(.mm-next):after,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.nav-item.has-sub > a:not(.mm-next):after,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.menu-item.has-sub > a:not(.mm-next):after,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.has-sub > a:not(.mm-next):after,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.menu-item.has-sub > a:not(.mm-next):after {
        content: "\f105" !important;
        font-family: 'FontAwesome' !important;
        font-size: 1rem !important;
        display: inline-block !important;
        position: absolute !important;
        right: 20px !important;
        top: 50% !important;
        transform: translateY(-50%) rotate(0deg) !important;
        transition: transform 0.2s ease-in-out !important;
    }
    
    /* Rotate chevron when submenu is open (expanded menu) */
    /* This applies to ALL submenu items when they have .open class */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open > a:not(.mm-next):after,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.nav-item.has-sub.open > a:not(.mm-next):after,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.menu-item.has-sub.open > a:not(.mm-next):after,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.has-sub.open > a:not(.mm-next):after,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.menu-item.has-sub.open > a:not(.mm-next):after {
        transform: translateY(-50%) rotate(90deg) !important;
    }

    /* Hide badges text by default when collapsed */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu .navigation > li > a > span.badge {
        display: none !important;
    }

    /* Ensure menu content (submenus) are properly positioned when collapsed */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu .menu-content {
        display: none !important;
    }

    /* Show menu content (submenus) when expanded and parent is open */
    /* This applies to ALL submenu items: nav-item, menu-item, and any nested submenus */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open > .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.nav-item.has-sub.open > .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.menu-item.has-sub.open > .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.has-sub.open > .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.menu-item.has-sub.open > .menu-content {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    /* Ensure menu-content is visible when parent has open class */
    /* This applies to ALL submenu items: nav-item, menu-item, and any nested submenus */
    body.menu-expanded .main-menu .navigation li.open .menu-content,
    body.menu-expanded .main-menu .navigation li.has-sub.open .menu-content,
    body.menu-expanded .main-menu .navigation li.menu-item.has-sub.open .menu-content,
    body.menu-expanded .main-menu .menu-content li.open .menu-content,
    body.menu-expanded .main-menu .menu-content li.has-sub.open .menu-content,
    body.menu-expanded .main-menu .menu-content li.menu-item.has-sub.open .menu-content {
        display: block !important;
        visibility: visible !important;
    }

    /* IMPORTANT: Hide submenus by default - only show when parent has .open class */
    /* This applies to ALL submenu items: nav-item, menu-item, and any nested submenus */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub:not(.open) > .menu-content,
    body.menu-expanded .main-menu .navigation li.has-sub:not(.open) > .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.nav-item.has-sub:not(.open) > .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.menu-item.has-sub:not(.open) > .menu-content,
    body.menu-expanded .main-menu .navigation li.menu-item.has-sub:not(.open) > .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.has-sub:not(.open) > .menu-content,
    body.menu-expanded .main-menu .menu-content li.has-sub:not(.open) > .menu-content {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
    }

    /* Override any external CSS that might hide menu-content */
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu.menu-fixed.menu-dark.menu-accordion.menu-shadow.expanded .main-menu-content .navigation.navigation-main li.has-sub.open > ul.menu-content,
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu.menu-fixed.menu-dark.menu-accordion.menu-shadow.expanded .main-menu-content .navigation.navigation-main li.nav-item.has-sub.open > ul.menu-content {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        height: auto !important;
        overflow: visible !important;
    }

    /* Force show menu-content and all menu-item children - CRITICAL for submenu visibility */
    /* This applies to ALL submenu items: nav-item, menu-item, and any nested submenus */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open > .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.menu-item.has-sub.open .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.menu-item.has-sub.open > .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.has-sub.open .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.has-sub.open > .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.menu-item.has-sub.open .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.menu-item.has-sub.open > .menu-content,
    body.menu-expanded .main-menu .navigation li.open .menu-content,
    body.menu-expanded .main-menu .navigation li.open > .menu-content,
    body.menu-expanded .main-menu .menu-content li.open .menu-content,
    body.menu-expanded .main-menu .menu-content li.open > .menu-content {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        transform: none !important;
    }

    /* Force show individual menu-item elements inside menu-content */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.open .menu-content .menu-item,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open .menu-content .menu-item,
    body.menu-expanded .main-menu .navigation li.open .menu-content li.menu-item,
    body.menu-expanded .main-menu .navigation li.has-sub.open .menu-content li.menu-item,
    body.menu-expanded .main-menu .navigation li.open .menu-content li.menu-item.is-shown {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        height: auto !important;
        max-height: none !important;
    }

    /* Force show links inside menu-item */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.open .menu-content .menu-item > a,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open .menu-content .menu-item > a,
    body.menu-expanded .main-menu .navigation li.open .menu-content li.menu-item > a {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    /* ULTIMATE OVERRIDE - Maximum specificity to force submenu visibility */
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu.menu-fixed.menu-dark.menu-accordion.menu-shadow.expanded .main-menu-content .navigation.navigation-main li.has-sub.open > ul.menu-content,
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu.menu-fixed.menu-dark.menu-accordion.menu-shadow.expanded .main-menu-content .navigation.navigation-main li.nav-item.has-sub.open > ul.menu-content,
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu.menu-fixed.menu-dark.menu-accordion.menu-shadow.expanded .main-menu-content .navigation.navigation-main li.has-sub.open > ul.menu-content li.menu-item,
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu.menu-fixed.menu-dark.menu-accordion.menu-shadow.expanded .main-menu-content .navigation.navigation-main li.has-sub.open > ul.menu-content li.menu-item.is-shown,
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu.menu-fixed.menu-dark.menu-accordion.menu-shadow.expanded .main-menu-content .navigation.navigation-main li.has-sub.open > ul.menu-content li.menu-item > a {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        transform: none !important;
        position: relative !important;
    }

    /* Main menu width - expand on hover */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover {
        width: 240px !important;
        z-index: 999999 !important;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15);
        transition: width 0.3s ease;
        position: fixed !important;
    }

    /* Navigation header - hide minus icon, show text on hover */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .navigation .navigation-header .ft-minus {
        display: none !important;
    }

    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .navigation .navigation-header span {
        display: block !important;
    }

    /* Menu item icons - add margin on hover */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .navigation > li > a > i {
        margin-right: 12px !important;
        float: left !important;
    }

    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .navigation > li > a > i:before {
        font-size: 1.2rem !important;
    }

    /* Menu item text - show on hover */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .navigation > li > a > span {
        display: inline-block !important;
        -webkit-animation: 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) 0s normal forwards 1 fadein;
        -moz-animation: 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) 0s normal forwards 1 fadein;
        -o-animation: 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) 0s normal forwards 1 fadein;
        animation: 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) 0s normal forwards 1 fadein;
    }

    /* Badges - show and position on hover */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .navigation > li > a > span.badge {
        display: inline-block !important;
        position: absolute !important;
        right: 20px !important;
    }

    /* Submenu arrows - show on hover */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .navigation li.has-sub > a:not(.mm-next):after {
        content: "\f105" !important;
        font-family: 'FontAwesome' !important;
        font-size: 1rem !important;
        display: inline-block !important;
        position: absolute !important;
        right: 20px !important;
        top: 10px !important;
        -webkit-transform: rotate(0deg) !important;
        -moz-transform: rotate(0deg) !important;
        -ms-transform: rotate(0deg) !important;
        -o-transform: rotate(0deg) !important;
        transform: rotate(0deg) !important;
        transition: -webkit-transform 0.2s ease-in-out !important;
    }

    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .navigation li.open > a:not(.mm-next):after {
        -webkit-transform: rotate(90deg) !important;
        -moz-transform: rotate(90deg) !important;
        -ms-transform: rotate(90deg) !important;
        -o-transform: rotate(90deg) !important;
        transform: rotate(90deg) !important;
    }

    /* Main menu footer - expand width on hover */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .main-menu-footer {
        width: 240px !important;
    }

    /* Main menu header - show content on hover */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .main-menu-header .media-body {
        opacity: 1 !important;
    }

    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .main-menu-header .media-body .media-heading,
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .main-menu-header .media-body .text-muted,
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .main-menu-header .media-right {
        display: block !important;
    }

    /* Content area - adjust margin on hover (mimics menu-expanded) */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover ~ #c_body ~ .app-content.content,
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover ~ .app-content.content {
        margin-left: 240px !important;
        width: calc(100% - 240px) !important;
        transition: margin-left 0.3s ease, width 0.3s ease;
    }

    /* Footer - adjust margin on hover */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover ~ .footer {
        margin-left: 240px !important;
        transition: margin-left 0.3s ease;
    }

    /* Navbar container - adjust margin on hover (using :has() for parent selector) */
    body.vertical-layout.vertical-menu-modern.menu-collapsed:has(.main-menu:hover) .navbar .navbar-container {
        margin-left: 240px !important;
        transition: margin-left 0.3s ease;
    }

    /* FINAL OVERRIDE - Ensure menu text is ALWAYS visible when menu-expanded - Highest Priority */
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu.menu-fixed.menu-dark.menu-accordion.menu-shadow.expanded .main-menu-content .navigation.navigation-main > li.nav-item > a > span,
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu.menu-fixed.menu-dark.menu-accordion.menu-shadow.expanded .main-menu-content .navigation.navigation-main > li.menu-item > a > span,
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu.menu-fixed.menu-dark.menu-accordion.menu-shadow.expanded .main-menu-content .navigation.navigation-main > li > a > span {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        width: auto !important;
        height: auto !important;
        max-width: none !important;
        overflow: visible !important;
        text-overflow: clip !important;
        position: relative !important;
        margin-left: 8px !important;
    }

    /* FINAL OVERRIDE FOR SUBMENUS - Force visibility of all submenu elements */
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open ul.menu-content,
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.nav-item.has-sub.open ul.menu-content,
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open ul.menu-content li,
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open ul.menu-content li.menu-item,
    html body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open ul.menu-content li.menu-item.is-shown {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        height: auto !important;
        max-height: 9999px !important;
        overflow: visible !important;
        transform: none !important;
        clip: auto !important;
        clip-path: none !important;
        position: relative !important;
        left: 0 !important;
        top: 0 !important;
        padding: 5px 0 !important;
    }

    /* CRITICAL FIX: Remove any height/transform restrictions on menu-content */
    ul.menu-content[style],
    .main-menu ul.menu-content,
    .main-menu .navigation ul.menu-content,
    body.menu-expanded .main-menu ul.menu-content {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
        overflow: visible !important;
        transform: translateY(0) !important;
        transition: none !important;
    }

    /* Force show when parent has open class - regardless of other classes */
    li.open > ul.menu-content,
    li.has-sub.open > ul.menu-content,
    li.nav-item.open > ul.menu-content,
    .navigation li.open > ul.menu-content {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        height: auto !important;
        max-height: none !important;
        position: relative !important;
    }

    /* Override menu-accordion collapsing behavior */
    .main-menu.menu-accordion .navigation li.open .menu-content,
    .main-menu.menu-accordion .navigation li.has-sub.open > .menu-content,
    body.menu-expanded .main-menu.menu-accordion .navigation li.open .menu-content {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }

    /* Ensure menu-content li items are also visible */
    .main-menu .menu-content li,
    .main-menu .navigation .menu-content li.menu-item {
        display: list-item !important;
        visibility: visible !important;
        opacity: 1 !important;
        height: auto !important;
        list-style: none !important;
        padding-left: 10px !important;
    }
    
    /* ============================================
       SIDEBAR VISUAL HIERARCHY - Spacing and Indentation
       Add gaps and indentation to clearly show menu hierarchy
       ============================================ */
    
    /* Add MORE spacing between main menu items */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation > li {
        margin-bottom: 12px !important;
    }
    
    /* Add padding to main menu item links for better spacing */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation > li > a {
        padding: 12px 20px !important;
        margin-bottom: 0 !important;
    }
    
    /* Add SIGNIFICANT top margin to submenu containers to create LARGE gap from parent */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open > .menu-content,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.has-sub.open > .menu-content {
        margin-top: 16px !important;
        margin-bottom: 16px !important;
        padding-left: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }
    
    /* Add MORE spacing between individual submenu items */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open > .menu-content > li.menu-item,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open > .menu-content > li,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.has-sub.open > .menu-content > li.menu-item,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.has-sub.open > .menu-content > li {
        margin-bottom: 8px !important;
        margin-top: 0 !important;
    }
    
    /* Indent submenu items MORE (first level - direct children of main navigation) */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open > .menu-content > li.menu-item,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open > .menu-content > li {
        padding-left: 50px !important;
    }
    
    /* Indent nested submenu items MORE (second level - submenus within submenus) */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.has-sub.open > .menu-content > li.menu-item,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.has-sub.open > .menu-content > li {
        padding-left: 70px !important;
    }
    
    /* Indent third level submenu items MORE (if any) */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content .menu-content li.has-sub.open > .menu-content > li.menu-item,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content .menu-content li.has-sub.open > .menu-content > li {
        padding-left: 90px !important;
    }
    
    /* Add VISIBLE separator line above submenu items for better hierarchy */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open > .menu-content::before,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.has-sub.open > .menu-content::before {
        content: '' !important;
        display: block !important;
        height: 2px !important;
        background-color: rgba(255, 255, 255, 0.2) !important;
        margin-bottom: 12px !important;
        margin-top: 8px !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        border-radius: 1px !important;
    }
    
    /* Add MORE padding and styling to submenu items for better visibility */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open > .menu-content > li.menu-item > a,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.has-sub.open > .menu-content > li.menu-item > a,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content .menu-content li.has-sub.open > .menu-content > li.menu-item > a {
        padding: 10px 15px 10px 30px !important;
        border-radius: 4px !important;
        transition: all 0.2s ease !important;
        display: block !important;
        /* border-left: 3px solid rgba(255, 255, 255, 0.2) !important; */
        margin-left: 0 !important;
    }
    
    /* Hover effect for submenu items at all levels */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open > .menu-content > li.menu-item > a:hover,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content li.has-sub.open > .menu-content > li.menu-item > a:hover,
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .menu-content .menu-content li.has-sub.open > .menu-content > li.menu-item > a:hover {
        background-color: rgba(255, 255, 255, 0.1) !important;
        border-left-color: rgba(255, 255, 255, 0.4) !important;
        transform: translateX(2px) !important;
    }
    
    /* Add extra bottom margin after submenu to separate from next main item */
    body.vertical-layout.vertical-menu-modern.menu-expanded .main-menu .navigation li.has-sub.open {
        margin-bottom: 16px !important;
    }
    
    /* ============================================
       COMPANY LOGO STYLING
       ============================================ */
    
    /* Logo container styling when menu is expanded */
    body.vertical-layout.vertical-menu-modern.menu-expanded .sidebar-logo-container {
        display: block !important;
        padding: 20px 15px !important;
        text-align: center !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        margin-bottom: 15px !important;
    }
    
    /* Logo image styling when expanded - BIGGER SIZE */
    body.vertical-layout.vertical-menu-modern.menu-expanded .sidebar-logo-container img {
        max-width: 100% !important;
        max-height: 120px !important;
        height: auto !important;
        width: auto !important;
        object-fit: contain !important;
        display: block !important;
        margin: 0 auto !important;
    }
    
    /* Company name styling when expanded */
    body.vertical-layout.vertical-menu-modern.menu-expanded .sidebar-logo-container div {
        margin-top: 12px !important;
        color: #fff !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
    }
    
    /* Logo container styling when menu is collapsed - SHOW IT */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .sidebar-logo-container {
        display: block !important;
        padding: 15px 10px !important;
        text-align: center !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        margin-bottom: 10px !important;
    }
    
    /* Logo image styling when collapsed - SHOW IT */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .sidebar-logo-container img {
        max-width: 100% !important;
        max-height: 50px !important;
        height: auto !important;
        width: auto !important;
        object-fit: contain !important;
        display: block !important;
        margin: 0 auto !important;
    }
    
    /* Hide company name when collapsed to save space */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .sidebar-logo-container div {
        display: none !important;
    }
    
    /* Show company name on hover when collapsed */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .sidebar-logo-container div {
        display: block !important;
        font-size: 11px !important;
        margin-top: 8px !important;
        color: #fff !important;
        font-weight: 500 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
    }
    
    /* Slightly bigger logo on hover when collapsed */
    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu:hover .sidebar-logo-container img {
        max-height: 60px !important;
    }
</style>
<script>
// Force submenu visibility - Override any external CSS or JavaScript
(function() {
    'use strict';
    
    function updateSubmenuVisibility() {
        // Get ALL menu items with submenus - including nav-item, menu-item, and nested submenus
        // This finds has-sub in navigation, menu-content, and any nested levels
        var allSubmenuItems = document.querySelectorAll('.main-menu li.has-sub');
        
        allSubmenuItems.forEach(function(menuItem) {
            // Fix: Find direct child with class menu-content (querySelector doesn't support > combinator)
            var submenu = null;
            for (var i = 0; i < menuItem.children.length; i++) {
                if (menuItem.children[i].classList.contains('menu-content')) {
                    submenu = menuItem.children[i];
                    break;
                }
            }
            if (!submenu) return;
            
            // Check if parent has 'open' class
            if (menuItem.classList.contains('open')) {
                // Show submenu
                submenu.style.display = 'block';
                submenu.style.visibility = 'visible';
                submenu.style.opacity = '1';
                submenu.style.height = 'auto';
                submenu.style.maxHeight = 'none';
                submenu.style.overflow = 'visible';
                submenu.style.transform = 'none';
                
                // Force visibility on all child menu-item elements
                var menuItems = submenu.querySelectorAll('li.menu-item');
                menuItems.forEach(function(item) {
                    item.style.display = 'block';
                    item.style.visibility = 'visible';
                    item.style.opacity = '1';
                    item.style.height = 'auto';
                });
            } else {
                // Hide submenu
                submenu.style.display = 'none';
                submenu.style.visibility = 'hidden';
                submenu.style.opacity = '0';
                submenu.style.height = '0';
                submenu.style.overflow = 'hidden';
            }
        });
    }
    
    // Run on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(updateSubmenuVisibility, 100);
        });
    } else {
        setTimeout(updateSubmenuVisibility, 100);
    }
    
    // Use MutationObserver to catch when 'open' class is added or removed
    var observer = new MutationObserver(function(mutations) {
        var shouldUpdate = false;
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                var target = mutation.target;
                if (target.classList.contains('has-sub')) {
                    shouldUpdate = true;
                }
            }
        });
        if (shouldUpdate) {
            setTimeout(updateSubmenuVisibility, 50);
        }
    });
    
    // Observe ALL menu items for class changes - including nested submenus
    setTimeout(function() {
        var menuItems = document.querySelectorAll('.main-menu li.has-sub');
        menuItems.forEach(function(item) {
            observer.observe(item, { attributes: true, attributeFilter: ['class'] });
        });
    }, 500);
    
    // Also listen for click events on menu items with submenus (all levels)
    document.addEventListener('click', function(e) {
        var menuItem = e.target.closest('li.has-sub');
        if (menuItem) {
            setTimeout(updateSubmenuVisibility, 100);
        }
    });
})();

// Fix Perfect Scrollbar excessive scrolling issue
(function() {
    'use strict';
    
    function fixPerfectScrollbar() {
        var menuContent = document.querySelector('.main-menu-content.ps-container');
        if (menuContent && typeof PerfectScrollbar !== 'undefined') {
            // Update Perfect Scrollbar to recalculate scroll area
            try {
                var ps = menuContent.ps;
                if (ps) {
                    ps.update();
                } else {
                    // Reinitialize if not already initialized
                    new PerfectScrollbar(menuContent, {
                        wheelSpeed: 1,
                        wheelPropagation: false,
                        minScrollbarLength: null,
                        maxScrollbarLength: null,
                        suppressScrollX: true,
                        suppressScrollY: false
                    });
                }
            } catch(e) {
                console.log('Perfect Scrollbar update:', e);
            }
        }
    }
    
    // Fix scrollbar on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(fixPerfectScrollbar, 100);
        });
    } else {
        setTimeout(fixPerfectScrollbar, 100);
    }
    
    // Fix scrollbar when menu items are expanded/collapsed
    document.addEventListener('click', function(e) {
        var menuItem = e.target.closest('li.has-sub');
        if (menuItem) {
            setTimeout(fixPerfectScrollbar, 200);
        }
    });
    
    // Use MutationObserver to fix scrollbar when menu state changes
    var menuObserver = new MutationObserver(function(mutations) {
        var shouldUpdate = false;
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                var target = mutation.target;
                if (target.classList.contains('open') || target.classList.contains('has-sub')) {
                    shouldUpdate = true;
                }
            }
        });
        if (shouldUpdate) {
            setTimeout(fixPerfectScrollbar, 200);
        }
    });
    
    // Observe menu for changes
    setTimeout(function() {
        var menu = document.querySelector('.main-menu .navigation');
        if (menu) {
            menuObserver.observe(menu, { 
                attributes: true, 
                attributeFilter: ['class'],
                subtree: true 
            });
        }
    }, 500);
})();
</script>
</head>
<?php
$CI = &get_instance();
$CI->load->model('settings_model', 'settings');

// Ensure language is loaded - this should fix the issue with lang->line() returning empty
$mylang = $CI->config->item('mylang');
if (empty($mylang)) {
    $mylang = 'english';
}

// Load the language file if not already loaded
if (empty($CI->lang->language) || !isset($CI->lang->language['sales'])) {
    $CI->lang->load($mylang, $mylang);
    $CI->lang->load('part', $mylang);
}

// Helper function to get language line with fallback (optional, for safety)
function lang_line($key, $fallback = '') {
    $CI = &get_instance();
    $line = $CI->lang->line($key);
    // If language line returns FALSE or empty, use fallback or formatted key
    if ($line === FALSE || $line === '') {
        return $fallback !== '' ? $fallback : ucfirst(str_replace('_', ' ', $key));
    }
    return $line;
}

// Get company details for logo
$company = $CI->settings->company_details(1);
$company_logo = isset($company['logo']) && !empty($company['logo']) ? $company['logo'] : 'default.png';
?>

<body class="vertical-layout vertical-menu-modern 2-columns menu-expanded fixed-navbar" data-open="click" data-menu="vertical-menu-modern" data-col="2-columns">
    <span id="hdata" data-df="<?php echo $this->config->item('dformat2'); ?>" data-curr="<?php echo currency($this->aauth->get_user()->loc); ?>"></span>
    <!-- fixed-top-->
 
    <!-- ////////////////////////////////////////////////////////////////////////////-->
    <!-- Horizontal navigation-->
    <!-- Navigation Menu -->
    <div class="main-menu menu-fixed menu-dark menu-accordion menu-shadow expanded" data-scroll-to-active="true">
        <div class="main-menu-content">
            <!-- Company Logo Section -->
            <div class="sidebar-logo-container">
                <a href="<?= base_url(); ?>dashboard/" style="display: block; text-decoration: none;">
                    <img src="<?php echo base_url('userfiles/company/') . $company_logo . '?t=' . time(); ?>" 
                         alt="<?php echo isset($company['cname']) ? htmlspecialchars($company['cname']) : 'Company Logo'; ?>" 
                         class="company-logo-img">
                    <?php if (isset($company['cname']) && !empty($company['cname'])) { ?>
                        <div class="company-name-text">
                            <?php echo htmlspecialchars($company['cname']); ?>
                        </div>
                    <?php } ?>
                </a>
            </div>
            <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
                <!-- Dashboard -->
                <?php if ($this->aauth->permission_new(null, 'dashboard')) { ?>
                    <li class="nav-item">
                        <a href="<?= base_url(); ?>dashboard/"><i class="fas fa-tachometer-alt"></i><span><?= $this->lang->line('Dashboard') ?></span></a>
                    </li>
                <?php } ?>

                <!-- Sales Section -->
                <?php if ($this->aauth->permission_new(null, 'salesAccess')) { ?>
                    <li class="nav-item has-sub <?php if ($this->li_a == "sales") {
                                                    echo ' open';
                                                } ?>">
                        <a href="#"><i class="fas fa-shopping-cart"></i> <span><?php echo $this->lang->line('sales') ?: 'Sales'; ?></span></a>
                        <ul class="menu-content">
                            <?php if ($this->aauth->permission_new(null, 'salesNewInvoice')) { ?>
                                <li class="menu-item">
                                    <a href="<?= base_url(); ?>invoices/create" data-toggle="dropdown"><i class="fas fa-plus-circle"></i> <?php echo $this->lang->line('New Invoice'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($CI->settings->auto_post()) { ?>
                                <?php if ($this->aauth->permission_new(null, 'salesManageInvoices')) { ?>
                                    <li class="menu-item">
                                        <a href="<?php echo base_url(); ?>invoices" data-toggle="dropdown"><i class="fas fa-list"></i> <?= $this->lang->line('ManageInvoices') ?></a>
                                    </li>
                                <?php } ?>
                            <?php } else { ?>
                                <?php if ($this->aauth->permission_new(null, 'salesUnpostedInvoices')) { ?>
                                    <li class="menu-item">
                                        <a href="<?php echo base_url(); ?>invoices/unposted_invoices" data-toggle="dropdown"><i class="fas fa-clock"></i> <?= $this->lang->line('UnpostedInvoices') ?></a>
                                    </li>
                                <?php } ?>
                                <?php if ($this->aauth->permission_new(null, 'salesPostedInvoices')) { ?>
                                    <li class="menu-item">
                                        <a href="<?php echo base_url(); ?>invoices" data-toggle="dropdown"><i class="fas fa-check-circle"></i> <?= $this->lang->line('PostedInvoices') ?></a>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                            <!-- Quotations Section -->
                            <?php if ($this->aauth->permission_new(null, 'quotationAccess')) { ?>
                                <li class="nav-item has-sub <?php if ($this->li_a == "quotations") {
                                                                echo ' open';
                                                            } ?>">
                                    <a href="#"><i class="fas fa-file-text"></i> <span><?php echo 'Quotations'; ?></span></a>
                                    <ul class="menu-content">
                                        <?php if ($this->aauth->permission_new(null, 'quotationNewQuotation')) { ?>
                                            <li class="menu-item">
                                                <a href="<?= base_url(); ?>quotations/newquotation" data-toggle="dropdown"><i class="fas fa-plus-circle"></i> <?php echo 'New Quotation'; ?></a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($this->aauth->permission_new(null, 'quotationManageQuotations')) { ?>
                                            <li class="menu-item">
                                                <a href="<?php echo base_url(); ?>quotations" data-toggle="dropdown"><i class="fas fa-list"></i> <?= 'Manage Quotations' ?></a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'salesPrintInvoices')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>invoices/print_invoices" data-toggle="dropdown"><i class="fas fa-print"></i> <?= $this->lang->line('PrintInvoices') ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'salesCustomerInvoices')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>invoices/print_customer_invoices" data-toggle="dropdown"><i class="fas fa-user"></i> <?= $this->lang->line('CustomerInvoices') ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'salesCreditNote')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>invoices/creditnotes" data-toggle="dropdown"><i class="fas fa-reply"></i> <?= $this->lang->line('Credit Note') ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <!-- Purchase Section -->
                <?php if ($this->aauth->permission_new(null, 'purchaseAccess')) { ?>
                    <li class="nav-item has-sub <?php if ($this->li_a == "purchase") {
                                                    echo ' open';
                                                } ?>">
                        <a href="#"><i class="fas fa-truck"></i> <span><?php echo $this->lang->line('Purchase') ?></span></a>
                        <ul class="menu-content">
                            <?php if ($this->aauth->permission_new(null, 'purchaseCreateOrder')) { ?>
                                <li class="menu-item">
                                    <a href="<?= base_url(); ?>purchase/create" data-toggle="dropdown"><i class="fas fa-plus-circle"></i> <?= $this->lang->line('CreateOrder'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'purchaseManageOrders')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>purchase"><i class="fas fa-list"></i> <?= $this->lang->line('Manage Orders'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'purchaseVerifyOrder')) { ?>
                                <li class="menu-item">
                                    <a href="<?= base_url(); ?>purchase/verification" data-toggle="dropdown"><i class="fas fa-check-square"></i> <?= $this->lang->line('VerifyOrder'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'purchaseCreditNotePO')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>purchase/creditnotes_po" data-toggle="dropdown"><i class="fas fa-reply"></i> <?= $this->lang->line('Credit Note') ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <!-- Stock Return Section -->

                <?php if ($this->aauth->permission_new(null, 'stockReturnAccess')) { ?>
                    <li class="nav-item has-sub <?php if ($this->li_a == "stockreturn") {
                                                    echo ' open';
                                                } ?>">
                        <a href="#"><i class="fas fa-undo"></i> <span><?php echo $this->lang->line('Stock Return') ?></span></a>
                        <ul class="menu-content">
                            <?php if ($this->aauth->permission_new(null, 'stockSuppliersReturns')) { ?>
                                <li class="menu-item" title="<?= $this->lang->line('SuppliersReturns'); ?>">
                                    <a href="<?= base_url(); ?>stockreturn"><i class="fas fa-truck"></i> <?php echo $this->lang->line('SuppliersReturns'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'stockCustomersReturns')) { ?>
                                <li class="menu-item" title="<?= $this->lang->line('CustomersReturns'); ?>">
                                    <a href="<?php echo base_url(); ?>stockreturn/customer_invoices"><i class="fas fa-file-text"></i> <?php echo $this->lang->line('CustomersReturns'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'stockCustomerViewReturns')) { ?>
                                <li class="menu-item" title="<?php echo $this->lang->line('CustomerViewReturns'); ?>">
                                    <a href="<?php echo base_url(); ?>stockreturn/customer" data-toggle="dropdown"><i class="fas fa-eye"></i> <?php echo $this->lang->line('CustomerViewReturns'); ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>


                <!-- Stock Section -->
                <?php if ($this->aauth->permission_new(null, 'stockAccess')) { ?>
                    <li class="nav-item has-sub <?php if ($this->li_a == "stock") {
                                                    echo ' open';
                                                } ?>">
                        <a href="#"><i class="fa fa-cubes"></i> <span><?php echo $this->lang->line('Stock') ?></span></a>
                        <ul class="menu-content">
                            <?php if ($this->aauth->permission_new(null, 'stockItemsManager')) { ?>
                                <li class="menu-item">
                                    <a href="#"><i class="fa fa-list"></i> <?php echo $this->lang->line('Items Manager') ?></a>
                                    <ul class="menu-content">
                                        <?php if ($this->aauth->permission_new(null, 'stockNewProduct')) { ?>
                                            <li class="menu-item">
                                                <a href="<?= base_url(); ?>products/add"><i class="fa fa-plus-circle"></i> <?php echo $this->lang->line('New Product'); ?></a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($this->aauth->permission_new(null, 'stockManageProducts')) { ?>
                                            <li class="menu-item">
                                                <a href="<?php echo base_url(); ?>products"><i class="fa fa-list"></i> <?= $this->lang->line('Manage Products'); ?></a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'stockProductCategories')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>productcategory" title="<?= $this->lang->line('Product Categories'); ?>"><i class="fas fa-tags"></i> <?php echo $this->lang->line('Product Categories'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'stockWarehouses')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>productcategory/warehouse"><i class="fas fa-building"></i> <?php echo $this->lang->line('Warehouses'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'stockTransfer')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>products/stock_transfer"><i class="fas fa-exchange-alt"></i> <?php echo $this->lang->line('Stock Transfer'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'stockAdjustment')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>products/stock_adjustment"><i class="fas fa-balance-scale"></i> <?php echo $this->lang->line('Stock Adjustment'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'stockProductsLabel')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>units"><i class="fas fa-ruler"></i> <?php echo $this->lang->line('Measurement Unit'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'stockProductsLabel')) { ?>
                                <li class="menu-item">
                                    <a href="#"><i class="fas fa-tags"></i> <?php echo $this->lang->line('ProductsLabel'); ?></a>
                                    <ul class="menu-content">
                                        <?php if ($this->aauth->permission_new(null, 'stockCustomLabel')) { ?>
                                            <li class="menu-item">
                                                <a href="<?php echo base_url(); ?>products/custom_label"><i class="fas fa-edit"></i> <?php echo $this->lang->line('custom_label'); ?></a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($this->aauth->permission_new(null, 'stockStandardLabel')) { ?>
                                            <li class="menu-item">
                                                <a href="<?php echo base_url(); ?>products/standard_label"><i class="fas fa-barcode"></i> <?php echo $this->lang->line('standard_label'); ?></a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <!-- Customer Section -->
                <?php if ($this->aauth->permission_new(null, 'customerAccess')) { ?>
                    <li class="nav-item has-sub <?php if ($this->li_a == "crm") {
                                                    echo ' open';
                                                } ?>">
                        <a href="#"><i class="fas fa-users"></i> <span><?php echo $this->lang->line('Customer'); ?></span></a>
                        <ul class="menu-content">
                            <?php if ($this->aauth->permission_new(null, 'customerNewCustomer')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>customers/create" data-toggle="dropdown"><i class="fas fa-user-plus"></i> <?php echo $this->lang->line('NewCustomer') ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'customerManageCustomer')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>customers" data-toggle="dropdown"><i class="fas fa-list"></i> <?php echo $this->lang->line('ManageCustomer') ?></a>
                                </li>
                            <?php } ?>
                            <!-- <?php if ($this->aauth->permission_new(null, 'customerItemPriceList')) { ?>
                                <li class="menu-item" title="<?php echo $this->lang->line('ItemPriceList') ?>">
                                    <a href="<?php echo base_url(); ?>customers/list_price"><i class="fa fa-tag"></i> <?php echo $this->lang->line('ItemPriceList') ?></a>
                                </li>
                            <?php } ?> -->
                            <?php if ($this->aauth->permission_new(null, 'customerGroupCustomerPrice')) { ?>
                                <li class="menu-item" title="<?php echo $this->lang->line('GroupCustomerprice') ?>">
                                    <a href="<?php echo base_url(); ?>customers/group_list_price"><i class="fas fa-users"></i> <?php echo $this->lang->line('GroupCustomerprice') ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'customerReceipt')) { ?>
                                <li class="menu-item" title="<?php echo $this->lang->line('CustomerReceipt') ?>">
                                    <a href="<?php echo base_url(); ?>customers/receipt"><i class="fas fa-file-alt"></i> <?php echo $this->lang->line('CustomerReceipt') ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'customerActivities')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>customers/activities" data-toggle="dropdown"><i class="fas fa-history"></i> <?php echo $this->lang->line('CustomerActivities') ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>

                    <!-- Suppliers Section -->
                    <?php if ($this->aauth->permission_new(null, 'suppliersAccess')) { ?>
                        <li class="nav-item has-sub <?php if ($this->li_a == "supplier") {
                                                        echo ' open';
                                                    } ?>">
                            <a href="#"><i class="fas fa-briefcase"></i> <span><?php echo $this->lang->line('Suppliers') ?></span></a>
                            <ul class="menu-content">
                                <?php if ($this->aauth->permission_new(null, 'suppliersNewSupplier')) { ?>
                                    <li class="menu-item">
                                        <a href="<?= base_url(); ?>supplier/create"><i class="fas fa-user-plus"></i> <?php echo $this->lang->line('New Supplier'); ?></a>
                                    </li>
                                <?php } ?>
                                <?php if ($this->aauth->permission_new(null, 'suppliersManageSuppliers')) { ?>
                                    <li class="menu-item">
                                        <a href="<?php echo base_url(); ?>supplier" title="<?= $this->lang->line('Manage Suppliers'); ?>"><i class="fas fa-list"></i> <?php echo $this->lang->line('Manage Suppliers'); ?></a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                <?php } ?>

                <!-- Project Section -->
                <?php if ($this->aauth->permission_new(null, 'projectAccess')) { ?>
                    <li class="menu-item has-sub <?php if ($this->li_a == "project") {
                                                        echo ' open';
                                                    } ?>">
                        <a href="#"><i class="ft-briefcase"></i> <span><?= $this->lang->line('Project') ?></span></a>
                        <ul class="menu-content">
                            <?php if ($this->aauth->permission_new(null, 'projectManagement')) { ?>
                                <li class="menu-item">
                                    <a href="#"><i class="ft-calendar"></i> <?php echo $this->lang->line('Project Management') ?></a>
                                    <ul class="menu-content">
                                        <?php if ($this->aauth->permission_new(null, 'projectNewProject')) { ?>
                                            <li class="menu-item">
                                                <a href="<?php echo base_url(); ?>projects/addproject"><i class="ft-plus-circle"></i> <?php echo $this->lang->line('New Project') ?></a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($this->aauth->permission_new(null, 'projectManageProjects')) { ?>
                                            <li class="menu-item">
                                                <a href="<?php echo base_url(); ?>projects"><i class="ft-list"></i> <?= $this->lang->line('Manage Projects'); ?></a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'projectToDoList')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>tools/todo"><i class="ft-check-square"></i> <?php echo $this->lang->line('To Do List'); ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <!-- Manager Section (Project Subset) -->
                <?php if (!$this->aauth->permission_new(null, 'projectAccess') && $this->aauth->permission_new(null, 'managerAccess')) { ?>
                    <li class="menu-item has-sub <?php if ($this->li_a == "manager") {
                                                        echo ' open';
                                                    } ?>">
                        <a href="#"><i class="ft-briefcase"></i> <span><?php echo $this->lang->line('Project') ?></span></a>
                        <ul class="menu-content">
                            <?php if ($this->aauth->permission_new(null, 'managerManageProjects')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>manager/projects"><i class="ft-calendar"></i> <?php echo $this->lang->line('Manage Projects'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'managerToDoList')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>manager/todo"><i class="ft-check-square"></i> <?php echo $this->lang->line('To Do List'); ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <!-- Sales Reports Section -->
                <?php if ($this->aauth->permission_new(null, 'salesReportsAccess')) { ?>
                    <li class="menu-item has-sub <?php if ($this->li_a == "data") {
                                                        echo ' open';
                                                    } ?>">
                        <a href="#"><i class="fas fa-chart-pie"></i> <span><?php echo $this->lang->line('SalesReports') ?></span></a>
                        <ul class="menu-content">
                            <li class="menu-item" title="<?= $this->lang->line('SalesByProduct(Summary)'); ?>">
                                <a href="<?php echo base_url(); ?>reports/extended"><i class="fas fa-chart-bar"></i> <?= $this->lang->line('SalesByProduct(Summary)'); ?></a>
                            </li>
                            <li class="menu-item">
                                <a href="<?php echo base_url(); ?>reports/stock_report"><i class="fas fa-chart-line"></i> <?php echo $this->lang->line('SalesByProduct') ?></a>
                            </li>
                            <li class="menu-item" title="<?php echo $this->lang->line('DailyBreakdownSheet'); ?>">
                                <a href="<?php echo base_url(); ?>reports/breakdown_sheet"><i class="fas fa-dollar-sign"></i><?php echo $this->lang->line('DailyBreakdownSheet'); ?></a>
                            </li>
                            <li class="menu-item">
                                <a href="<?php echo base_url(); ?>reports/daybook"><i class="fas fa-book"></i> <?php echo $this->lang->line('DayBook'); ?></a>
                            </li>
                            <li class="menu-item">
                                <a href="<?php echo base_url(); ?>reports/vat_collect"><i class="fas fa-percentage"></i><?php echo $this->lang->line('TaxCollection'); ?></a>
                            </li>
                            <li class="menu-item" title="<?php echo $this->lang->line('PaymentsRecievedSummary'); ?>">
                                <a href="<?php echo base_url(); ?>reports/payments_report"><i class="fas fa-credit-card"></i> <?php echo $this->lang->line('PaymentsRecievedSummary'); ?></a>
                            </li>
                            <li class="menu-item" title="<?php echo $this->lang->line('StockReturnReport'); ?>">
                                <a href="<?php echo base_url(); ?>reports/stock_return_report"><i class="fas fa-undo"></i> <?php echo $this->lang->line('StockReturnReport'); ?></a>
                            </li>
                            <li class="menu-item">
                                <a href="<?php echo base_url(); ?>reports/customer_ClosingBalance_report"><i class="fas fa-clipboard"></i> <?php echo $this->lang->line('CustomerClosingBalanceReport'); ?></a>
                            </li>
                            <li class="menu-item">
                                <a href="<?php echo base_url(); ?>reports/customer_report"><i class="fas fa-users"></i> <?php echo $this->lang->line('AllCustomersBalanceReport'); ?></a>
                            </li>
                            <li class="menu-item">
                                <a href="<?php echo base_url(); ?>reports/proftloss"><i class="fas fa-chart-line"></i> <?php echo $this->lang->line('Profit&Loss'); ?></a>
                            </li>
                            <li class="menu-item">
                                <a href="<?php echo base_url(); ?>reports/geofence"><i class="fas fa-map"></i> <?= $this->lang->line('ZoneReport'); ?></a>
                            </li>
                            <li class="menu-item" title="<?= $this->lang->line('Product Categories'); ?>">
                                <a href="<?php echo base_url(); ?>chart/product_cat"><i class="fas fa-layer-group"></i> <?= $this->lang->line('Product Categories Graphical'); ?></a>
                            </li>
                            <li class="menu-item" title="<?= $this->lang->line('Trending Products Graph'); ?>">
                                <a href="<?php echo base_url(); ?>chart/trending_products"><i class="fas fa-chart-line"></i> <?= $this->lang->line('Trending Products Graph'); ?></a>
                            </li>
                            <li class="menu-item" title="<?= $this->lang->line('Top_Customers Graph') ?>">
                                <a href="<?php echo base_url(); ?>chart/topcustomers"><i class="fas fa-trophy"></i> <?php echo $this->lang->line('Top_Customers Graph') ?></a>
                            </li>
                            <li class="menu-item">
                                <a href="<?php echo base_url(); ?>reports/sales"><i class="fas fa-shopping-cart"></i> <?php echo $this->lang->line('Sales') ?></a>
                            </li>
                            <li class="menu-item">
                                <a href="<?php echo base_url(); ?>reports/products"><i class="fas fa-box"></i> <?php echo $this->lang->line('Products') ?></a>
                            </li>
                        </ul>
                    </li>
                <?php } ?>

                <!-- Purchase Reports Section -->
                <?php if ($this->aauth->permission_new(null, 'purchaseReportsAccess')) { ?>
                    <li class="menu-item has-sub <?php if ($this->li_a == "purhcase_reports") {
                                                        echo ' open';
                                                    } ?>">
                        <a href="#"><i class="fas fa-chart-bar"></i> <span><?php echo $this->lang->line('Purchase Reports') ?></span></a>
                        <ul class="menu-content">
                            <?php if ($this->aauth->permission_new(null, 'purchaseReportsByCategory')) { ?>
                                <li class="menu-item" title="<?php echo $this->lang->line('Purchase By Category') ?>">
                                    <a href="<?php echo base_url(); ?>purchase_reports/extended"><i class="fas fa-chart-pie"></i> <?php echo $this->lang->line('Purchase By Category') ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'purchaseReportsBySupplier')) { ?>
                                <li class="menu-item" title="<?php echo $this->lang->line('Purchase By Supplier') ?>">
                                    <a href="<?php echo base_url(); ?>purchase_reports/purchase_by_supplier"><i class="fas fa-truck"></i> <?php echo $this->lang->line('Purchase By Supplier') ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'purchaseReportsSupplierPayment')) { ?>
                                <li class="menu-item" title="<?php echo $this->lang->line('Supplier Payment Report') ?>">
                                    <a href="<?php echo base_url(); ?>purchase_reports/supplier_payment"><i class="fas fa-credit-card"></i> <?php echo $this->lang->line('Supplier Payment Report') ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'purchaseReportsVatCollection')) { ?>
                                <li class="menu-item" title="<?php echo $this->lang->line('Tax Report') ?>">
                                    <a href="<?php echo base_url(); ?>purchase_reports/vat_purchase"><i class="fas fa-percentage"></i> <?php echo $this->lang->line('Tax Report') ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'purchaseReportsTransactionsBySupplier')) { ?>
                                <li class="menu-item" title="<?php echo $this->lang->line('Transactions By Supplier') ?>">
                                    <a href="<?php echo base_url(); ?>purchase_reports/payment_date"><i class="fas fa-calendar-alt"></i> <?php echo $this->lang->line('Transactions By Supplier') ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <!-- Users Section -->
                <?php if ($this->aauth->permission_new(null, 'usersAccess')) { ?>
                    <li class="menu-item has-sub <?php if ($this->li_a == "emp") {
                                                        echo ' open';
                                                    } ?>">
                        <a href="#"><i class="fas fa-user-friends"></i> <span><?php echo $this->lang->line('Users'); ?></span></a>
                        <ul class="menu-content">
                            <?php if ($this->aauth->permission_new(null, 'usersEmployees')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>employee"><i class="fas fa-user-tie"></i> <?php echo $this->lang->line('Employees') ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'usersEmployeesPermissions')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>employee/permissions"><i class="fas fa-unlock"></i><?= $this->lang->line('RolesPermissions'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'usersEmployeesSalaries')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>employee/salaries"><i class="fas fa-money-bill-wave"></i> <?= $this->lang->line('Salaries'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'usersEmployeesAttendances')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>employee/attendances"><i class="fas fa-calendar"></i> <?= $this->lang->line('Attendance'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'usersEmployeesHolidays')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>employee/holidays"><i class="fas fa-sun"></i> <?= $this->lang->line('Holidays'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'usersPayroll')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>employee/payroll"><i class="fas fa-credit-card"></i> <?php echo $this->lang->line('Payroll'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'usersDepartment')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>employee/departments"><i class="fas fa-folder"></i> <?php echo $this->lang->line('Departments'); ?></a>
                                </li>
                            <?php } ?>
                            <li class="menu-item">
                                <a href="<?php echo base_url(); ?>locations"><i class="fas fa-map-marker-alt"></i> <?php echo $this->lang->line('Location'); ?></a>
                            </li>
                        </ul>
                    </li>
                <?php } ?>

                <!-- Accounts Section -->
                <?php if ($this->aauth->permission_new(null, 'accountsAccess')) { ?>
                    <li class="menu-item has-sub <?php if ($this->li_a == "accounts") {
                                                        echo ' open';
                                                    } ?>">
                        <a href="#"><i class="fas fa-dollar-sign"></i> <span><?= $this->lang->line('Accounts') ?></span></a>
                        <ul class="menu-content">
                            <?php if ($this->aauth->permission_new(null, 'accountsManage')) { ?>
                                <li class="menu-item">
                                    <a href="#"><i class="fas fa-book"></i> <?php echo $this->lang->line('Accounts') ?></a>
                                    <ul class="menu-content">
                                        <?php if ($this->aauth->permission_new(null, 'accountsList')) { ?>
                                            <li class="menu-item">
                                                <a href="<?php echo base_url(); ?>accounts"><i class="fas fa-list"></i> <?php echo $this->lang->line('Manage Accounts') ?></a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($this->aauth->permission_new(null, 'accountsBalanceSheet')) { ?>
                                            <li class="menu-item" title="<?= $this->lang->line('BalanceSheet'); ?>">
                                                <a href="<?php echo base_url(); ?>accounts/balancesheet"><i class="fas fa-clipboard"></i> <?= $this->lang->line('BalanceSheet'); ?></a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($this->aauth->permission_new(null, 'accountsStatements')) { ?>
                                            <li class="menu-item" title="<?= $this->lang->line('Account Statements'); ?>">
                                                <a href="<?php echo base_url(); ?>accounts/accountstatement"><i class="fas fa-file-alt"></i> <?= $this->lang->line('Account Statements'); ?></a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'accountsTransactions')) { ?>
                                <li class="menu-item">
                                    <a href="#"><i class="fas fa-credit-card"></i> <?php echo $this->lang->line('Transactions') ?></a>
                                    <ul class="menu-content">
                                        <?php if ($this->aauth->permission_new(null, 'accountsViewTransactions')) { ?>
                                            <li class="menu-item">
                                                <a href="<?php echo base_url(); ?>transactions"><i class="fas fa-eye"></i> <?php echo $this->lang->line('View Transactions') ?></a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($this->aauth->permission_new(null, 'accountsNewTransaction')) { ?>
                                            <li class="menu-item">
                                                <a href="<?php echo base_url(); ?>transactions/add"><i class="fas fa-plus"></i> <?= $this->lang->line('New Transaction'); ?></a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($this->aauth->permission_new(null, 'accountsAdvancePayment')) { ?>
                                            <li class="menu-item">
                                                <a href="<?php echo base_url(); ?>transactions/advance" data-toggle="dropdown"><i class="fas fa-arrow-right"></i> <?= $this->lang->line('Advance Payment'); ?></a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($this->aauth->permission_new(null, 'accountsReversalTransaction')) { ?>
                                            <li class="menu-item">
                                                <a href="<?php echo base_url(); ?>transactions/reverse" data-toggle="dropdown"><i class="fas fa-undo"></i> <?= $this->lang->line('Reversal Transaction'); ?></a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($this->aauth->permission_new(null, 'accountsNewTransfer')) { ?>
                                            <li class="menu-item">
                                                <a href="<?php echo base_url(); ?>transactions/transfer"><i class="fas fa-exchange-alt"></i> <?= $this->lang->line('New Transfer'); ?></a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'accountsIncome')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>transactions/income"><i class="fas fa-arrow-up"></i> <?= $this->lang->line('Income'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'accountsExpense')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>transactions/expense"><i class="fas fa-arrow-down"></i> <?= $this->lang->line('Expense'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'accountsVATObligations')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>transactions/obligations"><i class="fas fa-percentage"></i> <?= $this->lang->line('TaxObligations'); ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <!-- Data Export/Import Section -->
                <?php if ($this->aauth->permission_new(null, 'dataExportImportAccess')) { ?>
                    <li class="menu-item has-sub <?php if ($this->li_a == "export") {
                                                        echo ' open';
                                                    } ?>">
                        <a href="#"><i class="fas fa-database"></i> <span><?php echo $this->lang->line('Export_Import'); ?></span></a>
                        <ul class="menu-content">
                            <?php if ($this->aauth->permission_new(null, 'dataExportPeople')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>export/crm"><i class="fas fa-users"></i> <?php echo $this->lang->line('Export People Data'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'dataExportTransactions')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>export/transactions"><i class="fas fa-credit-card"></i> <?php echo $this->lang->line('Export Transactions'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'dataExportProducts')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>export/products"><i class="fas fa-box"></i> <?php echo $this->lang->line('Export Products'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'dataExportLogData')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>export/logdata"><i class="fas fa-unlock"></i> <?php echo $this->lang->line('LogData'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'dataExportAccountStatements')) { ?>
                                <li class="menu-item" title="<?= $this->lang->line('Account Statements'); ?>">
                                    <a href="<?php echo base_url(); ?>export/account"><i class="fas fa-file-alt"></i> <?php echo $this->lang->line('Account Statements'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'dataExportTax')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>export/taxstatement"><i class="fas fa-percentage"></i> <?php echo $this->lang->line('Tax_Export'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'dataExportDatabaseBackup')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>export/dbexport"><i class="fas fa-cloud-upload-alt"></i> <?php echo $this->lang->line('Database Backup'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'dataImportProducts')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>import/products"><i class="fas fa-download"></i> <?php echo $this->lang->line('Import Products'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'dataImportCustomers')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>import/customers"><i class="fas fa-user-plus"></i> <?php echo $this->lang->line('Import Customers'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'dataExportPeopleProducts')) { ?>
                                <li class="menu-item" title="<?= $this->lang->line('ProductsAccount Statements'); ?>">
                                    <a href="<?php echo base_url(); ?>export/people_products"><i class="fas fa-file"></i> <?php echo $this->lang->line('ProductsAccount Statements'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'dataBulkDeletion')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>invoices/bulk_delete"><i class="fas fa-trash"></i> <?php echo $this->lang->line('BulkDeletion'); ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <!-- Miscellaneous Section -->
                <?php if ($this->aauth->permission_new(null, 'miscellaneousAccess')) { ?>
                    <li class="menu-item has-sub <?php if ($this->li_a == "misc") {
                                                        echo ' open';
                                                    } ?>">
                        <a href="#"><i class="fas fa-wrench"></i> <span><?php echo $this->lang->line('Miscellaneous') ?></span></a>
                        <ul class="menu-content">
                            <?php if ($this->aauth->permission_new(null, 'miscellaneousInvoiceNotes')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>tools/notes"><i class="fas fa-file-alt"></i> <?php echo $this->lang->line('InvoiceNotes'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'miscellaneousCalendar')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>events"><i class="fas fa-calendar"></i> <?php echo $this->lang->line('Calendar'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'miscellaneousDocuments')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>tools/documents"><i class="fas fa-file"></i> <?php echo $this->lang->line('Documents'); ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <!-- Settings Section -->
                <?php if ($this->aauth->permission_new(null, 'settingsAccess')) { ?>
                    <li class="menu-item has-sub <?php if ($this->li_a == "settings") {
                                                        echo ' open';
                                                    } ?>">
                        <a href="#"><i class="fas fa-cog"></i> <span><?php echo $this->lang->line('Settings'); ?></span></a>
                        <ul class="menu-content">
                            <?php if ($this->aauth->permission_new(null, 'settingsLanguage')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>settings/language"><i class="fas fa-globe"></i> <?php echo $this->lang->line('Language'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'settingsGeneral')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>settings/currency"><i class="fas fa-cog"></i> <?php echo $this->lang->line('GeneralSettings'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'settingsCompanyProfile')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>settings/company"><i class="fas fa-home"></i> <?php echo $this->lang->line('CompanyProfile'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'settingsInvoiceFormat')) { ?>
                                <li class="menu-item">
                                    <a href="<?php echo base_url(); ?>settings/format" data-toggle="dropdown"><i class="fas fa-file-invoice"></i> <?php echo $this->lang->line('InvoiceFormat'); ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($this->aauth->permission_new(null, 'settingsThirdPartyIntegration')) { ?>
                                <li class="menu-item" title="<?php echo $this->lang->line('ThirdPartyIntegeration'); ?>">
                                    <a href="<?php echo base_url(); ?>settings/integeration"><i class="fas fa-plug"></i> <?php echo $this->lang->line('ThirdPartyIntegeration'); ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <!-- POS Section -->
                <?php if ($this->aauth->permission_new(null, 'posAccess')) { ?>
                    <li class="menu-item">
                        <a href="<?php echo base_url(); ?>pos_invoices/create"><i class="fas fa-cash-register"></i> <span><?php echo $this->lang->line('POS'); ?></span></a>
                    </li>
                <?php } ?>

                <!-- Account Section -->
                <li class="menu-item has-sub">
                    <a href="#">
                        <i class="fas fa-user"></i>
                        <span><?php echo $this->lang->line('Account'); ?></span>
                        <?php if ($this->aauth->auto_attend()) { ?>
                            <?php if ($this->aauth->clock()) { ?>
                                <span class="badge badge-pill badge-default badge-success badge-default badge-up"><?= $this->lang->line('On') ?></span>
                            <?php } else { ?>
                                <span class="badge badge-pill badge-default badge-warning badge-default badge-up"><?= $this->lang->line('Off') ?></span>
                            <?php } ?>
                        <?php } ?>
                    </a>
                    <ul class="menu-content">
                        <li class="menu-item">
                            <a href="<?php echo base_url(); ?>user/profile"><i class="fas fa-user"></i> <?php echo $this->lang->line('Profile') ?></a>
                        </li>
                        <li class="menu-item">
                            <a href="<?php echo base_url(); ?>user/attendance"><i class="fas fa-list-ol"></i> <?php echo $this->lang->line('Attendance') ?></a>
                        </li>
                        <li class="menu-item">
                            <a href="<?php echo base_url(); ?>user/holidays"><i class="fas fa-hotel"></i> <?php echo $this->lang->line('Holidays') ?></a>
                        </li>
                        <li class="menu-item">
                            <a href="<?php echo base_url('user/logout'); ?>"><i class="fas fa-power-off"></i> <?php echo $this->lang->line('Logout') ?></a>
                        </li>
                        <?php if (!$this->aauth->clock()) { ?>
                            <li class="menu-item">
                                <a href="<?= base_url() ?>/dashboard/clock_in"><i class="fas fa-clock"></i> <?= $this->lang->line('Clock') ?> <?= $this->lang->line('In') ?></a>
                            </li>
                        <?php } else { ?>
                            <li class="menu-item">
                                <a href="<?= base_url() ?>/dashboard/clock_out"><i class="fas fa-clock"></i> <?= $this->lang->line('Clock') ?> <?= $this->lang->line('Out') ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <!-- Horizontal navigation-->
    <div id="c_body"></div>
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">