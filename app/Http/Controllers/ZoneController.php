<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\zone_1;
use App\Models\zone_2;
use App\Models\zone_3;
use App\Models\zone_4;
use App\Models\zone_5;
use App\Models\zone_6;
use App\Models\zone_7;
use App\Models\zone_8;
use App\Models\zone_9;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ZoneImport;

class ZoneController extends Controller
{
    public function show($id)
    {
        $model = "App\\Models\\zone_{$id}";
        
        if (class_exists($model)) {
            return view('zone.index', ['articles' => $model::paginate(10)]);
        }

        abort(404, 'Zone non trouvée');
    }

    public function index()
    {
        $model = "App\\Models\\zone_{$id}";
        
        if (class_exists($model)) {
            return view('zone.index', ['articles' => $model::paginate(10)]);
        }

        abort(404, 'Zone non trouvée');
    }

    public function importCSV(Request $request, $id)
{
    $request->validate([
        'csv_file' => 'required|mimes:csv,txt|max:2048' // max 2MB
    ]);

    $file = $request->file('csv_file');

    if (!$file) {
        return back()->with('error', 'Aucun fichier sélectionné.');
    }

    $modelClass = "App\\Models\\zone_{$id}";

    if (!class_exists($modelClass)) {
        return back()->with('error', "La zone {$id} est invalide.");
    }

    set_time_limit(1200); // Augmente le temps d'exécution si le fichier est volumineux

    try {
        $handle = fopen($file->getPathname(), 'r');

        if (!$handle) {
            return back()->with('error', "Impossible d'ouvrir le fichier.");
        }

        fgetcsv($handle, 1000, ';'); // Ignore la ligne d'en-tête

        $articles = [];

        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            // Vérifier si la ligne a assez de colonnes (ex: 4 colonnes attendues)
            if (count($data) < 4) {
                continue; // Ignore la ligne incorrecte
            }

            $articles[] = [
                'numéro' => $data[0] ?? null,
                'zone' => $data[1] ?? null,
                'coordonnées' => $data[2] ?? null,
                'type_pollution' => $data[3] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        fclose($handle);

        if (!empty($articles)) {
            $modelClass::insert($articles); // Insertion en batch pour optimiser
        }

        return back()->with('success', "Importation réussie pour la Zone {$id}");
    } catch (\Exception $e) {
        return back()->with('error', "Erreur lors de l'importation : " . $e->getMessage());
    }
    }

}