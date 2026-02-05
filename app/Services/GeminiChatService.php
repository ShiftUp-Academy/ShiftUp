<?php

namespace App\Services;

use App\Models\Offre;
use App\Models\ProgrammeFormation;
use Gemini\Laravel\Facades\Gemini;

class GeminiChatService
{
    public function getResponse(string $userMessage, array $history = [])
    {
        try {
            \Log::info('GeminiChatService: Starting request', ['message' => $userMessage]);
            
            $siteStructure = "STRUCTURE ET COMPOSANTS DU SITE :
            - Header (Haut) : Contient le logo ShiftUp (retour accueil) et le menu principal.
            - Navigation Principale (Menu) : 
                * 'Accueil' : Page principale.
                * 'Offres' : Packs promotionnels et réductions exclusives.
                * 'Programmes' : Liste complète des formations et séminaires.
                * 'Coachings' : Services d'accompagnement personnalisé.
                * 'Témoignages' : Retours d'expérience de nos membres.
                * 'Contact' : Pour nous joindre directement.
                * 'L'organisme' : Informations sur notre structure.
            - Sections de la page d'Accueil :
                * 'Hero' : Présentation principale de ShiftUp.
                * 'Séminaires' : Nos événements intensifs de formation.
                * 'Ressources Gratuites' : Formations accessibles sans frais.
                * 'Événements' : Calendrier de nos prochaines rencontres.
                * 'Témoignages' : Mur social de nos succès.
                * 'Vidéos' : Galerie de contenus inspirants.
                * 'Fondateur' (Founder Section) : L'histoire et la vision de l'initiateur.
            - Boutons Flottants (En bas) : 
                * 'Se connecter' : Pour accéder à votre espace membre.
                * 'Les programmes' : Raccourci vers les formations.
                * 'Coaching' : Raccourci vers le support personnalisé.
                * 'Bouton Flèche' : Pour remonter en haut de la page.
                * 'Icône Enveloppe' : Pour ouvrir le formulaire de contact.
                * 'Le Robot Assistant' : C'est moi ! On clique dessus pour voir les notifications ou me parler.";

            $context = $this->getSiteContext();
            
            $systemPrompt = "Tu es l'assistant IA expert de 'ShiftUp'. 
            
            CONSIGNES COMPORTEMENTALES :
            1. NE TE PRÉSENTE JAMAIS après le premier message. Ignore les salutations si la discussion est lancée.
            2. GUIDE L'UTILISATEUR avec précision dans l'interface. Si on cherche des avis, dis d'aller dans 'Témoignages' du menu. Si on cherche du gratuit, parle de la section 'Ressources Gratuites' sur l'accueil.
            3. Tu connais à la fois la ARCHITECTURE du site (sections, menus, boutons) et les DONNÉES (formations, prix).

            CONNAISSANCE DU SITE :
            $siteStructure

            DONNÉES ACTUELLES (DB) :
            $context
            
            Réponds de manière concise, efficace, en Markdown.";

            $fullPrompt = "INSTRUCTIONS :\n$systemPrompt\n\nMESSAGE UTILISATEUR : $userMessage";

            \Log::info('GeminiChatService: Calling Gemini API');
            $result = Gemini::generativeModel('gemini-2.5-flash')->generateContent($fullPrompt);
            
            \Log::info('GeminiChatService: Response received');
            return $result->text();
        } catch (\Exception $e) {
            \Log::error('GeminiChatService: Error occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function getSiteContext(): string
    {
        $programmes = ProgrammeFormation::select('Titre', 'Descriptions', 'Prix', 'Type')->where('Statut', 'Publié')->get();
        $offres = Offre::where('Statut', 'Publié')->get();

        $context = "PROGRAMMES ET FORMATIONS :\n";
        foreach ($programmes as $p) {
            $type = $p->Type ?? 'Formation';
            $context .= "- [$type] {$p->Titre} : {$p->Descriptions} (Prix indicatif: {$p->Prix}Ar)\n";
        }

        $context .= "\nOFFRES SPECIALES ET PACKS :\n";
        foreach ($offres as $o) {
            $reduction = $o->ReductionGlobal ? " (Réduction: {$o->ReductionGlobal}%)" : "";
            $context .= "- {$o->Titre} : {$o->Descriptions}$reduction\n";
        }
        return $context;
    }
}
