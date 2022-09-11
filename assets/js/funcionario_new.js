$(() => {
    $("#perfil_pessoa_cpf").on('blur', function (ev) {
        let $cpf = $("#perfil_pessoa_cpf");
        let cpfLen = $cpf.val().length;
        if (cpfLen == 11 || cpfLen == 14)
        {
            let cpfVal = $cpf.val().replaceAll('.', '').replace('-', '');
            $.get($cpf.data('path') + '?cpf=' + cpfVal, function (data) {
                    let pessoa = data;
                    toggleCamposPerfil(pessoa);
                }, 'json')
                .fail(function () {
                    toggleCamposPerfil();
                })
            ;
        }
        else
        {
            toggleCamposPerfil();
        }
    });
});

function toggleCamposPerfil(dados = undefined) {
    let $nome     = $("#perfil_pessoa_nome");
    let $telefone = $("#perfil_pessoa_telefone");
    let $endereco = $("#perfil_pessoa_endereco");
    if (dados == undefined)
    {
        $nome
            .val('')
            .removeAttr('disabled')
        ;
        $telefone
            .val('')
            .removeAttr('disabled')
        ;
        $endereco
            .val('')
            .removeAttr('disabled')
        ;
    }
    else
    {
        if (dados.nome != '' && dados.telefone != '')
        {
            $nome
                .val(dados.nome)
                .attr('disabled', true)
            ;
            $telefone
                .val(dados.telefone)
                .attr('disabled', true)
            ;
            $endereco
                .val(dados.endereco)
                .attr('disabled', true)
            ;
        }
    }
}