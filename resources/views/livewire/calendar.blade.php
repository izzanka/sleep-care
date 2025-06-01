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

    .fc-event-title {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: initial !important;
    }

    .fc-event {
        padding: 2px !important;
        font-size: 14px;
        line-height: 1.2;
    }

    .fc-event-main {
        white-space: normal !important;
    }

    .fc .fc-daygrid-event {
        padding: 4px 6px !important;
        margin-bottom: 3px !important;
        font-size: 14px !important;
        line-height: 1.4 !important;
        border-radius: 4px;
    }

    .fc .fc-event-title {
        white-space: normal !important;
    }

    .fc .fc-daygrid-event-dot {
        margin-top: 5px; /* for dot-style events */
    }

    .fc .fc-event {
        background-color: #2B7FFFFF !important;
        color: white;
        border: none;
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
            height: 600,
            timeZone: 'UTC',
            events: @json($schedules),
            buttonText: {
                today: 'Hari ini',
            },
            eventDidMount: function(info) {
                info.el.setAttribute("title", info.event.title);
            },
            eventClick: function(info) {
                const therapyId = parseInt(info.event.id, 10);
                window.location.href = "/doctor/therapies/in-progress/" + therapyId;
            },
            datesSet: function(info) {
                const originalTitle = info.view.title;
                document.querySelector('.fc-toolbar-title').textContent = 'Jadwal Sesi Terapi Bulan ' + originalTitle;
            }
        });

        calendar.render();
    }
</script>
@endscript
