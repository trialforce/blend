 
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

    if (element.length == 0)
    {
        return false;
    }

    if (action === 'show' || action === 'open')
    {
        $('.makePopupFade').addClass('popupFaded');
        $('body').css('overflow','hidden');

        element.fadeIn(600, function(){ setFocusOnFirstField(); });
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
        element.find('.inner').css('left', 0);

        //if is minimized only restore
        if (element.hasClass('minimized'))
        {
            element.removeClass('minimized')
        }
        else
        {
            element.removeClass('minimized')
            element.toggleClass('maximized');
        }
    }
    else if (action === 'minimize')
    {
        element.removeClass('maximized')
        element.toggleClass('minimized');

        //add left margin
        let extraStep = $('.popup.minimized').length - 1;

        if (extraStep > 0)
        {
            let extraWidth = $('.popup.minimized').eq(0).find('.header').width();
            let extraWidthPx = (extraWidth * extraStep) + 'px';
            element.find('.inner').css('left', extraWidthPx);
        }
    }

    return false;
}
