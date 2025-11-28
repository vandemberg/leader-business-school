<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoReportController extends Controller
{
    public function store(Request $request, Video $video)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $report = VideoReport::create([
            'video_id' => $video->id,
            'user_id' => Auth::id(),
            'reason' => $request->reason ?? 'Video indisponível',
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reporte enviado com sucesso. Obrigado pelo feedback!',
            'report' => $report,
        ]);
    }
}
