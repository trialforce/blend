/**
 * Remove all invalid information, and make it if necessary.
 * @returns boolean false
 */
function removeDataInvalid()
{
    $('[data-invalid=1]').removeAttr('title').unbind('hover').removeAttr('data-invalid');
    $('.hint.danger').remove();

    return false;
}

function showValidateErrors(errors)
{
    var html = '';
    
    errors.forEach( function(item, index)
    {
        var str = '<strong>'+item.label + '</strong> |' + item.messages.join(',');
        
        var tabLabel = null;

        if (typeof getTabLabel == 'function')
        {
            tabLabel = getTabLabel(getTabFromId(item.name).attr('id'));
        }
        
        if (tabLabel)
        {
            str = '<strong>'+tabLabel+'</strong> : ' + str;
        }
        
        str += '<br/>';
        
        html+= str;
    });
   
    toast('Verifique o preenchimento dos campos: <br/><br/>'+html+'<br/>','danger');
}

function validatorRemoveInvalid()
{
    $('[data-invalid=1]').change(function () 
    {
        //remove data-invalid for element
        $(this).removeAttr('data-invalid');
        
        //remove hint
        $(this).parent().find('.hint.danger').fadeOut(500, function () 
        {
            $(this).remove();
        });
        
        var tab = $(this).parents('.tabBody .item');
        
        //if is inside tab and tab don't has any element with data-invalid
        //remove data-invalid from tab
        if ( tab.length > 0)
        {
            tab = tab.eq(0);
            var hasInvalidInside = tab.find('[data-invalid="1"]').length > 0 ;

            if ( !hasInvalidInside)
            {
                $('#'+tab.attr('id')+'Label').removeAttr('data-invalid');
            }
        }
    });
}

function validatorApplyInvalid()
{
    //make invalid
    $('[data-invalid=1]').each(function () 
    {
        var element = $(this);
        var title = element.attr('title');
        var tab = element.parents('.tabBody .item');
        
        //inside tab
        if ( tab[0])
        {
            $('#'+$(tab[0]).attr('id')+'Label').attr('data-invalid',1);
        }
        
        //don't create hint for hidden elements
        if (!element.is(':visible'))
        {
            return;
        }
        
        if (invalidHover == true)
        {
            if (title !== undefined)
            {
                element.hover(function ()
                {
                    var position = element.position().left + element.width()
                    var myDiv = $('<div class="hint danger">' + element.attr('title') + '</div>');
                    myDiv.css('left', position);

                    element.parent().append(myDiv);
                },
                function ()
                {
                    $(element).parent().find(".hint").remove();
                });
            }
        } 
        else
        {
            var position = element.position().left + element.width()
            var myDiv = $('<div class="hint danger">' + element.attr('title') + '</div>');
            myDiv.css('left', position);

            element.parent().append(myDiv);
        }
    });
}