<?php

namespace App\Controller;

use App\Entity\Empresa;
use App\Entity\EmpresaProcessadorPagamento;
use App\Form\EmpresaPagamentoProcessadorType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Route("/empresa/{cnpj}/pagamento/processador")
 * @IsGranted("ROLE_ADMIN")
 */
class EmpresaPagamentoProcessadorController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $mr)
    {
        $this->doctrine = $mr;
    }
    /**
     * @Route("/new", name="empresa_pagamento_processador_new", methods={"GET", "POST"})
     */
    public function new(Request $request, Empresa $empresa, HttpClientInterface $httpClient): Response
    {
        $epp = (new EmpresaProcessadorPagamento())
            ->setEmpresa($empresa);
        $form = $this->createForm(EmpresaPagamentoProcessadorType::class, $epp, ['operacao' => 'novo']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ((isset($_ENV['MERCADOPAGO_PUBLIC']) && isset($_ENV['MERCADOPAGO_SECRET'])) && ($_ENV['MERCADOPAGO_PUBLIC'] != '' && $_ENV['MERCADOPAGO_SECRET'] != '')) {
                $paymentMethodsResponse = $httpClient->request(
                    'GET',
                    'https://api.mercadopago.com/v1/payment_methods',
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            // 'Authorization' =>  'Bearer ' . $_ENV['MERCADOPAGO_SECRET'],
                        ],
                        'auth_bearer' => $_ENV['MERCADOPAGO_SECRET'],
                    ]
                );
                try {
                    $streamDecoded = $paymentMethodsResponse->toArray(true);
                    $pixConfig = array_filter($streamDecoded, function ($el) {
                        return strtolower($el['id']) === 'pix' && strtolower($el['status']) === 'active';
                    });
                    if (count($pixConfig) > 0) {
                        $epp->setPix('1');
                    }
                    $politicasForm = $form->get('politicaParcelamento')->all();
                    $politicasArray = [];
                    foreach ($politicasForm as $row) {
                        $politicasArray[] = [
                            'startValue' => $row->get('startValue')->getData(),
                            'endValue' => $row->get('endValue')->getData(),
                            'maxInstallments' => $row->get('maxInstallments')->getData(),
                        ];
                    }
                    $epp->setPoliticaParcelamento($politicasArray);
                    $entityManager = $this->doctrine->getManager();
                    $entityManager->persist($epp);
                    $entityManager->flush();
                    $this->addFlash('sucesso', 'Informações salvas!');
                } catch (ServerExceptionInterface $serverException) {
                    $this->addFlash('erro', 'Houve um problema ao contatar o serviço de pagamento. Verifique se o serviço selecionado está operando normalmente e tente novamente.');
                }
                catch (ClientExceptionInterface $clientException)
                {
                    $this->addFlash('erro', 'Não foi possível recuperar suas informações de pagamento. Confira se as suas chaves de acesso foram configuradas adequadamente.');
                }
                catch (\Exception $ex)
                {
                    $this->addFlash('erro', 'Não foi possível concluir a operação');
                }
            } else {
                $this->addFlash('erro', 'Suas chaves do Mercado Pago não foram configuradas. Entre em contato com o suporte do Iroko');
            }

            return $this->redirectToRoute('configuracao_show', ['cnpj' => $empresa->getCnpj()]);
        }
        return $this->render('configuracao/processador_pagamento/new.html.twig', [
            'form' => $form->createView(),
            'epp' => $epp,
        ]);
    }

    /**
     * @Route("/{processador}/edit", name="empresa_pagamento_processador_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Empresa $empresa, EmpresaProcessadorPagamento $epp): Response
    {
        $form = $this->createForm(EmpresaPagamentoProcessadorType::class, $epp, ['operacao' => 'editar']);
        foreach ($epp->getPoliticaParcelamento() as $key => $value) {
            $form->get('politicaParcelamento')->add($key, null, ['data' => $value]);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $politicasForm = $form->get('politicaParcelamento')->all();
            $politicasArray = [];
            foreach ($politicasForm as $row) {
                $politicasArray[] = [
                    'startValue' => $row->get('startValue')->getData(),
                    'endValue' => $row->get('endValue')->getData(),
                    'maxInstallments' => $row->get('maxInstallments')->getData(),
                ];
            }
            $epp->setPoliticaParcelamento($politicasArray);
            $entityManager = $this->doctrine->getManager();
            $entityManager->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
            return $this->redirectToRoute('configuracao_show', ['cnpj' => $empresa->getCnpj()]);
        }
        return $this->render('configuracao/processador_pagamento/new.html.twig', [
            'form' => $form->createView(),
            'epp' => $epp,
        ]);
    }

    /**
     * @Route("/{processador}", name="empresa_pagamento_processador_delete", methods={"POST"})
     */
    public function delete(Request $request, Empresa $empresa, EmpresaProcessadorPagamento $epp): Response
    {
        if ($this->isCsrfTokenValid('delete' . $epp->getEmpresa()->getCnpj() . $epp->getProcessador(), $request->request->get('_token'))) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($epp);
            $entityManager->flush();
            $this->addFlash('sucesso', 'Serviço de pagamento apagado!');
        }
        return $this->redirectToRoute('configuracao_show', ['cnpj' => $empresa->getCnpj()]);
    }
}
