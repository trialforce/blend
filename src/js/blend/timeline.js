/* global blend */

blend.timeline = {};
blend.plugins.push(blend.timeline);

blend.timeline.start = function()
{
    let timelines = document.querySelectorAll('.timeline');

    timelines.forEach(blend.timeline.create);
};

blend.timeline.create = function(timeline)
{
    if (timeline.dataset.timelineFilterCreated)
    {
        return;
    }

    let items = timeline.querySelectorAll('.timeline-item');

    if (items.length == 0)
    {
        return;
    }

    timeline.dataset.timelineFilterCreated = 'true';
    timeline.dataset.timelineSearch = '';
    timeline.dataset.timelineType = '';

    let filter = document.createElement('div');
    filter.className = 'timeline-filter';

    let search = document.createElement('input');
    search.type = 'search';
    search.className = 'timeline-filter-search';
    search.placeholder = 'Pesquisar na linha do tempo';
    search.setAttribute('aria-label', 'Pesquisar na linha do tempo');
    search.addEventListener('input', blend.timeline.search);
    filter.append(search);

    let typeList = document.createElement('div');
    typeList.className = 'timeline-filter-types';
    typeList.setAttribute('aria-label', 'Filtrar por tipo');
    let types = [];

    items.forEach(function(item)
    {
        let icon = item.querySelector('.timeline-item-icon');
        let type = icon ? icon.innerHTML.trim() : '';
        item.dataset.timelineType = type;

        if (!type || types.indexOf(type) >= 0)
        {
            return;
        }

        types.push(type);
        let button = icon.cloneNode(true);
        button.classList.add('timeline-filter-type');
        button.dataset.timelineType = type;
        button.title = 'Filtrar por este tipo. Clique novamente para remover o filtro.';
        button.setAttribute('role', 'button');
        button.setAttribute('tabindex', '0');
        button.setAttribute('aria-label', 'Filtrar por este tipo');
        button.setAttribute('aria-pressed', 'false');
        button.addEventListener('click', blend.timeline.selectType);
        button.addEventListener('keydown', blend.timeline.selectTypeByKeyboard);
        typeList.append(button);
    });

    if (types.length > 0)
    {
        filter.append(typeList);
    }

    timeline.insertBefore(filter, items[0]);
};

blend.timeline.search = function(event)
{
    let timeline = event.currentTarget.closest('.timeline');
    let search = blend.timeline.normalize(event.currentTarget.value.trim());
    timeline.dataset.timelineSearch = search.length >= 3 ? search : '';
    blend.timeline.filter(timeline);
};

blend.timeline.selectType = function(event)
{
    let button = event.currentTarget;
    let timeline = button.closest('.timeline');
    let selectedType = button.getAttribute('aria-pressed') == 'true' ? '' : button.dataset.timelineType;
    let buttons = timeline.querySelectorAll('.timeline-filter-type');

    buttons.forEach(function(typeButton)
    {
        let isSelected = typeButton.dataset.timelineType == selectedType;
        typeButton.classList.toggle('active', isSelected);
        typeButton.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
    });

    timeline.dataset.timelineType = selectedType;
    blend.timeline.filter(timeline);
};

blend.timeline.selectTypeByKeyboard = function(event)
{
    if (event.key == 'Enter' || event.key == ' ')
    {
        event.preventDefault();
        event.currentTarget.click();
    }
};

blend.timeline.filter = function(timeline)
{
    let search = timeline.dataset.timelineSearch;
    let selectedType = timeline.dataset.timelineType;
    let items = timeline.querySelectorAll('.timeline-item');

    items.forEach(function(item)
    {
        let matchesSearch = !search || blend.timeline.normalize(item.textContent).includes(search);
        let matchesType = !selectedType || item.dataset.timelineType == selectedType;
        item.hidden = !matchesSearch || !matchesType;
    });
};

blend.timeline.normalize = function(value)
{
    // Remove os acentos para comparar o texto sem diferenciar acentuação.
    return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
};
