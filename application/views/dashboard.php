<style>
    /* Global Font Family and Size - Same for whole page */
    * {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 14px;
    }

    /* Consistent font sizing for all elements */
    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    .card-title,
    .card-header h4 {
        font-size: 16px;
        font-weight: 600;
    }

    .dashboard-card .card-title {
        font-size: 12px;
        font-weight: 500;
    }

    .dashboard-card .card-value,
    .stats-number {
        font-size: 18px;
        font-weight: 700;
    }

    .dashboard-card .card-subtitle,
    .stats-text,
    .badge,
    small,
    .small {
        font-size: 12px;
        font-weight: 400;
    }

    .table th,
    .table td,
    .btn,
    .media-body,
    .list-group-item {
        font-size: 14px;
    }

    /* Recent Customers specific font styling */
    .recent-customers-card .card-header h4,
    .recent-customers-card .customer-name,
    .recent-customers-card .customer-total {
        font-size: 16px;
        font-weight: 600;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .recent-customers-card .customer-name,
    .recent-customers-card .customer-total {
        font-size: 14px;
    }

    .recent-customers-card .customer-status {
        font-size: 11px;
        font-weight: 600;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .recent-customers-card .customer-vat {
        font-size: 12px;
        font-weight: 400;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* ===== DASHBOARD CLASS SYSTEM =====
     * 
     * CARD CLASSES:
     * - .dashboard-card (base card)
     * - .card-left (icon + content side by side)
     * - .card-center (centered content)
     * 
     * CARD TYPES:
     * - .card-sales (Sales Overview - white background)
     * - .card-purchase (Purchase Overview - white background) 
     * - .card-inventory (Inventory Summary - light blue background)
     * - .card-business (Business Partner - light blue background)
     * - .card-customer (Top Customers - light blue background)
     * - .card-balance (Balance cards - white background)
     * 
     * ICON SYSTEM:
     * - .card-icon (40x40px container)
     * - .card-icon img (24x24px images)
     * - .card-icon i (20px Font Awesome icons)
     * 
     * ====================================== */

    /* ===== UNIFIED CARD SYSTEM ===== */
    .dashboard-card {
        background: #ffffff;
        border: 1px solid #e6ecf5;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        padding: 12px;
        min-height: 80px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        position: relative;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .dashboard-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }

    /* Card Layout Variants */
    .dashboard-card.card-left {
        flex-direction: row;
        align-items: center;
        text-align: left;
        justify-content: flex-start;
    }

    .dashboard-card.card-center {
        align-items: center;
        text-align: center;
    }

    /* ===== UNIFIED ICON SYSTEM ===== */
    .dashboard-card .card-icon {
        width: 40px;
        height: 40px;
        margin-right: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: rgba(108, 117, 125, 0.1);
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .dashboard-card .card-icon img {
        width: 24px;
        height: 24px;
        object-fit: contain;
        filter: brightness(0.8);
        transition: all 0.2s ease;
    }

    .dashboard-card .card-icon i {
        font-size: 20px;
        color: #667eea;
        transition: all 0.2s ease;
        display: block;
    }

    .dashboard-card:hover .card-icon {
        background: rgba(108, 117, 125, 0.15);
        transform: scale(1.05);
    }

    .dashboard-card:hover .card-icon img {
        filter: brightness(1);
    }

    /* ===== CARD TYPE VARIANTS ===== */
    /* Sales & Purchase Overview Cards (Default) */
    .dashboard-card.card-sales,
    .dashboard-card.card-purchase {
        background: #ffffff;
        border: 1px solid #e6ecf5;
    }

    /* Inventory & Business Partner Cards */
    .dashboard-card.card-inventory,
    .dashboard-card.card-business {
        background: #f8faff;
        border: 1px solid #d1d9e6;
    }

    /* Customer Cards */
    .dashboard-card.card-customer {
        background: #f8faff;
        border: 1px solid #d1d9e6;
        min-height: 120px;
        height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    /* Balance Cards */
    .dashboard-card.card-balance {
        background: #ffffff;
        border: 1px solid #e6ecf5;
        min-height: 75px;
    }

    .dashboard-card .card-content {
        display: flex;
        flex-direction: column;
        flex: 1;
        align-items: flex-start;
    }

    .dashboard-card .card-title {
        font-size: 12px;
        color: #404e67;
        margin-bottom: 4px;
        font-weight: 500;
        line-height: 1.3;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .dashboard-card .card-value {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 2px;
        line-height: 1.1;
    }

    .dashboard-card .card-subtitle {
        font-size: 12px;
        color: #6c757d;
        margin: 0;
        font-weight: 400;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Ensure consistent formatting for VAT/GST values in parentheses */
    .dashboard-card .card-subtitle span {
        font-weight: 600;
    }


    /* ===== CARD CONTENT STYLING ===== */
    .dashboard-card.card-customer .card-title,
    .dashboard-card.card-customer .card-value,
    .dashboard-card.card-customer .card-subtitle,
    .dashboard-card.card-balance .card-title,
    .dashboard-card.card-balance .card-value,
    .dashboard-card.card-balance .card-subtitle {
        color: #2c3e50;
    }

    /* Ensure customer cards have equal height and width */
    .dashboard-stats .col-sm-2 {
        display: flex;
        flex-direction: column;
    }

    .dashboard-stats .col-sm-2 .dashboard-card {
        flex: 1;
        height: 120px;
        min-height: 120px;
    }

    /* Customer card specific styling for consistent layout */
    .dashboard-card.card-customer .card-title {
        font-size: 11px;
        font-weight: 600;
        color: #404e67;
        margin-bottom: 6px;
        line-height: 1.2;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100%;
    }

    .dashboard-card.card-customer .card-value {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0;
        line-height: 1.1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Customer card specific icon positioning */
    .dashboard-card.card-customer .card-icon {
        margin: 0 auto 8px auto;
    }


    /* Stats number styling */
    .stats-number {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 2px;
        line-height: 1.1;
    }

    /* Recent Customers Section - Attractive Styling */
    .recent-customers-card {
        background: #ffffff;
        border: 1px solid #e6ecf5;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    .recent-customers-card .card-header {
        background: #f8faff;
        border-bottom: 1px solid #e6ecf5;
        padding: 16px 20px;
    }

    .recent-customers-card .card-header h4 {
        color: #2c3e50;
        margin: 0;
    }

    .recent-customers-card .card-content {
        padding: 0;
    }

    .recent-customers-card .media-list {
        padding: 8px 0;
    }

    .recent-customers-card .customer-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 16px 20px;
        border-bottom: 1px solid #f1f3f4;
        transition: background-color 0.2s ease;
        text-decoration: none;
        color: inherit;
    }

    .recent-customers-card .customer-item:hover {
        background-color: #f8faff;
        text-decoration: none;
        color: inherit;
    }

    .recent-customers-card .customer-item:last-child {
        border-bottom: none;
    }

    .recent-customers-card .customer-info {
        flex: 1;
    }

    .recent-customers-card .customer-name {
        color: #2c3e50;
        margin-bottom: 6px;
        line-height: 1.3;
    }

    .recent-customers-card .customer-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .recent-customers-card .customer-status.st-paid {
        background-color: #d4edda;
        color: #155724;
    }

    .recent-customers-card .customer-status.st-partial {
        background-color: #cce5ff;
        color: #004085;
    }

    .recent-customers-card .customer-status.st-pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .recent-customers-card .customer-amounts {
        text-align: right;
        flex-shrink: 0;
        margin-left: 16px;
    }

    .recent-customers-card .customer-total {
        color: #2c3e50;
        margin-bottom: 4px;
        line-height: 1.2;
    }

    .recent-customers-card .customer-vat {
        color: #6c757d;
        line-height: 1.2;
    }

    /* Consistent icon styling for all section headers */
    .card-header h4 i,
    .card-title i {
        margin-right: 8px;
        color: #667eea;
        font-size: 16px;
        vertical-align: middle;
        display: inline-block;
    }

    /* Ensure icons are visible */
    .icon-bar-chart:before,
    .icon-shopping-cart:before,
    .icon-users:before,
    .icon-calendar:before {
        content: attr(data-icon);
        font-family: 'icon-font' !important;
    }

    /* Icon styling for received cards */
    .received-card .card-icon {
        background: rgba(102, 126, 234, 0.1);
        border-radius: 8px;
        padding: 8px;
        margin-bottom: 8px;
    }

    .received-card .card-icon img,
    .dashboard-card.card-center .card-icon img {
        width: 20px;
        height: 20px;
        object-fit: contain;
        filter: brightness(0.7);
    }


    /* Fallback for missing images */
    .dashboard-card .card-icon img[src=""],
    .dashboard-card .card-icon img:not([src]) {
        display: none;
    }

    .dashboard-card .card-icon:empty::after {
        content: "📊";
        font-size: 20px;
        color: #667eea;
    }


    /* Legacy support for existing classes */
    .card {
        border-radius: 12px;
    }

    .card .inventory-cards {
        background: #f8faff;
        box-shadow: 2px 4px 4px rgba(0, 0, 0, 0.1) !important;
    }

    .received-card {
        background: #ffffff;
        border: 1px solid #e6ecf5;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        transition: transform .12s ease, box-shadow .12s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        min-height: 140px;
    }

    .received-card:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .received-card .stats-number {
        margin-top: 4px;

    }

    /* Promo Modal Styles */
    .promo-modal {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        z-index: 9999;
        /* Sit on top */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed, though we aim to avoid scrolling */
        background-color: rgba(0, 0, 0, 0.4);
        /* Black w/ opacity */
        display: flex;
        /* Using flex to center the modal content */
        align-items: center;
        /* Center vertically */
        justify-content: center;
        /* Center horizontally */
    }

    .promo-content {
        background-color: #fefefe;
        margin: auto;
        /* This centers the modal content box in IE11 */
        padding: 20px;
        border: 1px solid #888;
        width: auto;
        /* Auto width based on the content size */
        max-width: 600px;
        /* Maximum width */
        box-sizing: border-box;
        /* Make sure padding doesn't affect the total width */
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        /* Center children, like the image */
    }

    img {
        max-width: 100%;
        /* Make the image responsive */
        height: auto;
        /* Keep the image aspect ratio */
    }

    .close-button {
        color: #aaa;
        position: absolute;
        top: 10px;
        right: 25px;
        font-size: 30px;
        font-weight: bold;
        cursor: pointer;
    }

    .close-button:hover,
    .close-button:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    .stats-text {
        color: #404e67;
        font-size: 12px;
        font-weight: 500;
    }

    /* Enhanced Calendar Styles */
    .calendar-card {
        background: #ffffff;
        border: 1px solid #e6ecf5;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .calendar-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .calendar-controls {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .calendar-controls .btn {
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease;
        border-width: 1px;
    }

    .calendar-controls .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .calendar-container {
        padding: 20px;
        background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);
        border-radius: 8px;
        margin: 10px;
    }

    .enhanced-calendar {
        max-width: 100%;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        padding: 20px;
        border: 1px solid #e6ecf5;
        transition: all 0.3s ease;
    }

    .enhanced-calendar:hover {
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12);
    }

    /* FullCalendar Custom Styling */
    .fc {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .fc-toolbar {
        margin-bottom: 20px;
        padding: 15px 0;
        border-bottom: 2px solid #f1f3f4;
    }

    .fc-toolbar-title {
        font-size: 24px;
        font-weight: 700;
        color: #2c3e50;
        text-transform: capitalize;
    }

    .fc-button {
        background: #25468d;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(37, 70, 141, 0.3);
    }

    .fc-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 70, 141, 0.4);
        background: #1e3a7a;
    }

    .fc-button:active {
        transform: translateY(0);
    }

    .fc-button-primary:not(:disabled):active,
    .fc-button-primary:not(:disabled).fc-button-active {
        background: #1a2f66;
    }

    .fc-daygrid-day {
        border: 1px solid #e6ecf5;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .fc-daygrid-day:hover {
        background: #f8faff;
        transform: scale(1.02);
        z-index: 10;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .fc-daygrid-day-number {
        padding: 8px;
        font-weight: 600;
        color: #2c3e50;
        transition: all 0.2s ease;
    }

    .fc-daygrid-day:hover .fc-daygrid-day-number {
        color: #25468d;
        transform: scale(1.1);
    }

    /* Today styling */
    .fc-day-today {
        background: #FFA87D;
        color: white;
    }

    .fc-day-today .fc-daygrid-day-number {
        color: white;
        font-weight: 700;
    }

    /* Weekend styling */
    .fc-day-sat,
    .fc-day-sun {
        background: #f8faff;
    }

    /* Event styling */
    .fc-event {
        border: none;
        border-radius: 6px;
        padding: 4px 8px;
        font-weight: 600;
        font-size: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .fc-event:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .fc-event-custom {
        background: #25468d;
        color: white;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 11px;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 3px 12px rgba(37, 70, 141, 0.3);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .fc-event-custom .event-title {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        margin-bottom: 2px;
    }

    .fc-event-custom .event-title i {
        font-size: 10px;
        opacity: 0.9;
    }

    .fc-event-custom .event-description {
        font-size: 9px;
        opacity: 0.9;
        font-weight: 500;
    }

    .fc-event-custom::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s ease;
    }

    .fc-event-custom:hover::before {
        left: 100%;
    }

    .fc-event-custom:hover {
        transform: translateY(-3px) scale(1.08);
        box-shadow: 0 8px 20px rgba(37, 70, 141, 0.5);
        border-color: rgba(255, 255, 255, 0.4);
    }

    /* Due date highlighting */
    .fc-daygrid-day.due-date-highlight {
        background: #25468d;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .fc-daygrid-day.due-date-highlight::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }

    .fc-daygrid-day.due-date-highlight .fc-daygrid-day-number {
        color: white;
        font-weight: 700;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    .fc-daygrid-day.due-date-highlight:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 24px rgba(37, 70, 141, 0.4);
    }

    /* Loading animation */
    .calendar-loading {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 400px;
        background: #f8faff;
        border-radius: 12px;
    }

    .calendar-loading::after {
        content: '';
        width: 40px;
        height: 40px;
        border: 4px solid #e6ecf5;
        border-top: 4px solid #25468d;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .calendar-controls {
            flex-direction: column;
            gap: 4px;
        }

        .calendar-controls .btn {
            font-size: 12px;
            padding: 6px 12px;
        }

        .fc-toolbar-title {
            font-size: 18px;
        }

        .enhanced-calendar {
            padding: 10px;
        }
    }

    /* ===== STANDARDIZE ALL NUMERIC VALUES TO 18px (matching Top Customers cards) ===== */

    /* Dashboard card values - set to 18px (Top Customers already has 18px, this standardizes others) */
    .dashboard-card .card-value {
        font-size: 18px !important;
        font-weight: 700;
    }

    /* Balance card values */
    .dashboard-card.card-balance .card-value {
        font-size: 18px !important;
        font-weight: 700;
    }

    /* Stats numbers (used in received amounts, purchase overview, etc.) */
    .stats-number {
        font-size: 18px !important;
        font-weight: 700;
    }

    /* Today Income, Expenses, Profit, Revenue cards */
    #card-today-income,
    #card-today-expenses,
    #card-today-profit,
    #card-today-revenue {
        font-size: 18px !important;
        font-weight: 700 !important;
        line-height: 1.2;
        margin-bottom: 8px;
    }

    /* Graph/chart axis numeric values - for Chart.js, Morris.js, ApexCharts, etc. */
    .ct-axis-y text,
    .ct-axis-x text,
    .morris-hover .morris-hover-point,
    .apexcharts-yaxis text,
    .apexcharts-xaxis text,
    .chartjs-axis text,
    #products-sales .ct-axis text,
    #products-sales .ct-label {
        font-size: 18px !important;
        font-weight: 600;
    }

    /* KPI card values */
    #kpi-totalSales,
    #kpi-todaySales,
    #kpi-monthSales,
    #kpi-yearSales {
        font-size: 18px !important;
        font-weight: 700;
    }

    /* KPI VAT values - match Purchase Overview font size (12px) */
    #kpi-totalSalesVat,
    #kpi-todaySalesVat,
    #kpi-monthSalesVat,
    #kpi-yearSalesVat {
        font-size: 12px !important;
        font-weight: 600;
    }

    /* Invoice count values */
    #invoice-today,
    #invoice-tomorrow,
    #invoice-month,
    #invoice-lastmonth {
        font-size: 18px !important;
        font-weight: 700;
    }

    /* Received amounts numeric values */
    #rec-yesterday-total,
    #rec-month-total,
    .received-card .stats-number {
        font-size: 18px !important;
        font-weight: 700;
    }

    /* Purchase overview numeric values */
    .purchase-overview .card-value,
    .purchase-overview .stats-number {
        font-size: 18px !important;
        font-weight: 700;
    }

    /* Inventory and other dashboard numeric values */
    #inv-inhand,
    #inv-willrecv {
        font-size: 18px !important;
        font-weight: 700;
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

        <div class="row" id="kpi-shell" data-endpoint="<?= base_url('dashboard/widget_kpis') ?>">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <span style="margin-right: 8px; color: #667eea; font-size: 16px;">📊</span>
                            Sales Overview
                        </h4>
                    </div>
                    <div class="card-content container-fluid dashboard-stats">
                        <div class="row">
                            <div class="col-sm-3 mb-1">
                                <a href="<?= base_url('invoices?param=total') ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-chart-line"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">Total Sales</div>
                                        <div class="card-value" id="kpi-totalSales"><?= amountExchange(0, 0, $this->aauth->get_user()->loc); ?></div>
                                        <div class="card-subtitle">(<?php echo $this->lang->line('Tax') ?> ) <span id="kpi-totalSalesVat"><?= amountExchange(0, 0, $this->aauth->get_user()->loc); ?></span></div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-3 mb-1">
                                <a href="<?= base_url('invoices?param=today') ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-chart-line"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">Today Sales</div>
                                        <div class="card-value" id="kpi-todaySales">--</div>
                                        <div class="card-subtitle">(<?php echo $this->lang->line('Tax') ?> ) <span id="kpi-todaySalesVat"><?= amountExchange(0, 0, $this->aauth->get_user()->loc); ?></span></div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-3 mb-1">
                                <a href="<?= base_url('invoices?param=month') ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-chart-line"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">This Month Sales</div>
                                        <div class="card-value" id="kpi-monthSales">--</div>
                                        <div class="card-subtitle">(<?php echo $this->lang->line('Tax') ?> ) <span id="kpi-monthSalesVat"><?= amountExchange(0, 0, $this->aauth->get_user()->loc); ?></span></div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-3 mb-1">
                                <a href="<?= base_url('invoices?param=year') ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-chart-line"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">This Year Sales</div>
                                        <div class="card-value" id="kpi-yearSales">--</div>
                                        <div class="card-subtitle">(<?php echo $this->lang->line('Tax') ?> ) <span id="kpi-yearSalesVat"><?= amountExchange(0, 0, $this->aauth->get_user()->loc); ?></span></div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-3 mb-1">
                                <a href="<?= base_url('invoices?param=today') ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-file-invoice"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">Today Invoices</div>
                                        <div class="card-value" id="invoice-today">--</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-3 mb-1">
                                <a href="<?= base_url('invoices?param=tomorrow') ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-file-invoice"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">Tomorrow Invoices</div>
                                        <div class="card-value" id="invoice-tomorrow">--</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-3 mb-1">
                                <a href="<?= base_url('invoices?param=month') ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-file-invoice"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">This Month Invoices</div>
                                        <div class="card-value" id="invoice-month">--</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-3 mb-1">
                                <a href="<?= base_url('invoices?param=lastmonth') ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-file-invoice"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">Last Month Invoices</div>
                                        <div class="card-value" id="invoice-lastmonth">--</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-content container-fluid dashboard-stats">
                <div class="row">
                    <?php
                    // Define the periods and their corresponding data
                    $periods = [
                        'Yesterday Received' => $yesterday_received,
                        'This Month Received' => $month_received
                    ];

                    // Function to calculate totals for each payment method
                    function calculateTotals($data)
                    {
                        $totals = ['Cash' => 0, 'Card Payment' => 0, 'Bank Transfer' => 0];
                        if (!is_array($data) || empty($data)) {
                            return $totals;
                        }
                        foreach ($data as $payment) {
                            // Handle both object and array formats
                            $method = is_object($payment) ? ($payment->paymt_method ?? '') : ($payment['paymt_method'] ?? '');
                            $total = floatval(is_object($payment) ? ($payment->total ?? 0) : ($payment['total'] ?? 0));

                            // Normalize payment method names (case-insensitive matching)
                            $methodLower = strtolower(trim($method));
                            if (strpos($methodLower, 'cash') !== false) {
                                $totals['Cash'] += $total;
                            } elseif (strpos($methodLower, 'card') !== false) {
                                $totals['Card Payment'] += $total;
                            } elseif (strpos($methodLower, 'bank') !== false || strpos($methodLower, 'transfer') !== false) {
                                $totals['Bank Transfer'] += $total;
                            }
                        }
                        return $totals;
                    }

                    // Loop through each period and render its card
                    foreach ($periods as $label => $data) {
                        $totals = calculateTotals($data);
                        // Calculate total sum - handle both object and array formats
                        $totalSum = 0;
                        if (is_array($data) && !empty($data)) {
                            foreach ($data as $item) {
                                $total = is_object($item) ? ($item->total ?? 0) : ($item['total'] ?? 0);
                                $totalSum += floatval($total);
                            }
                        }
                    ?>
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">
                                        <h4>
                                            <span style="margin-right: 8px; color: #667eea; font-size: 16px;"><?= ($label === 'Yesterday Received') ? '📅' : '📆' ?></span>
                                            <?= $label ?>: <span class="stats-number" id="<?= ($label === 'Yesterday Received') ? 'rec-yesterday-total' : 'rec-month-total' ?>">
                                                <?= amountExchange($totalSum, 0, $this->aauth->get_user()->loc); ?>
                                            </span>
                                        </h4>
                                    </div>
                                </div>
                                <div class="card-content container-fluid dashboard-stats">
                                    <div class="row text-center">
                                        <div class="col-sm-4 mb-1">
                                            <a href="<?= base_url('products'); ?>" class="dashboard-card card-center text-decoration-none text-reset">
                                                <div class="card-icon">
                                                    <i class="fa fa-money-bill-wave"></i>
                                                </div>
                                                <div class="card-title">Cash</div>
                                                <div class="card-value" id="<?= ($label === 'Yesterday Received') ? 'rec-yesterday-cash' : 'rec-month-cash' ?>"><?= amountExchange($totals['Cash'], 0, $this->aauth->get_user()->loc); ?></div>
                                            </a>
                                        </div>
                                        <div class="col-sm-4 mb-1">
                                            <a href="<?= base_url('products'); ?>" class="dashboard-card card-center text-decoration-none text-reset">
                                                <div class="card-icon">
                                                    <i class="fa fa-credit-card"></i>
                                                </div>
                                                <div class="card-title">Card Payment</div>
                                                <div class="card-value" id="<?= ($label === 'Yesterday Received') ? 'rec-yesterday-card' : 'rec-month-card' ?>"><?= amountExchange($totals['Card Payment'], 0, $this->aauth->get_user()->loc); ?></div>
                                            </a>
                                        </div>
                                        <div class="col-sm-4 mb-1">
                                            <a href="<?= base_url('products'); ?>" class="dashboard-card card-center text-decoration-none text-reset">
                                                <div class="card-icon">
                                                    <i class="fa fa-university"></i>
                                                </div>
                                                <div class="card-title">Bank Transfer</div>
                                                <div class="card-value" id="<?= ($label === 'Yesterday Received') ? 'rec-yesterday-bank' : 'rec-month-bank' ?>"><?= amountExchange($totals['Bank Transfer'], 0, $this->aauth->get_user()->loc); ?></div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>


            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-shopping-cart" style="margin-right: 8px; color: #667eea;"></i>
                            Purchase Overview
                        </h4>
                    </div>
                    <div class="card-content container-fluid dashboard-stats">
                        <div class="row">
                            <div class="col-sm-3 mb-1">
                                <a href="<?php echo base_url('purchase?param=total'); ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-chart-line"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">Total Purchases</div>
                                        <div class="card-value" id="po-total">--</div>
                                        <div class="card-subtitle">(<?php echo $this->lang->line('Tax') ?> ) <span id="po-total-vat"><?= amountExchange(0, 0, $this->aauth->get_user()->loc); ?></span></div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-3 mb-1">
                                <a href="<?php echo base_url('purchase?param=today'); ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-chart-line"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">Today Purchases</div>
                                        <div class="card-value" id="po-today">--</div>
                                        <div class="card-subtitle">(<?php echo $this->lang->line('Tax') ?> ) <span id="po-today-vat"><?= amountExchange(0, 0, $this->aauth->get_user()->loc); ?></span></div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-3 mb-1">
                                <a href="<?php echo base_url('purchase?param=month'); ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-chart-line"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">This Month Purchases</div>
                                        <div class="card-value" id="po-month">--</div>
                                        <div class="card-subtitle">(<?php echo $this->lang->line('Tax') ?> ) <span id="po-month-vat"><?= amountExchange(0, 0, $this->aauth->get_user()->loc); ?></span></div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-3 mb-1">
                                <a href="<?php echo base_url('purchase?param=year'); ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-chart-line"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">This Year Purchases</div>
                                        <div class="card-value" id="po-year">--</div>
                                        <div class="card-subtitle">(<?php echo $this->lang->line('Tax') ?> ) <span id="po-year-vat"><?= amountExchange(0, 0, $this->aauth->get_user()->loc); ?></span></div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-3 mb-1">
                                <a href="<?php echo base_url('purchase?param=total'); ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-shopping-cart"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">Total PO</div>
                                        <div class="card-value" id="po-count-total">--</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-3 mb-1">
                                <a href="<?php echo base_url('purchase?param=today'); ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">Today PO</div>
                                        <div class="card-value" id="po-count-today">--</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-3 mb-1">
                                <a href="<?php echo base_url('purchase?param=month'); ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-clock"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">This Month PO</div>
                                        <div class="card-value" id="po-count-month">--</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-3 mb-1">
                                <a href="<?php echo base_url('purchase?param=year'); ?>" class="dashboard-card card-left text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-times-circle"></i>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-title">Verified PO/Unverified PO</div>
                                        <div class="card-value" id="po-ver-unver">--</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0" style="color: #404e67;">
                            Top Customers
                        </h4>

                        <div class="col-auto">
                            <a href="<?= base_url('invoices?param=lastmonth') ?>" class="dashboard-card card-balance text-decoration-none text-reset">
                                <div class="card-icon">
                                    <i class="fa fa-dollar-sign"></i>
                                </div>
                                <div class="card-content">
                                    <div class="card-title">Total Balance</div>
                                    <div class="card-value">
                                        <?= amountExchange($ttlBalance, 0, $this->aauth->get_user()->loc); ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="card-content container-fluid dashboard-stats px-2">
                        <div class="row">
                            <?php if (isset($customerBalances) && !empty($customerBalances)) : ?>
                                <?php foreach ($customerBalances as $item) : ?>
                                    <div class="col-sm-2 mb-3">
                                        <a href="<?php echo base_url('customers/view?id=' . $item['csd']); ?>" class="dashboard-card card-customer text-decoration-none text-reset d-block">
                                            <div class="card-icon">
                                                <i class="fa fa-user"></i>
                                            </div>
                                            <div class="card-title"><?php echo $item['cust_name']; ?></div>
                                            <div class="card-value">
                                                <?= amountExchange($item['cust_balance'], 0, $this->aauth->get_user()->loc); ?>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php elseif ($customerBalances == 'sql') : ?>
                                <div class="col-12">
                                    <div class="media-body w-100">
                                        <h5 class="list-group-item-heading bg-danger white">Critical SQL Strict Mode Error:</h5>
                                        <p>Please disable Strict SQL Mode in your database settings.</p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="media-body w-100">
                                        <h5 class="list-group-item-heading" style="color: #404e67;">No data available</h5>
                                        <p>No customer balances to display at this time.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            Inventory Summary
                        </h4>
                    </div>
                    <div class="card-content container-fluid dashboard-stats px-2">
                        <div class="row">
                            <div class="col-sm-6 mb-1">
                                <a href="<?php echo base_url('products'); ?>" class="dashboard-card card-inventory card-center text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-boxes"></i>
                                    </div>
                                    <div class="card-title">Quantity in Hand</div>
                                    <div class="card-value" id="inv-inhand"><?= $allProductsInHand ?></div>
                                </a>
                            </div>
                            <div class="col-sm-6 mb-1">
                                <a href="<?php echo base_url('products'); ?>" class="dashboard-card card-inventory card-center text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-truck"></i>
                                    </div>
                                    <div class="card-title">Will be Received</div>
                                    <div class="card-value" id="inv-willrecv"><?= (int)$productWillBeReceived ?></div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            Product Details
                        </h4>
                    </div>
                    <div class="card-content container-fluid dashboard-stats">
                        <div class="row">
                            <div class="col-sm-4 mb-1">
                                <a href="<?php echo base_url('products') . '?stock_out_filter=stock_out'; ?>" class="dashboard-card card-inventory card-center text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="card-title">Low Stock</div>
                                    <div class="card-value" id="inv-low"><?= $stockedOutItems ?></div>
                                </a>
                            </div>
                            <div class="col-sm-4 mb-1">
                                <a href="<?php echo base_url('productcategory'); ?>" class="dashboard-card card-inventory card-center text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-tags"></i>
                                    </div>
                                    <div class="card-title">Categories</div>
                                    <div class="card-value" id="inv-cat"><?= $productCat ?></div>
                                </a>
                            </div>
                            <div class="col-sm-4 mb-1">
                                <a href="<?php echo base_url('products'); ?>" class="dashboard-card card-inventory card-center text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-check-double"></i>
                                    </div>
                                    <div class="card-title">Total</div>
                                    <div class="card-value" id="inv-all"><?= $allItems ?></div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            Business Partner
                        </h4>
                    </div>
                    <div class="card-content container-fluid dashboard-stats">
                        <div class="row">
                            <div class="col-sm-4 mb-1">
                                <a href="<?php echo base_url('customers'); ?>" class="dashboard-card card-inventory card-center text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-users"></i>
                                    </div>
                                    <div class="card-title">Customers</div>
                                    <div class="card-value" id="bp-cust"><?= $totalCust ?></div>
                                </a>
                            </div>
                            <div class="col-sm-4 mb-1">
                                <a href="<?php echo base_url('supplier'); ?>" class="dashboard-card card-inventory card-center text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-truck"></i>
                                    </div>
                                    <div class="card-title">Suppliers</div>
                                    <div class="card-value" id="bp-supp"><?= $totalSupp ?></div>
                                </a>
                            </div>
                            <div class="col-sm-4 mb-1">
                                <a href="<?php echo base_url('employee'); ?>" class="dashboard-card card-inventory card-center text-decoration-none text-reset">
                                    <div class="card-icon">
                                        <i class="fa fa-user"></i>
                                    </div>
                                    <div class="card-title">Users</div>
                                    <div class="card-value" id="bp-user"><?= $totalUser ?></div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card calendar-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fa fa-calendar-alt" style="margin-right: 8px; color: #25468d;"></i>
                            Due Invoices Calendar
                        </h4>
                        <div class="calendar-controls">
                            <button class="btn btn-sm btn-outline-primary" id="calendar-today">
                                <i class="fa fa-home"></i> Today
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="calendar-prev">
                                <i class="fa fa-chevron-left"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="calendar-next">
                                <i class="fa fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-content container-fluid dashboard-stats">
                        <div class="calendar-container">
                            <div id="calendar" class="enhanced-calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row match-height">
            <div class="col-xl-8 col-lg-12 order-1 order-lg-1 order-xl-1">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"><?php echo $this->lang->line('in_last _30') ?></h4>
                        <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                        <div class="heading-elements">
                            <ul class="list-inline mb-0">
                                <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                                <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div id="products-sales" class="height-300"></div>
                        </div>
                        <div class="row">
                            <div class="col-xl-3 col-lg-6 col-12">
                                <div class="card">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="media">
                                                <div class="media-body text-left w-100">
                                                    <h3 class="primary" id="card-today-income"><?= amountExchange(isset($todayIncome) ? $todayIncome : 0, 0, $this->aauth->get_user()->loc); ?></h3>
                                                    <span><?php echo $this->lang->line('today_income') ?></span>
                                                </div>

                                            </div>
                                            <div class="progress progress-sm mt-1 mb-0">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 100%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-12">
                                <div class="card">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="media">
                                                <div class="media-body text-left w-100">
                                                    <h3 class="danger" id="card-today-expenses"><?= amountExchange(isset($todayExpenses) ? $todayExpenses : 0, 0, $this->aauth->get_user()->loc); ?></h3>
                                                    <span><?php echo $this->lang->line('today_expenses') ?></span>
                                                </div>

                                            </div>
                                            <div class="progress progress-sm mt-1 mb-0">
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: 40%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-12">
                                <div class="card">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="media">
                                                <div class="media-body text-left w-100">
                                                    <h3 class="success" id="card-today-profit"><?= amountExchange(isset($todayProfit) ? $todayProfit : 0, 0, $this->aauth->get_user()->loc); ?></h3>
                                                    <span><?php echo $this->lang->line('today_profit') ?></span>
                                                </div>

                                            </div>
                                            <div class="progress progress-sm mt-1 mb-0">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 60%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-12">
                                <div class="card">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="media">
                                                <div class="media-body text-left w-100">
                                                    <h3 class="warning" id="card-today-revenue"><?= amountExchange(isset($todayRevenue) ? $todayRevenue : 0, 0, $this->aauth->get_user()->loc); ?></h3>
                                                    <span>Today Revenue</span>
                                                </div>

                                            </div>
                                            <div class="progress progress-sm mt-1 mb-0">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: 35%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-12 order-2 order-lg-2 order-xl-2">
                <div class="card recent-customers-card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-users" style="margin-right: 8px; color: #667eea;"></i>
                            <?php echo $this->lang->line('RecentCustomers'); ?>
                        </h4>
                    </div>
                    <div class="card-content">
                        <div id="recent-customers-body" class="media-list height-450"></div>
                    </div>
                </div>
            </div>







        </div>

        <div class="row match-height">
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"><?php echo $this->lang->line('recent_invoices') ?></h4>
                        <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                        <div class="heading-elements">
                            <p><span class="float-right">
                                    <a href="<?php echo base_url() ?>invoices/create" class="btn btn-primary btn-sm rounded"><?php echo $this->lang->line('Add Sale') ?></a>
                                    <a href="<?php echo base_url() ?>invoices" class="btn btn-success btn-sm rounded"><?php echo $this->lang->line('Manage Invoices') ?></a>

                            </p>
                        </div>
                    </div>
                    <div class="card-content">

                        <div class="table-responsive">
                            <table id="recent-orders" class="table table-hover mb-1">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('Invoices') ?>#</th>
                                        <th><?php echo $this->lang->line('Customer') ?></th>
                                        <th><?php echo $this->lang->line('Status') ?></th>
                                        <th><?php echo $this->lang->line('Due') ?></th>
                                        <th><?php echo $this->lang->line('Amount') ?></th>
                                        <th><?php echo $this->lang->line('Tax') ?></th>
                                    </tr>
                                </thead>
                                <tbody id="recent-invoices-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card-group">
                    <div class="card dashboard-cards">
                        <div class="card-content">

                            <div class="card-body">
                                <?php
                                // Get currency symbol from General Settings (uppercase)
                                if (!isset($currency_symbol)) {
                                    $user_loc = isset($this->aauth) && $this->aauth->get_user() ? ($this->aauth->get_user()->loc ?? 0) : 0;
                                    $currency_symbol = currency($user_loc);
                                }
                                ?>
                                <div class="media">
                                    <div class="media-body text-left w-100">
                                        <h3 class="primary" id="goal-income-pct">0%</h3><?= '<span class=" font-medium-1 display-block">' . date('F') . ' ' . $this->lang->line('income') . '</span>'; ?>
                                        <span class="font-medium-1" id="goal-income-amt"><?php echo $currency_symbol; ?> 0.00/<?php echo $currency_symbol; ?> 0.00</span>
                                    </div>
                                    <div class="media-right media-middle">
                                        <i class="fa fa-money primary font-large-2 float-right"></i>
                                    </div>
                                </div>
                                <div class="progress progress-sm mt-1 mb-0">
                                    <div class="progress-bar bg-success" id="goal-income-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card dashboard-cards">
                        <div class="card-content">

                            <div class="card-body">
                                <div class="media">
                                    <div class="media-body text-left w-100">
                                        <h3 class="red" id="goal-expense-pct">0%</h3><?= '<span class="font-medium-1 display-block">' . date('F') . ' ' . $this->lang->line('expenses') . '</span>'; ?>
                                        <span class="font-medium-1" id="goal-expense-amt"><?php echo $currency_symbol; ?> 0.00/<?php echo $currency_symbol; ?> 0.00</span>
                                    </div>
                                    <div class="media-right media-middle">
                                        <i class="ft-external-link red font-large-2 float-right"></i>
                                    </div>
                                </div>
                                <div class="progress progress-sm mt-1 mb-0">
                                    <div class="progress-bar bg-danger" id="goal-expense-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card dashboard-cards">
                        <div class="card-content">

                            <div class="card-body">
                                <div class="media">
                                    <div class="media-body text-left w-100">
                                        <h3 class="blue" id="goal-sales-pct">0%</h3><?= '<span class="font-medium-1 display-block">' . date('F') . ' ' . $this->lang->line('sales') . '</span>'; ?>
                                        <span class="font-medium-1" id="goal-sales-amt"><?php echo $currency_symbol; ?> 0.00/<?php echo $currency_symbol; ?> 0.00</span>
                                    </div>
                                    <div class="media-right media-middle">
                                        <i class="ft-flag blue font-large-2 float-right"></i>
                                    </div>
                                </div>
                                <div class="progress progress-sm mt-1 mb-0">
                                    <div class="progress-bar bg-blue" id="goal-sales-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card dashboard-cards">
                        <div class="card-content">

                            <div class="card-body">
                                <div class="media">
                                    <div class="media-body text-left w-100">
                                        <h3 class="purple" id="goal-net-pct">0%</h3><?= '<span class="font-medium-1 display-block">' . date('F') . ' ' . $this->lang->line('net_income') . '</span>'; ?>
                                        <span class="font-medium-1" id="goal-net-amt"><?php echo $currency_symbol; ?> 0.00/<?php echo $currency_symbol; ?> 0.00</span>
                                    </div>
                                    <div class="media-right media-middle">
                                        <i class="ft-inbox purple font-large-2 float-right"></i>
                                    </div>
                                </div>
                                <div class="progress progress-sm mt-1 mb-0">
                                    <div class="progress-bar bg-purple" id="goal-net-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row match-height">
            <div class="col-xl-8 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"><?php echo $this->lang->line('recent') ?>

                            <a href="<?php echo base_url() ?>transactions" class="btn btn-primary btn-sm rounded"><?php echo $this->lang->line('Transactions') ?></a>


                        </h4>
                        <a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
                        <div class="heading-elements">
                            <ul class="list-inline mb-0">
                                <li><a data-action="reload"><i class="icon-reload"></i></a></li>
                                <li><a data-action="expand"><i class="icon-expand2"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-hover mb-1">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('Date') ?>#</th>
                                        <th><?php echo $this->lang->line('Account') ?></th>
                                        <th><?php echo $this->lang->line('Debit') ?></th>
                                        <th><?php echo $this->lang->line('Credit') ?></th>

                                        <th><?php echo $this->lang->line('Method') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    foreach ($recent_payments as $item) {

                                        echo '<tr>
                                <td class="text-truncate"><a href="' . base_url() . 'transactions/view?id=' . $item['id'] . '">' . dateformat($item['date']) . '</a></td>
                                <td class="text-truncate"> ' . $item['account'] . '</td>
                                <td class="text-truncate">' . amountExchange($item['debit'], 0, $this->aauth->get_user()->loc) . '</td>
                                <td class="text-truncate">' . amountExchange($item['credit'], 0, $this->aauth->get_user()->loc) . '</td>                    
                                <td class="text-truncate">' . $this->lang->line($item['method']) . '</td>
                            </tr>';
                                    } ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-12">
                <div class="card">
                    <div class="card-header ">
                        <h4 class="card-title"><?php echo $this->lang->line('Stock Alert') ?></h4>

                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">

                            <?php

                            foreach ($stock as $item) {
                                echo '<li class="list-group-item"><span class="badge badge-danger float-xs-right">' . +$item['qty'] . ' ' . $item['unit'] . '</span> <a href="' . base_url() . 'products/edit?id=' . $item['pid'] . '">' . $item['product_name'] . '  </a><small class="purple"> <i class="ft-map-pin"></i> ' . $item['title'] . '</small>
                                </li>';
                            } ?>

                        </ul>

                    </div>
                </div>
            </div>
        </div>
<script type="text/javascript">
    document.title = 'Dashboard';
</script>
<script>
    // Get currency symbol and ensure it's uppercase
    var currencySymbol = '<?= addslashes(strtoupper(currency(isset($this->aauth) && $this->aauth->get_user() ? ($this->aauth->get_user()->loc ?? 0) : 0))) ?>';
    
    // Function to ensure currency in string is uppercase
    function ensureUppercaseCurrency(str) {
        if (!str || typeof str !== 'string') return str;
        // Get currency from the currencySymbol variable (already uppercase)
        // Replace any lowercase currency codes with uppercase
        var currencyLower = currencySymbol.toLowerCase();
        if (currencyLower !== currencySymbol && str.indexOf(currencyLower) !== -1) {
            str = str.replace(new RegExp(currencyLower, 'gi'), currencySymbol);
        }
        return str;
    }
    
    // Unified helper functions for setting values (used by both Sales and Purchase Overview)
    function setNum(id, val) {
        var el = document.getElementById(id);
        if (el) {
            var text = val || '0';
            text = ensureUppercaseCurrency(text);
            el.textContent = text;
        }
    }

    function setTaxNum(id, val) {
        var el = document.getElementById(id);
        if (el) {
            var text = val || '0';
            text = ensureUppercaseCurrency(text);
            el.textContent = text;
        }
    }

    // Sales Overview KPI loading - amounts are pre-formatted by server using amountExchange()
    (function() {
        try {
            var url = '<?= base_url('dashboard/widget_kpis') ?>';
            fetch(url, {
                    credentials: 'same-origin'
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(res) {
                    var t = res && res.totals ? res.totals : null;
                    if (!t) return;
                    setNum('kpi-totalSales', t.totalSales);
                    setTaxNum('kpi-totalSalesVat', t.totalSalesVat);
                    setNum('kpi-todaySales', t.todaySales);
                    setTaxNum('kpi-todaySalesVat', t.todaySalesVat);
                    setNum('kpi-monthSales', t.monthSales);
                    setTaxNum('kpi-monthSalesVat', t.monthSalesVat);
                    setNum('kpi-yearSales', t.yearSales);
                    setTaxNum('kpi-yearSalesVat', t.yearSalesVat);
                })
                .catch(function(e) {});
        } catch (e) {}
    })();

    // Purchase Overview KPI loading - same pattern as Sales Overview
    (function() {
        try {
            var url = '<?= base_url('dashboard/widget_purchase_overview') ?>';
            fetch(url, {
                    credentials: 'same-origin'
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(j) {
                    if (!j) return;
                    // Purchase amounts (same pattern as Sales)
                    setNum('po-total', j.total);
                    setTaxNum('po-total-vat', j.totalVat);
                    setNum('po-today', j.today);
                    setTaxNum('po-today-vat', j.todayVat);
                    setNum('po-month', j.month);
                    setTaxNum('po-month-vat', j.monthVat);
                    setNum('po-year', j.year);
                    setTaxNum('po-year-vat', j.yearVat);
                    // Purchase counts
                    setNum('po-count-total', j.countTotal);
                    setNum('po-count-today', j.countToday);
                    setNum('po-count-month', j.countMonth);
                    var elVU = document.getElementById('po-ver-unver');
                    if (elVU) elVU.textContent = ((+j.verCount || 0) + '/' + (+j.unverCount || 0));
                })
                .catch(function(e) {});
        } catch (e) {}
    })();
</script>
<script>
    // Ensure non-sales cards populate even if later scripts fail
    (function() {
        try {
            // Invoice KPI counts
            fetch('<?= base_url('dashboard/ajax_invoice_kpis') ?>', {
                    credentials: 'same-origin'
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(j) {
                    if (!j || j.error) return;
                    var set = function(id, v) {
                        var el = document.getElementById(id);
                        if (el) el.textContent = (+v || 0);
                    };
                    set('invoice-today', j.todayInvoices);
                    set('invoice-tomorrow', j.tomorrowInvoices);
                    set('invoice-month', j.monthInvoices);
                    set('invoice-lastmonth', j.lastMonthInvoices);
                })
                .catch(function() {});


            // Inventory and partners
            fetch('<?= base_url('dashboard/widget_inventory_summary') ?>', {
                    credentials: 'same-origin'
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(j) {
                    if (!j) return;
                    var set = function(id, v) {
                        var el = document.getElementById(id);
                        if (el) el.textContent = (+v || 0);
                    };
                    set('inv-inhand', j.allProductsInHand);
                    set('inv-willrecv', j.productWillBeReceived);
                    set('inv-low', j.stockedOutItems);
                    set('inv-cat', j.productCat);
                    set('inv-all', j.allItems);
                })
                .catch(function() {});
            fetch('<?= base_url('dashboard/widget_business_partners') ?>', {
                    credentials: 'same-origin'
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(j) {
                    if (!j) return;
                    var set = function(id, v) {
                        var el = document.getElementById(id);
                        if (el) el.textContent = (+v || 0);
                    };
                    set('bp-cust', j.totalCust);
                    set('bp-supp', j.totalSupp);
                    set('bp-user', j.totalUser);
                    var el = document.getElementById('bp-balance');
                    if (el) el.textContent = j.ttlBalance || '0';
                })
                .catch(function() {});

            // Goals summary (Income, Expense, Sales, Net) force-fetch
            fetch('<?= base_url('dashboard/ajax_goals_data') ?>', {
                    credentials: 'same-origin'
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(j) {
                    if (!j) return;

                    function pct(n, d) {
                        d = +d || 0;
                        n = +n || 0;
                        if (d <= 0) return 0;
                        var p = Math.round((n * 100) / d);
                        if (p > 100) p = 100;
                        if (p < 0) p = 0;
                        return p;
                    }

                    var inc = +(j.tt_inc_raw || 0);
                    var exp = +(j.tt_exp_raw || 0);
                    var sales = +(j.tt_sales_raw || 0);
                    var net = +(j.tt_net_raw || 0);
                    var g = (j.goals_raw || {
                        income: 999999000,
                        expense: 999999000,
                        sales: 999999000,
                        netincome: 999999000
                    });

                    var gp = document.getElementById('goal-income-pct');
                    if (gp) gp.textContent = pct(inc, g.income) + '%';
                    var ga = document.getElementById('goal-income-amt');
                    if (ga) ga.textContent = (j.tt_inc || '0') + '/' + (j.goals.income || '0');
                    var gb = document.getElementById('goal-income-bar');
                    if (gb) gb.style.width = pct(inc, g.income) + '%';

                    gp = document.getElementById('goal-expense-pct');
                    if (gp) gp.textContent = pct(exp, g.expense) + '%';
                    ga = document.getElementById('goal-expense-amt');
                    if (ga) ga.textContent = (j.tt_exp || '0') + '/' + (j.goals.expense || '0');
                    gb = document.getElementById('goal-expense-bar');
                    if (gb) gb.style.width = pct(exp, g.expense) + '%';

                    gp = document.getElementById('goal-sales-pct');
                    if (gp) gp.textContent = pct(sales, g.sales) + '%';
                    ga = document.getElementById('goal-sales-amt');
                    if (ga) ga.textContent = (j.tt_sales || '0') + '/' + (j.goals.sales || '0');
                    gb = document.getElementById('goal-sales-bar');
                    if (gb) gb.style.width = pct(sales, g.sales) + '%';

                    gp = document.getElementById('goal-net-pct');
                    if (gp) gp.textContent = pct(net, g.netincome) + '%';
                    ga = document.getElementById('goal-net-amt');
                    if (ga) ga.textContent = (j.tt_net || '0') + '/' + (j.goals.netincome || '0');
                    gb = document.getElementById('goal-net-bar');
                    if (gb) gb.style.width = pct(net, g.netincome) + '%';
                })
                .catch(function() {});

            // Recent transactions table
            fetch('<?= base_url('dashboard/widget_recent_payments') ?>', {
                    credentials: 'same-origin'
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(j) {
                    if (!j || !Array.isArray(j.rows)) return;
                    var tbody = document.querySelector('#recent-transactions-body');
                    if (!tbody) return;
                    tbody.innerHTML = '';
                    for (var i = 0; i < j.rows.length; i++) {
                        var row = j.rows[i];
                        var tr = document.createElement('tr');
                        tr.innerHTML = '<td>' + (row.date || '') + '</td>' +
                            '<td>' + (row.account || '') + '</td>' +
                            '<td>' + (row.debit || 0) + '</td>' +
                            '<td>' + (row.credit || 0) + '</td>' +
                            '<td>' + (row.method || '') + '</td>';
                        tbody.appendChild(tr);
                    }
                })
                .catch(function() {});
        } catch (e) {}
    })();
</script>
<script type="text/javascript">
    $(window).on("load", function() {
        $('#recent-buyers').perfectScrollbar({
            wheelPropagation: true
        });
        // Chart is now rendered exclusively from AJAX (widget_income_chart)
    });
</script>
<script type="text/javascript">
    <?php
    // Get currency symbol from database for JavaScript (already uppercase from helper)
    // siteconfig helper is auto-loaded, so currency() function is available
    $user_loc = isset($this->aauth) && $this->aauth->get_user() ? ($this->aauth->get_user()->loc ?? 0) : 0;
    $currency_symbol_js = currency($user_loc); // This already returns uppercase
    ?>
    // Currency symbol from General Settings (uppercase) - use the one defined earlier if available
    if (typeof currencySymbol === 'undefined') {
        var currencySymbol = '<?= addslashes($currency_symbol_js) ?>';
    }
    
    function drawIncomeChart(dataVisits) {
        if (!document.getElementById('dashboard-income-chart')) return; // guard when placeholder not present
        $('#dashboard-income-chart').empty();
        Morris.Area({
            element: 'dashboard-income-chart',
            data: dataVisits,
            xkey: 'x',
            ykeys: ['y'],
            ymin: 'auto 40',
            labels: ['<?php echo $this->lang->line('Amount') ?>'],
            xLabels: "day",
            hideHover: 'auto',
            yLabelFormat: function(y) {
                // Format with PKR and 3 decimal places
                var num = parseFloat(y) || 0;
                var nf = new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 3,
                    maximumFractionDigits: 3
                });
                return currencySymbol + ' ' + nf.format(num);
            },
            resize: true,
            lineColors: [
                '#00A5A8',
            ],
            pointFillColors: [
                '#00A5A8',
            ],
            fillOpacity: 0.4,
        });
    }

    function drawExpenseChart(dataVisits2) {
        if (!document.getElementById('dashboard-expense-chart')) return; // guard when placeholder not present
        $('#dashboard-expense-chart').empty();
        Morris.Area({
            element: 'dashboard-expense-chart',
            data: dataVisits2,
            xkey: 'x',
            ykeys: ['y'],
            ymin: 'auto 0',
            labels: ['<?php echo $this->lang->line('Amount') ?>'],
            xLabels: "day",
            hideHover: 'auto',
            yLabelFormat: function(y) {
                // Only integers
                if (y === parseInt(y, 10)) {
                    return y;
                } else {
                    return '';
                }
            },
            resize: true,
            lineColors: [
                '#ff6e40',
            ],
            pointFillColors: [
                '#34cea7',
            ]
        });
    }

    function drawProductsSalesChart(rows) {
        var el = document.getElementById('products-sales');
        if (!el) return;
        $('#products-sales').empty();
        
        // Check if we have data
        if (!rows || rows.length === 0) {
            $(el).html('<div style="text-align: center; padding: 50px; color: #999;">No data available for the last 30 days</div>');
            return;
        }
        
        // Convert date strings to timestamps for Morris.js
        // Morris.js works better with timestamps or properly formatted date strings
        var formattedRows = rows.map(function(row) {
            // Parse date and convert to timestamp (milliseconds since epoch)
            var dateObj = new Date(row.y + 'T00:00:00');
            var timestamp = dateObj.getTime();
            
            return {
                y: timestamp, // Use timestamp instead of string
                sales: parseFloat(row.sales) || 0,
                invoices: parseInt(row.invoices, 10) || 0
            };
        });
        
        console.log('Formatted rows for Morris.js:', formattedRows);
        
        // Ensure element has dimensions before rendering
        var checkAndRender = function() {
            var $el = $(el);
            var width = $el.width();
            var height = $el.height();
            
            if (width === 0 || height === 0) {
                console.warn('Chart element has no dimensions, retrying...', {width: width, height: height});
                setTimeout(checkAndRender, 200);
                return;
            }
            
            try {
                // For single data point, we need to handle it differently
                // Morris.js needs at least 2 points to draw lines, so we'll show area instead
                var chartOptions = {
                    element: 'products-sales',
                    data: formattedRows,
                    xkey: 'y',
                    ykeys: ['sales', 'invoices'],
                    labels: ['Sales', 'Invoices'],
                    behaveLikeLine: false, // Changed to false to show area chart
                    resize: true,
                    pointSize: 6, // Increased for better visibility
                    smooth: false, // Disabled smooth for single point
                    gridLineColor: '#E4E7ED',
                    numLines: 6,
                    gridtextSize: 14,
                    lineWidth: 3, // Increased line width
                    fillOpacity: 0.7, // Slightly reduced for better visibility
                    hideHover: 'auto',
                    lineColors: ['#00B5B8', '#F25E75'],
                    pointFillColors: ['#00B5B8', '#F25E75'],
                    pointStrokeColors: ['#00B5B8', '#F25E75'],
                    xLabels: 'day',
                    xLabelFormat: function(x) {
                        // x is a timestamp, convert to Date
                        var date = new Date(x);
                        if (!isNaN(date.getTime())) {
                            var day = date.getDate();
                            var month = date.getMonth() + 1;
                            return day + '/' + month;
                        }
                        return x;
                    },
                    yLabelFormat: function(y) {
                        // Format with currency symbol and 3 decimal places
                        var num = parseFloat(y) || 0;
                        var nf = new Intl.NumberFormat(undefined, {
                            minimumFractionDigits: 3,
                            maximumFractionDigits: 3
                        });
                        return currencySymbol + ' ' + nf.format(num);
                    }
                };
                
                // If only one data point, add a duplicate point slightly offset to create a visible area
                if (formattedRows.length === 1) {
                    var singlePoint = formattedRows[0];
                    // Add a second point 1 day later with same values to create a visible area
                    var nextDay = new Date(singlePoint.y);
                    nextDay.setDate(nextDay.getDate() + 1);
                    formattedRows.push({
                        y: nextDay.getTime(),
                        sales: singlePoint.sales,
                        invoices: singlePoint.invoices
                    });
                    console.log('Added duplicate point for single data point visualization');
                }
                
                chartOptions.data = formattedRows;
                var chart = Morris.Area(chartOptions);
                
                console.log('Morris.js chart initialized successfully', chart);
                
                // Force redraw after a short delay to ensure rendering
                setTimeout(function() {
                    if (chart && typeof chart.redraw === 'function') {
                        chart.redraw();
                        console.log('Chart redrawn');
                    }
                    // Trigger window resize to force Morris.js to recalculate
                    window.dispatchEvent(new Event('resize'));
                }, 300);
                
            } catch (error) {
                console.error('Error initializing Morris.js chart:', error);
                $(el).html('<div style="text-align: center; padding: 50px; color: #d32f2f;">Error rendering chart: ' + error.message + '</div>');
            }
        };
        
        // Start rendering after a short delay
        setTimeout(checkAndRender, 100);
    }
    // Lazy-load income/expense chart data when chart enters viewport
    (function() {
        var url = '<?= base_url('dashboard/widget_income_chart') ?>';
        console.log('Chart loader initialized. URL:', url);

        function loadChart() {
            console.log('Loading chart data from:', url);
            fetch(url, {
                    credentials: 'same-origin',
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(r) {
                    console.log('Response status:', r.status, r.statusText);
                    console.log('Response headers:', r.headers.get('content-type'));
                    
                    // Check for redirects (302, 301) which might indicate auth issues
                    if (r.redirected) {
                        console.warn('Request was redirected. This might indicate an authentication issue.');
                    }
                    
                    // Check if response is HTML (likely a redirect to login or error page)
                    var contentType = r.headers.get('content-type') || '';
                    if (contentType.indexOf('text/html') !== -1) {
                        return r.text().then(function(text) {
                            console.error('Received HTML instead of JSON. This might be a redirect or error page.');
                            console.error('HTML preview:', text.substring(0, 500));
                            throw new Error('Server returned HTML instead of JSON. Status: ' + r.status + '. This might indicate an authentication or routing issue.');
                        });
                    }
                    
                    // First, get the response as text to see what we're dealing with
                    return r.text().then(function(text) {
                        console.log('Response text length:', text.length);
                        console.log('Response preview:', text.substring(0, 200));
                        
                        // Check if response is empty
                        if (!text || text.trim().length === 0) {
                            throw new Error('Empty response from server. Status: ' + r.status);
                        }
                        
                        // Try to parse as JSON
                        try {
                            var json = JSON.parse(text);
                            // Check if there's an error in the response
                            if (json.error) {
                                console.error('API returned error:', json.error);
                                throw new Error('API error: ' + json.error);
                            }
                            return json;
                        } catch (e) {
                            // If it's not JSON, log the actual response
                            console.error('JSON parse error:', e.message);
                            console.error('Non-JSON response received:', text.substring(0, 500));
                            throw new Error('Invalid JSON response. Status: ' + r.status + '. Error: ' + e.message);
                        }
                    });
                })
                .then(function(json) {
                    if (!json) {
                        console.warn('No data returned from widget_income_chart');
                        drawProductsSalesChart([]);
                        return;
                    }
                    
                    // Debug: log the response
                    console.log('Chart data received:', json);
                    
                    var salesSeries = [],
                        invoiceSeries = [];
                    if (Array.isArray(json.income)) {
                        for (var i = 0; i < json.income.length; i++) {
                            salesSeries.push({
                                x: json.income[i].date,
                                y: parseFloat(json.income[i].total) || 0
                            });
                        }
                    }
                    if (Array.isArray(json.expense)) {
                        for (var j = 0; j < json.expense.length; j++) {
                            invoiceSeries.push({
                                x: json.expense[j].date,
                                y: parseInt(json.expense[j].total, 10) || 0
                            });
                        }
                    }
                    var map = {};
                    salesSeries.forEach(function(p) {
                        map[p.x] = {
                            y: p.x,
                            sales: p.y,
                            invoices: 0
                        };
                    });
                    invoiceSeries.forEach(function(p) {
                        if (!map[p.x]) map[p.x] = {
                            y: p.x,
                            sales: 0,
                            invoices: p.y
                        };
                        else map[p.x].invoices = p.y;
                    });
                    var rows = Object.keys(map).sort().map(function(k) {
                        return map[k];
                    });
                    
                    // Ensure all values are numbers (not strings)
                    rows = rows.map(function(row) {
                        return {
                            y: row.y, // Date string
                            sales: parseFloat(row.sales) || 0,
                            invoices: parseInt(row.invoices, 10) || 0
                        };
                    });
                    
                    // Debug: log processed rows
                    console.log('Processed chart rows:', rows);
                    console.log('Number of data points:', rows.length);
                    if (rows.length > 0) {
                        console.log('First row sample:', rows[0]);
                        console.log('Last row sample:', rows[rows.length - 1]);
                    }
                    
                    drawProductsSalesChart(rows);
                })
                .catch(function(error) {
                    console.error('=== CHART LOAD ERROR ===');
                    console.error('Error type:', error.name);
                    console.error('Error message:', error.message);
                    console.error('Error stack:', error.stack);
                    console.error('URL attempted:', url);
                    console.error('Full error object:', error);
                    
                    var el = document.getElementById('products-sales');
                    if (el) {
                        var errorMsg = '<div style="text-align: center; padding: 20px; color: #d32f2f;">';
                        errorMsg += '<strong>Error loading chart data</strong><br>';
                        if (error.message) {
                            errorMsg += '<small>' + error.message + '</small><br>';
                        }
                        errorMsg += '<small style="color: #666;">Check browser console (F12) for details</small><br>';
                        errorMsg += '<small style="color: #666;">URL: ' + url + '</small>';
                        errorMsg += '</div>';
                        $(el).html(errorMsg);
                    }
                });
        }
        var el = document.getElementById('products-sales');
        if (!el) {
            return;
        }
        
        // Load immediately if IntersectionObserver is not supported, or if element is already visible
        if (!('IntersectionObserver' in window)) {
            loadChart();
            return;
        }
        
        // Check if element is already visible
        var rect = el.getBoundingClientRect();
        var isVisible = rect.top < window.innerHeight && rect.bottom > 0;
        
        if (isVisible) {
            // Element is already visible, load immediately
            loadChart();
            return;
        }
        
        // Use IntersectionObserver for lazy loading
        var loaded = false,
            io = new IntersectionObserver(function(es) {
                es.forEach(function(en) {
                    if (en.isIntersecting && !loaded) {
                        loaded = true;
                        io.disconnect();
                        loadChart();
                    }
                });
            }, {
                threshold: 0.1
            });
        io.observe(el);
    })();
    if (document.getElementById('dashboard-sales-breakdown-chart')) {
        $('#dashboard-sales-breakdown-chart').empty();
        Morris.Donut({
            element: 'dashboard-sales-breakdown-chart',
            data: [{
                    label: "<?php echo $this->lang->line('Income') ?>",
                    value: <?= intval(amountExchange_s($tt_inc, 0, $this->aauth->get_user()->loc)); ?>
                },
                {
                    label: "<?php echo $this->lang->line('Expenses') ?>",
                    value: <?= intval(amountExchange_s($tt_exp, 0, $this->aauth->get_user()->loc)); ?>
                }
            ],
            resize: true,
            colors: ['#34cea7', '#ff6e40'],
            gridTextSize: 6,
            gridTextWeight: 400
        });
    }
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        window.dispatchEvent(new Event('resize'));
    });



    // Received summary tiles via AJAX - same pattern as Sales/Purchase Overview
    (function() {
        var url = '<?= base_url('dashboard/widget_received_summary') ?>';
        fetch(url, {
                credentials: 'same-origin'
            })
            .then(function(r) {
                return r.json();
            })
            .then(function(j) {
                if (!j) return;

                function setText(id, value) {
                    var el = document.getElementById(id);
                    if (el) {
                        var text = value || '0';
                        text = ensureUppercaseCurrency(text);
                        el.textContent = text;
                    }
                }

                // Use pre-calculated totals if available (new format)
                if (typeof j.yesterday_total !== 'undefined') {
                    setText('rec-yesterday-total', j.yesterday_total || 0);
                    setText('rec-yesterday-cash', j.yesterday_cash || 0);
                    setText('rec-yesterday-card', j.yesterday_card || 0);
                    setText('rec-yesterday-bank', j.yesterday_bank || 0);
                } else if (Array.isArray(j.yesterday)) {
                    // Fallback: calculate from array (backward compatibility)
                    var yc = 0,
                        ycard = 0,
                        ybank = 0,
                        ytotal = 0;
                    for (var i = 0; i < j.yesterday.length; i++) {
                        var row = j.yesterday[i];
                        ytotal += (+row.total || 0);
                        var method = (row.paymt_method || '').toLowerCase();
                        if (method === 'cash') yc = +row.total || 0;
                        else if (method === 'card payment') ycard = +row.total || 0;
                        else if (method === 'bank transfer') ybank = +row.total || 0;
                    }
                    setText('rec-yesterday-total', ytotal);
                    setText('rec-yesterday-cash', yc);
                    setText('rec-yesterday-card', ycard);
                    setText('rec-yesterday-bank', ybank);
                }

                // Use pre-calculated totals if available (new format)
                if (typeof j.month_total !== 'undefined') {
                    setText('rec-month-total', j.month_total || 0);
                    setText('rec-month-cash', j.month_cash || 0);
                    setText('rec-month-card', j.month_card || 0);
                    setText('rec-month-bank', j.month_bank || 0);
                } else if (Array.isArray(j.month)) {
                    // Fallback: calculate from array (backward compatibility)
                    var mc = 0,
                        mcard = 0,
                        mbank = 0,
                        mtotal = 0;
                    for (var k = 0; k < j.month.length; k++) {
                        var r = j.month[k];
                        mtotal += (+r.total || 0);
                        var m = (r.paymt_method || '').toLowerCase();
                        if (m === 'cash') mc = +r.total || 0;
                        else if (m === 'card payment') mcard = +r.total || 0;
                        else if (m === 'bank transfer') mbank = +r.total || 0;
                    }
                    setText('rec-month-total', mtotal);
                    setText('rec-month-cash', mc);
                    setText('rec-month-card', mcard);
                    setText('rec-month-bank', mbank);
                }
            })
            .catch(function(err) {
                try {
                    console.error('Dashboard: received summary load failed', err);
                } catch (e) {}
            });
    })();

    // Inventory and partners
    (function() {
        var urlInv = '<?= base_url('dashboard/widget_inventory_summary') ?>';

        function updateInventory(j) {
            if (!j) return false;
            var nf = new Intl.NumberFormat();
            var ids = ['inv-inhand', 'inv-willrecv', 'inv-low', 'inv-cat', 'inv-all'];
            var vals = [j.allProductsInHand, j.productWillBeReceived, j.stockedOutItems, j.productCat, j.allItems];
            var ok = true;
            for (var i = 0; i < ids.length; i++) {
                var el = document.getElementById(ids[i]);
                if (!el) {
                    ok = false;
                    continue;
                }
                el.textContent = nf.format(+vals[i] || 0);
            }
            return ok;
        }
        fetch(urlInv, {
                credentials: 'same-origin'
            })
            .then(function(r) {
                return r.json();
            })
            .then(function(j) {
                if (!j) return;
                // Try immediately, then retry for up to 3 seconds until elements exist
                if (!updateInventory(j)) {
                    var left = 20; // 20 * 150ms ≈ 3s
                    var t = setInterval(function() {
                        if (updateInventory(j) || --left <= 0) {
                            clearInterval(t);
                        }
                    }, 150);
                }
            }).catch(function(e) {
                try {
                    console.error('Inventory update failed', e);
                } catch (_) {}
            });
        fetch('<?= base_url('dashboard/widget_business_partners') ?>', {
                credentials: 'same-origin'
            })
            .then(function(r) {
                return r.json();
            })
            .then(function(j) {
                function set(id, v) {
                    var el = document.getElementById(id);
                    if (el) el.textContent = (+v || 0);
                }
                if (!j) return;
                set('bp-cust', j.totalCust);
                set('bp-supp', j.totalSupp);
                set('bp-user', j.totalUser);
                var el = document.getElementById('bp-balance');
                if (el) el.textContent = j.ttlBalance || '0';
            }).catch(function() {});
    })();
    // Make dashboard media lighter: lazy-load all images
    (function() {
        try {
            var imgs = document.querySelectorAll('img');
            for (var i = 0; i < imgs.length; i++) {
                if (!imgs[i].hasAttribute('loading')) imgs[i].setAttribute('loading', 'lazy');
                if (!imgs[i].hasAttribute('decoding')) imgs[i].setAttribute('decoding', 'async');
            }
        } catch (e) {}
    })();

    // Populate Today Income/Expenses/Profit/Revenue cards (robust triggers)
    function loadTodayCards() {
        var url = '<?= base_url('dashboard/widget_today_cards') ?>';
        try {
            console.log('Dashboard: request today cards ->', url);
        } catch (e) {}
        fetch(url, {
                credentials: 'same-origin'
            })
            .then(function(r) {
                return r.json();
            })
            .then(function(j) {
                try {
                    console.log('Dashboard: today cards response', j);
                } catch (e) {}
                if (!j) return;

                function setAmt(id, v) {
                    var el = document.getElementById(id);
                    if (el) {
                        var text = v || '0';
                        text = ensureUppercaseCurrency(text);
                        el.textContent = text;
                    }
                }
                setAmt('card-today-income', j.todayIncome);
                setAmt('card-today-expenses', j.todayExpenses);
                setAmt('card-today-profit', j.todayProfit);
                setAmt('card-today-revenue', j.todayRevenue);
            })
            .catch(function(err) {
                try {
                    console.error('Dashboard: today cards load failed', err);
                } catch (e) {}
            });
    }
    var _todayCardsLoaded = false;

    function runTodayCards() {
        if (_todayCardsLoaded) return;
        _todayCardsLoaded = true;
        loadTodayCards();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runTodayCards, {
            once: true
        });
    } else {
        runTodayCards();
    }

    // Populate recent customers list (lazy when visible)
    (function() {
        var url = '<?= base_url('dashboard/widget_recent_buyers') ?>';
        var wrap = document.getElementById('recent-customers-body');

        function load() {
            fetch(url, {
                    credentials: 'same-origin'
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(j) {
                    if (!j || !Array.isArray(j.rows)) return;
                    if (!wrap) return;
                    wrap.innerHTML = '';
                    for (var i = 0; i < j.rows.length; i++) {
                        var it = j.rows[i] || {};
                        var name = (it.name || '');
                        var status = (it.status || '').toString().toLowerCase();
                        var total = +it.total || 0;
                        var vat = +it.tax || 0;
                        var a = document.createElement('a');
                        a.href = '<?= base_url('customers/view?id=') ?>' + (it.csd || '');
                        a.className = 'customer-item';
                        a.innerHTML =
                            '<div class="customer-info">' +
                            '<div class="customer-name">' + name + '</div>' +
                            '<span class="customer-status st-' + status + '">' + (status ? status.charAt(0).toUpperCase() + status.slice(1) : '') + '</span>' +
                            '</div>' +
                            '<div class="customer-amounts">' +
                            '<div class="customer-total">' + (it.formatted_total || total) + '</div>' +
                            '<div class="customer-vat">' + (it.formatted_tax || vat) + ' (<?php echo $this->lang->line('Tax') ?>)</div>' +
                            '</div>';
                        wrap.appendChild(a);
                    }
                })
                .catch(function() {});
        }
        if (!wrap) {
            return;
        }
        if (!('IntersectionObserver' in window)) {
            load();
            return;
        }
        var loaded = false,
            io = new IntersectionObserver(function(es) {
                es.forEach(function(en) {
                    if (en.isIntersecting && !loaded) {
                        loaded = true;
                        io.disconnect();
                        load();
                    }
                });
            }, {
                threshold: 0.1
            });
        io.observe(wrap);
    })();

    // Populate recent invoices via AJAX (lazy when visible)
    (function() {
        var url = '<?= base_url('dashboard/widget_recent_invoices') ?>';
        var tbody = document.getElementById('recent-invoices-body');

        function load() {
            fetch(url, {
                    credentials: 'same-origin'
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(j) {
                    if (!j || !Array.isArray(j.recent)) return;
                    if (!tbody) return;
                    tbody.innerHTML = '';
                    for (var i = 0; i < j.recent.length; i++) {
                        var it = j.recent[i];
                        var page = (it.i_class == 0 ? 'invoices' : (it.i_class == 1 ? 'pos_invoices' : 'subscriptions'));
                        var tr = document.createElement('tr');
                        tr.innerHTML = '<td class="text-truncate"><a href="<?= base_url() ?>' + page + '/view?id=' + it.id + '">#' + it.tid + '</a></td>' +
                            '<td class="text-truncate">' + (it.name || '') + '</td>' +
                            '<td class="text-truncate"><span class="badge st-' + (it.status || '') + '">' + (it.status || '') + '</span></td>' +
                            '<td class="text-truncate">' + (it.invoicedate || '') + '</td>' +
                            '<td class="text-truncate">' + (it.formatted_total || it.total || '0') + '</td>' +
                            '<td class="text-truncate">' + (it.formatted_tax || it.tax || '0') + '</td>';
                        tbody.appendChild(tr);
                    }
                })
                .catch(function() {});
        }
        if (!tbody) {
            return;
        }
        if (!('IntersectionObserver' in window)) {
            load();
            return;
        }
        var loaded = false,
            io = new IntersectionObserver(function(es) {
                es.forEach(function(en) {
                    if (en.isIntersecting && !loaded) {
                        loaded = true;
                        io.disconnect();
                        load();
                    }
                });
            }, {
                threshold: 0.1
            });
        io.observe(tbody);
    })();
</script>

<!-- Include FullCalendar CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show loading animation
        var calendarEl = document.getElementById('calendar');
        calendarEl.innerHTML = '<div class="calendar-loading"></div>';

        // Initialize calendar after a short delay for smooth loading
        setTimeout(function() {
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 600,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listWeek'
                },
                buttonText: {
                    today: 'Today',
                    month: 'Month',
                    week: 'Week',
                    list: 'List'
                },
                firstDay: 1, // Start week on Monday
                showNonCurrentDates: true,
                fixedWeekCount: false,
                events: [
                    <?php if (!empty($due_invoices)) : ?>
                        <?php foreach ($due_invoices as $invoice) : ?> {
                                title: '<?= $invoice['invoice_count'] ?> Invoice<?= $invoice['invoice_count'] > 1 ? 's' : '' ?>',
                                start: '<?= $invoice['date'] ?>',
                                allDay: true,
                                description: 'Balance: <?= amountExchange($invoice['total_due'], 0, $this->aauth->get_user()->loc) ?>',
                                color: '#25468d',
                                backgroundColor: '#25468d',
                                borderColor: '#25468d',
                                textColor: '#ffffff',
                                extendedProps: {
                                    invoiceId: '<?= $invoice['invoice_count'] ?>',
                                    totalDue: '<?= amountExchange($invoice['total_due'], 0, $this->aauth->get_user()->loc) ?>',
                                    dueDate: '<?= $invoice['date'] ?>'
                                }
                            },
                        <?php endforeach; ?>
                    <?php endif; ?>
                ],
                eventContent: function(arg) {
                    var event = arg.event;
                    var title = event.title;
                    var description = event.extendedProps.totalDue;

                    return {
                        html: `
                            <div class="fc-event-custom">
                                <div class="event-title">
                                    <i class="fa fa-file-invoice"></i>
                                    <strong>${title}</strong>
                                </div>
                                <div class="event-description">
                                    ${description}
                                </div>
                            </div>
                        `
                    };
                },
                eventClick: function(info) {
                    var dueDate = info.event.extendedProps.dueDate;
                    if (dueDate) {
                        window.location.href = '<?= site_url("invoices?due_date=") ?>' + dueDate;
                    }
                },
                datesSet: function() {
                    // Add smooth animation to calendar cells
                    var dateCells = document.querySelectorAll('.fc-daygrid-day');
                    dateCells.forEach(function(cell, index) {
                        cell.style.opacity = '0';
                        cell.style.transform = 'translateY(20px)';

                        setTimeout(function() {
                            cell.style.transition = 'all 0.3s ease';
                            cell.style.opacity = '1';
                            cell.style.transform = 'translateY(0)';
                        }, index * 10);
                    });

                    // Highlight due dates
                    <?php if (!empty($due_invoices)) : ?>
                        <?php foreach ($due_invoices as $invoice) : ?>
                            var dueDate = '<?= $invoice['date'] ?>';
                            dateCells.forEach(function(cell) {
                                var cellDate = cell.getAttribute('data-date');
                                if (cellDate === dueDate) {
                                    cell.classList.add('due-date-highlight');

                                    // Add click listener with smooth transition
                                    cell.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        cell.style.transform = 'scale(0.95)';
                                        setTimeout(function() {
                                            cell.style.transform = 'scale(1)';
                                            window.location.href = '<?= site_url("invoices?due_date={$invoice['date']}") ?>';
                                        }, 150);
                                    });

                                    // Add hover effect
                                    cell.addEventListener('mouseenter', function() {
                                        this.style.transform = 'scale(1.05)';
                                    });

                                    cell.addEventListener('mouseleave', function() {
                                        this.style.transform = 'scale(1)';
                                    });
                                }
                            });
                        <?php endforeach; ?>
                    <?php endif; ?>
                },
                dayCellContent: function(arg) {
                    // Add custom styling to day numbers
                    return {
                        html: '<div class="fc-daygrid-day-number">' + arg.dayNumberText + '</div>'
                    };
                },
                eventDidMount: function(info) {
                    // Add entrance animation to events
                    info.el.style.opacity = '0';
                    info.el.style.transform = 'translateY(-20px)';

                    setTimeout(function() {
                        info.el.style.transition = 'all 0.4s ease';
                        info.el.style.opacity = '1';
                        info.el.style.transform = 'translateY(0)';
                    }, 200);
                }
            });

            calendar.render();

            // Add custom navigation controls
            document.getElementById('calendar-today').addEventListener('click', function() {
                calendar.today();
                this.style.transform = 'scale(0.95)';
                setTimeout(() => this.style.transform = 'scale(1)', 150);
            });

            document.getElementById('calendar-prev').addEventListener('click', function() {
                calendar.prev();
                this.style.transform = 'scale(0.95)';
                setTimeout(() => this.style.transform = 'scale(1)', 150);
            });

            document.getElementById('calendar-next').addEventListener('click', function() {
                calendar.next();
                this.style.transform = 'scale(0.95)';
                setTimeout(() => this.style.transform = 'scale(1)', 150);
            });

            // Add smooth transitions for view changes
            calendar.on('datesSet', function() {
                var cells = document.querySelectorAll('.fc-daygrid-day');
                cells.forEach(function(cell, index) {
                    cell.style.transition = 'all 0.3s ease';
                    cell.style.transform = 'translateY(0)';
                });
            });

        }, 300); // 300ms delay for smooth loading
    });
</script>