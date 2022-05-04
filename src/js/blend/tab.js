
function selectTab(tabItemId, callback )
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

    //if tab alreaddy has content don't call callback
    var hasContent = $( '#'+tabItemId + ' *').length > 0
   
    if ( !hasContent && typeof callback == 'function')
    {
        callback();
        //$('#'+tabItemId+'Label').attr('onclick', "return selectTab('"+tabItemId+"')");
    }

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