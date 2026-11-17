<?php
declare(strict_types=1);

/**
 * Controle local e determinístico de cobertura por prefixos de cinco dígitos.
 * Cada prefixo representa os 100 CEPs que começam com ele; não prova cidade,
 * acessibilidade ou a existência do endereço informado.
 */
function normalizeDeliveryPrefixes(string $raw): ?string
{
    $values = preg_split('/[,;\\s]+/', trim($raw), -1, PREG_SPLIT_NO_EMPTY);

    if ($values === false || count($values) > 50) {
        throw new InvalidArgumentException('Informe no máximo 50 prefixos de CEP.');
    }

    if ($values === []) {
        return null;
    }

    foreach ($values as $prefix) {
        if (!preg_match('/^\\d{5}$/D', $prefix)) {
            throw new InvalidArgumentException('Cada prefixo de entrega precisa de cinco dígitos.');
        }
    }

    $values = array_values(array_unique($values));
    sort($values, SORT_STRING);

    return implode(',', $values);
}

function deliveryZipSupported(string $cep, ?string $storedPrefixes): bool
{
    $cep = preg_replace('/\\D/', '', $cep);
    if ($storedPrefixes === null || $storedPrefixes === '' ||
        !preg_match('/^\\d{8}$/D', $cep)) {
        return false;
    }

    return in_array(substr($cep, 0, 5), explode(',', $storedPrefixes), true);
}
