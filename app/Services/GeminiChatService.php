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
            
            $systemPrompt = "Tu es l'assistant IA expert et exclusif de 'ShiftUp'. 
            
            MISSION : Guider l'utilisateur précisément vers le contenu (formations, coaching, articles) qui répond à son besoin.

            RÈGLES DE LIENS (TRÈS IMPORTANT) :
            1. UTILISE TOUJOURS LE FORMAT [Nom](Lien) avec les motifs suivants :
               - Programmes/Formations : /programmes/{id} (ex: [Titre de formation](/programmes/42))
               - Séminaires : /seminaires/{id}
               - Offres Spéciales : /offres
               - Coaching : /coaching
               - Articles & Conseils (Gratuit) : /articles-conseils
            2. SOIS PRÉCIS : Si l'utilisateur demande une formation spécifique que tu vois dans la liste DB ci-dessous, donne-lui le lien DIRECT avec son ID exact.

            RÈGLES DE RÉPONSE :
            1. DOMAINE : Réponds UNIQUEMENT sur ShiftUp.
            2. REFUS : Refuse les hors-sujets poliment.
            3. PAS D'INVENTION : Si un programme n'est pas dans la liste DB ci-dessous, dis que tu ne le trouves pas.
            4. CONCISION : Réponds en Markdown de manière concise.

            STRUCTURE DU SITE :
            $siteStructure

            DONNÉES ACTUELLES (DB) :
            $context";

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
        // 1. Programmes et Formations (Payants / Gratuits)
        $programmes = ProgrammeFormation::select('IdProgrammeFormation', 'Titre', 'Descriptions', 'Prix', 'Type')
            ->where('Statut', 'Publié')
            ->get();
        
        // 2. Offres
        $offres = Offre::select('IdOffre', 'Titre', 'Descriptions', 'ReductionGlobal')
            ->where('Statut', 'Publié')
            ->get();
            
        // 3. Coaching
        $coachings = \App\Models\TypeDeCoaching::select('IdTypeCoaching', 'NomDeType', 'Descriptions', 'Prix')
            ->where('Statut', 'Publié')
            ->get();

        $context = "LISTE DES CONTENUS DISPONIBLES :\n\n";
        
        $context .= "--- FORMATIONS ET SÉMINAIRES ---\n";
        foreach ($programmes as $p) {
            $isGratuit = ($p->Prix == 0 || is_null($p->Prix));
            $label = $isGratuit ? "ARTICLE/CONSEIL GRATUIT" : ($p->Type ?? 'FORMATION');
            $route = ($p->Type === 'Seminaire') ? "/seminaires/" : "/programmes/";
            $context .= "- ID:{$p->IdProgrammeFormation} | $label: \"{$p->Titre}\" | Prix: " . ($isGratuit ? '0' : $p->Prix) . "Ar | Lien: $route{$p->IdProgrammeFormation}\n";
            $context .= "  Desc: " . substr($p->Descriptions, 0, 150) . "...\n";
        }

        $context .= "\n--- OFFRES SPÉCIALES (Packs) ---\n";
        foreach ($offres as $o) {
            $reduction = $o->ReductionGlobal ? " (Réduction: {$o->ReductionGlobal}%)" : "";
            $context .= "- \"{$o->Titre}\" : {$o->Descriptions}$reduction | Lien: /offres\n";
        }

        $context .= "\n--- TYPES DE COACHING ---\n";
        foreach ($coachings as $c) {
            $context .= "- \"{$c->NomDeType}\" : {$c->Descriptions} (Prix: {$c->Prix}Ar) | Lien: /coaching\n";
        }

        return $context;
    }
}
