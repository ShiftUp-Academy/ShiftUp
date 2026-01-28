<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::orderBy('IdCategorie', 'desc')->get();    
        return Inertia::render('PagesAdmin/AdminCategories', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Nom' => 'required|string|max:255',
            'Descriptions' => 'nullable|string',
            'Statut' => 'required|string|in:Publié,Dépublié',
        ]);

        Categorie::create($validated);

        return back()->with('success', 'Catégorie ajoutée.');
    }

    public function update(Request $request, $id)
    {
        $categorie = Categorie::findOrFail($id);
        
        $validated = $request->validate([
            'Nom' => 'required|string|max:255',
            'Descriptions' => 'nullable|string',
            'Statut' => 'required|string|in:Publié,Dépublié',
        ]);

        $categorie->update($validated);
    }
    
    public function destroy($id)
    {
        Categorie::findOrFail($id)->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }
}
