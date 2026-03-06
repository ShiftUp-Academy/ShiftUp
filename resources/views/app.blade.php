<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Titre & SEO --}}
        <title>ShiftUp Academy — Devenez un Entrepreneur Libre | Madagascar</title>
        <meta name="description" content="ShiftUp Academy à Antananarivo, Madagascar — Formation, coaching et séminaires pour les dirigeants de TPE/PME et cadres en reconversion. Devenez un Entrepreneur Libre avec Nantenaina Randria.">
        <meta name="keywords" content="ShiftUp, ShiftUp Academy, entrepreneur libre, formation Madagascar, coaching Antananarivo, séminaire Madagascar, business coaching, développement personnel, marketing digital, fiofanana, mpandraharaha afaka, Nantenaina Randria, formation en ligne Madagascar, coaching entreprise Antananarivo, TPE PME Madagascar">
        <meta name="author" content="ShiftUp Academy">
        <meta name="robots" content="index, follow">
        <meta name="content-language" content="fr, mg, en">

        {{-- Ciblage géographique Madagascar --}}
        <meta name="geo.region" content="MG-T">
        <meta name="geo.placename" content="Antananarivo, Madagascar">
        <meta name="geo.position" content="-18.9137;47.5361">
        <meta name="ICBM" content="-18.9137, 47.5361">

        {{-- URL Canonical --}}
        <link rel="canonical" href="{{ url()->current() }}">

        {{-- Favicon --}}
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="icon" type="image/png" sizes="192x192" href="/flavicon.png">
        <link rel="apple-touch-icon" href="/flavicon.png">

        {{-- Open Graph (Facebook, LinkedIn, etc.) --}}
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="ShiftUp Academy — Devenez un Entrepreneur Libre">
        <meta property="og:description" content="Formation, coaching et séminaires à Antananarivo pour propulser votre entreprise et devenir un Entrepreneur Libre.">
        <meta property="og:image" content="{{ asset('flavicon.png') }}">
        <meta property="og:site_name" content="ShiftUp Academy">
        <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
        <meta property="og:locale:alternate" content="mg">
        <meta property="og:locale:alternate" content="fr">
        <meta property="og:locale:alternate" content="en">

        {{-- Twitter Card --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="ShiftUp Academy — Entrepreneur Libre | Madagascar">
        <meta name="twitter:description" content="Formation, coaching et séminaires à Antananarivo pour devenir un Entrepreneur Libre.">
        <meta name="twitter:image" content="{{ asset('flavicon.png') }}">

        {{-- Theme color --}}
        <meta name="theme-color" content="#050505">

        {{-- JSON-LD : Organisation --}}
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "Organization",
            "name": "ShiftUp Academy",
            "alternateName": ["ShiftUp", "ShiftUp Madagascar"],
            "url": "{{ url('/') }}",
            "logo": "{{ asset('flavicon.png') }}",
            "description": "ShiftUp Academy à Antananarivo, Madagascar — Formation, coaching et séminaires pour les dirigeants de TPE/PME. Devenez un Entrepreneur Libre.",
            "address": {
                "@@type": "PostalAddress",
                "streetAddress": "Lot II A 12 E Ampandrana",
                "addressLocality": "Antananarivo",
                "addressRegion": "Analamanga",
                "postalCode": "101",
                "addressCountry": "MG"
            },
            "contactPoint": {
                "@@type": "ContactPoint",
                "telephone": "+261383454081",
                "contactType": "customer service",
                "email": "hello@shiftup.academy",
                "availableLanguage": ["French", "Malagasy", "English"]
            },
            "sameAs": [
                "https://www.facebook.com/nante.randria.gel",
                "https://www.youtube.com/@nr.shiftup"
            ],
            "founder": {
                "@@type": "Person",
                "name": "Nantenaina Randria",
                "nationality": "Malagasy"
            },
            "areaServed": {
                "@@type": "Country",
                "name": "Madagascar"
            }
        }
        </script>

        {{-- JSON-LD : LocalBusiness (référencement local Madagascar) --}}
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "EducationalOrganization",
            "name": "ShiftUp Academy",
            "image": "{{ asset('flavicon.png') }}",
            "url": "{{ url('/') }}",
            "telephone": "+261383454081",
            "email": "hello@shiftup.academy",
            "address": {
                "@@type": "PostalAddress",
                "streetAddress": "Lot II A 12 E Ampandrana",
                "addressLocality": "Antananarivo",
                "addressRegion": "Analamanga",
                "postalCode": "101",
                "addressCountry": "MG"
            },
            "geo": {
                "@@type": "GeoCoordinates",
                "latitude": -18.9137,
                "longitude": 47.5361
            },
            "openingHoursSpecification": [
                {
                    "@@type": "OpeningHoursSpecification",
                    "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
                    "opens": "09:00",
                    "closes": "18:00"
                },
                {
                    "@@type": "OpeningHoursSpecification",
                    "dayOfWeek": "Saturday",
                    "opens": "12:00",
                    "closes": "18:00"
                }
            ],
            "priceRange": "Ar",
            "areaServed": {
                "@@type": "Country",
                "name": "Madagascar"
            },
            "knowsLanguage": ["fr", "mg", "en"],
            "sameAs": [
                "https://www.facebook.com/nante.randria.gel",
                "https://www.youtube.com/@nr.shiftup"
            ]
        }
        </script>

        {{-- JSON-LD : WebSite (pour la boîte de recherche Google) --}}
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "WebSite",
            "name": "ShiftUp Academy",
            "url": "{{ url('/') }}",
            "potentialAction": {
                "@@type": "SearchAction",
                "target": "{{ url('/programmes') }}?q={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        }
        </script>

        {{-- JSON-LD : SiteNavigationElement (sitelinks Google) --}}
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@graph": [
                {
                    "@@type": "SiteNavigationElement",
                    "name": "Accueil",
                    "url": "{{ url('/') }}"
                },
                {
                    "@@type": "SiteNavigationElement",
                    "name": "Programmes de Formation",
                    "url": "{{ url('/programmes') }}"
                },
                {
                    "@@type": "SiteNavigationElement",
                    "name": "Toutes les Catégories",
                    "url": "{{ url('/toutcategorie') }}"
                },
                {
                    "@@type": "SiteNavigationElement",
                    "name": "Coaching",
                    "url": "{{ url('/coaching') }}"
                },
                {
                    "@@type": "SiteNavigationElement",
                    "name": "Consultations",
                    "url": "{{ url('/consultations') }}"
                },
                {
                    "@@type": "SiteNavigationElement",
                    "name": "Séminaires & Lives",
                    "url": "{{ url('/live') }}"
                },
                {
                    "@@type": "SiteNavigationElement",
                    "name": "Offres",
                    "url": "{{ url('/offres') }}"
                },
                {
                    "@@type": "SiteNavigationElement",
                    "name": "Articles et Conseils",
                    "url": "{{ url('/articles-conseils') }}"
                },
                {
                    "@@type": "SiteNavigationElement",
                    "name": "Témoignages",
                    "url": "{{ url('/temoignages') }}"
                },
                {
                    "@@type": "SiteNavigationElement",
                    "name": "L'Organisme",
                    "url": "{{ url('/organisme') }}"
                },
                {
                    "@@type": "SiteNavigationElement",
                    "name": "Contact",
                    "url": "{{ url('/contact') }}"
                }
            ]
        }
        </script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>