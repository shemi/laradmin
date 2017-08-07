import MixinsLoader from '../../Helpers/MixinsLoader';
import FormatDate from '../../Mixins/FormatDate';
import LaHttp from '../../Forms/LaHttp';

export default {
    mixins: MixinsLoader.load('crudBrowse', [FormatDate]),

    props: ['typeName', 'typeSlug'],

    data() {
        return {
            loading: true,
            checkedRows: [],
            selected: {},
            data: []
        }
    },

    created() {
        this.fetchData();
    },

    methods: {

        fetchData() {
            let query = {};

            LaHttp.get(`/${this.typeSlug}/query`, query)
                .then(res => {
                    this.data = res.data.data.data;
                    this.loading = false;
                })
                .catch(err => {
                    console.log(err);
                });

        }

    },

}