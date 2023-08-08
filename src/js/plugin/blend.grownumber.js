/* global blend */

/**
 * Animated number from zero to the real number
 *
 */

blend.grownumber = {};
blend.grownumber.defaultTime = 1000; //ms
blend.plugins.push(blend.grownumber);

blend.grownumber.register = function ()
{
};

blend.grownumber.start = function ()
{
    let elements = $('[data-grow-number]');

    elements.each(function (idx)
    {
        let element = $(elements[idx]);
        let valueOriginal = toNumber(element.data('grow-number'));
        let intervalTime = blend.grownumber.defaultTime / valueOriginal;
        let increment =  Math.round(valueOriginal / blend.grownumber.defaultTime);
        increment = increment == 0 ? 1 : increment;
        element.html('0');

        let myInterval = setInterval(function ()
        {
            let newValue = parseInt(element.text()) + increment;

            if (newValue > valueOriginal)
            {
                newValue = valueOriginal;
            }

            if (newValue < valueOriginal)
            {
                element.html(newValue);
            } else
            {
                element.html(newValue);
                clearInterval(myInterval);
                //avoid do it again
                element.removeAttr('data-grow-number');
            }

        }, intervalTime);

    });
}; 