import submitReembolso from './_reembolso_modal';
import submitCancellingReason from './_cancelling_reason_modal'

var tCh = [];
var tAt = [];
var tCa = [];
$(function () {
    if ($(window).width() < 992) //BS lg breakpoint
    {
        $("#toast-container").css('bottom', $('#menu-mobile').height());
    }

    $('.toast').toast({delay:5000});

    $('.btn-chegada:not(.disabled)').on('click', clienteCheckIn);

    $('.btn-atraso:not(.disabled)').on('click', clienteAtrasado);

    $('#cancel-reason').on('show.bs.modal', updateCancelReasonModalRelatedId);
    $('#cancel-reason .btn-danger').on('click', clienteCancela);

    $('.btn-estorno:not(.disabled)').on('click', estornarPagamento);

    $('.btn-concluir').on('click', concluirAgendamento);

    $('.btn-pagar').on('click', selecionaFormatoPagamento);

    $('#modalFormButtonPagar').on('click', preparaModalPagamento);

    $('#submit-reembolso-btn').on('click', submitReembolso);

    $('#reembolso-modal').on('show.bs.modal', getIdForReembolsoModal);
});

function preparaModalPagamento(e) {
    let urlId = $('#modalFormHiddenId').val();

    let $toast = $('<div class="toast" role="alert" aria-live="assertive" aria-atomic="true"><div class="toast-header"><img src="" class="rounded mr-2"><strong class="mr-auto">Iroko</strong><button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Fechar"><span aria-hidden="true">&times;</span></button></div><div class="toast-body">Agendamento pago</div></div>');

    let $tzone = $('#toast-zone');

    $.ajax({
        type:"POST",
        url: urlId,
        data: {"fp": $('#modalFormSelectFormaPagto').val() , "valor": $('#modalFormNumberValor').val()},
        crossDomain: true,
        dataType: 'json'
    });

    $toast.toast({delay:5000});
    $tzone.prepend($toast);
    $toast.toast('show');
    let l = urlId.match(/[0-9]+/)[0];
    let pos = urlId.search(/[0-9]+/);
    $(`#${urlId.slice(pos, pos + l.length)}`).remove();
}

async function definirChegada(dado) {
    let link = $('#ajax').data('ch');
    link = link.replace('valor', dado);

    enviaAjax(link).then();
}
async function definirAtraso(dado) {
    let link = $('#ajax').data('at');
    link = link.replace('valor', dado);

    enviaAjax(link).then();
}
async function processarPagamento(id_agendamento) {
    let link = $('#ajax').data('rq');
    link = link.replace('valor', id_agendamento);

    return enviaAjax(link);
}

async function enviaAjax(link)
{
    return await $.ajax({
        type:"POST",
        url: link,
        crossDomain: true,
        dataType: 'json'
    });
}
function clienteCheckIn(e) {
    let $el = $(e.target);
    let $parent = $el.parent();
    let $bts = $parent.children().slice(1);
    let val = setTimeout( () => {
        definirChegada($parent.data('id')).then(
            (res) => {
                $el.siblings('.btn-concluir').removeClass('disabled');
                $('.disabled').removeAttr('href');
            },
            (err) => console.log(err)
        )
    }, 5*1000);
    $bts.addClass('disabled');
    tCh.push(val);
    mostraToast({
        'agendamento' : $el.data('toast'),
        'evento' : 'chegada',
        'timerId' : val,
        'bts' : $parent.children().slice(1)
    });
}

function clienteAtrasado(e) {
    let $el = $(e.target);
    let $parent = $el.parent();
    let val = setTimeout( () => {
        definirAtraso($parent.data('id')).then(
            (res) => $el
                    .addClass('disabled')
                    .off('click')
                    .text('Atrasado')
            ,
            (err) => console.error(err)
        )
    }, 5*1000);
    tAt.push(val);
    mostraToast({
        'agendamento' : $el.data('toast'),
        'evento' : 'atraso',
        'timerId' : val
    });
}

function clienteCancela(e) {
    let link = $('#ajax').data('ca').replace('valor', $(e.target).parent().data('id'));
    submitCancellingReason(link, 'Agendamento cancelado');
}

function mostraToast(params) {
    let $toast = $(`<div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-t=${params.timerId}><div class="toast-header"><img src="" class="rounded mr-2"><strong class="mr-auto">Iroko</strong><button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Fechar"><span aria-hidden="true">&times;</span></button></div><div class="toast-body">Agendamento ${params.agendamento}</div></div>`);
    let $desfazer = $('<a class="btn btn-secondary btn-sm">Desfazer</a>');
    let idx;
    switch (params.evento){
        case 'chegada':
            $desfazer
                .on('click', function (e) {
                    let $toast = $(e.currentTarget.parentElement);
                    idx = tCh.indexOf($toast.data('t'));
                    clearTimeout(tCh.splice(idx,1));
                    $toast.toast('hide');
                    params.bts.removeClass('disabled');
                })
                .appendTo($toast)
            ;
        break;

        case 'atraso':
            $desfazer
                .on('click', function (e) {
                    let $toast = $(e.currentTarget.parentElement);
                    idx = tAt.indexOf($toast.data('t'));
                    clearTimeout(tAt.splice(idx,1));
                    $toast.toast('hide');
                })
                .appendTo($toast)
            ;
        break;

        case 'cancelamento':
            $desfazer
                .on('click', function (e) {
                    let $toast = $(e.currentTarget.parentElement);
                    idx = tCa.indexOf($toast.data('t'));
                    clearTimeout(tCa.splice(idx,1));
                    $toast.toast('hide');
                    $(`#${params.caller_id}`).fadeIn('fast');
                })
                .appendTo($toast)
            ;
        break;

        case 'estorno':
            $toast.on('hide.bs.toast', function (e) {
                location.reload();
            });
            break;

        default:
            break;
    }

    let $tzone = $('#toast-zone');
    $toast.toast({delay:5000});
    $tzone.prepend($toast);
    $toast.toast('show');
}

function chamaModalPagamento(event) {
    $('#modalFormHiddenId').val($(event.currentTarget).data('path'));
}

function selecionaFormatoPagamento(e) {
    let $caller = $(e.target);
    if ($caller.data('toggle') == 'modal')
    {
        chamaModalPagamento(e)
    }
    else
    {
        processarPagamento($caller.parent().data('id')).then(
            (data) => {
                let $toast = $(`<div class="toast" role="alert" aria-live="assertive" aria-atomic="true"><div class="toast-header"><img src="" class="rounded mr-2"><strong class="mr-auto">Iroko</strong><button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Fechar"><span aria-hidden="true">&times;</span></button></div><div class="toast-body">${data.message}</div></div>`);
                let $tzone = $('#toast-zone');
                $toast.toast({delay:5000});
                $tzone.prepend($toast);
                $toast.toast('show');
            },
            (error) => {
                let $toast = $(`<div class="toast" role="alert" aria-live="assertive" aria-atomic="true"><div class="toast-header"><img src="" class="rounded mr-2"><strong class="mr-auto">Iroko</strong><button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Fechar"><span aria-hidden="true">&times;</span></button></div><div class="toast-body">${error.responseJSON.message}</div></div>`);
                let $tzone = $('#toast-zone');
                $toast.toast({delay:5000});
                $tzone.prepend($toast);
                $toast.toast('show');
            }
        );
    }
}

function concluirAgendamento(e) {
    let $target = $(e.target);
    let $parent = $target.parent();
    let $btnPaga = $target.next('.btn-pagar');

    if ($parent.data('paytype') == 'rq')
    {
        let url = $('#ajax')
            .data('rq')
            .replace('valor', $parent.data('id'))
        ;
        $btnPaga.attr('data-path', url);
    }
    else
    {
        $btnPaga
            .attr('data-path', $el.data('pagamento'))
            .attr('data-toggle', 'modal')
            .attr('data-target', '#modalPagamento')
        ;
    }
    $btnPaga.on('click', selecionaFormatoPagamento);
    $btnPaga.removeAttr('disabled');
    $btnPaga.removeClass('disabled');

    let url = $('#ajax')
        .data('fi')
        .replace(/valor/g, $parent.data('id'))
    ;
    $.post(url, '',
        function (data, textStatus, jqXHR) {
            mostraToast({
                'agendamento': `concluido`,
                'evento': 'concluir'
            })
        },
        "json"
    );
    $target.addClass('disabled');
}

function estornarPagamento(e) {
    let $el = $(e.target);
    let $parent = $el.parent();
    let url = $('#ajax').data('es').replace('valor', $parent.data('id'));
    fetch(url, {
        method: "POST",
        headers: {
            "Accept": "application/json"
        }
    })
        .then(
            (response) => {
                if (response.ok)
                {
                    mostraToast({
                        'agendamento' : ` está aguardando pagamento. O pagamento anterior foi estornado. A página será recarregada.`,
                        'evento': 'estorno'
                    });
                }
                response.json().then(
                    (body) => {
                        mostraToast({
                            'agendamento' : ` info: ${body.message}`
                        });
                    }
                )
            },
            (error) => console.error(error)
        )
    ;
}

function getIdForReembolsoModal(e) {
    let $caller = $(e.relatedTarget);
    $('#reembolso-modal input[name="id"]').val($caller.parent().data('id'));
}

function updateCancelReasonModalRelatedId(e) {
    const newId = $(e.relatedTarget).parent().data('id');
    $('#cancel-reason .btn-danger').parent().attr('data-id', newId);
}