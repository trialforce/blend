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
    var elements = $('[data-grow-number]');

    elements.each(function (idx)
    {
        var element = $(elements[idx]);
        var valueOriginal = toNumber(element.data('grow-number'));
        var intervalTime = blend.grownumber.defaultTime / valueOriginal;
        var increment = 1;

        if (intervalTime < 10)
        {
            increment = 10;
            intervalTime *= 10;
        }

        element.html('0');

        var myInterval = setInterval(function ()
        {
            var newValue = parseInt(element.text()) + increment;

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
                //avoid do ite again
                element.removeAttr('data-grow-number');
            }

        }, intervalTime);

    });
}; 