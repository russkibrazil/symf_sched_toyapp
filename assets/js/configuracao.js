//https://github.com/gka/chroma.js/blob/master/docs/src/index.md
var $collectionHolder;
var $addHoraButton = $('<button type="button" class="btn btn-primary addSvcBtn">Adicionar</button>');
var $adicionarBtn = $('<div class="mt-2 mb-4"></div>').append($addHoraButton);
var $colorPickerFundo = $('#empresa_corFundo');
var w3color = require('chroma-js');

alternaRangeBloqueioVisible(0);

$(function () {
    $collectionHolder = $('#horarioTrabalho');
    $collectionHolder.find('.itemHorarioTrabalho .form-row').each( function () {
        addHtFormDeleteLink($(this));
    })
    $collectionHolder.append($adicionarBtn);
    $addHoraButton.on('click', function (e) {
        addHtForm($collectionHolder, $adicionarBtn);
    });
    $colorPickerFundo.on('change', function(e) {
        var cor = w3color($('#empresa_corFundo').val());
        let estrat = $('#empresa_estrategiaGeracaoCor').val();
        var cor1 = 0, cor2 = 0, cor3 = 0, cor4 =0;
        if (~isNaN(cor.get('hsv.h')))
        {
            switch (estrat) {
                case 'MONO':
                  let v = geraCoresMono(cor);
                  cor1 = v[0]; cor2 = v[1]; cor3 = v[2]; cor4 = v[3];
                  break;

                default:
                  break;
            }
            $('body').css('background-color', cor.hex());
            $('label').css('color', cor4);
            $('h1').css('color', cor4);
            $('p').css('color', cor1);
        }
        else
        {
            $('body').css('background-color', $('#empresa_corFundo').val());
            $('label').css('color', '#C8C8C8');
            $('h1').css('color', '#C8C8C8');
            $('p').css('color', '#646464');
        }
    });

    $('#empresa_intervaloBloqueio').on('change', function(e) {
        alternaRangeBloqueioVisible('fast');
    });

    $('#empresa_qtdeBloqueio').on('input', function(e) {
       $('#range_qtdeBloqueio_value').text($('#empresa_qtdeBloqueio').val());
    });

    $('#empresa_qtdeAnalise').on('input', function(e) {
        $('#range_qtdeAnalise_value').text($('#empresa_qtdeAnalise').val());
     });

    $('#empresa_atrasosTolerados').on('input', function(e) {
       $('#range_atrasosTolerados_value').text($('#empresa_atrasosTolerados').val());
    });

    $('#empresa_cancelamentosTolerados').on('input', function(e) {
        $('#range_cancelamentosTolerados').text($('#empresa_cancelamentosTolerados').val());
     });
});

function addHtForm($collectionHolder, $newLinkLi, $id = Date.now().toString())
{
    let prototype = $collectionHolder.data('prototype');
    let newForm = prototype;

    newForm = newForm.replace(/__name__/g, 'div' + $id);

    let diaSemana = $('<div class="form-group col"></div>')
        .append('<label>Dia da Semana</label>')
        .append(/<select.+(?=diaSemana).+<\/select>/g.exec(newForm)[0])
    ;
    let horaInicio = $('<div class="form-group col"></div>')
        .append('<label>Início</label>')
        .append(
            $('<div class="form-inline"></div>')
                .append(/<select.+(?=horaInicio).+<\/select>/g.exec(newForm)[0])
        )
    ;
    let horaFim = $('<div class="form-group col"></div>')
        .append('<label>Fim</label>')
        .append(
            $('<div class="form-inline"></div>')
                .append(/<select.+(?=horaFim).+<\/select>/g.exec(newForm)[0])
        )
    ;

    let $formRow = $('<div class="form-row"></div>')
        .append(diaSemana)
        .append(horaInicio)
        .append(horaFim)
    ;

    let $newFormLi = $('<div class="itemHorarioTrabalho my-1"></div>').append($formRow);
    addHtFormDeleteLink($formRow);
    $newLinkLi.before($newFormLi);
}

function addHtFormDeleteLink($tagFormLi)
{
    let $removeFormButton = $('<button type="button" class="btn btn-danger btn-sm">Apagar</button>');
    $tagFormLi.append($removeFormButton);

    $removeFormButton.on('click', function(e) {
        $tagFormLi.closest('div.itemHorarioTrabalho').remove();
    });
}

function geraCoresMono(entrada)
{
    var c1 , c2, c3, c4, t = entrada;
    if (entrada.get('hsv.s') >= 0.4 )
    //se a cor tem a saturacao e brilho altos. Se tiver a saturação baixa a ponto de "virar" os valores, os resultados devem estar em módulo
    {
      t = t.set('hsv.s', entrada.get('hsv.s') -0.3);
      c2 = t;
      c3 = t;
    }
    else
    {
        //t = t.saturate(0.3);
        t = t.set('hsv.s', entrada.get('hsv.s') +0.3);
        c2 = t;
        c3 = t;
    }

    if (entrada.get('hsv.v') < 0.1)
    {
        //c2.brighten(0.1 - entrada.get('hsv.v'));
        c2 = c2.set('hsv.v', 0.2)
    }
    else
    {
        //c2.brighten(0.1 - (entrada.get('hsv.v') / 10));
        c2 = c2.set('hsv.v', entrada.get('hsv.v') + (0.1 - entrada.get('hsv.v')/10));
    }
    t = entrada;
    if (entrada.get('hsv.v') < 0.7)
    {
        //t.brighten(0.3);
        c1 = t.set('hsv.v', entrada.get('hsv.v') + 0.3);
        c3 = c3.set('hsv.v', entrada.get('hsv.v') + 0.3);
    }
    else
    {
        //t.brighten(-0.3);
        c1 = t.set('hsv.v', entrada.get('hsv.v') - 0.5);
        c3 = c3.set('hsv.v', entrada.get('hsv.v') -0.5);
    }

    if (entrada.get('hsv.v') < 0.4)
    {
        //c4.brighten(0.6);
        c4 = entrada.set('hsv.v', entrada.get('hsv.v') + 0.6);
    }
    else
    {
        //c4.brighten(-0.2);
        c4 = entrada.set('hsv.v', entrada.get('hsv.v') - 0.2);
    }
    return [c1.hex(), c2.hex(), c3.hex(), c4.hex()];
}

function alternaRangeBloqueioVisible(animacao)
{
    $combo = $('#empresa_intervaloBloqueio');
    if ($combo.val() == 'NUNCA')
    {
      $combo.parent().prev().hide(animacao);
      $('.politicas').hide(animacao);
      return;
    }
    $combo.parent().prev().show(animacao);
    $('.politicas').show(animacao);
}