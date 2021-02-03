
function toolTip(selector, message)
{
    var element = $(selector);
    //remove title
    element.attr('title', '').removeAttr('title');
    //var parent = element.parent();
    var tagName = element.prop('tagName');
    
    if (tagName== 'input'|| tagName == 'select')
    {
        var toolTipHolder = $(document.createElement('div'));
        toolTipHolder.addClass('tooltip');
        toolTipHolder.append('<span class="tooltiptext">' + message + '</span>');
        element.after(toolTipHolder);
        toolTipHolder.prepend(element);
    }
    else
    {
        element.addClass('tooltip');
        element.append('<span class="tooltiptext">' + message + '</span>');
    }
}

/**
 * Make the default tooltip for all elements that has title
 * 
 * @returns void
 */
function defaultTooltTipForAllTitle()
{
    $('[title]').each(function()
    {
        toolTip(this, $(this).attr('title'));
    });
}