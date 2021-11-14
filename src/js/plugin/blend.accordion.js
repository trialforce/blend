/* global blend */

/**
 * Animated number from zero to the real number
 * 
 */

blend.accordion = {};
blend.plugins.push(blend.accordion); 

blend.accordion.register = function()
{
};

blend.accordion.start = function()
{
};

blend.accordion.toggle = function(id)
{
    var element = document.getElementById(id);
    element.classList.toggle('accordion-open');
    
    return false;
};

blend.accordion.close = function(id)
{
    var element = document.getElementById(id);
    element.classList.remove('accordion-open');
    
    return false;
};

blend.accordion.open = function(id)
{
    var element = document.getElementById(id);
    element.classList.add('accordion-open');
    
    return false;
};