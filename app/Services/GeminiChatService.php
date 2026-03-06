<?php

namespace App\Services;

use App\Models\Offre;
use App\Models\ProgrammeFormation;
use Gemini\Laravel\Facades\Gemini;

class GeminiChatService
{
    public function translateText(string $text, string $targetLocale)
    {
        try {
            $prompt = "Expert Translator. Translate from French to " . $this->getLanguageName($targetLocale) . ". 
            RULES:
            - Output ONLY the translated text.
            - NO explanations.
            - Keep formatting (HTML tags, etc) if present.
            - If it's already in " . $this->getLanguageName($targetLocale) . ", just return it.
            
            TEXT: $text";

            $result = Gemini::generativeModel('gemini-1.5-flash')->generateContent($prompt);
            $translated = trim($result->text());
            \Log::info("Gemini Translated: [$text] ($targetLocale) -> [$translated]");
            return $translated;
        } catch (\Exception $e) {
            \Log::error('Gemini Translation Error: ' . $e->getMessage());
            return $text;
        }
    }

    private function getLanguageName($code)
    {
        return [
            'fr' => 'Français',
            'en' => 'Anglais',
            'mg' => 'Malgache'
        ][$code] ?? 'Français';
    }

    public function getResponse(string $userMessage, array $history = [])
    {
        try {
            \Log::info('GeminiChatService: Starting request', ['message' => $userMessage]);
            
            $siteStructure = "STRUCTURE ET LIENS DU SITE (UTILISE LES [Nom](LienMD) !) :
            - Accueil : /
            - Offres (Promotionnels) : /offres
            - Programmes (Formations) : /programmes
            - Coachings (Personnalisé) : /coaching
            - Témoignages (Avis clients) : /temoignages
            - Contact : /contact
            - Panier (Mes achats) : /panier
            - Mes Réservations (Historique) : /reservations
            - Mes Réussites (Badges) : /mes-reussites
            - L'organisme : /organisme";

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
            
            Réponds de manière concise, efficace, en Markdown. 
            IMPORTANT : Propose toujours des liens cliquables à l'utilisateur vers les sections correspondantes s'il en a besoin.";

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
