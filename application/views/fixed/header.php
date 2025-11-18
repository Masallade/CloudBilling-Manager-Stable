<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="<?= LTR ?>">

<head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">

      <style>
            body>.alert {
                  display: none;
            }

            #c_body {
                  position: relative;
                  z-index: 9999;
            }
            
            /* ============================================
               FLATPICKR - MODERN CALENDAR STYLING
               Beautiful, Modern Calendar Component
               ============================================ */
            
            /* Flatpickr Calendar Container */
            .flatpickr-calendar {
                  z-index: 999999 !important;
                  position: absolute !important;
                  display: block !important;
                  visibility: visible !important;
                  opacity: 1 !important;
                  background: #ffffff !important;
                  border: 2px solid #007bff !important;
                  border-radius: 12px !important;
                  box-shadow: 0 15px 50px rgba(0,0,0,0.3) !important;
                  font-family: inherit !important;
                  font-size: 14px !important;
                  line-height: 24px !important;
                  padding: 15px !important;
                  min-width: 280px !important;
                  max-width: 320px !important;
            }
            
            /* Flatpickr Month Navigation */
            .flatpickr-months {
                  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
                  border-radius: 10px 10px 0 0 !important;
                  padding: 15px 10px !important;
                  margin: -15px -15px 10px -15px !important;
                  color: #ffffff !important;
            }
            
            .flatpickr-month {
                  color: #ffffff !important;
                  fill: #ffffff !important;
            }
            
            .flatpickr-current-month {
                  color: #ffffff !important;
                  font-size: 16px !important;
                  font-weight: 600 !important;
            }
            
            /* Navigation Arrows */
            .flatpickr-prev-month,
            .flatpickr-next-month {
                  color: #ffffff !important;
                  fill: #ffffff !important;
                  padding: 8px !important;
                  border-radius: 6px !important;
                  transition: all 0.2s ease !important;
            }
            
            .flatpickr-prev-month:hover,
            .flatpickr-next-month:hover {
                  background: rgba(255,255,255,0.2) !important;
                  transform: scale(1.1) !important;
            }
            
            /* Weekday Headers */
            .flatpickr-weekdays {
                  background: #f8f9fa !important;
                  padding: 10px 0 !important;
                  margin: 0 -15px 10px -15px !important;
                  border-bottom: 2px solid #e9ecef !important;
            }
            
            .flatpickr-weekday {
                  color: #495057 !important;
                  font-weight: 600 !important;
                  font-size: 13px !important;
            }
            
            /* Day Cells */
            .flatpickr-day {
                  border-radius: 8px !important;
                  margin: 2px !important;
                  height: 36px !important;
                  line-height: 36px !important;
                  color: #333333 !important;
                  font-weight: 500 !important;
                  transition: all 0.2s ease !important;
            }
            
            /* Today */
            .flatpickr-day.today {
                  border: 2px solid #007bff !important;
                  background: #e3f2fd !important;
                  color: #007bff !important;
                  font-weight: 700 !important;
            }
            
            /* Selected Date */
            .flatpickr-day.selected {
                  background: #007bff !important;
                  color: #ffffff !important;
                  border-color: #0056b3 !important;
                  font-weight: 700 !important;
                  box-shadow: 0 4px 8px rgba(0,123,255,0.3) !important;
            }
            
            /* Hover State */
            .flatpickr-day:hover {
                  background: #e3f2fd !important;
                  color: #007bff !important;
                  border-color: #007bff !important;
                  transform: scale(1.1) !important;
            }
            
            /* Disabled/Muted Days */
            .flatpickr-day.flatpickr-disabled,
            .flatpickr-day.prevMonthDay,
            .flatpickr-day.nextMonthDay {
                  color: #adb5bd !important;
                  background: #f8f9fa !important;
            }
            
            /* Time Picker (if enabled) */
            .flatpickr-time {
                  border-top: 2px solid #e9ecef !important;
                  padding: 10px 0 !important;
                  margin-top: 10px !important;
            }
            
            /* Done/OK Button Styling */
            .flatpickr-confirm {
                  background: #007bff !important;
                  color: #ffffff !important;
                  border: none !important;
                  border-radius: 6px !important;
                  padding: 8px 20px !important;
                  font-weight: 600 !important;
                  cursor: pointer !important;
                  margin: 10px auto !important;
                  display: block !important;
                  transition: all 0.2s ease !important;
            }
            
            .flatpickr-confirm:hover {
                  background: #0056b3 !important;
                  transform: scale(1.05) !important;
            }
            
            /* Ensure calendar is above everything */
            .flatpickr-calendar,
            .flatpickr-calendar * {
                  z-index: 999999 !important;
                  pointer-events: auto !important;
            }
            
            /* Override conflicting styles */
            .card,
            .modal,
            .dropdown-menu {
                  z-index: 1050 !important;
            }
            
            /* Old datepicker fallback styles */
            .datepicker-container,
            .datepicker-dropdown,
            .datepicker-panel {
                  z-index: 999999 !important;
            }
      </style>

      <link href="https://fonts.googleapis.com/css?family=Montserrat:300,300i,400,400i,500,500i%7COpen+Sans:300,300i,400,400i,600,600i,700,700i"
            rel="stylesheet">
      <!-- BEGIN VENDOR CSS-->
      <link rel="stylesheet" type="text/css" href="<?= assets_url() ?>app-assets/<?= LTR ?>/vendors.css">
      <link rel="stylesheet" type="text/css" href="<?= assets_url() ?>app-assets/vendors/css/extensions/unslider.css">
      <link rel="stylesheet" type="text/css" href="<?= assets_url() ?>app-assets/vendors/css/weather-icons/climacons.min.css">
      <link rel="stylesheet" type="text/css" href="<?= assets_url() ?>app-assets/<?= LTR ?>/core/menu/menu-types/vertical-menu-modern.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
      <link rel="stylesheet" type="text/css" href="<?= assets_url() ?>app-assets/fonts/meteocons/style.css">
      <link rel="stylesheet" type="text/css" href="<?= assets_url() ?>app-assets/vendors/css/charts/morris.css">
      <link rel="stylesheet" type="text/css"
            href="<?= assets_url() ?>app-assets/vendors/css/tables/datatable/datatables.min.css">
      <link rel="stylesheet" type="text/css"
            href="<?= assets_url() ?>app-assets/vendors/css/tables/extensions/buttons.dataTables.min.css">
      <!-- END VENDOR CSS-->
      <!-- BEGIN STACK CSS-->
      <link rel="stylesheet" type="text/css" href="<?= assets_url() ?>app-assets/<?= LTR ?>/app.css">
      <!-- END STACK CSS-->
      <!-- BEGIN Page Level CSS-->
      <link rel="stylesheet" type="text/css"
            href="<?= assets_url() ?>app-assets/<?= LTR ?>/core/colors/palette-gradient.css">
      <link rel="stylesheet" type="text/css" href="<?= assets_url() ?>app-assets/fonts/simple-line-icons/style.css">
      <link rel="stylesheet" type="text/css"
            href="<?= assets_url() ?>app-assets/<?= LTR ?>/core/colors/palette-gradient.css">
      <!-- Flatpickr - Modern, Beautiful Calendar -->
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
      <!-- Fallback for old datepicker -->
      <link rel="stylesheet" href="<?php echo assets_url('assets/custom/datepicker.min.css') . APPVER ?>">
      <!--<link rel="stylesheet" href="<?php echo assets_url('assets/custom/summernote-bs4.css') . APPVER; ?>">-->
      <link rel="stylesheet" type="text/css"
            href="<?= assets_url() ?>app-assets/vendors/css/forms/selects/select2.min.css">
      <link rel="stylesheet" href="<?= assets_url('assets/custom/owlcarousel/assets/owl.carousel.min.css') ?>">
      <link rel="stylesheet" href="<?= assets_url('assets/custom/owlcarousel/assets/owl.theme.default.min.css') ?>">
      <!-- END Page Level CSS-->
      <!-- BEGIN Custom CSS-->
      <link rel="stylesheet" type="text/css" href="<?= assets_url() ?>assets/css/style.css<?= APPVER ?>">
      <?php if (LTR == 'rtl') echo '<link rel="stylesheet" type="text/css" href="' . assets_url() . 'assets/css/style-rtl.css' . APPVER . '">'; ?>
      <!-- END Custom CSS-->
      <script src="<?= assets_url() ?>app-assets/vendors/js/vendors.min.js"></script>
      <script type="text/javascript" src="<?= assets_url() ?>app-assets/vendors/js/ui/jquery.sticky.js"></script>
      <script type="text/javascript"
            src="<?= assets_url() ?>app-assets/vendors/js/charts/jquery.sparkline.min.js"></script>
      <script src="<?php echo assets_url(); ?>assets/portjs/raphael.min.js" type="text/javascript"></script>
      <script src="<?php echo assets_url(); ?>assets/portjs/morris.min.js" type="text/javascript"></script>
      <!-- Flatpickr - Modern Calendar Library -->
      <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
      <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/default.js"></script>
      <!-- jQuery Plugin for Flatpickr -->
      <script>
        // Make Flatpickr work with jQuery
        if (typeof jQuery !== 'undefined' && typeof flatpickr !== 'undefined') {
            jQuery.fn.flatpickr = function(options) {
                return this.each(function() {
                    var $this = jQuery(this);
                    if (!$this.data('flatpickr')) {
                        try {
                            var instance = flatpickr(this, options || {});
                            $this.data('flatpickr', instance);
                            
                            // Ensure input field is updated when date is selected
                            if (instance && instance.config && instance.config.onChange) {
                                var originalOnChange = instance.config.onChange;
                                instance.config.onChange = function(selectedDates, dateStr, instance) {
                                    // Update the input field value
                                    if (dateStr) {
                                        $this.val(dateStr);
                                    }
                                    // Call original onChange if it exists
                                    if (originalOnChange) {
                                        originalOnChange.call(this, selectedDates, dateStr, instance);
                                    }
                                };
                            }
                        } catch(e) {
                            console.error('Flatpickr initialization error:', e);
                        }
                    }
                });
            };
        }
      </script>
      <!-- Fallback for old datepicker -->
      <script src="<?php echo assets_url('assets/myjs/datepicker.min.js') . APPVER; ?>"></script>
      <link rel="icon" type="image/x-icon" href="<?= assets_url('app-assets/images/ico/favicon.ico') ?>">
      <!--<script src="<?php echo assets_url('assets/myjs/summernote-bs4.min.js') . APPVER; ?>"></script>-->

      <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
      <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


      <script src="<?php echo assets_url('assets/myjs/select2.min.js') . APPVER; ?>"></script>
      <script type="text/javascript">
            var baseurl = '<?php echo base_url() ?>';
            var crsf_token = '<?= $this->security->get_csrf_token_name() ?>';
            var crsf_hash = '<?= $this->security->get_csrf_hash(); ?>';
            
            <?php 
            // Get decimal settings and tax settings from database for JavaScript
            $CI =& get_instance();
            $CI->load->database();
            $query = $CI->db->query("SELECT * FROM univarsal_api WHERE id=4 LIMIT 1");
            $decimal_settings = $query->row_array();
            
            // Try to get tax settings, with fallback if columns don't exist yet
            try {
                $query2 = $CI->db->query("SELECT tax_type, tax_rate FROM geopos_system WHERE id=1 LIMIT 1");
                $tax_settings = $query2 ? $query2->row_array() : array();
            } catch (Exception $e) {
                $tax_settings = array();
            }
            ?>
            // Decimal configuration from settings
            var decimal_places = <?= (int)$decimal_settings['url'] ?>;
            var two_fixed = <?= (int)$decimal_settings['url'] ?>;
            var decimal_separator = '<?= $decimal_settings['key1'] ?>';
            var thousand_separator = '<?= $decimal_settings['key2'] ?>';
            
            // Tax configuration from settings
            var tax_type = '<?= isset($tax_settings['tax_type']) && $tax_settings['tax_type'] ? $tax_settings['tax_type'] : 'VAT' ?>';
            var tax_rate = <?= isset($tax_settings['tax_rate']) && $tax_settings['tax_rate'] ? (float)$tax_settings['tax_rate'] : 20 ?>;
            var tax_rate_decimal = <?= isset($tax_settings['tax_rate']) && $tax_settings['tax_rate'] ? (float)$tax_settings['tax_rate'] / 100 : 0.20 ?>;
            
            // Force menu to be collapsed by default and prevent auto-expansion
            (function() {
                // Set localStorage to force collapsed state
                if (typeof(Storage) !== "undefined") {
                    localStorage.setItem("menuLocked", "false");
                }
                
                function forceCollapsed() {
                    var body = document.body;
                    if (body && body.classList.contains('menu-expanded')) {
                        body.classList.remove('menu-expanded', 'menu-open');
                        body.classList.add('menu-collapsed');
                    }
                }
                
                // Ensure body has menu-collapsed class on page load
                document.addEventListener('DOMContentLoaded', function() {
                    forceCollapsed();
                });
                
                // Override menu expansion after page load (multiple attempts to catch JS initialization)
                window.addEventListener('load', function() {
                    setTimeout(forceCollapsed, 50);
                    setTimeout(forceCollapsed, 200);
                    setTimeout(forceCollapsed, 500);
                    setTimeout(forceCollapsed, 1000);
                });
                
                // Monitor for class changes and force collapsed if expanded
                if (window.MutationObserver) {
                    var observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                                var body = document.body;
                                if (body && body.classList.contains('menu-expanded') && !body.classList.contains('menu-collapsed')) {
                                    setTimeout(forceCollapsed, 10);
                                }
                            }
                        });
                    });
                    
                    // Start observing after DOM is ready
                    document.addEventListener('DOMContentLoaded', function() {
                        if (document.body) {
                            observer.observe(document.body, {
                                attributes: true,
                                attributeFilter: ['class']
                            });
                        }
                    });
                }
            })();
      </script>
      <script src="<?php echo assets_url(); ?>assets/portjs/accounting.min.js" type="text/javascript"></script>
      <script src="<?= assets_url('assets/custom/owlcarousel/owl.carousel.min.js') ?>" type="text/javascript"></script>
      <?php
      include_once('header-style.php');
      accounting();
      ?>
</head>
<?php
include_once('header-va.php');
// if (MENU) {
// } else {
//     include_once('header-ha.php');
// }