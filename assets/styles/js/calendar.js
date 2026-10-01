document.addEventListener('DOMContentLoaded', () => {
    const calendarDays = document.getElementById('calendarDays');
    const currentMonthElement = document.getElementById('currentMonth');

    const previousButton = document.getElementById('prevMonth');
    const nextButton = document.getElementById('nextMonth');

    // On récupère tous les jours qui possèdent réellement des créneaux dans le HTML.
    const daySlots = document.querySelectorAll('.day-slots');

    /*
     * On crée un objet :
     * {
     *     "2026-10-05": true,
     *     "2026-10-08": true
     * }
     */
    const availableDays = {};

    daySlots.forEach(slot => {
        const date = slot.dataset.date;
        availableDays[date] = true;
    });


    // Date actuellement affichée.
    let currentDate = new Date();
    currentDate.setDate(1);

    /*
     * =========================
     * AFFICHER LE CALENDRIER
     * =========================
     */

    function renderCalendar() {
        calendarDays.innerHTML = '';

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        //Nom du mois
        currentMonthElement.textContent =
            new Intl.DateTimeFormat('en-US', {
                month: 'long',
                year: 'numeric'
            }).format(currentDate);

        // Premier jour du mois
        const firstDay = new Date(year, month, 1);
        // Nombre de jours dans le mois
        const lastDay = new Date(year, month + 1, 0);

        const numberOfDays = lastDay.getDate();

        /*
         * JavaScript considère dimanche comme 0.
         * Notre calendrier commence lundi.
         */
        let firstDayIndex = firstDay.getDay();

        if (firstDayIndex === 0) {
            firstDayIndex = 6;
        } else {
            firstDayIndex--;
        }


        // Cases vides avant le premier jour
        for (let i = 0; i < firstDayIndex; i++) {
            const emptyDay = document.createElement('div');

            emptyDay.classList.add('calendar-day', 'empty');
            calendarDays.appendChild(emptyDay);
        }


        // Création des jours
        for (let day = 1; day <= numberOfDays; day++) {
            const dateKey =
                `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

            const button = document.createElement('button');

            button.type = 'button';
            button.classList.add('calendar-day');
            button.textContent = day;


            // Vérifie si cette date possède des créneaux
            if (availableDays[dateKey]) {
                button.classList.add('available');

                button.dataset.date = dateKey;

                button.addEventListener('click', () => {
                    selectDay(dateKey);
                    }
                );
            } else {
                button.classList.add('unavailable');
                button.disabled = true;
            }


            // Aujourd'hui
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

    /*
     * =========================
     * SÉLECTION D'UN JOUR
     * =========================
     */
    function selectDay(dateKey) {
        // Retire la sélection précédente
        document.querySelectorAll('.calendar-day.selected')
            .forEach(day => {
                day.classList.remove(
                    'selected'
                );
            });

        // Sélectionne le nouveau jour.
        const selectedDay =
            document.querySelector(`.calendar-day[data-date="${dateKey}"]`);

        if (selectedDay) {
            selectedDay.classList.add(
                'selected'
            );
        }

        // Cache tous les créneaux.
        daySlots.forEach(slot => {
            slot.hidden = true;
        });

        // Affiche les créneaux du jour.
        const selectedSlots = document.querySelector(`.day-slots[data-date="${dateKey}"]`);

        if (selectedSlots) {
            selectedSlots.hidden = false;
        }

        // Cache le message initial.
        const chooseDay = document.querySelector('.choose-day');

        if (chooseDay) {
            chooseDay.hidden = true;
        }
    }

    /*
     * =========================
     * MOIS PRÉCÉDENT
     * =========================
     */
    previousButton.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        }
    );

    /*
     * =========================
     * MOIS SUIVANT
     * =========================
     */
    nextButton.addEventListener(
        'click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        }
    );

    /*
     * =========================
     * PREMIER AFFICHAGE
     * =========================
     */
    renderCalendar();
});

