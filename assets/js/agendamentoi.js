import { Date } from "core-js";

var tempoTotal, $collectionHolder, servicosSelecionados, horarioFuncionamento;
servicosSelecionados = [];
tempoTotal = [];
$collectionHolder = $('#svcs');

var $addSvcButton = $('<button type="button" class="addSvcBtn btn btn-primary">Adicionar</button>');
var $adicionarBtn = $('<div class="mt-2 mb-4"></div>').append($addSvcButton);
var t = null;
const spinner = '<div id="access-spinner" class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>';

RetornarHorarioFuncionamento(0).then( (r) => {
    r.map((el) => {
        let inicioExpediente = new Date(el.horaInicio.replace('T', ' ').slice(0,19));
        let fimExpediente = new Date(el.horaFim.replace('T', ' ').slice(0,19));
        return {
            "diaSemana": el.diaSemana,
            "horaInicio": inicioExpediente,
            "horaFim": fimExpediente,
            "tsInicio": el.tsInicio,
            "tsFim": el.tsFim
        };
    });
    horarioFuncionamento = r;
});

$( function ()  {
    let $buscaCliente = $('#buscaCliente')
    if ($buscaCliente.length > 0)
    {
        $buscaCliente.on('input', function (e) {
            if (t)
            {
                clearTimeout(t);
            }
            if (e.target.value.length >= 3)
            {
                t = setTimeout(() => {
                    $('#resultadosAx').after($(spinner));
                    RetornarClientePorNome(e.target.value).then(
                        (res) => {
                            ApresentarResultadosForm(res);
                            $('#access-spinner').remove();
                        },
                        (err) => {
                            nenhumUsuarioClienteEncontrado();
                            $('#access-spinner').remove();
                        }
                    );
                }, 750);
            }
        });

        $('#nCli').on('click', function () {
            if ($(".resultadosAxItem.active").length > 0)
            {
                $('#cli').fadeOut('slow', function () {
                    $('#svc').fadeIn('slow');
                 });
            }
            else
            {
                $('.alert').remove();
                let erro = '<div class="alert alert-danger alert-dismissible fade show my-4" role="alert"><p>Selecione um cliente para continuar</p><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('#resultadosAx').after(erro);
            }
         });

         $('#bSvc').on('click', function () {
             $('#svc').fadeOut('slow', function () {
                 $('#cli').fadeIn('slow');
             });
         })
    }
    else
    {
        $('#svc').show(0);
    }

    $('#nSvc').on('click', () => {
        let $_ = null;
        let $svcEscolhe = $("input[name=\"servico\"]:checked");
        let t, n;
        tempoTotal = [];
        if ($svcEscolhe.length > 0)
        {
            $svcEscolhe.each(function () {
                let $el = $(this);
                $_ = $el.parent().children('input[type=\"hidden\"]');
                servicosSelecionados.push($el.val());
                tempoTotal.push($_.val());
            });
            $_ = tempoTotal.map((valor) => {
                let res;
                res = valor.slice(0,2) * 1000 * 60 * 60;
                res += valor.slice(3,5) * 1000 * 60;
                return res;
            });
            tempoTotal = $_.reduce((acc, atual) => acc + atual);

            if ($collectionHolder.children().count > 0)
            {
                $collectionHolder.children().each((el) => {
                    $(el).remove();
                });
            }

            for (let s of servicosSelecionados){
                t = Date.now().toString();
                addSvcForm($collectionHolder, $adicionarBtn, t);
                n = '#agendamento_servicos_div'+t+'_servico';
                $(n).val(s);
            }

            $('#svc').fadeOut('slow', function () {
                $('#func').fadeIn('slow');
            });
        }
        else
        {
            $('.alert').remove();
            let erro = '<div class="alert alert-danger alert-dismissible fade show mt-4" role="alert"><p>Selecione ao menos um serviço para continuar</p><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('#nSvc').parent().parent().before(erro);
        }
    });

    $('#nFunc').on('click', () => {
        let $func = $('input[name=\"funcionario\"]:checked');
        if ($func.length > 0)
        {
            $('#agendamento_funcionario').val($('input[name=\"funcionario\"]:checked').val());
            $('#func').fadeOut('slow', function() {
                $('#hora').fadeIn('slow');
            });
        }
        else
        {
            $('.alert').remove();
            let erro = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><p>Selecione um funcionário para continuar</p><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('#nFunc').parent().parent().before(erro);
        }
    });

    $('#bFunc').on('click', function () {
        $('#func').fadeOut('slow', function () {
            $('#svc').fadeIn('slow');
        });
     })

    $('#nHora').on('click', function () {

        let $data = $("#date");
        let $hora = $("#timePicker");

        let valHora = $hora.val();

        if (valHora != "" && valHora != null)
        {
            $('#agendamento_horario').val(`${$data.val().toString()}T${valHora}`);

            let horasMSec = (valHora.slice(0,2)-0) * 1000 * 60 * 60;
            let minutosMSec = (valHora.slice(3)-0) * 1000 * 60;

            $('#agendamento_conclusaoEsperada').val(new Date($data[0].valueAsNumber + horasMSec + minutosMSec + tempoTotal).toISOString());

            $('#hora').fadeOut('slow', function() {
                $('#conclusao').fadeIn('slow');
            });
            document.getElementById('agendamento_pagamentoPresencial').checked = document.getElementById('pagaPresencial').checked;
        }
        else
        {
            $('.alert').remove();
            let erro = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><p>Selecione data e hora para continuar</p><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('#nHora').parent().parent().before(erro);
        }

    });

    $('#bHora').on('click', function () {
        $('#hora').fadeOut('slow', function () {
            $('#func').fadeIn('slow');
        });
     })

    $addSvcButton.on('click', function(e) {
         addSvcForm($collectionHolder, $adicionarBtn);
    });
    $collectionHolder.append($adicionarBtn);

    $('#date').on('change', function (e)
    {
        const dt = $("#date").val();
        const f = $('#agendamento_funcionario').val();
        const dtObj = new Date(dt);

        $('#time').val('');
        $('#timePicker').children().each((index, el) => {
            $(el).remove();
        })

        // FIXME Inserir offset da TIMEZONE
        if (t)
        {
            clearTimeout(t);
        }
        if (dt.length == 10)
        {
            t = setTimeout(() => {
                $('#target-link').after(spinner);
                RetornarAgendaFuncionario(f, dt).then(
                    (res) => {
                        var agIni = [], agFim = [];
                        for (const agendamento of res){
                            agIni.push(new Date(agendamento.inicio.replace('T', ' ').slice(0,19)).getTime());
                            agFim.push(new Date(agendamento.fim.replace('T', ' ').slice(0,19)).getTime());
                        }
                        let tsInicioExpediente, tsFimExpediente, indexFuncionamento;
                        if (horarioFuncionamento.length == 7)
                        {
                            let temp = horarioFuncionamento[dtObj.getDay()];
                            tsInicioExpediente = dtObj.getTime() + temp.tsInicio * 1000;
                            tsFimExpediente = dtObj.getTime() + temp.tsFim * 1000;
                            indexFuncionamento = dtObj.getDay();
                        }
                        else
                        {
                            let i = 0;
                            let stop = false;
                            for (i; i < horarioFuncionamento.length && stop == false; i++)
                            {
                                if (horarioFuncionamento[i].diaSemana == dtObj.getDay()+1)
                                {
                                    let temp = horarioFuncionamento[dtObj.getDay()];
                                    tsInicioExpediente = dtObj.getTime() + temp.tsInicio * 1000;
                                    tsFimExpediente = dtObj.getTime() + temp.tsFim * 1000;
                                    indexFuncionamento = i;
                                    stop = true;
                                }
                            }
                            if (indexFuncionamento == undefined) return;
                        }
                        // const itemRolo = `<a class="dropdown-item rolo-horario"></a>`;
                        const itemRolo = `<option value=""></option>`;
                        let horaInicial = '';
                        if (Date.now() >= tsInicioExpediente)
                        {
                            horaInicial = (new Date()).getHours();
                        }
                        else
                        {
                            horaInicial = (new Date(horarioFuncionamento[indexFuncionamento].horaInicio.replace('T', ' ').slice(0,19))).getHours();
                        }
                        const horaFinal = (new Date(horarioFuncionamento[indexFuncionamento].horaFim.replace('T', ' ').slice(0,19))).getHours();
                        let primeiroValorValido = true;
                        for (let h = horaInicial; h <= horaFinal; h++){
                            for (let m = 0; m < 60; m+=30){
                                const dtLi = tsInicioExpediente + (h * 60 + m) * 60 * 1000;
                                const dtConclusao = dtLi + tempoTotal;
                                let $elementoHora = $(itemRolo);
                                const valor = `${h < 10 ? '0'.concat(h) : h }:${m < 10 ? '0'.concat(m) : m }`;
                                $elementoHora.text(valor);
                                $elementoHora.attr('value', valor);
                                if (agIni.length > 0)
                                {
                                    let para = false;
                                    for (let i = 0; i < agIni.length && para === false; i++)
                                    {
                                        if ((dtConclusao > agIni[i] && dtConclusao < agFim[i]) || (dtLi >= agIni[i] && dtLi < agFim[i]))
                                        {
                                            $elementoHora.attr('disabled', 'true');
                                            para = true;
                                            if (dtLi >= agFim[i])
                                            {
                                                agIni.splice(i,1);
                                                agFim.splice(i,1);
                                            }
                                        }
                                        else
                                        {
                                            $elementoHora.on('click', selecionarHoraPicker);
                                            if (primeiroValorValido)
                                            {
                                                $('#time').val(valor);
                                                primeiroValorValido = false;
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $elementoHora.on('click', selecionarHoraPicker);
                                    if (primeiroValorValido)
                                    {
                                        $('#time').val(valor);
                                        primeiroValorValido = false;
                                    }
                                }
                                $('#timePicker').append($elementoHora);
                            }
                        }
                        $('#access-spinner').remove();
                    },
                    (err) => {
                        $('#timePicker').children().remove();
                    }
                )
            }, 700);
        }
    });
});

function selecionarHoraPicker (event) {
    const v = event.currentTarget.innerText;
    $('#time').val(v);
}

function addSvcForm($collectionHolder, $newLinkLi, $id = Date.now().toString()){
    let prototype = $collectionHolder.data('prototype');
    let newForm = prototype;

    newForm = newForm.replace(/__name__/g, 'div' + $id);
    let $inputGroupAppend = $('<div class="input-group-append"></div>');
    addSvcFormDeleteLink($inputGroupAppend);

    let $inputGroup = $('<div class=input-group></div>')
        .append(/<select.*<\/select>/g.exec(newForm)[0])
        .append($inputGroupAppend)
    ;

    let $newFormLi = $('<div class="itemSvcs my-1"></div>').append($inputGroup);
    $newLinkLi.before($newFormLi);
}

function addSvcFormDeleteLink($tagFormLi) {
    let $removeFormButton = $('<button type="button" class="btn btn-danger">Apagar</button>');
    $tagFormLi.append($removeFormButton);

    $removeFormButton.on('click', function(e) {
        $tagFormLi.closest('div.itemSvcs').remove();
    });
}

async function RetornarAgendaFuncionario(funcionario, data)
{
    var link = $('#target-link').data('af-link');
    link = link.replace('funccpf', funcionario).replace('datanum', data);
    const r = await $.ajax({
        type:"GET",
        url: link,
        crossDomain: true,
        dataType: 'json'
    });
    return r;
}
async function RetornarHorarioFuncionamento(data)
{
    var link = $('#target-link').data('he-link');
    link = link.replace('dian', data);
    const r = await $.ajax({
        type:"GET",
        url: link,
        crossDomain: true,
        dataType: 'json'
    });
    return r;
}

async function RetornarClientePorNome(dado)
{
    var link = $('#resultadosAx').data('nome');
    if (dado == undefined)
    {
        link = link.replace(link.slice(33), dado);
    }
    else
    {
        link = link.replace('valor', dado);
    }

    const r = await $.ajax({
        type:"GET",
        url: link,
        crossDomain: true,
        dataType: 'json'
    });
    return r;
}

function ApresentarResultadosForm(i)
{
    let $res = $('#resultadosAx');
    let $itemModelo = $('<li class=\"list-group-item list-group-item-action resultadosAxItem\"></li>');
    $itemModelo.on('click', function (e) {
        $('.resultadosAxItem').removeClass('active');
        e.target.classList.add('active');
        $('#form_nomeCliente').text('Cliente:' + e.target.textContent);
        $('#agendamento_cpf').val(e.target.c);
    });
    $('.resultadosAxItem').remove();
    if ($res.children.length == 0)
    {
        nenhumUsuarioClienteEncontrado();
    }
    for (let item of i)
    {
        let $v = $itemModelo
            .clone(true)
            .text(item.p_nome +  ' Telefone: (' + item.p_telefone.slice(0,2) + ') ***** ' + item.p_telefone.slice(8,13))
            .prop('c', item.nomeUsuario)
        ;
        $res = $res.append($v);
    }
}

function nenhumUsuarioClienteEncontrado()
{
    $('.resultadosAxItem').remove();
    $('<li class=\"list-group-item list-group-item-action disabled resultadosAxItem\">Nada encontrado</li>').appendTo('#resultadosAx');
}