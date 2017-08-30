class MixinsLoader {
    constructor() {
        this.mixins = window.laradmin.mixins;
    }

    load(key, defaults = []) {
        let userMixins = this.get(key),
            mixins = defaults,
            mixinIndex;

        if (userMixins.length <= 0) {
            return defaults;
        }

        for (mixinIndex in userMixins) {
            mixins = mixins.concat(userMixins[mixinIndex]);
        }

        return mixins;
    }

    get(key) {
        let mixins = [],
            mixinGroup,
            mixinIndex;

        if (this.mixins.length <= 0 || !key) {
            return [];
        }

        for (mixinIndex in this.mixins) {
            mixinGroup = this.mixins[mixinIndex];

            if (
                !mixinGroup ||
                typeof mixinGroup !== 'object' ||
                !mixinGroup.key ||
                typeof mixinGroup.mixin !== 'object' ||
                mixinGroup.key !== key
            )
            {
                continue;
            }

            mixins.push(mixinGroup.mixin);
        }

        return mixins;
    }

}

export default new MixinsLoader();