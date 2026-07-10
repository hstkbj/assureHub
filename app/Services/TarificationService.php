<?php

namespace App\Services;

use App\Models\GrilleTarif;
use Carbon\Carbon;

class TarificationService
{
    /**
     * Calcule le montant de la prime en interrogant la grille tarifaire
     * (base CENTRALE, partagée par toutes les entreprises).
     *
     * @param array $donnees categorie_id, carburant, type_client, duree,
     *                       puissance_fiscale, place (optionnel), poids (optionnel)
     * @return float
     */
    public function calculerMontant(array $donnees): float
    {
        $tarif = GrilleTarif::where('categorie_id', $donnees['categorie_id'])
            ->where('carburant', $donnees['carburant'])
            ->where('type_client', $donnees['type_client'])
            ->where('duree', $donnees['duree'])
            ->where('cv_min', '<=', $donnees['puissance_fiscale'])
            ->where('cv_max', '>=', $donnees['puissance_fiscale'])
            ->where('date_debut_validite', '<=', Carbon::today())
            ->where(function ($q) {
                $q->whereNull('date_fin_validite')
                  ->orWhere('date_fin_validite', '>=', Carbon::today());
            })
            ->when(!empty($donnees['place']), fn($q) => $q->where('place', $donnees['place']))
            ->when(!empty($donnees['poids']), fn($q) => $q->where('poids', $donnees['poids']))
            ->first();

        if (!$tarif) {
            throw new \Exception("Aucun tarif ne correspond à ces caractéristiques. Vérifiez la grille tarifaire.");
        }

        return (float) $tarif->montant;
    }

    /**
     * Calcule la date de fin du contrat selon la durée choisie.
     * Adaptez les libellés selon ceux que vous utilisez réellement
     * dans votre grille tarifaire (ex: "1MOIS", "3MOIS", "1ANS"...).
     */
    public function calculerDateFin(string $dateDebut, string $duree): string
    {
        $debut = Carbon::parse($dateDebut);

        return match ($duree) {
            '5JOURS'  => $debut->copy()->addDays(5)->toDateString(),
            '10JOURS' => $debut->copy()->addDays(10)->toDateString(),
            '20JOURS' => $debut->copy()->addDays(20)->toDateString(),
            '1MOIS'   => $debut->copy()->addMonth()->toDateString(),
            '2MOIS'   => $debut->copy()->addMonths(2)->toDateString(),
            '3MOIS'   => $debut->copy()->addMonths(3)->toDateString(),
            '6MOIS'   => $debut->copy()->addMonths(6)->toDateString(),
            '1ANS'    => $debut->copy()->addYear()->toDateString(),
            default   => throw new \Exception("Durée inconnue : {$duree}"),
        };
    }
}
