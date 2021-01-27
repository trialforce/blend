function preparaVer()
{
    //remove botão de adicionar
    $('#btnInsert').hide().data('hide-by-see');
    //remove filtros
    $('#savedListGroup').hide();

    //adiciona botão de voltar, caso necessáiro
    if ($('#btnVoltar').length == 0)
    {
        $('#btnGroup').append('<button id=\"btnVoltar\" class=\"btn\" onclick=\"history.back(1);\" type=\"button\" title=\"Volta para a listagem!\" ><i class=\"fa fa-arrow-left\"></i><span class=\"btn-label\"> Voltar</span></button>');
    }

    //esconde botões de adicionar de mestre-detalhe
    $('[id^="btnAdd"]').hide().data('hide-by-see');
    //esconde botão de salvar
    $('#btnSalvar').hide().data('hide-by-see');

    $('.fa-trash-o,.fa-trash,.fa-edit').each(
            function ()
            {
                var parent = $(this).parent();
                
                if (parent.prop('tagName') == 'A')
                {
                    parent.data('hide-by-see');
                    parent.hide();
                }
            }
    );
    
    //remove clique duplo
    $('[ondblclick]').removeAttr('ondblclick');

    $('input, select, textarea').not('[data-see-not-disable=1]').attr('disabled', 'disabled');

    //add support for autocomplete/combo input
    //TODO avoid setimeout
    setTimeout(function () {
        $('.labelValue').attr('disabled', 'disabled');
        $('input, select, textarea').not('[data-see-not-disable=1]').attr('disabled', 'disabled');
    }, 200);
}