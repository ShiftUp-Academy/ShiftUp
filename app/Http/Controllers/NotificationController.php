<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Liste des notifications de l'utilisateur connecté.
     */
    public function index(Request $request)
    {
        $utilisateur = Auth::user();
        
        if (!$utilisateur) {
            return response()->json([], 401);
        }

        $notifications = Notification::where('IdDestinataire', $utilisateur->IdUtilisateur)
            ->where('TypeDestinataire', get_class($utilisateur))
            ->orderBy('DateCreation', 'desc')
            ->limit(20)
            ->get();

        // Retourner JSON uniquement pour les requêtes AJAX/Fetch
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json($notifications);
        }

        // Sinon rediriger (éviter l'erreur Inertia)
        return redirect('/');
    }

    /**
     * Marquer une notification comme lue.
     */
    public function markAsRead($id)
    {
        $notification = Notification::find($id);

        if ($notification && $notification->IdDestinataire == Auth::id()) {
            $notification->marquerCommeLu();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * Marquer toutes les notifications comme lues.
     */
    public function markAllAsRead()
    {
        Notification::where('IdDestinataire', Auth::id())
            ->whereNull('DateLecture')
            ->update(['DateLecture' => now()]);

        return response()->json(['success' => true]);
    }
}
