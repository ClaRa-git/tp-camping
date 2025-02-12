<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class NavExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * Méthode permettant de trier les items
     * @return array
     */
    public function filtersItems()
    {
        return [
            ['label' => 'Type', 'filter' => 're.type ASC', 'icon' => 'fa-sharp fa-solid fa-arrow-up'],
            ['label' => 'Type', 'filter' => 're.type DESC', 'icon' => 'fa-sharp fa-solid fa-arrow-down'],
            ['label' => 'Statut', 'filter' => 'r.status ASC', 'icon' => 'fa-sharp fa-solid fa-arrow-up'],
            ['label' => 'Statut', 'filter' => 'r.status DESC', 'icon' => 'fa-sharp fa-solid fa-arrow-down'],
            ['label' => 'Date de début', 'filter' => 'r.dateStart ASC', 'icon' => 'fa-sharp fa-solid fa-arrow-up'],
            ['label' => 'Date de début', 'filter' => 'r.dateStart DESC', 'icon' => 'fa-sharp fa-solid fa-arrow-down'],
            ['label' => 'Date de fin', 'filter' => 'r.dateEnd ASC', 'icon' => 'fa-sharp fa-solid fa-arrow-up'],
            ['label' => 'Date de fin', 'filter' => 'r.dateEnd DESC', 'icon' => 'fa-sharp fa-solid fa-arrow-down'],
        ];
    }

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
