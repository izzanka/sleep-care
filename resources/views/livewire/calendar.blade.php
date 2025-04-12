<div>
    <div id="calendar"></div>
</div>

@script
<script>

    document.addEventListener('livewire:initialized', initializeCalendar);
    document.addEventListener('livewire:navigated', initializeCalendar);

    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            timeZone: 'UTC',
            events: @json($schedules),
            eventClick: function(info) {
                window.location.href = "/doctor/therapies/in-progress/schedule";
            }
        });

        calendar.render();
    }
</script>
@endscript
