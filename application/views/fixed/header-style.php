<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

    body,
    .navigation,
    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    .h1,
    .h2,
    .h3,
    .h4,
    .h5,
    .h6 {
        font-family: 'Poppins', sans-serif;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    .h1,
    .h2,
    .h3,
    .h4,
    .h5,
    .h6 {
        font-weight: 600;
    }

    .card-header .heading-elements a[data-action="close"] {
        background: #f57777;
        color: #fff;
    }

    .card-header .card-title {
        color: #000;
        font-size: 15px;
        font-weight: 600;
    }

    .card-header .card-title .btn {
        margin: 0 8px;
    }

    /* Redesign UI */
    .card {
        border-radius: 4px;
        overflow: hidden;
    }

    .content-body>.card>.card-header {
        background-color: #e8f0ffe3;
        padding-bottom: 21px !important;
        position: relative;
    }

    .content-body>.card>.card-body>form>h5+hr {
        display: none;
    }

    body.menu-expanded .main-menu {
        width: 240px;
        z-index: 99999;
    }

    body .main-menu .navigation>li>a,
    .main-menu.menu-dark .navigation>li ul li a {
        color: #fff !important;
    }

    .main-menu.menu-dark .navigation>li>ul {
        background: rgba(0, 0, 0, 0.18);
    }

    .main-menu.menu-dark .navigation>li.open>a {
        background-color: rgba(0, 0, 0, 0.1);
    }

    .main-menu .main-menu-content,
    .main-menu.menu-dark .navigation,
    .main-menu.menu-dark {
        background: #25468d;
    }

    .navbar-semi-dark .navbar-header {
        background-color: #25468d;
    }

    .main-menu.menu-dark .navigation>li:hover>a,
    .main-menu.menu-dark .navigation>li.open .hover>a,
    .main-menu.menu-dark .navigation>li ul li.hover a,
    .main-menu.menu-dark .navigation>li ul .open .hover>a {
        background-color: rgba(0, 0, 0, 0.1);
    }

    tr.sub_c[style="display: table-row;"]>td {
        padding: 4px 8px;
    }

    .cmp-pnl {
        margin-top: 4px !important;
    }

    .main-menu.menu-dark .navigation>li:last-child {
        position: fixed;
        bottom: 0;
        width: 100%;
        border-left: 0;
        background-color: #25468d;
        border-top: 2px solid #F36E38;
    }

    
    <?php if ($this->aauth->clock()) { ?>
    .main-menu.menu-dark .navigation>li:last-child {
        border-color: #61e3a7;
    }
    <?php } ?>

    .main-menu.menu-dark .navigation>li.open {
        border-left: 4px solid #7696d7;
    }

    .main-menu.menu-dark .navigation {
        padding-bottom: 48px;
    }

    .content-body>.card>.card-body>form>h5 {
        background: #e8f0ffe3;
        padding: 21px;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
    }

    .content-body>.card>.card-body>form>.form-group:nth-child(3) {
        margin-top: 56px;
    }

    .header-navbar .navbar-container {
        padding: 0.4rem 18px;
    }

    .content-body>.card>.card-header>h5 {
        margin-bottom: 0;
        color: #3c4043;
    }

    .dashboard-row {
        padding: 0 14px;
    }

    .dashboard-row .card {
        transition: all 300ms;
    }

    .dashboard-row .card:hover {
        transform: scale(1.05);
        box-shadow: 0 0 16px 2px rgba(0, 0, 0, 0.15);
        z-index: 5;
    }

    .content-body>.card {
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        z-index: 9;
    }

    html body:before,
    .app-content.content:before {
        content: "";
        background: #f9ffec00;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
    }

    .content-body>.card>.card-header>hr {
        display: none;
    }

    html body {
        background: url(<?= assets_url('userfiles/company/bg.jpg') ?>);
        background-repeat: no-repeat;
        background-size: cover;
        background-attachment: fixed;
        background-position: center center;
    }

    .table .st-paid,
    .table .st-due,
    .table .st-partial,
    .table .st-canceled,
    .table .st-rejected,
    .table .st-pending,
    .table .st-accepted .st-Recurring,
    .table .st-Stopped {
        width: 100%;
    }

    .media.align-items-stretch>.bg-darken-2 {
        display: none;
    }

    .media.align-items-stretch>.media-body {
        padding: 25px 16px !important;
    }

    .card .card-header {
        position: relative;
    }

    .menu-toggle {
        color: #fff !important;
        font-size: 24px;
        line-height: 42px;
        position: absolute;
        left: 10px;
        top: 2px;
    }

    .card-header .heading-elements a,
    .card-header .heading-elements-toggle a {
        padding: 2px 8px;
        min-height: 30px;
        min-width: 30px;
        display: inline-block;
        border-radius: 20px;
        background-color: #c8e3e3;
        transition: all 300ms;
    }

    .card-header .heading-elements a[data-action="close"]:hover {
        background: #f57777;
        color: white;
    }

    .navbar-semi-dark {
        background: transparent;
        box-shadow: unset !important;
    }

    .header-navbar .navbar-container ul.nav li a.dropdown-user-link {
        display: none;
    }

    body.menu-collapsed .app-content.content {
        width: calc(100% - 64px);
    }

    .app-content.content {
        position: absolute;
        top: 0;
        z-index: 9999;
        width: calc(100% - 240px);
        right: 0;
    }

    body.menu-collapsed .app-content.content {
        width: calc(100% - 64px);
    }

    .card form select:hover {
        cursor: pointer;
    }

    .card input:not([type="submit"]),
    .card form select {
        border: 1px solid #ccd6e6 !important;
        color: #3f587e !important;
        padding: 8px 14px !important;
        background-color: #f7f7f766 !important;
    }

    .card form textarea {
        min-height: 72px;
    }

    /* .input-group .input-group-addon {
    padding: 8px;
    min-width: 42px;
    text-align: center;
    border: 1px solid #eceff1;
    font-weight: 600;
} */

    tr.item_header {
        height: 42px;
        display: table-row;
    }

    .card .form-control:disabled,
    .card .form-control[readonly] {
        background: #efefef !important;
    }

    .buttons-html5,
    html button,
    html [type='button'],
    [type='reset'],
    [type='submit'],
    .btn-primary,
    .btn {
        padding: 12px 14px;
        font-size: 13px;
        border: none;
        line-height: 13px;
        border-radius: 4px;
    }

    table.table-bordered.dataTable td:last-child {
        white-space: nowrap;
    }

    .btn-group-sm>.btn,
    .btn-sm {
        padding: 8px;
    }

    .btn.btn-secondary.buttons-html5:hover {
        background-color: #25468d;
    }

    .card-header .heading-elements,
    .card-header .heading-elements-toggle {
        top: 50%;
        transform: translateY(-50%);
    }

    .card-header .heading-elements a[data-action="collapse"]:hover,
    .card-header .heading-elements a[data-action="expand"]:hover {
        background: #a4c3c3;
    }

    .card-header .card-title {
        text-transform: capitalize;
    }

    .content-body>.card>.card-header>h5>a {
        margin-left: 8px;
    }

    .dataTables_length {
        margin: 2px 0;
        display: inline-block;
    }

    div.dataTables_wrapper div.dataTables_length select {
        padding: 10px 24px 10px 8px !important;
        height: auto;
    }

    .tfr td {
        padding: 2px;
    }

    body .main-menu .navigation>li>a i,
    .main-menu.menu-dark .navigation>li ul li a i {
        min-width: 20px;
        display: inline-block;
    }

    [data-action="expand"] {
        display: none;
    }

    .card-header .heading-elements a[data-action="expand"] {
        display: none;
    }

    .input-group-addon {
        padding: 0.2rem;
        display: flex;
        align-items: center;
    }

    .select2-container--default .select2-search--inline .select2-search__field {
        width: 100% !important;
        padding: 0 !important;
        border: none !important;
    }

    .main-menu.menu-dark .navigation>li ul li>a {
        padding: 10px 18px 10px 32px;
    }

    h5.fw-bold {
        font-weight: 500;
    }

    .main-menu.menu-dark .navigation>li ul .open>ul li>a {
        padding: 10px 18px 10px 50px;
    }

    body.menu-collapsed .main-menu.menu-dark .navigation>li ul li>a {
        padding: 10px 16px;
    }

    body.menu-collapsed .main-menu.menu-dark .navigation>li ul li>a>i {
        margin-right: 18px;
    }


    .user-button .btn {
        padding: 6px;
        font-size: 11px;
        width: 100%;
    }

    .user-button .col-md-4:nth-child(2) {
        padding: 0;
    }

    .user-button .col-md-4:nth-child(1) {
        padding-right: 4px;
    }

    .user-button .col-md-4:nth-child(3) {
        padding-left: 4px;
    }

    table.table-bordered.dataTable tbody td {
        vertical-align: middle;
    }

    table.table-bordered.dataTable td:last-child>a {
        margin: 0px 2px !important;
    }

    table.dataTable {
        width: 100% !important;
    }

    .dashboard-cards:hover {
        background-size: cover !important;
        background-position: right center;
    }

    .dashboard-cards:hover h3,
    .dashboard-cards:hover h5,
    .dashboard-cards:hover h4,
    .dashboard-cards:hover span,
    .dashboard-cards:hover small,
    .dashboard-cards:hover i {
        color: #fff !important;
        position: relative;
        z-index: 5;
    }

    .dashboard-cards:hover {
        background: url(<?= assets_url('assets/images/summery-bg1.png') ?>) no-repeat right #953fb9;
        box-shadow: 0px 10px 30px #953fb9;
        background-size: 100% 100%;
    }

    .dashboard-cards:hover::before {
        border-radius: 10px;
        background-size: cover;
        width: 90%;
        left: 5%;
    }

    .dashboard-cards:hover:before {
        background: url(<?= assets_url('assets/images/summery-bg2.png') ?>) no-repeat center;
        top: 8px;
    }

    .first-row .dashboard-cards:hover:before {
        top: 32px;
    }

    .dashboard-cards::before {
        min-height: 136px;
        width: 90%;
    }

    .dashboard-cards:before,
    .dashboard-cards:after {
        content: "";
        background: transparent;
        min-height: 100px;
        width: 100%;
        position: absolute;
        left: 0px;
        top: 0px;
        -webkit-transition: all 0.4s ease 0s;
        -moz-transition: all 0.4s ease 0s;
        -o-transition: all 0.4s ease 0s;
        transition: all 0.4s ease 0s;
    }

    .dashboard-cards:hover::after {
        border-radius: 10px;
        background-size: cover;
        width: 80%;
        left: 10%;
    }

    .dashboard-cards:hover:after {
        background: url(<?= assets_url('assets/images/summery-bg3.png') ?>) no-repeat center;
        top: 16px;
    }

    .first-row .dashboard-cards {
        min-height: 116px;
    }

    .dashboard-cards::after {
        min-height: 136px;
        width: 80%;
    }

    .dashboard-cards:before,
    .dashboard-cards:after {
        content: "";
        background: transparent;
        min-height: 100px;
        width: 100%;
        position: absolute;
        left: 0px;
        top: 0px;
        -webkit-transition: all 0.4s ease 0s;
        -moz-transition: all 0.4s ease 0s;
        -o-transition: all 0.4s ease 0s;
        transition: all 0.4s ease 0s;
    }

    .dashboard-cards {
        position: relative;
        overflow: visible;
        border-radius: 0px !important;
    }

    div#c_body {
        position: absolute;
        z-index: 99999;
        max-width: 420px;
        right: 16px;
        top: 16px;
    }


    .ui-autocomplete {
        max-width: 572px;
        overflow-y: auto;
        max-height: 300px;
        z-index: 9999;
    }

    .ui-autocomplete .ui-menu-item-wrapper {
        max-width: 100%;
    }

    html body {
        background: #f4f6f9;
    }


    .ui-autocomplete::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .ui-autocomplete::-webkit-scrollbar-thumb {
        background: #1976D2;
    }

    .ui-autocomplete::-webkit-scrollbar-thumb:hover {
        background: #64B5F6;
    }

    .ui-menu-item .ui-menu-item-wrapper.ui-state-active {
        background: #6693bc !important;
        font-weight: bold !important;
        color: #ffffff !important;
    }

    body>.modal-backdrop.show {
        display: none;
    }

    .modal.show:before {
        content: "";
        position: absolute;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        background-color: rgb(0 0 0 / 50%);
    }

    html body .pace .pace-progress {
        background: orangered;
    }

    body.swal2-toast-shown .swal2-container {
        z-index: 9999;
    }

    .datepicker-container {
        z-index: 9999 !important;
    }

    html body:before,
    .app-content.content:before {
        visibility: hidden;
    }

    body.vertical-layout.vertical-menu-modern.menu-collapsed .main-menu {
        z-index: 999999 !important;
        position: fixed !important;
    }

    .header-navbar:has(.expanded) {
        z-index: 99999;
    }

    body.menu-expanded .header-navbar:has(.expanded) {
        z-index: 99;
    }

    .swal2-container {
        z-index: 99999;
    }

    .select2-container {
        z-index: 99999;
    }

    .modal-open .modal {
        width: calc(100% - 240px);
        right: 0;
        left: unset;
    }

    .navigation>li ul li a i.icon-clock.spinner {
        min-width: auto;
        margin-right: 6px;
    }

    .stats-icon {
        padding: 8px;
        height: 58px;
        width: 58px;
        border-radius: 8px;
        background: #F8FAFF;
        margin-right: 12px;
        box-shadow: 2px 4px 4px rgba(0, 0, 0, 0.1);
    }
</style>