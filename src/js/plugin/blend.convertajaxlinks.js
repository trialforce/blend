/* global blend, b */
blend.convertAjaxLinks = {};
blend.plugins.push(blend.convertAjaxLinks);

blend.convertAjaxLinks.start = function ()
{
    var baseUrl = b('base').attr('href');
    
    //links
    $("[data-ajax]").each(function ()
    {
        var element = $(this);
        var dataAjax = element.attr('data-ajax');
        var href = element.attr('href');
        var disabled = element.attr('disabled');
        element.removeAttr('data-ajax');
        
        //if is an outside link do not use ajax system
        if (href && href.startsWith('http') && !href.startsWith(baseUrl))
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