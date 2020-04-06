<?php

/* 
View.Chart.BarVertical = class extends View.Div
{
    constructor(id, percent, extraClass)
    {
        super(id);
        this.addClass('chart-bar-vertical').addClass(extraClass);

        if ( !isNull(percent))
        {
            percent = toFloat(percent.toFixed(2))
            this.addSegment('chart-bar-vertical-segment-'+id,percent);
            this.addLabel('chart-bar-vertical-label-'+id,percent);
        }
    }

    addSegment(id, percent, offset)
    {
        percent = percent > 100? 100: percent;
        let bar = new View.Div(id)
                .addClass('chart-bar-vertical-segment')
                .css('height',percent+'%');

        if ( offset> 0 )
        {
            bar.css('bottom', offset+'%');
        }

        this.append(bar);

        return bar;
    }

    addLabel(id,percent)
    {
        let label = new View.Div(id)
                .addClass('chart-bar-vertical-label')
                .attr('title',percent+'%')
                .html(percent+'%');

        this.append(label);

        return label;
    }
};*/