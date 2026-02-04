<?php

namespace App\Services;

use App\Models\Offre;
use App\Models\ProgrammeFormation;
use Gemini\Laravel\Facades\Gemini;

class GeminiChatService
{
    public function getResponse(string $userMessage, array $history = [])
    {
        $context = $this->getSiteContext();
        
        $systemPrompt = "Tu es l'assistant IA officiel de 'ShiftUp', une plateforme de formation et de coaching premium. 
        Ton but est d'aider les utilisateurs à trouver les meilleures formations, répondre à leurs questions sur le contenu du site, et les guider dans leur apprentissage.
        
        Voici les informations actuelles sur nos offres et programmes :
        $context
        
        Consignes :
        1. Sois poli, professionnel et encourageant.
        2. Si tu ne connais pas la réponse, suggère poliment de contacter le support.
        3. Réponds en Markdown pour que ce soit lisible.
        4. Utilise les informations fournies pour être précis sur les prix et le contenu.
        5. N'invente pas de fausses promotions ou de nouveaux programmes qui n'existent pas dans le contexte fourni.";

        $fullPrompt = "CONTEXT INTERNE SHIFTUP :\n$systemPrompt\n\nMESSAGE UTILISATEUR : $userMessage";

        $result = Gemini::gemini15Flash()->generateContent($fullPrompt);

        return $result->text();
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
