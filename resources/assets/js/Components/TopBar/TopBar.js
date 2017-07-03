export default {

    data() {
        return {
            isMobileMenuOpen: false
        }
    },

    methods: {
        logout() {
            document.getElementById('logout-form')
                .submit();
        },

        toggleMobileMenu() {
            this.isMobileMenuOpen = ! this.isMobileMenuOpen;
        }
    }

}