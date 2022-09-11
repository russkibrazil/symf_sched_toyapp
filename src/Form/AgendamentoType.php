<?php

namespace App\Form;

use App\Entity\Agendamento;
use App\Entity\FuncionarioLocalTrabalho;
use App\Entity\PerfilFuncionario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints\DateTime;

class AgendamentoType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    private function getFuncionarios($emp)
    {
        $lt = $this->entityManager->getRepository(FuncionarioLocalTrabalho::class)->findFuncionarioByPrivilegio('PRESTADOR', $emp);
        $funcionarios = [];
        foreach ($lt as $r) {
            $funcionarios[] = $r->getcpfFuncionario();
        }
        return $funcionarios;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('horario', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'string',
                'label' => 'Horário',
                'attr' => ['min' => (new \DateTime())->format('Y-m-d\TH:i')],
                'constraints' => [
                    new DateTime('Y-m-d H:i:s')
                ]
            ])
            ->add('formaPagto', TextType::class, [
                'help' => 'Como vão ser pagos os serviços.',
                'label' => 'Forma Pagamento'
            ])
            ->add('pesquisa_cliente', TextType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Cliente',
                'disabled' => $options['operacao'] == 'EDITAR' ? true : false,
            ])
            ->add('cpf', HiddenType::class, [
                'required' => false,
                'mapped' => false
            ])
            ->add('funcionario', ChoiceType::class, [
                'choices' => $this->getFuncionarios($options['empresa']),
                'choice_value' => 'nomeUsuario',
                'choice_label' => function(?PerfilFuncionario $f){
                    return $f ? $f->getPessoa()->getNome() : '';
                },
                'label' => 'Funcionário',
                'help' => 'Quem vai executar o serviço.'
            ])
            ->add('servicos', CollectionType::class, [
                'entry_type' => AgendamentoServicosType::class,
                'entry_options' => [
                    'label' => false,
                    'empresa' => $options['empresa']
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('conclusaoEsperada', HiddenType::class, [
                'required' => false
            ])
            ->add('pagamentoPresencial', CheckboxType::class, [
                'label' => 'Pagar no local',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Agendamento::class,
            'empresa' => '0',
            'operacao' => 'EDITAR'
        ]);
        $resolver->setAllowedTypes('empresa', 'string');
        $resolver->setAllowedTypes('operacao', 'string');
        $resolver->setAllowedValues('operacao', ['NOVO', 'EDITAR']);
    }
}
