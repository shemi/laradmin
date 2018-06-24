export default {

    data() {
        return {
            defaultImageServiceUrl: 'https://dummyimage.com'
        }
    },

    methods: {
        getDefaultImageUri(size, text = "Missing Image") {
            text = text ? '?text='+encodeURI(text) : "";
            size = this.imageSize ? this.imageSize.toLowerCase().split('x') : null;

            let width = 128,
                height = 128;

            if(size && size.length > 0) {
                width = size[0] || 128;
                height = size[1] || width;
            }

            return `${this.defaultImageServiceUrl}/${width}X${height || width}/6e46db/fff.jpg${text}`;
        }
    },

}