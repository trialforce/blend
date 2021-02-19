/* global blend */

/**
 * Animated number from zero to the real number
 * 
 */

blend.grownumber = {};
blend.grownumber.defaultTime = 1000; //ms
blend.plugins.push(blend.grownumber); 

blend.grownumber.register = function()
{
    console.log('abc1');
};

blend.grownumber.start = function()
{
    console.log('abc2');
    var elements = $('[data-grow-number]');
    
    elements.each (function(idx)
    {
        var element = $(elements[idx]);
        console.log(element.html());
        var type = element.data('grow-number');
        var valueOriginal = parseInt(element.text());
        var intervalTime = blend.grownumber.defaultTime / valueOriginal;
        
        //element.attr('data-value', value);
        
        if (type == 'int')
        {
            element.html('0');
        }
        
        var myInterval = setInterval( function()
        {
            console.log();
            var newValue = parseInt(element.text())+1;
            if (newValue < valueOriginal)
            {
                element.html(newValue);
            }
            
            if (newValue == valueOriginal)
            {
                element.html(newValue);
                clearInterval(myInterval);
                element.data('grow-number', '');
            }
            
        }, intervalTime);
        
    });
}; 