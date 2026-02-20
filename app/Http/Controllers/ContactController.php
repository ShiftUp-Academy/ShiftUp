<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'sujet' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
        ]);

        try {
            // Find the main admin and get their contact email
            $adminProfile = \App\Models\ProfilUtilisateur::whereHas('utilisateur', function($q) {
                $q->where('Role', 'admin');
            })->whereNotNull('EmailContact')->first();

            $recipient = $adminProfile 
                ? $adminProfile->EmailContact 
                : config('mail.from.address');

            Mail::to($recipient)->send(new ContactMail($data));
            
            return response()->json(['message' => 'Message envoyé avec succès !'], 200);
        } catch (\Exception $e) {
            \Log::error("Contact form error: " . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de l\'envoi du message.'], 500);
        }
    }
}
