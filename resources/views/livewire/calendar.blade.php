<style>
    #calendar {
        width: 100%;
        margin: 0 auto;
    }

    .fc-toolbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        padding: 10px;
    }

    .fc-toolbar-title {
        font-size: 1rem !important;
        font-weight: bold;
        white-space: normal;
        text-align: center;
        width: 100%;
    }

    .fc-toolbar-chunk {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        justify-content: center;
        width: 100%;
    }

    /* Button Styles */
    .fc .fc-button {
        background-color: #2B7FFFFF;
        border: none;
        color: white;
        font-size: 0.8rem;
        padding: 5px 10px;
        margin: 2px;
    }

    .fc .fc-button:hover {
        background-color: #155DFCFF;
    }

    .fc-event {
        padding: 2px !important;
        font-size: 0.75rem;
        line-height: 1.2;
        margin-bottom: 2px !important;
        border-radius: 4px;
        background-color: #2B7FFFFF !important;
        color: white;
        border: none;
    }

    .fc-event-title {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: initial !important;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .fc-event-main {
        white-space: normal !important;
    }

    .fc .fc-daygrid-event-dot {
        margin-top: 5px;
    }

    /* Header Cells */
    .fc-col-header-cell-cushion {
        font-size: 0.7rem;
        padding: 2px 4px;
        color: #000000;
    }

    /* Day Cells */
    .fc-daygrid-day-frame {
        min-height: 60px;
    }

    .fc-daygrid-day-number {
        font-size: 0.8rem;
        padding: 2px;
    }

    @media (min-width: 768px) {
        .fc-toolbar {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }

        .fc-toolbar-title {
            font-size: 1.25rem !important;
            text-align: left;
            width: auto;
        }

        .fc-toolbar-chunk {
            width: auto;
            justify-content: flex-start;
        }

        .fc .fc-button {
            font-size: 0.9rem;
            padding: 6px 12px;
        }

        .fc-event {
            font-size: 0.85rem;
        }

        .fc-col-header-cell-cushion {
            font-size: 0.8rem;
        }
    }


    @media (min-width: 1024px) {
        .fc-toolbar-title {
            font-size: 1.5rem !important;
        }

        .fc .fc-button {
            font-size: 1rem;
        }

        .fc-event {
            font-size: 0.9rem;
        }
    }
</style>

<div class="w-full overflow-x-auto">
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
            height: 'auto',
            aspectRatio: 1.35,
            timeZone: 'UTC',
            events: @json($schedules),
            headerToolbar: {
                left: 'title',
                center: '',
                right: 'prev,next today',
            },
            buttonText: {
                today: 'Hari ini',
                month: 'Bulan',
                week: 'Minggu',
                day: 'Hari',
                list: 'Daftar'
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
            },
            windowResize: function(arg) {
                if (window.innerWidth < 768) {
                    calendar.changeView('dayGridMonth');
                }
            }
        });

        calendar.render();

        if (window.innerWidth < 768) {
            calendar.setOption('headerToolbar', {
                left: 'prev,next',
                center: 'title',
                right: 'today'
            });
        }
    }
</script>
@endscript
