$(() => {
    $('form').on('submit', function (e) {
        let $genPixBtn = $('#gerar-pix');
        let form_elements = document.forms[0].elements;
        e.preventDefault();
        fetch($genPixBtn.data('path'), {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                "name": form_elements[2].value,
                "email": form_elements[3].value,
                "identification_type": form_elements[0].selectedOptions[0].text,
                "identification_number": form_elements[1].value,
            })
        })
            .then(
                (response) => {
                    console.log(response);
                    if (response.ok)
                    {
                        response.json().then((body) => {
                            $('#qr-pix')
                                .attr('src', `data:image/jpeg;base64,${body.qr_code_base64}`)
                                .next()
                                .remove()
                            ;
                            $('#copiar-pix').on('click', function (e) {
                                // copiar codigo de data.qr_code
                                // https://developer.mozilla.org/en-US/docs/Mozilla/Add-ons/WebExtensions/Interact_with_the_clipboard
                                // https://stackoverflow.com/questions/400212/how-do-i-copy-to-the-clipboard-in-javascript
                                navigator.clipboard.writeText(body.qr_code)
                                    .then(() => console.log('Check your clipboard'),
                                        () => console.error('Failed to copy')
                                    )
                                ;
                            });
                            $('#gerar-pix').closest('.row').closest('.col').children().first().hide('fast', function () {
                                $(this).next().show('fast');
                            });
                        },
                        () => console.error('problemas on reading json body'));
                    }
                    else
                    {
                        resolved.json().then((body) => {
                            console.log(body);
                            // printBackendError(body);
                        })
                    }
                },
                (error) => {}
            );
    });

    otherPaymentsMenu();
});

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