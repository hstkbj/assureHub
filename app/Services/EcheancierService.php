<?php

namespace App\Services;

use App\Models\Assurance;
use App\Models\Echeance;
use Carbon\Carbon;

class EcheancierService
{
    public function genererEcheancier(Assurance $assurance): void
    {
        $debut = Carbon::parse($assurance->date_debut);
        $fin   = Carbon::parse($assurance->date_fin);

        $dates = $this->genererDates($debut, $fin, $assurance->frequence_paiement);
        $nbEcheances = count($dates);

        if ($nbEcheances === 0) {
            return;
        }

        $montantParEcheance = floor(($assurance->montant_total / $nbEcheances) * 100) / 100;
        $sommeArrondie = $montantParEcheance * ($nbEcheances - 1);
        $derniereEcheance = round($assurance->montant_total - $sommeArrondie, 2);

        foreach ($dates as $index => $date) {
            $estDerniere = ($index === $nbEcheances - 1);

            Echeance::create([
                'assurance_id'    => $assurance->id,
                'numero_echeance' => $index + 1,
                'date_echeance'   => $date,
                'montant_du'      => $estDerniere ? $derniereEcheance : $montantParEcheance,
                'montant_paye'    => 0,
                'statut'          => 'impaye',
            ]);
        }
    }

    protected function genererDates(Carbon $debut, Carbon $fin, string $frequence): array
    {
        // Paiement en une seule fois : une seule échéance, due au début du contrat
        if ($frequence === 'enBloc') {
            return [$debut->copy()];
        }

        $dates = [];
        $courant = $debut->copy();

        while ($courant->lessThanOrEqualTo($fin)) {
            $dates[] = $courant->copy();

            $courant = match ($frequence) {
                'journalier'   => $courant->addDay(),
                'hebdomadaire' => $courant->addWeek(),
                'mensuel'      => $courant->addMonth(),
                default        => $courant->addMonth(),
            };
        }

        return $dates;
    }

    public function enregistrerPaiement(Echeance $echeance, float $montant): void
    {
        $echeance->montant_paye += $montant;

        if ($echeance->montant_paye >= $echeance->montant_du) {
            $echeance->statut = 'paye';
        } elseif ($echeance->montant_paye > 0) {
            $echeance->statut = 'partiel';
        }

        $echeance->save();

        $assurance = $echeance->assurance;
        $assurance->montant_paye_cumule += $montant;

        // Si toutes les échéances sont payées, on peut clôturer le contrat
        $toutesPayees = $assurance->echeances()->where('statut', '!=', 'paye')->doesntExist();
        if ($toutesPayees) {
            $assurance->statut = 'solder';
        }

        $assurance->save();
    }
}
