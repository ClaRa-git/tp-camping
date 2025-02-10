<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class NavExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * Méthode de formatage des nombres pour les prix
     * @param int $number
     * @param int $decimals
     * @param string $thousandsSep
     * @param string $decPoint
     * @return string
     */
    public function numberFormat($number, $decimals = 2, $thousandsSep = ',', $decPoint = '.') : string
    {
        if ($number != 0) {
            return number_format($number, $decimals, $thousandsSep, $decPoint) . '€';
        } else {
            return 'Gratuit';
        }
    }

    /**
     * Méthode pour afficher le badge de l'utilisateur
     * @param array $roles
     * @return string
     */
    public function badgeUser($roles): string
    {
        switch ($roles[0]) {
            case 'ROLE_ADMIN':
                return  '<span class="badge text-bg-warning">Admin</span>';
            case 'ROLE_USER':
                return '<span class="badge text-bg-primary">Client</span>';
            default:
                return '<span class="badge text-bg-secondary">Inconnu</span>';
        }
    }

}
