
export default {

    props: ['item'],

    data() {
        return {
            isActive: false
        }
    },

    methods: {
        linkClicked($event) {
            if(! this.hasItems) {
                return true;
            }

            $event.preventDefault();
            this.isActive = ! this.isActive;
        },

        getAnime (targets) {
            if (this.anime) {
                return this.anime;
            }

            return this.anime = anime({ targets });
        },

        cancel () {
            this.anime.pause();
        },

        before (targets) {
            if (!this.targets) {
                this.targets = targets;
            }

            targets.removeAttribute('style');
        },

        enter (targets, done) {
            const height = targets.scrollHeight;
            targets.style.height = 0;
            targets.style.opacity = 0;

            this.getAnime(targets).play({
                targets,
                duration: 377,
                easing: 'easeOutExpo',
                opacity: [0, 1],
                height,
                complete () {
                    targets.removeAttribute('style');
                    done();
                }
            });
        },

        leave (targets, complete) {
            this.getAnime(targets).play({
                targets,
                duration: 377,
                easing: 'easeOutExpo',
                opacity: [1, 0],
                height: 0,
                complete
            });
        }

    },

    computed: {
        hasItems() {
            return this.items && this.items.length > 0;
        },

        items() {
            return this.item.items;
        },

        href() {
            return this.item.type === 'route' ?
                this.item.route_url :
                this.item.url;
        }
    }

}