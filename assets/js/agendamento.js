var $servicosCollection;
var $addSvcButton = $('<button type="button" class="addSvcBtn btn btn-primary">Adicionar</button>');
var $adicionarBtn = $('<div class="mt-2 mb-4"></div>').append($addSvcButton);
var t = null;

$(function () {
    $servicosCollection = $('#svcs');

    $servicosCollection.find('.input-group-append').each(function() {
        addSvcFormDeleteLink($(this));
    });

    $addSvcButton.on('click', function(e) {
        addSvcForm($servicosCollection, $adicionarBtn);
    });

    $servicosCollection.append($adicionarBtn);

    if ($('#agendamento_cpf').val())
    {
        let dataN = $('#resultadosAx').data('nome');
        $('#agendamento_pesquisa_cliente').val(decodeURI(dataN.slice(33)));
    }

    $('#agendamento_pesquisa_cliente').on('input', function (e) {
        if (t)
        {
            clearTimeout(t);
        }
        if (e.target.value.length > 3)
        {
            t = setTimeout(() => {
                RetornarClientePorNome(e.target.value).then(
                    (res) => {
                        ApresentarResultadosForm(res);
                    },
                    (err) => {
                        console.log(err);
                    }
                );
            }, 650);
        }
    });
});

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

async function RetornarClientePorTelefone(dado)
{
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
    var $res = $('#resultadosAx');
    var $itemModelo = $('<li class=\"list-group-item list-group-item-action resultadosAxItem\"></li>');
    $itemModelo.on('click', function (e) {
        //let inn = e.target.innerText;
        //$('#agendamento_busca_cliente').text(inn.slice(0,inn.search(/ Telefone/)));
        $('.resultadosAxItem').removeClass('active');
        e.target.classList.add('active');
        $('#agendamento_cpf').val(e.target.c);
    });
    if ($res.children.length > 0)
    {
        $('.resultadosAxItem').remove();
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