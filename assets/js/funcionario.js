var $collectionHolder;
var $addSvcButton = $('<button type="button" class="addSvcBtn btn btn-primary">Adicionar</button>');
var $newLinkLi = $('<li></li>').append($addSvcButton);

$( function () {
    $collectionHolder = $('#escala');
    $collectionHolder.find('li').each( function () {
        addHtFormDeleteLink($(this));
    })
    $collectionHolder.append($newLinkLi);
    $addSvcButton.on('click', function (e) {
        addHtForm($collectionHolder, $newLinkLi);
    });
});

function addHtForm($collectionHolder, $newLinkLi, $id = Date.now().toString())
{
    let prototype = $collectionHolder.data('prototype');
    let newForm = prototype;

    newForm = newForm.replace(/__name__/g, 'div' + $id);

    let $newFormLi = $('<li></li>').append(newForm);
    addHtFormDeleteLink($newFormLi);
    $newLinkLi.before($newFormLi);
}

function addHtFormDeleteLink($tagFormLi)
{
    let $removeFormButton = $('<button type="button" class="btn btn-danger btn-sm">Apagar</button>');
    $tagFormLi.append($removeFormButton);

    $removeFormButton.on('click', function(e) {
        $tagFormLi.remove();
    });
}