/* global blend */
blend.onpressenter = {};
blend.plugins.push(blend.onpressenter);

blend.onpressenter.parse = function(element)
{
    //mark as converted
    var myEvent = element.attr('data-on-press-enter');
    element.attr('data-on-press-enter-converted', "1");
    element.keydown(
        function (e)
        {
            if (e.keyCode == "13" && !e.shiftKey)
            {
                eval(myEvent);
                e.preventDefault();
            } else
            {
                return true;
            }
        }
    );
};

blend.onpressenter.start = function ()
{
    $("[data-on-press-enter]").each(function ()
    {
        var element = $(this);

        //get out if converted
        if (element.attr('data-on-press-enter-converted') == "1")
        {
            return;
        }

        blend.onpressenter.parse(element);
    });
};
