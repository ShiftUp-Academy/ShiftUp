<?php

namespace App\Http\Controllers;

use App\Models\HeropageVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HeropageVideoController extends Controller
{
    public function update(Request $request)
    {
        // On suppose que l'admin a le rôle 'admin'
        if (!Auth::check() || Auth::user()->Role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $request->validate([
            'video_url' => 'required|url',
        ]);

        // On ne garde qu'une seule vidéo
        $video = HeropageVideo::first() ?: new HeropageVideo();
        $video->video_url = $request->input('video_url');
        $video->save();

        return back()->with('message', 'Vidéo mise à jour avec succès');
    }

    public static function getHeroVideo()
    {
        $video = HeropageVideo::first();
        return $video ? $video->video_url : null;
    }
}
