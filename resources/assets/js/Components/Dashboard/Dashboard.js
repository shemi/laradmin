import MixinsLoader from '../../Helpers/MixinsLoader';
import LaFieldRenderer from  '../FieldRenderer/FieldRenderer';

export default {
    name: 'la-dashboard',

    mixins: MixinsLoader.load('dashboard', []),

    props: [],

    data() {
        return {

        }
    },

    components: {
        LaFieldRenderer
    }

}