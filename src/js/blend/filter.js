

function filterRemove(element)
{
    var element = $(element);
    var parent = element.parent().parent();
    parent.find('input, select').attr('disabled', 'disabled');
    parent.hide('fast', function ()
    {
        parent.remove()
    });
}

function filterAdd(element)
{
    var element = $(element);
    var parent = element.parent();

    var filterBase = parent.find('.filterBase');
    var filterConditionValue = filterBase.find('.filterCondition').val();
    var clone = filterBase.clone().removeClass('filterBase');

    //add remove button
    clone.append('<i class="fa fa-trash trashFilter" onclick="filterTrash(this)"></i>');
    //clear cloned value
    clone.find('.filterInput').val('').removeAttr('data-on-press-enter-converted');
    //restore condition value (clone is not filling it)
    clone.find('.filterCondition').val(filterConditionValue);
    clone.addClass('filterBase-cloned');

    //show with animation
    clone.hide()
    parent.append(clone);
    clone.slideDown('fast');

    //process ajax fields
    dataAjax();

    return false;
}

function filterTrash(element)
{
    var element = $(element);
    var parent = element.parent();
    parent.slideUp('fast', function ()
    {
        $(this).remove()
    });
}

function filterChangeText(element)
{
    var val = $(element).val();

    var input = $(element).parent().find('.filterInput');

    if (val == 'nullorempty' || val == 'notnullorempty' || val == 'today')
    {
        input.val('').hide();
        element.addClass('fullWidth');
    } else
    {
        input.show();
        element.removeClass('fullWidth');
    }
}

function filterChangeInteger(element)
{
    var val = $(element).val();
    var input = $(element).parent().find('.filterInput');
    var inputFinal = $(element).parent().find('.final');

    if (val == 'between')
    {
        element.removeClass('fullWidth');
        input.show().addClass('filterInterval');
        inputFinal.removeAttr('disabled').add('filterInterval').show();
    } else if (val == 'nullorempty' || val == 'notnullorempty')
    {
        input.hide();
        element.addClass('fullWidth');
    } else
    {
        element.removeClass('fullWidth');
        input.show().removeClass('filterInterval');
        inputFinal.hide().attr('disabled', 'disabled');
    }
}

function filterChangeDate(element)
{
    var val = $(element).val();
    var prefix = $(element).attr('id').replace('Condition', '');
    var elValue = $(element).parent().find('.filter-date-date');
    var elValueFinal = $(element).parent().find('.final');
    var elValueMonth = $(element).parent().find('.filter-date-month');

    elValueMonth.attr('disabled', 'disabled').hide();
    elValue.removeAttr('disabled');

    if (val == 'birthmonth')
    {
        element.removeClass('fullWidth');

        elValueMonth.show().removeAttr('disabled');

        elValue.hide().val('').attr('disabled', 'disabled').removeClass('filterInterval');
        elValueFinal.hide().val('').attr('disabled', 'disabled').removeClass('filterInterval');
    } else if (val == 'nullorempty'
            || val == 'notnullorempty'
            || val == 'today'
            || val == 'yesterday'
            || val == 'tomorrow'
            || val == 'currentmonth'
            || val == 'pastmonth'
            || val == 'nextmonth'
            || val.indexOf('month-') == 0)
    {
        element.addClass('fullWidth');
        elValue.hide().val('');
        elValueFinal.hide().val('');
    } else if (val == 'between')
    {
        element.removeClass('fullWidth');
        elValue.show().addClass('filterInterval');
        elValueFinal.removeAttr('disabled').addClass('filterInterval').show();
    } else
    {
        element.removeClass('fullWidth');
        elValue.show().removeClass('filterInterval');
        elValueFinal.hide().attr('disabled', 'disabled').removeClass('filterInterval');
        elValue.value = '';
        elValueFinal.value = '';
    }
}

function filterChangeBoolean(element)
{
    var val = $(element).val();
    var input = $(element).parent().find('.filterInput');

    input.val('').hide();
    element.addClass('fullWidth');
}

function mountExtraFiltersLabel()
{
    var labels = $('#tab-filters-right .filterLabel');
    var filterCount = 0;

    for (var i = 1; i < labels.length; i++)
    {
        var label = $(labels[i]);
        var parent = label.parent();
        var inputs = parent.find('.filterInput');
        var hasValue = false;

        for (var x = 0; x < inputs.length; x++)
        {
            var input = $(inputs[0]);
            
            if (input.val())
            {
                var condition = input.parent().find('.filterCondition');
                
                if (condition.val())
                {
                    hasValue = true;
                }
            }
        }

        if (hasValue)
        {
            filterCount++;
        }
    }

    $('#filter-count').remove();
    $('#tab-filterLabel .tab-title').append(' <small class="search-tab-count" id="filter-count">(' + filterCount + ')</small>');
    
    var columnCount = $('.columns-holder .grid-addcolumn-field').length;
    
    $('#column-count').remove();
    $('#tab-columnLabel .tab-title').append(' <small class="search-tab-count" id="column-count">(' + columnCount + ')</small>');
    
    var groupCount = $('#tab-group .grid-addcolumn-field').length;
    
    $('#group-count').remove();
    $('#tab-groupLabel .tab-title').append(' <small class="search-tab-count" id="group-count">(' + groupCount + ')</small>');
    
    var saveCount = $('.grid-savedlist-holder .grid-addcolumn-field').length-1;
    
    $('#save-count').remove();
    $('#tab-saveLabel .tab-title').append(' <small class="search-tab-count" id="save-count">(' + saveCount + ')</small>');
    
    //update filters
    $('.filterCondition').change();
    //remmove empty option
    $('#advancedFiltersList #select-null-option').remove();
    $('.column-list-holder #select-null-option').remove();
    $('.column-list-holder #select-null-option').remove();
    
    var filters= $('#tab-filters-right .filterLabel');
     
    if (filters.length <= 1)
    {
         $('#filters-tooltip').html('');
        return;
    }
     
    var html = '';
     
    filters.each( function(idx) 
    {
         //jump header
        if(idx==0)
        {
            return;
        }
    
        var element = $(filters[idx]);
        var values = element.parent().find('.filterInput');
        var valuesTxt = '';
        
        values.each (function(idx2) 
        {
            var element2 = $(values[idx2]);
            console.log(element2);
            var value = element2.val();
            var condition = element2.parent().find('.filterCondition');
            var conditionVal = condition.val();
            var conditionText = condition.find('option:selected').text();
            console.log(condition);
            
            var text = element2.find('option:selected').text();
            
            value = text ? text:  value;
            
            //without condition it's not a applyed filter
            if (!conditionVal)
            {
                value = null;
            }

            if ( value && conditionText)
            {
                //valuesTxt += '['+value+']';
                valuesTxt += ' <i>['+conditionText +' - ' + value+']</i>';
            }
         });
         
         if (valuesTxt)
         {
             html += ' <strong>['+element.text()+']</strong> ';
         }
    });
    
    if (html)
    {
        html = 'Filtros aplicados: '+html;
        $('#filters-tooltip').html(html);
    }
}

function gridAddColumnRemove(element)
{
    var parent = $(element).parent();

    parent.hide('fast', function ()
    {
        parent.remove()
    });
}

function gridAddColumnUp(element)
{
    var element = $(element);
    var parent = element.parent();

    if (parent.not(':first-child'))
    {
        parent.prev().before(parent);
    }
}

function gridAddColumnDown(element)
{
    var element = $(element);
    var parent = element.parent();

    if (parent.not(':first-child'))
    {
        parent.next().after(parent);
    }
}

function gridClosePopupAndMakeSearch()
{
    popup('close', '#popupSearchField');
    $('#buscar').click();
    return false;
}