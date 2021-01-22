/**
 * Mark for as changed
 * @returns false
 */
function markFormChanged()
{
    //mark form as changed
    $('#formChanged').val(1);
    //enable all save button
    $('.save').removeAttr('disabled');
    //disable advice flag
    formChangedAdvice = false;

    return false;
}

function showFormChangedAdvice()
{
    toast('Os dados foram modificados! Tem certeza que quer realizar esta ação?');
    formChangedAdvice = true;

    event.preventDefault();
    //event.stopImmediatePropagation();

    return false;
}

function preparaVer()
{
    //remove botão de adicionar
    $('#btnInsert').hide().data('hide-by-see');
    //remove filtros
    $('#savedListGroup').hide();

    //adiciona botão de voltar, caso necessáiro
    if ($('#btnVoltar').length == 0)
    {
        $('#btnGroup').append('<button id=\"btnVoltar\" class=\"btn\" onclick=\"history.back(1);\" type=\"button\" title=\"Volta para a listagem!\" data-form-changed-advice=\"1\"><i class=\"fa fa-arrow-left\"></i><span class=\"btn-label\"> Voltar</span></button>');
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