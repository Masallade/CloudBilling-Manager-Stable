<div class="container">
    <h3>Invoices Due Calendar</h3>
    <div id="calendar"></div>
</div>

<!-- Include FullCalendar CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: [
                <?php if (!empty($due_invoices)) : ?>
                    <?php foreach ($due_invoices as $invoice) : ?>
                        {
                            title: 'Count: <?= $invoice['invoice_count'] ?>, Total: <?= $invoice['total_due'] ?>',
                            start: '<?= $invoice['date'] ?>',
                            allDay: true
                        },
                    <?php endforeach; ?>
                <?php endif; ?>
            ]
        });
        calendar.render();
    });
</script>
