alternaRangeBloqueioVisible($('#configuracao_politicas_bloqueio_intervaloBloqueio'), 0);

$(() => {
   $('#configuracao_politicas_bloqueio_intervaloBloqueio').on('change', function(e) {
      let $combo = $(e.target);
      alternaRangeBloqueioVisible($combo, 'fast');
   });

    $('#configuracao_politicas_bloqueio_qtdeBloqueio').on('input', function(e) {
        $('#range_qtdeBloqueio_value').text($('#configuracao_politicas_bloqueio_qtdeBloqueio').val()); 
     });
 
     $('#configuracao_politicas_bloqueio_qtdeAnalise').on('input', function(e) {
         $('#range_qtdeAnalise_value').text($('#configuracao_politicas_bloqueio_qtdeAnalise').val()); 
      });
 
     $('#configuracao_politicas_bloqueio_atrasosTolerados').on('input', function(e) {
        $('#range_atrasosTolerados_value').text($('#configuracao_politicas_bloqueio_atrasosTolerados').val()); 
     });
 
     $('#configuracao_politicas_bloqueio_cancelamentosTolerados').on('input', function(e) {
         $('#range_cancelamentosTolerados_value').text($('#configuracao_politicas_bloqueio_cancelamentosTolerados').val()); 
      });
});

function alternaRangeBloqueioVisible($combo, animacao)
{
   if ($combo.val() == 'NUNCA')
   {
      $combo.parent().prev().hide(animacao);
      $('.politicas').hide(animacao);
   }
   else
   {
      $combo.parent().prev().show(animacao);
      $('.politicas').show(animacao);
   }
}