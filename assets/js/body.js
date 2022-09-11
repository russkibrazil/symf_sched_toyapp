let corTexto = $('input[name=\"baseInputsCor-corTexto\"]').val();
let corInput = $('input[name=\"baseInputsCor-corInput\"]').val();
let corLabel = $('input[name=\"baseInputsCor-corLabel\"]').val();
let corBoxes = $('input[name=\"baseInputsCor-corBoxes\"]').val();
let corFundo = $('input[name=\"baseInputsCor-corFundo\"]').val();

switch (corFundo)
{
    case '#000000':
        $('body').css('background-color', corFundo);
        $('label').css('color', '#C8C8C8');
        $('h1').css('color', '#C8C8C8');
        $('p').css('color', '#646464');
        $('th').css('color', '#C8C8C8');
        $('td').css('color', '#C8C8C8');
        $('small').css('color', '#AAAAAA');
        $('nav')
            .removeClass('bg-light')
            .removeClass('navbar-light')
            .addClass('bg-dark')
            .addClass('navbar-dark');
        $('li').css('color', '#646464');

    break;

    case '':
    case '#ffffff':

    default:
        $('body').css('background-color', corFundo);
        $('label').css('color', corLabel);
        $('h1').css('color', corLabel);
        $('p').css('color', corLabel);
        $('th').css('color', corLabel);
        $('td').css('color', corLabel);
        $('small').css('color', corTexto);
        $('li').css('color', corLabel);
    break;
}

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
  })
