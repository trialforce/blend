var timerTypeWatch = 0;
comboStart();

/**
 * Start the combo script and allow to close combo if clicked outside
 */
function comboStart()
{
    let body = $('body')[0];
    //close combos on click outside
    if(!body)
    {
        return;
    }

    body.addEventListener("click", function()
    {
        let currentElement = $(document.activeElement);
        let isCombo = currentElement.hasClass('labelValue');

        if ( !isCombo)
        {
            //close all combos
            $('.dropDownContainer').slideUp(50);
        }
        //comboHideDropdown(id);
    }, false);
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

/**
 * Show the dropdown table
 * @param id
 * @returns {boolean}
 */
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

/**
 * Hide the drop down table
 * @param id
 */
function comboHideDropdown(id)
{
    if ( id )
    {
        $('#dropDownContainer_' + id).slideUp(30);
    }
    else
    {
        $('.dropDownContainer').slideUp(30);
    }
}

/**
 * Execute the search url
 * @param id
 */
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

    comboValue(comboId, value, label);
    //trigger onchange
    $('#' + comboId).trigger('change');
    //open the dropdrown table
    comboHideDropdown(comboId);  
}

/**
 * Fill the value on hidden field and label
 *
 * @param id string
 * @param value string
 * @param label string
 */
function comboValue(id, value, label)
{
    //change the value of hidden input
    let element = $('#' + id);
    element.val(value);
    element.attr('value', value);

    if ( typeof label != 'undefined' && label)
    {
        //change the value of label field
        let elementLabel = $('#labelField_' + id);
        elementLabel.val(label);
    }
}

/**
 * Make the typewatch of element
 * @param element js element
 * @param event js event
 * @param callback normally comboDoSearch
 * @param ms normally 700
 * @returns {boolean}
 */
function comboTypeWatch(element, event, callback, ms)
{
    let idCombo = $(element).data('invalid-id');
    let parente = $(element).parent();
    let id = parente.find('.inputValue').attr('id');

    //if is readonly does nothing
    if ($('#labelField_' + id).is('[readonly]'))
    {
        return false;
    }

    //TAB or ESC is called when enter input, will make work normally, and clear timeout
    if (event.keyCode == 9 || event.keyCode == 27)
    {
        clearTimeout(timerTypeWatch);
        comboHideDropdown();
        return true;
    }

    //down
    if (event.keyCode === 40)
    {
        comboShowDropdown(id);

        let next = parente.find('table tr.selected').next();

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
        let prev = parente.find('table tr.selected').prev();
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
        comboValue(idCombo, '');
        clearTimeout(timerTypeWatch);
        timerTypeWatch = setTimeout(callback, ms);
    }
}

function comboModelClick(idInput)
{
    let input = $('#'+idInput);
    let value = input.val();
    let page = input.data('model');
    
    p(page+'/editarpopup/'+value+'&idInput='+idInput);
}

function comboModelClose(idInput)
{
    let iframe = document.getElementById("edit-popup-iframe");
    let doc = iframe.contentWindow.document;
    let editEditId = $(doc).find('#id').val();
    
    //closes popup
    popup('destroy','.popup.form');
    comboValue(idInput, editEditId);
    //call the ajax action to fill dropdown, put hideCombo, to make it work properly
    let code = $('#labelField_'+idInput).data('change');
    //run the code fill label value
    eval(code.replace('mountDropDown','fillLabelByValue'));
    //fill dropdown
    setTimeout(function(){eval(code.replace(idInput,idInput+'?hideCombo=true'));},500);
}
