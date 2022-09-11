var $collectionHolder;
var $addHoraButton = $('<button type="button" class="btn btn-primary addPolicyBtn">Adicionar</button>');
var $adicionarBtn = $('<div class="mt-2 mb-4"></div>').append($addHoraButton);
var formName = '';

$( function () {
    formName = document.querySelector('form').name;
    $collectionHolder = $('#politicaParcelamentos');
    $collectionHolder.find('.itemPolitica .form-row').each( function () {
        addHtFormDeleteLink($(this));
    })
    $collectionHolder.append($adicionarBtn);
    $addHoraButton.on('click', function (e) {
        addHtForm($collectionHolder, $adicionarBtn);
    });
    allowEditPaymentPolicies();
    $(`#${formName}_processador`).on('change', allowEditPaymentPolicies);
});

function addHtForm($collectionHolder, $newLinkLi, $id = Date.now().toString())
{
    let prototype = $collectionHolder.data('prototype');
    let newForm = prototype;

    newForm = newForm.replace(/__name__/g, 'div' + $id);

    let $valorInicial = $('<div class="form-group col"></div>')
        .append('<label>Dia da Semana</label>')
        .append(/<input.+(?=startValue).+>/g.exec(newForm)[0])
    ;
    let $valorFinal = $('<div class="form-group col"></div>')
        .append('<label>Início</label>')
        .append(
            $('<div class="form-inline"></div>')
                .append(/<input.+(?=endValue).+>/g.exec(newForm)[0])
        )
    ;
    let $maxParc = $('<div class="form-group col"></div>')
        .append('<label>Fim</label>')
        .append(
            $('<div class="form-inline"></div>')
                .append(/<input.+(?=maxInstallments).+>/g.exec(newForm)[0])
        )
    ;

    let $formRow = $('<div class="form-row"></div>')
        .append($valorInicial)
        .append($valorFinal)
        .append($maxParc)
    ;

    let $newFormLi = $('<div class="itemPolitica my-1"></div>').append($formRow);
    addHtFormDeleteLink($formRow);
    $newLinkLi.before($newFormLi);
}

function addHtFormDeleteLink($tagFormLi)
{
    let $removeFormButton = $('<button type="button" class="btn btn-danger btn-sm">Apagar</button>');
    $tagFormLi.append($removeFormButton);

    $removeFormButton.on('click', function(e) {
        $tagFormLi.closest('div.itemPolitica').remove();
    });
}

function allowEditPaymentPolicies(e) {

    let $processadorSelect = (undefined !== e ? $(e.target) : $(`#${formName}_processador`));
    let $maxParcelasInput = $(`#${formName}_maxParcelasCartao`);
    let $addPolicyBtn = $('.addPolicyBtn');
    let $tipoChavePixSelect = $(`#${formName}_tipoChavePix`);
    let $chavePixInput = $(`#${formName}_pix`);
    if ($processadorSelect.val() == 'MERPAGO')
    {
        $maxParcelasInput
            .attr('disabled', 'true')
            .val(12)
        ;
        $addPolicyBtn
            .addClass('disabled')
            .attr('disabled', 'true')
        ;
        $tipoChavePixSelect
            .attr('disabled', 'true')
        ;
        $chavePixInput
            .attr('disabled', 'true')
            .val('')
        ;

        let $alert = $(`<div class="alert alert-warning" role="alert">
            <p>As políticas de pagamento serão importadas diretamente da conta da empresa no momento do pagamento.</p>
            <hr>
            <p>Se desejar alterar as condições de pagamento, acesse o <a href="https://www.mercadopago.com.br/costs-section" target="_blank" class="alert-link">painel da sua conta Mercado Pago</a>.</p>
            <p>Se desejar rever as taxas e prazos de recebimento, <a href="https://www.mercadopago.com.br/costs-section/release-options/edit/merchant-services" target="_blank" class="alert-link">confira a seção de custos da sua conta Mercado Pago</a>.</p>
        </div>`);

        $processadorSelect
            .closest('.row')
            .after($alert)
        ;

        // sendo MercadoPago, não há alterações a serem salvas ao visitar a página de edição
        if ($processadorSelect.attr('disabled') == 'disabled')
        {
            document.querySelector('button[type=submit]').setAttribute('disabled', 'disabled');
        }
    }
    else
    {
        $maxParcelasInput.removeAttr('disabled');
        $addPolicyBtn
            .removeAttr('disabled')
            .removeClass('disabled')
        ;
        $tipoChavePixSelect.removeAttr('disabled');
        $chavePixInput.removeAttr('disabled');
        $('.alert-warning').remove();
    }
}
