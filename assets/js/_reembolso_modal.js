export default function submitReembolso(e) {
    let $submitBtn = $(e.target);
    let url = $submitBtn.data('path');
    let $valorInput = $('#valor-reembolso-input');
    let $inputFeedback = $('#reembolso-modal .invalid-feedback');

    $inputFeedback.children().remove();
    $inputFeedback.prev().removeClass('is-invalid');
    $inputFeedback.closest('form').removeClass('was-validated');

    if (url.includes('__id__'))
    {
        url = url.replace(/__id__/, $('input[name=id]').val());
    }
    if (document.querySelector('#reembolso-modal form').checkValidity() && $valorInput.val().length > 0)
    {
        fetch(url, {
            method: 'POST',
            headers: {
                "Content-type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({
                "valor_reembolso": $valorInput.val(),
                "_token": $('#token_reembolso').val()
            })
        })
            .then(
                (response) => {
                    if (response.ok)
                    {
                        location.reload();
                        return;
                    }
                    response.json().then(
                        (body) => {
                            let message = body.message;
                            if (message == undefined)
                            {
                                switch(response.status)
                                {
                                    case 404:
                                        message = 'Pagamento não encontrado';
                                        break;
                                    default:
                                        message = 'Erro não especificado';
                                        break;
                                }
                            }
                            $('#reembolso-modal .alert-danger').remove();
                            let $error_alert = $(`<div class="alert alert-danger" role="alert">
                                ${message}
                            </div>`);
                            $('#reembolso-modal .modal-footer').prepend($error_alert);
                        }
                    )
                },
                (error) => console.error(error)
            )
        ;
        return;
    }
    let valor = $valorInput.val();
    if (valor.search(/[a-zA-Z \s]+/) != -1)
    {
        // contém caracteres inválidos
        $inputFeedback.append('<span>O valor contém caracteres inválidos. Utilize somente números</span>');
        $inputFeedback.prev().addClass('is-invalid');
    }
    if (valor.length == 0)
    {
        $inputFeedback.append('<span>Digite um valor</span>');
        $inputFeedback.prev().addClass('is-invalid');
    }
    if (valor.length > 20)
    {
        $inputFeedback.append('<span>Digite um valor menor</span>');
        $inputFeedback.prev().addClass('is-invalid');
    }
    $inputFeedback.closest('form').addClass('was-validated');
}