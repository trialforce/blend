
function selectTab(tabItemId)
{
    tabItemId = tabItemId.replace('#', '');
    var tab = $('#' + tabItemId).parents('.tab').eq(0);
    var tabId = tab.attr('id') ;

    //body
    $(tab).find('>.tabBody>.item').hide();
    $(tab).find('>.tabBody #' + tabItemId).show();

    //head
    $(tab).find('>.tabHead>.item').removeClass('selected');
    $(tab).find('>.tabHead #' + tabItemId + 'Label').addClass('selected');
    
    //show actions as tab-group needed
    $('.action-list li').hide();
    $('.action-list li[data-group=""]').show();
    $('.action-list li[data-group="'+tabItemId+'"]').show();

    return false;
}

function getTabFromId(id)
{
    return $('#'+id).parents('.tabBody .item');
}

function getTabLabel(tabId)
{
    if (typeof tabId == 'undefined')
    {
        return null;
    }
    
    return stripTags($('#'+tabId+'Label').html()).replace(/(\r\n|\n|\r)/gm, "");
}