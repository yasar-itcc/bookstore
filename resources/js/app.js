import Alpine from 'alpinejs';

// Home page slider: auto-advances, pauses on hover, swipeable via the dots and arrows.
Alpine.data('slider', (count, interval = 6000) => ({
    active: 0,
    count,
    timer: null,

    init() {
        this.start();
    },

    start() {
        if (this.count > 1) {
            this.timer = setInterval(() => this.next(), interval);
        }
    },

    stop() {
        clearInterval(this.timer);
    },

    next() {
        this.active = (this.active + 1) % this.count;
    },

    prev() {
        this.active = (this.active - 1 + this.count) % this.count;
    },

    go(index) {
        this.active = index;
        this.stop();
        this.start();
    },
}));

// Book shelf: scrolls a row of cards left and right.
Alpine.data('shelf', () => ({
    scroll(direction) {
        const track = this.$refs.track;
        track.scrollBy({ left: direction * track.clientWidth * 0.8, behavior: 'smooth' });
    },
}));

window.Alpine = Alpine;
Alpine.start();
