
var timerTypeWatch = 0;
var body = $('body')[0];
//close combos on click outside
if(body)
{
    body.addEventListener("click", function()
    {
        var currentElement = $(document.activeElement);
        var isCombo = currentElement.hasClass('labelValue');

        if ( !isCombo)
        {
            //close all combos
            $('.dropDownContainer').slideUp(50);
        }
        //comboHideDropdown(id);
    }
    , false);
}

function comboInputClick(id,eThis)
{
    //close other popups
    $('.dropDownContainer').not('#'+id).slideUp(50);
    //open this popup
    comboToggleDropdown(id);
}

function comboToggleDropdown(id)
{
    var element = $('#dropDownContainer_' + id);

    if ( $('#labelField_'+id).attr('readonly')  )
    {
        comboHideDropdown(id);
        return;
    }
    
    if(element.is(':visible'))
    {
        comboHideDropdown(id);
    }
    else
    {
        comboShowDropdown(id);
    }
}

function comboShowDropdown(id)
{
    var element = $('#dropDownContainer_' + id);

    //realonly, avoid open dropdown
    if (element.is('[readonly]'))
    {
        comboHideDropdown(id);
        return false;
    }

    //mininum width
    element.css('min-width', $('#labelField_' + id).width() + 'px');
    element.slideDown(30);
}

function comboHideDropdown(id)
{
    $('#dropDownContainer_' + id).slideUp(30);
}

function comboDoSearch(id)
{
    eval($('#labelField_' + id).data('change'));
}

function comboSelectItem(comboId, value, label, eThis)
{
    //remove selected from other tr's
    $(eThis).parent().find('tr').removeClass('selected');
    //mark this as select
    $(eThis).addClass('selected');
    
    //change the value and trigger onchange
    var element = $('#' + comboId);
    element.val(value);
    element.trigger('change');

    //change the value of label field
    var elementLabel = $('#labelField_' + comboId);
    elementLabel.val(label);
    
    //open the dropdrown table
    comboHideDropdown(comboId);  
}

function comboTypeWatch(element, event, callback, ms)
{
    var parente = $(element).parent();
    var id = parente.find('.inputValue').attr('id');

    if ($('#labelField_' + id).is('[readonly]'))
    {
        return false;
    }

    //TAB, is called when enter input, will make work normally, and clear timeout
    if (event.keyCode == 9)
    {
        clearTimeout(timerTypeWatch);
        return true;
    }

    //down
    if (event.keyCode === 40)
    {
        comboShowDropdown(id);

        var next = parente.find('table tr.selected').next();

        if ( next.length > 0 )
        {
            parente.find('table tr.selected').removeClass('selected');
            next.addClass('selected');
        }
        else
        {
            parente.find('table tr').eq(0).addClass('selected');
        }

        return false;
    }
    //up
    else if (event.keyCode === 38)
    {
        comboShowDropdown(id);
        var prev = parente.find('table tr.selected').prev();
        parente.find('table tr.selected').removeClass('selected');
        prev.addClass('selected');

        return false;
    }
    //enter
    else if (event.keyCode === 13)
    {
        //make the selection
        parente.find('table tr.selected').click();
        comboHideDropdown(id);

        return false;
    } 
    else
    {
        clearTimeout(timerTypeWatch);
        timerTypeWatch = setTimeout(callback, ms);
    }
}

function comboModelClick(idInput)
{
    var input = $('#'+idInput);
    var value = input.val();
    var page = input.data('model');
    
    p(page+'/editarpopup/'+value+'&idInput='+idInput);
}

function comboModelClose(idInput)
{
    var iframe = document.getElementById("edit-popup-iframe");
    var doc = iframe.contentWindow.document;
    var editEditId = $(doc).find('#id').val();
    
    //closes popup
    popup('destroy','#edit-popup');
    
    //fill the value on hidden field
    $('#'+idInput).val(editEditId);
    //call the ajax action to fill dropdown, put hideCombo, to make it work properly
    var code = $('#labelField_'+idInput).data('change');
    //run the code fill label value
    eval(code.replace('mountDropDown','fillLabelByValue'));
    //fill dropdown
    setTimeout(function(){eval(code.replace(idInput,idInput+'?hideCombo=true'));},500);
}
