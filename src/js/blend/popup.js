 
/**
 * Control all popup behavior.
 *
 * @param action (show, opem, close, destroy).
 * @param selector popups id.
 * @returns Boolean false.
 */
function popup(action, selector)
{
    if (selector + "" === 'undefined')
    {
        selector = '';
    }

    var element = $('.popup' + selector);

    if (action === 'show' || action === 'open')
    {
        $('.makePopupFade').addClass('popupFaded');
        $('body').css('overflow','hidden');
        setFocusOnFirstField();

        element.fadeIn(600);
    } 
    else if (action === 'close' || action === 'hide')
    {
        $('body').css('overflow','auto');
        $('.makePopupFade').removeClass('popupFaded');

        element.find('.inner').animate(
                {
            opacity: 0,
            width: 0,
            minWidth: 0,
            height: 0,
        }, 500, function () 
        {
            element.hide();
            //restore style
            $('.inner').removeAttr('style');
        });
    }
    else if (action === 'destroy')
    {
        $('body').css('overflow','auto');
        //remake popup fade
        $('.makePopupFade').removeClass('popupFaded');
        //remove any action-list that has popup
        $('.action-list-popup').remove();

        //coll animantion
        element.find('.inner').animate({
            opacity: 0,
            width: 0,
            minWidth: 0,
            height: 0,
        }, 300, function ()
        {
            element.remove();
        });
    } 
    else if (action === 'maximize')
    {
        element.find('.inner')
                .css('position', 'fixed')
                .css('left', '50%')
                .css('marginLeft', ($('.inner').width() / 2) * -1);

        element.find('.inner').animate({
            top: 0,
            left: 0,
            margin: 0,
            width: "100%",
            height: "100%",
        }, 500, function () 
        {
            element.find('.body').addClass('maximized');
        });
    }

    return false;
}
