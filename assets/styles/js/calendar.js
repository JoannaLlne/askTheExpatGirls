function initCalendar() {

    const calendarDays = document.getElementById('calendarDays');
    const currentMonthElement = document.getElementById('currentMonth');

    const previousButton = document.getElementById('prevMonth');
    const nextButton = document.getElementById('nextMonth');

    /*
     * Si pas sur la page du calendrier, on ne fait rien.
     */
    if (
        !calendarDays ||
        !currentMonthElement ||
        !previousButton ||
        !nextButton
    ) {
        return;
    }


    const daySlots = document.querySelectorAll('.day-slots');
    const availableDays = {};

    daySlots.forEach(slot => {
        const date = slot.dataset.date;
        availableDays[date] = true;
    });


    let currentDate = new Date();
    currentDate.setDate(1);


    function renderCalendar() {
        calendarDays.innerHTML = '';

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        currentMonthElement.textContent =
            new Intl.DateTimeFormat('en-US', {
                month: 'long',
                year: 'numeric'
            }).format(currentDate);


        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const numberOfDays = lastDay.getDate();

        let firstDayIndex = firstDay.getDay();
        if (firstDayIndex === 0) {
            firstDayIndex = 6;
        } else {
            firstDayIndex--;
        }


        for (let i = 0; i < firstDayIndex; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.classList.add('calendar-day', 'empty');
            calendarDays.appendChild(emptyDay);
        }


        for (let day = 1; day <= numberOfDays; day++) {
            const dateKey =
                `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

            const button = document.createElement('button');

            button.type = 'button';
            button.classList.add('calendar-day');
            button.textContent = day;


            if (availableDays[dateKey]) {
                button.classList.add('available');
                button.dataset.date = dateKey;

                button.addEventListener('click', () =>
                    selectDay(dateKey));
            } else {
                button.classList.add('unavailable');
                button.disabled = true;
            }


            const today = new Date();
            if (
                day === today.getDate() &&
                month === today.getMonth() &&
                year === today.getFullYear()
            ) {
                button.classList.add('today');
            }
            calendarDays.appendChild(button);
        }
    }


    function selectDay(dateKey) {
        document
            .querySelectorAll('.calendar-day.selected')
            .forEach(day => {
                day.classList.remove('selected');
            });


        const selectedDay = document.querySelector(`.calendar-day[data-date="${dateKey}"]`);
        if (selectedDay) {
            selectedDay.classList.add('selected');
        }

        daySlots.forEach(slot => {
            slot.hidden = true;
        });


        const selectedSlots = document.querySelector(`.day-slots[data-date="${dateKey}"]`);

        if (selectedSlots) {
            selectedSlots.hidden = false;
        }


        const chooseDay = document.querySelector('.choose-day');
        if (chooseDay) {
            chooseDay.hidden = true;
        }
    }

    previousButton.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        }
    );

    nextButton.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        }
    );
    renderCalendar();
}


// Chargement classique
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCalendar);
} else {
    initCalendar();
}

// Navigation avec Symfony UX Turbo
document.addEventListener(
    'turbo:load',
    initCalendar
);
