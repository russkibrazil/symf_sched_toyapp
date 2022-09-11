<?php

namespace App\Service;

use Symfony\Component\Uid\Uuid;
use App\Entity\FuncionarioLocalTrabalho;
use Doctrine\Persistence\ManagerRegistry;

class LicensingHelper
{
    private const LEN_CHECK = 40;
    private const LEN_ID = 32;
    private const LEN_LIC = 3;
    private const LEN_MEN = 10;
    private const UID_ID = '70d40631-b4b8-4e5d-9bae-1cea67715a1b';

    private $doctrine;
    private $path_licenca;

    public function __construct(ManagerRegistry $mr)
    {
        $this->doctrine = $mr;
        $this->path_licenca  = dirname(__DIR__, 2) . '/config/.license.l';
    }

    private function obterLicenca(string $empresa)
    {
        $curlHandler = curl_init($_ENV['LICENSE_HOME'] . 'cliente=' . $empresa . '&prdouto=iroko');
        curl_setopt_array($curlHandler, [
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYSTATUS => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true
        ]);
        $streamContent = curl_exec($curlHandler);
        curl_close($curlHandler);
        if ($streamContent === false)
        {
            throw new \Exception('curl cannot handle the request');
        }
        $streamDecoded = json_decode($streamContent, true);
        $arquivo = fopen($this->path_licenca, 'w');
        fwrite($arquivo, $streamDecoded[0]['licenseString']);
        fclose($arquivo);
    }

    public function validarPagamento(string $empresa): bool
    {
        if (!file_exists($this->path_licenca))
        {
            $this->obterLicenca($empresa);
            $licFile = fopen($this->path_licenca, 'r');
        }
        else {
            // Verficando validade do arquivo
            $licFile = fopen($this->path_licenca, 'r');
            fseek($licFile, (0-(self::LEN_CHECK + self::LEN_MEN)), SEEK_END);
            $valid = fread($licFile, self::LEN_MEN);
            if ($valid <= time())
            {
                $statsL = stat($this->path_licenca);
                if ($statsL['mtime'] <= (time() - (15*60)))
                {
                    fclose($licFile);
                    $this->obterLicenca($empresa);
                    $licFile = fopen($this->path_licenca, 'r');
                    fseek($licFile, (0-(self::LEN_CHECK + self::LEN_MEN)), SEEK_END);
                    $valid = fread($licFile, self::LEN_MEN);
                    if ($valid <= time())
                        return false;
                }
            }
        }

        // verificando hashing
        fseek($licFile,0);
        $checks = fread($licFile, self::LEN_CHECK);
        fseek($licFile, (self::LEN_CHECK * (0-1)), SEEK_END);
        $checke = fread($licFile, self::LEN_CHECK);
        if (strlen($checke) != strlen($checks))
        {
            return false;
        }
        if ($checke !== $checks)
        {
            return false;
        }
        // verificando empresa
        fseek($licFile, self::LEN_CHECK);
        $uid = fread($licFile, 8) . '-' . fread($licFile, 4) . '-' . fread($licFile, 4) . '-' . fread($licFile, 4) . '-' . fread($licFile, 12);
        if (!Uuid::isValid($uid))
        {
            return false;
        }
        $uuid = Uuid::fromString($uid);
        $me = Uuid::v5(Uuid::fromString(self::UID_ID), $empresa);
        if ($uuid->compare($me) !== 0)
        {
            return false;
        }

        // verificando pagamento
        fseek($licFile, (0-self::LEN_CHECK-self::LEN_MEN), SEEK_END);
        $mens = (int) fread($licFile, self::LEN_MEN);
        if ($mens <= time())
        {
            return false;
        }
        fclose($licFile);
        return true;
    }
    public function validarCotaFuncionarios(string $empresa)
    {
        if (!$this->validarPagamento($empresa))
        {
            return false;
        }
        $licFile = fopen($this->path_licenca, 'r');
        fseek($licFile, (self::LEN_CHECK + self::LEN_ID));
        $nFun = (int) fread($licFile, self::LEN_LIC);
        $resultados = count($this->doctrine->getManager()->getRepository(FuncionarioLocalTrabalho::class)->findBy(['cnpj' => $empresa]));
        if ($resultados >= $nFun)
        {
            return false;
        }
        fclose($licFile);
        return true;
    }
}
