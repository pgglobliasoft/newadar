
define([
    'underscore'
], function (_) {
    'use strict';

    return function (TabGroup) {
        return TabGroup.extend({
            /**
             * Delegates 'validate' method on element, then reads 'invalid' property
             * of params storage, and if defined, activates element, sets
             * 'allValid' property of instance to false and sets invalid's
             * 'focused' property to true.
             *
             * @param {Object} elem
             */
            validate: function (elem) {
                // Pass through if element is not fieldset
                if (elem.index !== 'banner_grid_listing') {
                    return this._super();
                }

                var result = elem.delegate('validate'),
                    invalid;

                invalid = _.find(result, function (item) {
                    if (item === undefined) {
                        return 0;
                    }

                    return !item.valid;
                });

                if (invalid) {
                    elem.activate();
                    invalid.target.focused(true);
                }

                return invalid;
            }
        });
    }
});