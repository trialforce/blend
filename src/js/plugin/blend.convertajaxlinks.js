/* global blend */
blend.convertAjaxLinks = {};
blend.plugins.push(blend.convertAjaxLinks);

blend.convertAjaxLinks.register = function ()
{
};

blend.convertAjaxLinks.start = function ()
{
    //links
    $("[data-ajax]").each(function ()
    {
        var element = $(this);
        var dataAjax = element.attr('data-ajax');
        var href = element.attr('href');
        var disabled = element.attr('disabled');
        element.removeAttr('data-ajax');
        
        //if is an outside link do not use ajax system
        if (href && (href.indexOf('http://') === 0 || href.indexOf('https://') === 0) )
        {
            href = null;
        }

        if (href && dataAjax)
        {
            if (disabled == 'disabled')
            {
                element.click(function ()
                {
                    toast('Ação desabilitada!');
                    return false;
                });
            }
            else if (dataAjax === 'noFormData')
            {
                element.click(function () {
                    return g(href, '');
                });
            } 
            else
            {
                element.click(function () {
                    return p(href);
                });
            }
        }
    }
    );
};

//only for backward compatibility
function convertAjaxLinks()
{
    blend.convertAjaxLinks.start();
}