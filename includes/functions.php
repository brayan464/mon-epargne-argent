<?php
function formatMontant($montant) {
    $montant = floatval($montant ?: 0); // empêche NULL
    return number_format($montant, 2, ',', ' ') . ' FCFA';
}

function getProgress($saved, $target) {
    if ($target <= 0) return 0;
    return round(($saved / $target) * 100, 1);
}
