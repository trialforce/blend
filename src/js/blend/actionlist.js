/* global blend */
var actionList = {};
blend.actionList = {};
blend.plugins.push(blend.actionList);

blend.actionList.register = function ()
{
};

blend.actionList.start = function ()
{
    actionList.restore();
};

actionList.toggle = function()
{
    if ( $('html').hasClass('action-list-open'))
    {
        actionList.close();
    }
    else
    {
        actionList.open();
    }
};

actionList.restore = function()
{
    if ( $('.action-list-toogle').is(':visible') && !isCellPhone() )
    {
        var wasOpen = localStorage.getItem('action-list-open') == 1;
    
        if ( wasOpen )
        {
            actionList.open();
        }
        else
        {
            actionList.close();
        }
    }
    else
    {
        $('html').removeClass('action-list-open');
    }
};

actionList.open = function()
{
    $('.blend-floating-menu').removeClass('open');
    $('html').removeClass('todo-open');
    $('html').addClass('action-list-open');
    localStorage.setItem('action-list-open', 1);
};

actionList.close = function()
{
    $('html').removeClass('action-list-open');
    localStorage.setItem('action-list-open', 0);
    
    return false;
};