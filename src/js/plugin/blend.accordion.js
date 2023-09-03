/* global blend */

/**
 * Simple accordion
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
    let element = document.getElementById(id);
    let isOpen = element.classList.contains('accordion-open');

    if (isOpen)
    {
        blend.accordion.close(id);
    }
    else
    {
        blend.accordion.open(id);
    }

    return false;
};

blend.accordion.close = function(id)
{
    let element = document.getElementById(id);
    element.classList.remove('accordion-open');
    
    return false;
};

blend.accordion.open = function(id)
{
    let element = document.getElementById(id);
    let onOpen = element.getAttribute('data-on-open');

    //call on open just one time
    if (onOpen)
    {
        eval(onOpen);
        element.removeAttribute('data-on-open')
    }

    element.classList.add('accordion-open');
    
    return false;
};