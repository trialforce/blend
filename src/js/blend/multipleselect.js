
function multipleSelect()
{
    //nao faz se for android ou iphone
    if (isAndroid() || isIos())
    {
        return;
    }
    
    $('select[multiple] option').mousemove(function ()
    {
        return false;
    });
    
    $('select[multiple]').each( function ()
    {
        var element = $(this);
        var originalName = element.attr('name').replace('[','').replace(']','');
        element.find('option[value=""]').html('Multiplos selecionados...');
        
        element.find('option:selected').each( function()
        {
            var option = $(this);
            option.addClass('selected');
            option.removeAttr('selected');
            var select = option.parent();
            var value = option.attr('value');
            var valueName = originalName+'['+value+']';
            var input = '<input type="hidden" id="'+valueName+'"name="'+valueName+'" value="'+value+'" />';
            select.parent().append(input);
        });
        
        element.attr('data-original-name',originalName);
        element.attr('data-multiple',"multiple");
        element.removeAttr('id');
        element.removeAttr('name');
        element.removeAttr('multiple');
        
        element.focus( function() 
        {
            var pos = $(this).offset();
            $(this).css("position","absolute");
            $(this).offset(pos);
            $(this).attr("size","10");
        });
        
        element.blur( function() {
            $(this).css("position","static");
            $(this).attr("size","1");
            $(this).val('');
        });
        
        element.change( function()
        {
            var select = $(this);
            var option = select.find('option:selected');
            var value = option.attr('value');
            var originalName = select.attr('data-original-name');
            var valueName = originalName+'['+value+']';
            var selector = '#'+originalName+'\\['+value+'\\]';
            var optionSelected = $(selector).length > 0;
           
            if (!optionSelected)
            {
                select.parent().append('<input type="hidden" id="'+valueName+'"name="'+valueName+'" value="'+value+'" />');
                option.addClass('selected');
            }
            else
            {
                option.removeClass('selected');
                $(selector).remove();
            }
            
            ///select.val('');  
        });
    });
}