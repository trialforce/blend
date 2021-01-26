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