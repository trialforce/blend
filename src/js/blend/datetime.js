
function dateTimeInputMobile()
{
    $('.dateinput').not('[readonly]').each(function ()
    {
        $(this).mask('99/99/9999');
    });

    $('.datetimeinput').not('[readonly]').each(function ()
    {
        $(this).mask('99/99/9999 99:99:99');
    });

    $('.timeinput').not('[readonly]').each(function ()
    {
        $(this).mask('99:99:99');
    });
}

function dateTimeInputDesktopOnChange(dp,input)
{
    //console.log(dp, input);
}

function dateTimeInputDesktopOnShow(currentTime, input)
{
    //console.log(currentTime, input);
}

//https://xdsoft.net/jqplugins/datetimepicker/
function dateTimeInputDesktop()
{   
    $('.dateinput').not('[readonly]').each(function()
    {
        $(this).datetimepicker({
            onChangeDateTime:dateTimeInputDesktopOnChange,
            onShow: dateTimeInputDesktopOnShow,
            id: 'dialog-date-'+$(this).attr('id'),
            className: 'dialog-date',
            timepicker: false,
            defaultSelect: false,
            validateOnBlur: false,
            closeOnDateSelect: true,
            mask: true,
            allowBlank: true,
            format: 'd/m/Y',
            scrollMonth:false,
            scrollTime:false,
            scrollInput:false,
            step: 15
        });
    });

    $('.datetimeinput').not('[readonly]').each(function()
    {
        $(this).datetimepicker(
        {
            onChangeDateTime:dateTimeInputDesktopOnChange,
            onShow: dateTimeInputDesktopOnShow,
            id: 'dialog-datetime-'+$(this).attr('id'),
            className: 'dialog-datetime',
            format: 'd/m/Y H:i:s',
            mask: true,
            defaultSelect: false,
            validateOnBlur: false,
            closeOnDateSelect: true,
            allowBlank: true,
            scrollMonth:false,
            scrollTime:false,
            scrollInput:false,
            step: 15
        });
    });

    $('.timeinput').not('[readonly]').datetimepicker(
    {
        onChangeDateTime:dateTimeInputDesktopOnChange,
        onShow: dateTimeInputDesktopOnShow,
        id: 'dialog-time-'+$(this).attr('id'),
        className: 'dialog-time',
        format: 'H:i:s',
        defaultSelect: false,
        datepicker: false,
        validateOnBlur: false,
        closeOnDateSelect: true,
        allowBlank: true,
        mask: true,
        step: 15,
        scrollMonth:false,
        scrollTime:false,
        scrollInput:false,
    });
    
    $('.dateinput,.datetimeinput,.timeinput').on('blur', function () 
    {
        markFormChanged();
    });
    
    //avoid open the keyboard
    $('.dateinput,.datetimeinput,.timeinput').on('click', function () 
    {     
        if (isCellPhone())
        {
            $(this).blur();
        }
    });
    
    //close if click in background
    if (isCellPhone())
    {
        $('.xdsoft_datetimepicker.xdsoft_noselect').click(function()
        {
            $(this).hide();
            event.preventDefault();
            return false;
        });
    }
}

function dateTimeInputFallBackNative()
{
    //fallback to default date of browser
    $('.dateinput').each(function ()
    {
        var element = $(this);
        var value = element.val();

        //don't format
        if (value.indexOf('/') > 0)
        {
            var date = value.split('/').reverse().join('-');
            element.val(date);
        }

        element.prop('type', 'date');
    });

    $('.datetimeinput').each(function () 
    {
        var element = $(this);
        var value = element.val();

        //don't format
        if (value.indexOf('T') < 0)
        {
            var datetime = value.split(' ');
            var date = datetime[0].split('/').reverse().join('-');
            element.val(date + 'T' + datetime[1]);
        }

        element.prop('type', 'datetime-local');
    });
}

function dateTimeInput()
{ 
    if ( isIos() )
    {
        dateTimeInputMobile();
    } 
    else if (isAndroid())
    {
        dateTimeInputFallBackNative();
    }
    else if (typeof $().datetimepicker === 'function')
    {
        dateTimeInputDesktop();
    }
    else
    {
        dateTimeInputFallBackNative();
    }
}