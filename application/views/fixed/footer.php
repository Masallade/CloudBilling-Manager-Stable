</div>
</div>
</div>
<!-- BEGIN VENDOR JS-->
<script type="text/javascript">
    // Initialize Flatpickr - Modern, Beautiful Calendar
    $(document).ready(function() {
        // Convert date format from PHP config to Flatpickr format
        var phpFormat = '<?php echo $this->config->item('dformat2'); ?>';
        var flatpickrFormat = phpFormat
            .replace('dd', 'd')
            .replace('mm', 'm')
            .replace('yyyy', 'Y')
            .replace('yy', 'y');
        
        // Flatpickr configuration
        var flatpickrConfig = {
            dateFormat: flatpickrFormat,
            allowInput: true,
            clickOpens: true,
            closeOnSelect: true,  // Close calendar when date is selected
            animate: true,
            theme: 'material_blue',
            zIndex: 999999,
            appendTo: document.body,
            static: false,
            monthSelectorType: 'static',
            locale: {
                firstDayOfWeek: 1
            }
        };
        
        // Initialize date range pairs
        function initDateRange(fromId, toId) {
            var $fromField = $('#' + fromId);
            var $toField = $('#' + toId);
            
            if ($fromField.length && $toField.length) {
                // Skip native date inputs - they should always be enabled
                if ($fromField.attr('type') === 'date' || $toField.attr('type') === 'date') {
                    return;
                }
                
                // Disable "to" field initially
                $toField.prop('disabled', true).css('opacity', '0.6');
                
                // Initialize "from" datepicker
                if (!$fromField.data('flatpickr') && !$fromField.data('datepicker')) {
                    try {
                        // Use direct flatpickr call with enhanced config
                        var fromPickerInstance = flatpickr($fromField[0], $.extend({}, flatpickrConfig, {
                            closeOnSelect: true,
                            wrap: true,  // Enable wrapping for custom buttons
                            onChange: function(selectedDates, dateStr, instance) {
                                // Force update the input field value immediately
                                if (dateStr) {
                                    $fromField.val(dateStr).trigger('change');
                                }
                                
                                if (selectedDates.length > 0) {
                                    // Enable "to" field when "from" date is selected
                                    $toField.prop('disabled', false).css('opacity', '1');
                                    
                                    // Initialize or update "to" datepicker with minDate
                                    if (!$toField.data('flatpickr') && !$toField.data('datepicker')) {
                                        try {
                                            var toPickerInstance = flatpickr($toField[0], $.extend({}, flatpickrConfig, {
                                                closeOnSelect: true,
                                                wrap: true,  // Enable wrapping for custom buttons
                                                minDate: selectedDates[0],
                                                onChange: function(selectedDates2, dateStr2, instance2) {
                                                    // Force update the input field value immediately
                                                    if (dateStr2) {
                                                        $toField.val(dateStr2).trigger('change');
                                                    }
                                                    
                                                    // Validate that "to" date is not before "from" date
                                                    if (selectedDates2.length > 0 && selectedDates2[0] < selectedDates[0]) {
                                                        instance2.setDate(selectedDates[0]);
                                                        var correctedDate = instance2.formatDate(selectedDates[0], flatpickrConfig.dateFormat);
                                                        $toField.val(correctedDate).trigger('change');
                                                    }
                                                },
                                                onReady: function(selectedDates2, dateStr2, instance2) {
                                                    // Add Done button after calendar is ready
                                                    var confirmBtn = document.createElement('button');
                                                    confirmBtn.className = 'flatpickr-confirm';
                                                    confirmBtn.type = 'button';
                                                    confirmBtn.textContent = 'Done';
                                                    confirmBtn.onclick = function() {
                                                        if (instance2.selectedDates.length > 0) {
                                                            var formatted = instance2.formatDate(instance2.selectedDates[0], flatpickrConfig.dateFormat);
                                                            $toField.val(formatted).trigger('change');
                                                        }
                                                        instance2.close();
                                                    };
                                                    
                                                    // Insert button before closing tag of calendar
                                                    var calendarContainer = instance2.calendarContainer;
                                                    if (calendarContainer) {
                                                        var existingBtn = calendarContainer.querySelector('.flatpickr-confirm');
                                                        if (!existingBtn) {
                                                            calendarContainer.appendChild(confirmBtn);
                                                        }
                                                    }
                                                }
                                            }));
                                            $toField.data('flatpickr', toPickerInstance);
                                        } catch(e) {
                                            console.error('Error initializing to datepicker:', e);
                                            $toField.datepicker({
                                                autoHide: true,
                                                format: phpFormat,
                                                zIndex: 999999,
                                                minDate: selectedDates[0],
                                                onSelect: function(dateText) {
                                                    $toField.val(dateText).trigger('change');
                                                }
                                            });
                                        }
                                    } else {
                                        // Update existing "to" datepicker minDate
                                        var toPicker = $toField.data('flatpickr');
                                        if (toPicker && typeof toPicker.set === 'function') {
                                            toPicker.set('minDate', selectedDates[0]);
                                        } else if (toPicker && toPicker.config) {
                                            toPicker.config.minDate = selectedDates[0];
                                        }
                                    }
                                } else {
                                    // Disable "to" field if "from" date is cleared
                                    $toField.prop('disabled', true).css('opacity', '0.6').val('');
                                    var toPicker = $toField.data('flatpickr');
                                    if (toPicker && typeof toPicker.clear === 'function') {
                                        toPicker.clear();
                                    }
                                }
                            },
                            onClose: function(selectedDates, dateStr, instance) {
                                // Ensure field is filled when calendar closes
                                if (dateStr) {
                                    $fromField.val(dateStr).trigger('change');
                                }
                            },
                            onReady: function(selectedDates, dateStr, instance) {
                                // Add Done button after calendar is ready
                                var confirmBtn = document.createElement('button');
                                confirmBtn.className = 'flatpickr-confirm';
                                confirmBtn.type = 'button';
                                confirmBtn.textContent = 'Done';
                                confirmBtn.onclick = function() {
                                    if (instance.selectedDates.length > 0) {
                                        var formatted = instance.formatDate(instance.selectedDates[0], flatpickrConfig.dateFormat);
                                        $fromField.val(formatted).trigger('change');
                                    }
                                    instance.close();
                                };
                                
                                // Insert button before closing tag of calendar
                                var calendarContainer = instance.calendarContainer;
                                if (calendarContainer) {
                                    var existingBtn = calendarContainer.querySelector('.flatpickr-confirm');
                                    if (!existingBtn) {
                                        calendarContainer.appendChild(confirmBtn);
                                    }
                                }
                            }
                        }));
                        $fromField.data('flatpickr', fromPickerInstance);
                    } catch(e) {
                        console.error('Flatpickr error for ' + fromId, e);
                        $fromField.datepicker({
                            autoHide: true,
                            format: phpFormat,
                            zIndex: 999999,
                            onSelect: function(dateText) {
                                $fromField.val(dateText).trigger('change');
                                $toField.prop('disabled', false).css('opacity', '1');
                                if (!$toField.data('datepicker')) {
                                    $toField.datepicker({
                                        autoHide: true,
                                        format: phpFormat,
                                        zIndex: 999999,
                                        minDate: dateText,
                                        onSelect: function(dateText2) {
                                            $toField.val(dateText2).trigger('change');
                                        }
                                    });
                                } else {
                                    $toField.datepicker('option', 'minDate', dateText);
                                }
                            }
                        });
                    }
                }
            }
        }
        
        // Initialize Flatpickr on all datepicker fields (except date ranges)
        function initFlatpickr() {
            // Handle fields with data-toggle="datepicker"
            $('[data-toggle="datepicker"]').each(function() {
                var $this = $(this);
                var id = $this.attr('id');
                var name = $this.attr('name');
                
                // Skip date range fields - they will be handled separately
                if (id === 'start_date' || id === 'end_date' || 
                    id === 'start_prod_date' || id === 'end_prod_date') {
                    return;
                }
                
                // Skip native date inputs and specific fields that should not use flatpickr
                if ($this.attr('type') === 'date' || 
                    name === 'invoicedate' || 
                    name === 'invocieduedate' ||
                    $this.hasClass('no-flatpickr')) {
                    return;
                }
                
                if (!$this.data('flatpickr') && !$this.data('datepicker')) {
                    try {
                        var instance = flatpickr(this, $.extend({}, flatpickrConfig, {
                            closeOnSelect: true,
                            onChange: function(selectedDates, dateStr, instance) {
                                // Ensure input field is updated
                                if (dateStr) {
                                    $this.val(dateStr).trigger('change');
                                }
                            },
                            onClose: function(selectedDates, dateStr, instance) {
                                // Ensure field is filled when calendar closes
                                if (dateStr) {
                                    $this.val(dateStr).trigger('change');
                                }
                            }
                        }));
                        $this.data('flatpickr', instance);
                    } catch(e) {
                        // Fallback to old datepicker if Flatpickr fails
                        $this.datepicker({
                            autoHide: true,
                            format: phpFormat,
                            zIndex: 999999,
                            onSelect: function(dateText) {
                                $this.val(dateText).trigger('change');
                            }
                        });
                    }
                }
            });
            
            // Also handle .date30 class fields (used in purchase invoices)
            $('.date30').each(function() {
                var $this = $(this);
                var id = $this.attr('id');
                var name = $this.attr('name');
                
                // Skip native date inputs and specific fields
                if ($this.attr('type') === 'date' || 
                    name === 'invoicedate' || 
                    name === 'invocieduedate' ||
                    $this.hasClass('no-flatpickr')) {
                    return;
                }
                
                // Only initialize if it's a date range field and not already initialized
                if ((id === 'start_date' || id === 'start_prod_date') && 
                    !$this.data('flatpickr') && !$this.data('datepicker')) {
                    // This will be handled by initDateRange, so skip here
                    return;
                }
                
                // For other .date30 fields that aren't date ranges
                if (id !== 'start_date' && id !== 'end_date' && 
                    id !== 'start_prod_date' && id !== 'end_prod_date' &&
                    !$this.data('flatpickr') && !$this.data('datepicker')) {
                    try {
                        var instance = flatpickr(this, $.extend({}, flatpickrConfig, {
                            closeOnSelect: true,
                            onChange: function(selectedDates, dateStr, instance) {
                                if (dateStr) {
                                    $this.val(dateStr).trigger('change');
                                }
                            }
                        }));
                        $this.data('flatpickr', instance);
                    } catch(e) {
                        $this.datepicker({
                            autoHide: true,
                            format: phpFormat,
                            zIndex: 999999,
                            onSelect: function(dateText) {
                                $this.val(dateText).trigger('change');
                            }
                        });
                    }
                }
            });
        }
        
        // Initialize date ranges
        function initDateRanges() {
            // Invoice date range
            initDateRange('start_date', 'end_date');
            // Purchase date range
            initDateRange('start_prod_date', 'end_prod_date');
        }
        
        // Initialize on page load
        initFlatpickr();
        initDateRanges();
        
        // Initialize specific date fields (only if not type="date")
        if ($('#sdate').length && $('#sdate').attr('type') !== 'date' && !$('#sdate').data('flatpickr')) {
            try {
                $('#sdate').flatpickr($.extend({}, flatpickrConfig, {
                    defaultDate: '<?php echo dateformat(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d'))))); ?>'
                }));
            } catch(e) {
                $('#sdate').datepicker({
                    autoHide: true, 
                    format: phpFormat,
                    zIndex: 999999
                });
                $('#sdate').datepicker('setDate', '<?php echo dateformat(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d'))))); ?>');
            }
        }

        $('.date30').each(function() {
            var $this = $(this);
            var name = $this.attr('name');
            
            // Skip native date inputs and specific fields that should not use flatpickr
            if ($this.attr('type') === 'date' || 
                name === 'invoicedate' || 
                name === 'invocieduedate' ||
                $this.hasClass('no-flatpickr')) {
                return;
            }
            
            if (!$this.data('flatpickr')) {
                try {
                    $this.flatpickr($.extend({}, flatpickrConfig, {
                        defaultDate: '<?php echo dateformat(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d'))))); ?>'
                    }));
                } catch(e) {
                    $this.datepicker({
                        autoHide: true, 
                        format: phpFormat,
                        zIndex: 999999
                    });
                    $this.datepicker('setDate', '<?php echo dateformat(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d'))))); ?>');
                }
            }
        });

        // Re-initialize for dynamically added content
        $(document).on('DOMNodeInserted', function(e) {
            if ($(e.target).find('[data-toggle="datepicker"]').length || $(e.target).is('[data-toggle="datepicker"]')) {
                setTimeout(function() {
                    initFlatpickr();
                    initDateRanges();
                }, 100);
            }
        });
        
        // Also use MutationObserver for better dynamic content detection
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function(mutations) {
                var needsInit = false;
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        $(mutation.addedNodes).find('[data-toggle="datepicker"]').each(function() {
                            if (!$(this).data('flatpickr') && !$(this).data('datepicker')) {
                                needsInit = true;
                            }
                        });
                    }
                });
                if (needsInit) {
                    setTimeout(function() {
                        initFlatpickr();
                        initDateRanges();
                    }, 100);
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    });
</script>
<script src="<?= assets_url() ?>app-assets/vendors/js/extensions/unslider-min.js"></script>
<script src="<?= assets_url() ?>app-assets/vendors/js/timeline/horizontal-timeline.js"></script>
<script src="<?= assets_url() ?>app-assets/js/core/app-menu.js"></script>
<script src="<?= assets_url() ?>app-assets/js/core/app.js"></script>
<script type="text/javascript" src="<?= assets_url() ?>app-assets/js/scripts/ui/breadcrumbs-with-stats.js"></script>
<script src="<?php echo assets_url(); ?>assets/myjs/jquery-ui.js"></script>
<script src="<?php echo assets_url(); ?>app-assets/vendors/js/tables/datatable/datatables.min.js"></script>

<script type="text/javascript">var dtformat = $('#hdata').attr('data-df');
    var currency = $('#hdata').attr('data-curr');
    ;</script>
<script src="<?php echo assets_url('assets/myjs/custom____script.js') ?>"></script>
<script src="<?php echo assets_url('assets/myjs/basic.js') . APPVER; ?>"></script>
<script src="<?php echo assets_url('assets/myjs/control__1scr.js') ?>"></script>


<script type="text/javascript">

$( document ).ready(function() {

    var myobj = $('.alert').eq(0);
myobj.remove();

   $('.alert').eq(0).hide();
});



    $.ajax({

        url: baseurl + 'manager/pendingtasks',
        dataType: 'json',
        success: function (data) {
            $('#tasklist').html(data.tasks);
            $('#taskcount').html(data.tcount);

        },
        error: function (data) {
            $('#response').html('Error')
        }

    });


</script>

</body>
</html>
