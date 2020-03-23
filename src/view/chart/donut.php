<?php

/*
 * 
View.Chart.Donut = class extends jQuery.fn.init
{
    //https://medium.com/@heyoka/scratch-made-svg-donut-pie-charts-in-html5-2c587e935d72
    constructor(id, percent, extraClass, strokeWidth)
    {
        //xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns# xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg"
        super('<svg viewBox="0 0 42 42"></svg>');
        //super('svg');
        this.attr('id', id);
        this.addClass('chart-donut').addClass(extraClass);
        this.attr('width','100%');
        this.attr('height','100%');
        //this[0].setAttribute('viewBox','0 0 42 42');
        //this.attr('viewBox','0 0 42 42');

        this.addHole();
        this.addRing(strokeWidth);

        if (percent>0)
        {
            this.addSegment(percent,0,'#ce4b99',strokeWidth);
        }
    }

    refresh()
    {
        this.html(this.html());

        return this;
    }

    addHole()
    {
        let hole = new View.View('circle',null, null, 'donut-hole')
                .attr('cx',21)
                .attr('cy',21)
                .attr('r',15.91549430918954)
                .attr('fill', '#fff');

        this.append(hole);

        return this;
    }

    addRing(strokeWidth)
    {
        strokeWidth = isNull(strokeWidth) ? 3 : strokeWidth;
        let ring = new View.View('circle',null, null,'donut-ring')
                .attr('cx',21)
                .attr('cy',21)
                .attr('r',15.91549430918954)
                .attr('fill', 'transparent')
                .attr('stroke','#d2d3d4')
                .attr('stroke-width',strokeWidth);

        this.append(ring);
        this.refresh();

        return this;
    }

    addSegment(percent,offset, color, strokeWidth)
    {
        color = isNull(color)? '#ce4b99': color;
        percent = parseInt(percent);
        strokeWidth = isNull(strokeWidth) ? 3 : strokeWidth;
        let segment = new View.View('circle',null, null,'donut-segment')
                .attr('cx',21)
                .attr('cy',21)
                .attr('r',15.91549430918954)
                .attr('fill', 'transparent')
                .attr('stroke',color)
                .attr('stroke-width',strokeWidth)
                .attr('stroke-dasharray', percent+' ' + (100-percent))
                .attr('stroke-dashoffset',25-offset);

        this.append(segment);
        this.refresh();

        return this;
    }
};

