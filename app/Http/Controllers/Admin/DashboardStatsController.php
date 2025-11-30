<?php

namespace App\Http\Controllers\Admin;

use App\Models\VideoRating;
use App\Models\WatchVideo;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardStatsController extends Controller
{
    public function index(Request $request)
    {
        $period = $this->normalizePeriod($request->query('period', '30d'));
        [$startDate, $endDate] = $this->resolveDateRange($period);

        $platformId = $this->getPlatformId($request);
        $videoIds = $this->getPlatformVideoIds($platformId);

        if ($videoIds->isEmpty()) {
            return response()->json($this->emptyResponse($period));
        }

        if ($startDate === null) {
            $firstWatchDate = WatchVideo::query()
                ->whereIn('video_id', $videoIds)
                ->min('updated_at');

            $startDate = $firstWatchDate
                ? Carbon::parse($firstWatchDate)->startOfDay()
                : $endDate->copy()->subDays(29)->startOfDay();
        }

        $summary = $this->buildSummaryStats($videoIds, $startDate, $endDate);
        $engagement = $this->buildEngagementSeries($videoIds, $startDate, $endDate);
        $topStudents = $this->buildTopStudents($videoIds, $startDate, $endDate);
        $ratings = $this->buildRatingsOverview($videoIds, $startDate, $endDate);

        return response()->json([
            'period' => $period,
            'summary' => $summary,
            'engagement' => $engagement,
            'topStudents' => $topStudents,
            'ratings' => $ratings,
        ]);
    }

    protected function normalizePeriod(string $period): string
    {
        $allowed = ['7d', '30d', 'month', 'all'];

        return in_array($period, $allowed, true) ? $period : '30d';
    }

    /**
     * @return array{0: ?Carbon, 1: Carbon}
     */
    protected function resolveDateRange(string $period): array
    {
        $now = Carbon::now()->endOfDay();

        return match ($period) {
            '7d' => [$now->copy()->subDays(6)->startOfDay(), $now],
            '30d' => [$now->copy()->subDays(29)->startOfDay(), $now],
            'month' => [$now->copy()->startOfMonth(), $now],
            default => [null, $now],
        };
    }

    protected function buildSummaryStats(Collection $videoIds, Carbon $startDate, Carbon $endDate): array
    {
        $baseWatchQuery = WatchVideo::query()
            ->from('watch_videos as wv')
            ->whereIn('wv.video_id', $videoIds)
            ->whereBetween('wv.updated_at', [$startDate, $endDate]);

        $totalWatchEntries = (clone $baseWatchQuery)->count();

        $completedWatchQuery = (clone $baseWatchQuery)->where('wv.status', WatchVideo::STATUS_WATCHED);
        $completedCount = (clone $completedWatchQuery)->count();

        $totalSecondsWatched = (clone $completedWatchQuery)
            ->join('videos as v', 'v.id', '=', 'wv.video_id')
            ->sum('v.time_in_seconds');

        $averageWatchMinutes = $completedCount > 0
            ? round(($totalSecondsWatched / $completedCount) / 60, 1)
            : 0;

        $completionRate = $totalWatchEntries > 0
            ? round(($completedCount / $totalWatchEntries) * 100, 1)
            : 0;

        $totalStudents = (clone $baseWatchQuery)
            ->distinct('wv.user_id')
            ->count('wv.user_id');

        $positiveReactions = VideoRating::query()
            ->whereIn('video_id', $videoIds)
            ->where('rating', '>=', 4)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        return [
            'averageWatchMinutes' => $averageWatchMinutes,
            'completionRate' => $completionRate,
            'positiveReactions' => $positiveReactions,
            'totalStudents' => $totalStudents,
        ];
    }

    protected function buildEngagementSeries(Collection $videoIds, Carbon $startDate, Carbon $endDate): array
    {
        $dateExpression = DB::raw('DATE(COALESCE(watch_videos.finished_at, watch_videos.updated_at))');

        $rawEngagement = WatchVideo::query()
            ->selectRaw('DATE(COALESCE(watch_videos.finished_at, watch_videos.updated_at)) as watch_date')
            ->selectRaw('COUNT(*) as total')
            ->whereIn('watch_videos.video_id', $videoIds)
            ->where('watch_videos.status', WatchVideo::STATUS_WATCHED)
            ->whereBetween(DB::raw('COALESCE(watch_videos.finished_at, watch_videos.updated_at)'), [$startDate, $endDate])
            ->groupBy($dateExpression)
            ->orderBy('watch_date')
            ->get()
            ->pluck('total', 'watch_date')
            ->map(fn ($value) => (int) $value);

        $series = [];
        $period = CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay());

        foreach ($period as $day) {
            $dateKey = $day->format('Y-m-d');
            $series[] = [
                'date' => $dateKey,
                'count' => $rawEngagement->get($dateKey, 0),
            ];
        }

        return $series;
    }

    protected function buildTopStudents(Collection $videoIds, Carbon $startDate, Carbon $endDate): array
    {
        $totalVideos = $videoIds->count();

        if ($totalVideos === 0) {
            return [];
        }

        $students = WatchVideo::query()
            ->select('watch_videos.user_id')
            ->selectRaw('COUNT(DISTINCT watch_videos.video_id) as completed_videos')
            ->whereIn('watch_videos.video_id', $videoIds)
            ->where('watch_videos.status', WatchVideo::STATUS_WATCHED)
            ->whereBetween('watch_videos.updated_at', [$startDate, $endDate])
            ->groupBy('watch_videos.user_id')
            ->orderByDesc('completed_videos')
            ->with('user:id,name')
            ->limit(4)
            ->get();

        return $students->map(function ($record) use ($totalVideos) {
            $completed = (int) $record->completed_videos;
            $progress = $totalVideos > 0
                ? round(($completed / $totalVideos) * 100)
                : 0;

            return [
                'userId' => $record->user_id,
                'name' => optional($record->user)->name ?? 'Aluno',
                'completedVideos' => $completed,
                'totalVideos' => $totalVideos,
                'progressPercent' => $progress,
                'completedLabel' => sprintf('%d/%d aulas concluídas', $completed, $totalVideos),
            ];
        })->toArray();
    }

    protected function buildRatingsOverview(Collection $videoIds, Carbon $startDate, Carbon $endDate): array
    {
        $baseQuery = VideoRating::query()
            ->whereIn('video_id', $videoIds)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalReviews = (clone $baseQuery)->count();

        $averageRating = $totalReviews > 0
            ? round((clone $baseQuery)->avg('rating'), 2)
            : 0;

        $grouped = (clone $baseQuery)
            ->select('rating')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $distribution = collect(range(5, 1))->map(function ($stars) use ($grouped, $totalReviews) {
            $count = (int) ($grouped[$stars] ?? 0);
            $percentage = $totalReviews > 0
                ? round(($count / $totalReviews) * 100)
                : 0;

            return [
                'stars' => $stars,
                'count' => $count,
                'percentage' => $percentage,
            ];
        })->values()->toArray();

        return [
            'average' => $averageRating,
            'totalReviews' => $totalReviews,
            'distribution' => $distribution,
        ];
    }

    protected function emptyResponse(string $period): array
    {
        return [
            'period' => $period,
            'summary' => [
                'averageWatchMinutes' => 0,
                'completionRate' => 0,
                'positiveReactions' => 0,
                'totalStudents' => 0,
            ],
            'engagement' => [],
            'topStudents' => [],
            'ratings' => [
                'average' => 0,
                'totalReviews' => 0,
                'distribution' => [],
            ],
        ];
    }
}

