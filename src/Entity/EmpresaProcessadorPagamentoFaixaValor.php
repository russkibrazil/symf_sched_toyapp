<?php

namespace App\Entity;

/**
 * Classe para determinar as faixas de valores dos parcelamentos
 */
class EmpresaProcessadorPagamentoFaixaValor
{
  private $startValue = '0.00';
  private $endValue;
  private $maxInstallments = 1;

  public function getStartValue(): string
  {
    return $this->startValue;
  }
  public function setStartValue(string $startValue): self
  {
    $this->startValue = $startValue;
    return $this;
  }
  public function getEndValue(): string
  {
    return $this->endValue;
  }
  public function setEndValue(string $endValue): self
  {
    $this->endValue = $endValue;
    return $this;
  }
  public function getMaxInstallments(): int
  {
    return $this->maxInstallments;
  }
  public function setMaxInstallments(int $installments): self
  {
    $this->maxInstallments = $installments;
    return $this;
  }
}
