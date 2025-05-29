<style>
    .fc-toolbar-title {
        font-size: 16px !important;
        font-weight: bold;
    }

    .fc .fc-button {
        background-color: #2B7FFFFF;
        border: none;
        color: white;
    }

    .fc .fc-button:hover {
        background-color: #155DFCFF;
    }
</style>
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
            locale: 'id',
            initialView: 'dayGridMonth',
            height: 540,
            timeZone: 'UTC',
            events: @json($schedules),
            buttonText: {
                today: 'Hari ini',
            },
            eventClick: function(info) {
                window.location.href = "/doctor/therapies/in-progress/schedule";
            },
            datesSet: function(info) {
                const originalTitle = info.view.title;
                const newTitle = 'Jadwal Sesi Terapi Bulan ' + originalTitle;
                document.querySelector('.fc-toolbar-title').textContent = newTitle;
            }
        });

        calendar.render();
    }
</script>
@endscript
