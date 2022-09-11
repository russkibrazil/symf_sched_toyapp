export default function submitCancellingReason(targetUrl, toastMessage) {
    let $cancelModal = $("#cancel-reason");
    let $selectCancel = $cancelModal.find("select");
    let $descriptionCancel = $cancelModal.find("textarea");
    fetch(targetUrl, {
        method: 'POST',
        headers: {'Content-type': 'application/json'},
        body: JSON.stringify({
            "reason": $selectCancel.val().trim(),
            "reason_description": $descriptionCancel.text().trim()
        })
    }).then(
        (res) => {
            if (res.ok){
                $cancelModal.modal("hide");
                let $toast = $(`<div class="toast" role="alert" aria-live="assertive" aria-atomic="true"><div class="toast-header"><img src="" class="rounded mr-2"><strong class="mr-auto">Iroko</strong><button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Fechar"><span aria-hidden="true">&times;</span></button></div><div class="toast-body">${toastMessage}</div></div>`);
                $toast.on("hide.bs.toast", function () {
                    location.reload();
                });

                let $tzone = $('#toast-zone');
                $toast.toast({delay:3000});
                $tzone.prepend($toast);
                $toast.toast('show');
            }
        },
        (error) => console.error(error)
    );
}