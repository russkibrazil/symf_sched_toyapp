var key = document.getElementById('mercadopago-public').dataset['public'];
var process_route = document.getElementById('mercadopago-public').dataset['process'];
var form_name = document.getElementById('mercadopago-public').dataset['fname'];
var valor = document.getElementById('mercadopago-public').dataset['amount'];
var description = document.getElementById('mercadopago-public').dataset['description'];

$(() => {
    document.getElementsByTagName('form')[0].id = form_name;
    document.getElementsByTagName('form')[0].attributes.removeNamedItem('name');
    const mp = new MercadoPago(key, {locale: 'pt-BR'});
    const cardForm = mp.cardForm({
        amount: valor,
        autoMount: true,
        form: {
            id: `${form_name}`,
            cardholderName: {
                id: `${form_name}_cardHolderName`,
                placeholder: "Titular do cartão",
            },
            cardholderEmail: {
                id: `${form_name}_cardHolderEmail`,
                placeholder: "E-mail",
            },
            cardNumber: {
                id: `${form_name}_cardNumber`,
                placeholder: "Número do cartão",
            },
            // cardExpirationDate: {
            //     id: `${form_name}_cardExpirationDate`,
            //     placeholder: "Data de vencimento (MM/YYYY)",
            // },
            cardExpirationMonth: {
                id: `${form_name}_cardExpirationMonth`,
                placeholder: 'MM'
            },
            cardExpirationYear: {
                id: `${form_name}_cardExpirationYear`,
                placeholder: 'YYYY'
            },
            securityCode: {
                id: `${form_name}_securityCode`,
                placeholder: "Código de segurança",
            },
            installments: {
                id: `${form_name}_installments`,
                placeholder: "Parcelas",
            },
            identificationType: {
                id: `${form_name}_identificationType`,
                placeholder: "Tipo de documento",
            },
            identificationNumber: {
                id: `${form_name}_identificationNumber`,
                placeholder: "Número do documento",
            },
            issuer: {
                id: `${form_name}_issuer`,
                placeholder: "Banco emissor",
            },
        },
        callbacks: {
            onFormMounted: error => {
                if (error) return console.warn("Form Mounted handling error: ", error);
                console.log("Form mounted");
            },
            onSubmit: event => {
                event.preventDefault();
                const {
                    paymentMethodId: payment_method_id,
                    issuerId: issuer_id,
                    cardholderEmail: email,
                    amount,
                    token,
                    installments,
                    identificationNumber,
                    identificationType,
                } = cardForm.getCardFormData();

                fetch(process_route, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        token,
                        issuer_id,
                        payment_method_id,
                        transaction_amount: Number(amount),
                        installments: Number(installments),
                        description: description,
                        payer: {
                            email,
                            identification: {
                                type: identificationType,
                                number: identificationNumber,
                            },
                        },
                    }),
                })
                    .then(
                        (resolved) => {
                            if (resolved.ok)
                            {
                                location.href = '/agendamentos';
                            }
                            else
                            {
                                resolved.json().then((body) => {
                                    console.log(body);
                                    printBackendError(body);
                                })
                            }
                        },
                        (error) => {
                            console.error('I\'d resolved but: ',  error);
                            let errorObj = JSON.parse(error);
                            printErrors(errorObj);
                        }
                    )
                    .catch(error =>
                        {console.error('I had not resolved because ', error);}
                    );
            },
            onFetching: (resource) => {
                console.log("Fetching resource: ", resource);

                // Animate progress bar
                const progressBar = document.querySelector(".progress-bar");
                progressBar.removeAttribute("value");

                return () => {
                    progressBar.setAttribute("value", "0");
                };
            },
            onFormUnmounted: error => {
                if (error) return console.warn('Form Unmounted handling error: ', error)
                console.log('Form unmounted')
            },
            onIdentificationTypesReceived: (error, identificationTypes) => {
                if (error)
                {
                    console.warn('identificationTypes handling error: ', error);
                    const id_types = mp.getIdentificationTypes();
                    if (id_types == [])
                        return console.error('Empty idTypes again');
                    return console.log('Got these IDTYPES ', id_types);
                }
                console.log('Identification types available: ', identificationTypes)
            },
            onPaymentMethodsReceived: (error, paymentMethods) => {
                if (error) return console.warn('paymentMethods handling error: ', error)
                console.log('Payment Methods available: ', paymentMethods)
            },
            onIssuersReceived: (error, issuers) => {
                if (error) return console.warn('issuers handling error: ', error)
                console.log('Issuers available: ', issuers)
            },
            onInstallmentsReceived: (error, installments) => {
                if (error) {
                    console.warn('installments handling error: ', error);
                    const installments = mp.getInstallments({amount: valor, bin: $(`#${form_name}_cardNumber`).val().slice(0,6)});
                    if (installments == [])
                        return console.error('Empty installments again');
                    return console.log('Got these INSTALLMENTS ', installments);
                }
                console.log('Installments available: ', installments)
            },
            onCardTokenReceived: (error, token) => {
                if (error) {
                    printErrors(error.cause);
                    return console.warn('Token handling error: ', error)
                }
                console.log('Token available: ', token)
            }
        }
    });

    otherPaymentsMenu();

    $('button[type=submit]').on('click', (e) => {

        $('.alert-warning').remove();
        $('.invalid-feedback')
            .text('')
            .prev('input.form-control')
            .removeClass('is-invalid')
        ;
    })
})

function printErrors(errorData) {
    for (el of errorData)
    {
        switch (el.code) {
            // card token errors
            case '106':
                presentErrorMessage('cardNumber', 'Não pode efetuar pagamentos a usuários de outros países.');
                break;
            case '109':
                presentErrorMessage('cardNumber', 'Escolha outro cartão ou outra forma de pagamento.');
                break;
            case '126':
                presentWarningAlert('Não conseguimos processar seu pagamento.');
                break;
            case '129':
                presentErrorMessage('cardNumber', 'Este valor não pode ser processado com este cartão. Escolha outro cartão ou outrra forma de pagamento');
                break;
            case '145':
                presentWarningAlert('Uma das partes com a qual está tentando realizar o pagamento é um usuário de teste e a outra é um usuário real.');
                break;
            case '150':
                presentWarningAlert('Você não pode efetuar pagamentos.');
                break;
            case '151':
                presentErrorMessage('cardNumber', 'Você não pode efetuar pagamentos com cartão.');
                break;
            case '160':
                presentWarningAlert('Não conseguimos processar seu pagamento.');
                break;
            case '204':
                presentErrorMessage('cardNumber', 'Escolha outro cartão ou outra forma de pagamento.');
                break;
            case 'E205':
                presentErrorMessage('cardExpirationYear', 'Escolha outro cartão ou outra forma de pagamento.');
                break;
            case '801':
                presentWarningAlert('Você realizou um pagamento similar há poucos instantes. Tente novamente em alguns minutos.');
                break;

            // form errors
            case '205':
                presentErrorMessage('cardNumber', 'Digite o número do seu cartão');
                break;
            case '208':
                presentErrorMessage('cardExpirationMonth', 'Insira o mês de vencimento do cartão');
                break;
            case '209':
                presentErrorMessage('cardExpirationYear', 'Insira o ano de venciamento do cartão');
                break;
            case '212':
            case '214':
                presentErrorMessage('identificationNumber', 'Digite o seu documento');
                break;
            case '213':
                presentErrorMessage('identificationType', 'selecione o tipo de documento');
                break;
            case '220':
                presentErrorMessage('issuer', 'Informe seu banco emissor.');
                break;
            case '221':
                presentErrorMessage('cardHolderName', 'Digite o nome e sobrenome.');
                break;
            case '224':
                presentErrorMessage('securityCode', 'Digite o código de segurança');
                break;
            case 'E301':
                presentErrorMessage('cardNumber', 'Número digitado incorretamente. Tente novamente');
                break;
            case 'E302':
                presentErrorMessage('securityCode' , 'Confira o código de segurança.');
                break;
            case '316':
                presentErrorMessage('cardHolderName', 'Por favor, digite um nome válido.');
                break;
            case '322':
            case '323':
            case '324':
                presentErrorMessage('identificationNumber', 'Confira seu documento.');
                break;
            case '325':
                presentErrorMessage('cardExpirationMonth', 'confira o mês digitado');
                break;
            case '326':
                presentErrorMessage('cardExpirationYear', 'confira o ano digitado');
                break;
            default:
                presentWarningAlert('Confira os dados.')
                break;
        }
        $('form')
            .removeClass('needs-validation')
            .addClass('validated')
        ;
    }
}

function presentErrorMessage(field_id, message) {
    $(`#${form_name}_${field_id} + div.invalid-feedback`)
        .text(message)
        .prev('input.form-control')
        .addClass('is-invalid');
}

function presentWarningAlert(message) {
    $('form').prepend($(`<div class="alert alert-warning" role="alert">
        ${message}
    </div>`));
}
function printBackendError(errorData) {
    if (errorData.field_id == '')
    {
        presentWarningAlert(errorData.message);
    }
    else
    {
        presentErrorMessage(errorData.field_id, errorData.message);
        $('form')
            .removeClass('needs-validation')
            .addClass('validated')
    ;
    }
}

function otherPaymentsMenu() {
    let $menu_items = $('#other-payments ~ div').children();
    $menu_items.map(function () {
        let $el = $(this);
        const path = $el.data('path');
        if (path != null && path != undefined)
        {
            $el.on('click', function (e) {
                location.href = $(e.target).data('path');
            })
        }
        else
        {
            if (!$el.hasClass('active'))
                $el.attr('disabled', 'disabled');
        }
    })
}
