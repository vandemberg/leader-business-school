import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import React, { useState, useEffect, useRef, useCallback } from "react";
import YouTube, { YouTubeEvent, YouTubeProps } from "react-youtube";
import { Head, router, Link } from "@inertiajs/react";
import axios from "axios";

interface Video {
    id: number;
    title: string;
    description: string;
    url: string;
    time_in_seconds: number | null;
    watched: boolean;
    module: {
        id: number;
        name: string;
    } | null;
}

interface CourseProps {
    videos: Video[];
    currentVideo: {
        id: number;
        title: string;
        description: string;
        url: string;
        time_in_seconds: number | null;
        module: {
            id: number;
            name: string;
        } | null;
    };
    course: {
        id: number;
        title: string;
        description: string;
        thumbnail: string;
    };
    auth: any;
    userRating?: {
        id: number;
        rating: number;
        feedback: string | null;
    } | null;
    commentsCount: number;
    progress: number;
}

interface Comment {
    id: number;
    content: string;
    user: {
        id: number;
        name: string;
    };
    created_at: string;
    likes: number;
    dislikes: number;
    replies: Reply[];
}

interface Reply {
    id: number;
    content: string;
    user: {
        id: number;
        name: string;
    };
    created_at: string;
}

function formatTextToUrl(url: string) {
    let newUrl = url;
    if (!url.startsWith("http")) {
        newUrl = `https://${url}`;
    }
    return `<a href="${newUrl}" target="_blank" rel="noopener noreferrer">${newUrl}</a>`;
}

function urlMatch(text: string, url: string) {
    return text.includes(url) ? formatTextToUrl(url) : text;
}

function parseDescription(description: string) {
    if (!description) return "";
    return description
        .split("\n")
        .map((line) => {
            const urlRegex = /(https?:\/\/[^\s]+)/g;
            return line.replace(urlRegex, (url) => urlMatch(line, url));
        })
        .join("\n");
}

function extractYouTubeId(url: string): string {
    if (!url) return "";
    // Handle both full URLs and just IDs
    if (url.includes("youtube.com") || url.includes("youtu.be")) {
        const match = url.match(
            /(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/
        );
        return match ? match[1] : url;
    }
    return url;
}

function formatTime(seconds: number | null): string {
    if (!seconds) return "0:00";
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;

    if (hours > 0) {
        return `${hours}:${minutes
            .toString()
            .padStart(2, "0")}:${remainingSeconds.toString().padStart(2, "0")}`;
    }
    return `${minutes}:${remainingSeconds.toString().padStart(2, "0")}`;
}

const Course: React.FC<CourseProps> = ({
    auth,
    course,
    currentVideo,
    videos,
    userRating,
    commentsCount,
    progress,
}) => {
    const [activeTab, setActiveTab] = useState<
        "description" | "notes" | "resources"
    >("description");
    const [rating, setRating] = useState<number>(userRating?.rating || 0);
    const [feedback, setFeedback] = useState<string>(
        userRating?.feedback || ""
    );
    const [comments, setComments] = useState<Comment[]>([]);
    const [newComment, setNewComment] = useState<string>("");
    const [replyingTo, setReplyingTo] = useState<number | null>(null);
    const [replyContent, setReplyContent] = useState<string>("");
    const [sortBy, setSortBy] = useState<"recent" | "relevant">("recent");
    const [loading, setLoading] = useState<boolean>(false);
    const [showReportModal, setShowReportModal] = useState<boolean>(false);
    const [reportReason, setReportReason] = useState<string>("");
    const [reportDescription, setReportDescription] = useState<string>("");
    const [reportSubmitted, setReportSubmitted] = useState<boolean>(false);
    const [videoUnavailable, setVideoUnavailable] = useState<boolean>(false);
    const [playerReady, setPlayerReady] = useState<boolean>(false);
    const playerRef = useRef<any>(null);

    const videoId = extractYouTubeId(currentVideo.url);

    const loadComments = useCallback(async () => {
        try {
            const response = await axios.get(
                `/videos/${currentVideo.id}/comments`,
                {
                    params: {
                        sort: sortBy,
                    },
                }
            );
            if (response.data.success) {
                setComments(response.data.comments);
            }
        } catch (error) {
            console.error("Error loading comments:", error);
        }
    }, [currentVideo.id, sortBy]);

    useEffect(() => {
        loadComments();
        setVideoUnavailable(false); // Reset when video changes
        setPlayerReady(false);

        setTimeout(() => {
            if (!playerRef.current) {
                setVideoUnavailable(true);
            }
        }, 3500);

        // Add global error handler for YouTube API errors
        const handleYouTubeError = (event: ErrorEvent) => {
            if (
                event.message &&
                (event.message.includes("Invalid video id") ||
                    event.message.includes("postMessage") ||
                    event.message.includes("youtube"))
            ) {
                console.error("YouTube API error:", event.message);
                setVideoUnavailable(true);
            }
        };

        // Add unhandled promise rejection handler
        const handleUnhandledRejection = (event: PromiseRejectionEvent) => {
            const reason =
                event.reason?.message || event.reason?.toString() || "";
            if (
                reason.includes("Invalid video id") ||
                reason.includes("youtube")
            ) {
                console.error("YouTube promise rejection:", reason);
                setVideoUnavailable(true);
            }
        };

        window.addEventListener("error", handleYouTubeError);
        window.addEventListener("unhandledrejection", handleUnhandledRejection);

        return () => {
            window.removeEventListener("error", handleYouTubeError);
            window.removeEventListener(
                "unhandledrejection",
                handleUnhandledRejection
            );
        };
    }, [currentVideo.id, sortBy, loadComments]);

    const handleComplete = () => {
        axios
            .post(`/videos/${currentVideo.id}/complete`)
            .then(() => {
                router.reload();
            })
            .catch((error) => {
                console.error("Error toggling video completion:", error);
            });
    };

    const handleNextVideo = () => {
        const currentIndex = videos.findIndex((v) => v.id === currentVideo.id);
        if (currentIndex < videos.length - 1) {
            const nextVideo = videos[currentIndex + 1];
            router.visit(`/courses/${course.id}/videos/${nextVideo.id}`);
        }
    };

    const handleRatingSubmit = async () => {
        if (rating === 0) return;
        setLoading(true);
        try {
            await axios.post(`/videos/${currentVideo.id}/rating`, {
                rating,
                feedback: feedback || null,
            });
            router.reload();
        } catch (error) {
            console.error("Error submitting rating:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleCommentSubmit = async () => {
        if (!newComment.trim()) return;
        setLoading(true);
        try {
            await axios.post(`/videos/${currentVideo.id}/comments`, {
                content: newComment,
            });
            setNewComment("");
            loadComments();
        } catch (error) {
            console.error("Error submitting comment:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleReplySubmit = async (commentId: number) => {
        if (!replyContent.trim()) return;
        setLoading(true);
        try {
            await axios.post(`/comments/${commentId}/reply`, {
                content: replyContent,
            });
            setReplyContent("");
            setReplyingTo(null);
            loadComments();
        } catch (error) {
            console.error("Error submitting reply:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleToggleLike = async (
        commentId: number,
        type: "like" | "dislike"
    ) => {
        try {
            const response = await axios.post(`/comments/${commentId}/like`, {
                type,
            });
            if (response.data.success) {
                loadComments();
            }
        } catch (error) {
            console.error("Error toggling like:", error);
        }
    };

    const handleReportSubmit = async () => {
        setLoading(true);
        try {
            const response = await axios.post(
                `/videos/${currentVideo.id}/report`,
                {
                    reason: reportReason || "Video indisponível",
                    description: reportDescription,
                }
            );

            if (response.data.success) {
                setReportSubmitted(false);
                setReportReason("");
                setReportDescription("");
                setShowReportModal(false);
            }
        } catch (error) {
            console.error("Error submitting report:", error);
            alert("Erro ao enviar reporte. Tente novamente.");
        } finally {
            setLoading(false);
        }
    };

    const getNextVideo = () => {
        const currentIndex = videos.findIndex((v) => v.id === currentVideo.id);
        return currentIndex < videos.length - 1
            ? videos[currentIndex + 1]
            : null;
    };

    const nextVideo = getNextVideo();
    const isCompleted =
        videos.find((v) => v.id === currentVideo.id)?.watched || false;

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={currentVideo.title} />

            <div className="flex-grow p-4 sm:p-6 lg:p-10">
                <div className="mx-auto max-w-7xl">
                    <div className="grid grid-cols-1 lg:grid-cols-3 lg:gap-8">
                        {/* Main Content */}
                        <div className="lg:col-span-2">
                            {/* Video Player */}
                            <div className="relative w-full aspect-video rounded-xl overflow-hidden bg-black shadow-lg">
                                {videoId &&
                                videoId.trim() !== "" &&
                                !videoUnavailable ? (
                                    <>
                                        {!playerReady && (
                                            <div className="absolute inset-0 flex flex-col items-center justify-center bg-black z-10">
                                                <div className="relative">
                                                    <div className="w-16 h-16 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
                                                </div>
                                                <p className="mt-4 text-white text-sm">
                                                    Carregando vídeo...
                                                </p>
                                            </div>
                                        )}
                                        <YouTube
                                            videoId={videoId}
                                            ref={playerRef}
                                            onEnd={handleComplete}
                                            onReady={(event: YouTubeEvent) => {
                                                setPlayerReady(true);
                                                playerRef.current =
                                                    event.target;
                                            }}
                                            style={{
                                                width: "100%",
                                                height: "100%",
                                            }}
                                            opts={{
                                                height: "100%",
                                                width: "100%",
                                                playerVars: {
                                                    autoplay: 0,
                                                    controls: 1,
                                                    modestbranding: 1,
                                                },
                                            }}
                                        />
                                    </>
                                ) : (
                                    <div className="w-full h-full flex flex-col items-center justify-center text-white p-8 bg-surface-dark">
                                        <span className="material-symbols-outlined text-6xl mb-4 text-text-secondary-dark">
                                            error_outline
                                        </span>
                                        <h3 className="text-2xl font-bold font-heading mb-2">
                                            Vídeo Indisponível
                                        </h3>
                                        <p className="text-text-secondary-dark text-center mb-6 max-w-md">
                                            Este vídeo não está disponível no
                                            momento.
                                        </p>
                                        <button
                                            onClick={() =>
                                                setShowReportModal(true)
                                            }
                                            className="flex items-center justify-center gap-2 px-6 py-3 bg-primary text-white rounded-lg font-bold hover:bg-opacity-90 transition-opacity"
                                        >
                                            <span className="material-symbols-outlined">
                                                report
                                            </span>
                                            <span>Reportar Problema</span>
                                        </button>
                                    </div>
                                )}
                            </div>

                            {/* Report Success Message */}
                            {reportSubmitted && (
                                <div className="mt-4 p-4 bg-green-500/20 border border-green-500 rounded-lg flex items-center gap-3">
                                    <span className="material-symbols-outlined text-green-400">
                                        check_circle
                                    </span>
                                    <p className="text-green-400 font-medium">
                                        Reporte enviado com sucesso! Obrigado
                                        pelo feedback.
                                    </p>
                                </div>
                            )}

                            {/* Report Modal */}
                            {showReportModal && (
                                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                                    <div className="bg-surface-dark rounded-xl p-6 max-w-md w-full border border-border-dark">
                                        <div className="flex items-center justify-between mb-4">
                                            <h3 className="text-xl font-bold font-heading text-white">
                                                Reportar Problema
                                            </h3>
                                            <button
                                                onClick={() =>
                                                    setShowReportModal(false)
                                                }
                                                className="text-text-secondary-dark hover:text-white transition-colors"
                                            >
                                                <span className="material-symbols-outlined">
                                                    close
                                                </span>
                                            </button>
                                        </div>
                                        <p className="text-text-secondary-dark mb-4">
                                            Ajude-nos a entender o problema com
                                            este vídeo.
                                        </p>
                                        <div className="space-y-4">
                                            <div>
                                                <label className="block text-sm font-medium text-white mb-2">
                                                    Motivo (opcional)
                                                </label>
                                                <input
                                                    type="text"
                                                    value={reportReason}
                                                    onChange={(e) =>
                                                        setReportReason(
                                                            e.target.value
                                                        )
                                                    }
                                                    placeholder="Ex: Vídeo não carrega, áudio sem som, etc."
                                                    className="w-full rounded-lg border-0 bg-background-dark p-3 text-text-primary-dark placeholder:text-text-secondary-dark ring-1 ring-inset ring-border-dark focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm"
                                                />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-white mb-2">
                                                    Descrição (opcional)
                                                </label>
                                                <textarea
                                                    value={reportDescription}
                                                    onChange={(e) =>
                                                        setReportDescription(
                                                            e.target.value
                                                        )
                                                    }
                                                    placeholder="Descreva o problema em detalhes..."
                                                    rows={4}
                                                    className="w-full rounded-lg border-0 bg-background-dark p-3 text-text-primary-dark placeholder:text-text-secondary-dark ring-1 ring-inset ring-border-dark focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm"
                                                />
                                            </div>
                                            <div className="flex gap-3 justify-end">
                                                <button
                                                    onClick={() => {
                                                        setShowReportModal(
                                                            false
                                                        );
                                                        setReportReason("");
                                                        setReportDescription(
                                                            ""
                                                        );
                                                    }}
                                                    className="px-4 py-2 text-sm text-text-secondary-dark hover:text-white transition-colors"
                                                >
                                                    Cancelar
                                                </button>
                                                <button
                                                    onClick={handleReportSubmit}
                                                    disabled={loading}
                                                    className="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:bg-opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed"
                                                >
                                                    {loading
                                                        ? "Enviando..."
                                                        : "Enviar Reporte"}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Video Title and Actions */}
                            <div className="mt-6">
                                <h1 className="text-white font-heading tracking-tight text-3xl font-bold leading-tight pb-4 pt-2">
                                    {currentVideo.title}
                                </h1>
                                <div className="flex flex-wrap items-center gap-4">
                                    <button
                                        onClick={handleComplete}
                                        className={`flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-11 px-5 text-white text-sm font-bold leading-normal tracking-[0.015em] transition-colors ${
                                            isCompleted
                                                ? "bg-green-500 hover:bg-green-600"
                                                : "bg-surface-dark hover:bg-border-dark"
                                        }`}
                                    >
                                        <span className="material-symbols-outlined text-lg">
                                            {isCompleted
                                                ? "check_circle"
                                                : "check_circle_outline"}
                                        </span>
                                        <span className="truncate">
                                            {isCompleted
                                                ? "Concluída"
                                                : "Marcar como concluída"}
                                        </span>
                                    </button>
                                    {nextVideo && (
                                        <Link
                                            href={`/courses/${course.id}/videos/${nextVideo.id}`}
                                            className="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-11 px-5 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90 transition-opacity"
                                        >
                                            <span className="truncate">
                                                Próxima Aula
                                            </span>
                                            <span className="material-symbols-outlined text-lg">
                                                arrow_forward
                                            </span>
                                        </Link>
                                    )}
                                </div>
                            </div>

                            {/* Tabs */}
                            <div className="mt-8">
                                <div className="border-b border-border-dark">
                                    <nav
                                        aria-label="Tabs"
                                        className="flex -mb-px gap-6"
                                    >
                                        <button
                                            onClick={() =>
                                                setActiveTab("description")
                                            }
                                            className={`flex items-center justify-center border-b-2 py-3 px-1 text-sm font-bold leading-normal tracking-[0.015em] transition-colors ${
                                                activeTab === "description"
                                                    ? "border-primary text-white"
                                                    : "border-transparent text-text-secondary-dark hover:text-white hover:border-text-secondary-dark"
                                            }`}
                                        >
                                            Descrição
                                        </button>
                                        <button
                                            onClick={() =>
                                                setActiveTab("notes")
                                            }
                                            className={`flex items-center justify-center border-b-2 py-3 px-1 text-sm font-bold leading-normal tracking-[0.015em] transition-colors ${
                                                activeTab === "notes"
                                                    ? "border-primary text-white"
                                                    : "border-transparent text-text-secondary-dark hover:text-white hover:border-text-secondary-dark"
                                            }`}
                                        >
                                            Anotações
                                        </button>
                                        <button
                                            onClick={() =>
                                                setActiveTab("resources")
                                            }
                                            className={`flex items-center justify-center border-b-2 py-3 px-1 text-sm font-bold leading-normal tracking-[0.015em] transition-colors ${
                                                activeTab === "resources"
                                                    ? "border-primary text-white"
                                                    : "border-transparent text-text-secondary-dark hover:text-white hover:border-text-secondary-dark"
                                            }`}
                                        >
                                            Recursos
                                        </button>
                                    </nav>
                                </div>
                                <div className="py-6">
                                    {activeTab === "description" && (
                                        <p
                                            className="text-text-secondary-dark leading-relaxed"
                                            dangerouslySetInnerHTML={{
                                                __html: parseDescription(
                                                    currentVideo.description
                                                ),
                                            }}
                                        />
                                    )}
                                    {activeTab === "notes" && (
                                        <p className="text-text-secondary-dark leading-relaxed">
                                            Anotações serão implementadas em
                                            breve.
                                        </p>
                                    )}
                                    {activeTab === "resources" && (
                                        <p className="text-text-secondary-dark leading-relaxed">
                                            Recursos adicionais serão
                                            disponibilizados aqui.
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* Rating Section */}
                            <div className="mt-8 border-t border-border-dark pt-8 bg-surface-dark rounded-xl p-6">
                                <h2 className="text-xl font-bold font-heading text-white">
                                    Avalie esta aula
                                </h2>
                                <p className="text-text-secondary-dark mt-1">
                                    Seu feedback é importante para nós.
                                </p>
                                <div className="mt-4 flex items-center gap-2 text-text-secondary-dark">
                                    {[1, 2, 3, 4, 5].map((star) => (
                                        <button
                                            key={star}
                                            onClick={() => setRating(star)}
                                            className="group"
                                        >
                                            <span
                                                className={`material-symbols-outlined text-3xl transition-transform group-hover:scale-110 ${
                                                    star <= rating
                                                        ? "filled text-yellow-400"
                                                        : "text-text-secondary-dark hover:text-yellow-400"
                                                }`}
                                            >
                                                star
                                            </span>
                                        </button>
                                    ))}
                                </div>
                                <div className="mt-4">
                                    <textarea
                                        value={feedback}
                                        onChange={(e) =>
                                            setFeedback(e.target.value)
                                        }
                                        className="block w-full rounded-lg border-0 bg-background-dark p-4 text-text-primary-dark placeholder:text-text-secondary-dark ring-1 ring-inset ring-border-dark focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-colors"
                                        placeholder="Deixe um feedback (opcional)..."
                                        rows={3}
                                    />
                                    <div className="mt-3 flex justify-end">
                                        <button
                                            onClick={handleRatingSubmit}
                                            disabled={rating === 0 || loading}
                                            className="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span className="truncate">
                                                Enviar Avaliação
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {/* Comments Section */}
                            <div className="mt-8 border-t border-border-dark pt-8">
                                <div className="flex items-center justify-between mb-6">
                                    <h2 className="text-2xl font-bold font-heading text-white">
                                        Comentários ({commentsCount})
                                    </h2>
                                    <div className="flex items-center gap-2">
                                        <span className="text-sm text-text-secondary-dark">
                                            Ordenar por:
                                        </span>
                                        <select
                                            value={sortBy}
                                            onChange={(e) =>
                                                setSortBy(
                                                    e.target.value as
                                                        | "recent"
                                                        | "relevant"
                                                )
                                            }
                                            className="block w-full rounded-md border-0 bg-surface-dark py-1.5 pl-3 pr-8 text-white ring-1 ring-inset ring-border-dark focus:ring-2 focus:ring-primary text-sm leading-6"
                                        >
                                            <option value="recent">
                                                Mais recentes
                                            </option>
                                            <option value="relevant">
                                                Mais relevantes
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                {/* Comment Form */}
                                <div className="flex items-start gap-4 mb-8">
                                    <div className="bg-gradient-to-br from-primary to-secondary rounded-full size-10 flex items-center justify-center text-white font-bold">
                                        {auth.user.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div className="flex-1">
                                        <textarea
                                            value={newComment}
                                            onChange={(e) =>
                                                setNewComment(e.target.value)
                                            }
                                            className="block w-full rounded-lg border-0 bg-surface-dark p-4 text-text-primary-dark placeholder:text-text-secondary-dark ring-1 ring-inset ring-border-dark focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-colors"
                                            placeholder="Adicione um comentário..."
                                            rows={3}
                                        />
                                        <div className="mt-3 flex justify-end">
                                            <button
                                                onClick={handleCommentSubmit}
                                                disabled={
                                                    !newComment.trim() ||
                                                    loading
                                                }
                                                className="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <span className="truncate">
                                                    Comentar
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {/* Comments List */}
                                <div className="space-y-8">
                                    {comments.map((comment) => (
                                        <div
                                            key={comment.id}
                                            className="flex items-start gap-4"
                                        >
                                            <div className="bg-gradient-to-br from-primary to-secondary rounded-full size-10 flex items-center justify-center text-white font-bold">
                                                {comment.user.name
                                                    .charAt(0)
                                                    .toUpperCase()}
                                            </div>
                                            <div className="flex-1">
                                                <div className="flex items-center gap-2">
                                                    <p className="font-bold text-white">
                                                        {comment.user.name}
                                                    </p>
                                                    <p className="text-xs text-text-secondary-dark">
                                                        {comment.created_at}
                                                    </p>
                                                </div>
                                                <p className="mt-2 text-text-secondary-dark leading-relaxed">
                                                    {comment.content}
                                                </p>
                                                <div className="mt-3 flex items-center gap-4 text-sm text-text-secondary-dark">
                                                    <div className="flex items-center gap-3">
                                                        <button
                                                            onClick={() =>
                                                                handleToggleLike(
                                                                    comment.id,
                                                                    "like"
                                                                )
                                                            }
                                                            className="flex items-center gap-1.5 hover:text-white transition-colors text-primary"
                                                        >
                                                            <span className="material-symbols-outlined filled text-base">
                                                                thumb_up
                                                            </span>
                                                            <span>
                                                                {comment.likes}
                                                            </span>
                                                        </button>
                                                        <button
                                                            onClick={() =>
                                                                handleToggleLike(
                                                                    comment.id,
                                                                    "dislike"
                                                                )
                                                            }
                                                            className="flex items-center gap-1.5 hover:text-white transition-colors"
                                                        >
                                                            <span className="material-symbols-outlined text-base">
                                                                thumb_down
                                                            </span>
                                                        </button>
                                                    </div>
                                                    <button
                                                        onClick={() =>
                                                            setReplyingTo(
                                                                replyingTo ===
                                                                    comment.id
                                                                    ? null
                                                                    : comment.id
                                                            )
                                                        }
                                                        className="flex items-center gap-1.5 hover:text-white transition-colors"
                                                    >
                                                        <span className="material-symbols-outlined text-base">
                                                            reply
                                                        </span>
                                                        <span>Responder</span>
                                                    </button>
                                                </div>

                                                {/* Reply Form */}
                                                {replyingTo === comment.id && (
                                                    <div className="mt-4 flex items-start gap-4">
                                                        <div className="bg-gradient-to-br from-primary to-secondary rounded-full size-8 flex items-center justify-center text-white font-bold text-sm">
                                                            {auth.user.name
                                                                .charAt(0)
                                                                .toUpperCase()}
                                                        </div>
                                                        <div className="flex-1">
                                                            <textarea
                                                                value={
                                                                    replyContent
                                                                }
                                                                onChange={(e) =>
                                                                    setReplyContent(
                                                                        e.target
                                                                            .value
                                                                    )
                                                                }
                                                                className="block w-full rounded-lg border-0 bg-surface-dark p-3 text-text-primary-dark placeholder:text-text-secondary-dark ring-1 ring-inset ring-border-dark focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-colors"
                                                                placeholder="Escreva uma resposta..."
                                                                rows={2}
                                                            />
                                                            <div className="mt-2 flex justify-end gap-2">
                                                                <button
                                                                    onClick={() => {
                                                                        setReplyingTo(
                                                                            null
                                                                        );
                                                                        setReplyContent(
                                                                            ""
                                                                        );
                                                                    }}
                                                                    className="px-4 py-1.5 text-sm text-text-secondary-dark hover:text-white transition-colors"
                                                                >
                                                                    Cancelar
                                                                </button>
                                                                <button
                                                                    onClick={() =>
                                                                        handleReplySubmit(
                                                                            comment.id
                                                                        )
                                                                    }
                                                                    disabled={
                                                                        !replyContent.trim() ||
                                                                        loading
                                                                    }
                                                                    className="px-4 py-1.5 text-sm bg-primary text-white rounded-lg hover:bg-opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed"
                                                                >
                                                                    Responder
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                )}

                                                {/* Replies */}
                                                {comment.replies &&
                                                    comment.replies.length >
                                                        0 && (
                                                        <div className="mt-6 space-y-4">
                                                            {comment.replies.map(
                                                                (reply) => (
                                                                    <div
                                                                        key={
                                                                            reply.id
                                                                        }
                                                                        className="flex items-start gap-4"
                                                                    >
                                                                        <div className="bg-gradient-to-br from-primary to-secondary rounded-full size-8 flex items-center justify-center text-white font-bold text-sm">
                                                                            {reply.user.name
                                                                                .charAt(
                                                                                    0
                                                                                )
                                                                                .toUpperCase()}
                                                                        </div>
                                                                        <div className="flex-1">
                                                                            <div className="rounded-lg bg-surface-dark p-4">
                                                                                <div className="flex items-center gap-2">
                                                                                    <p className="font-bold text-white">
                                                                                        {
                                                                                            reply
                                                                                                .user
                                                                                                .name
                                                                                        }
                                                                                    </p>
                                                                                    <p className="text-xs text-text-secondary-dark">
                                                                                        {
                                                                                            reply.created_at
                                                                                        }
                                                                                    </p>
                                                                                </div>
                                                                                <p className="mt-2 text-text-secondary-dark leading-relaxed">
                                                                                    {
                                                                                        reply.content
                                                                                    }
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                )
                                                            )}
                                                        </div>
                                                    )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Sidebar */}
                        <div className="lg:col-span-1 mt-8 lg:mt-0">
                            <div className="bg-surface-dark rounded-xl shadow-lg p-6 sticky top-24">
                                <div className="flex items-center gap-4">
                                    <div className="relative size-16">
                                        <svg
                                            className="size-full"
                                            viewBox="0 0 36 36"
                                        >
                                            <circle
                                                className="stroke-border-dark"
                                                cx="18"
                                                cy="18"
                                                fill="none"
                                                r="16"
                                                strokeWidth="3"
                                            />
                                            <circle
                                                className="stroke-primary"
                                                cx="18"
                                                cy="18"
                                                fill="none"
                                                r="16"
                                                strokeDasharray={`${progress} ${
                                                    100 - progress
                                                }`}
                                                strokeDashoffset="0"
                                                strokeWidth="3"
                                                transform="rotate(-90 18 18)"
                                            />
                                        </svg>
                                        <div className="absolute inset-0 flex items-center justify-center">
                                            <span className="text-white font-bold text-sm">
                                                {progress}%
                                            </span>
                                        </div>
                                    </div>
                                    <div className="flex-1">
                                        <p className="text-sm text-text-secondary-dark font-medium">
                                            {course.title}
                                        </p>
                                        <h3 className="text-lg font-bold font-heading text-white mt-1">
                                            {currentVideo.title}
                                        </h3>
                                    </div>
                                </div>
                                <div className="mt-6 h-px bg-border-dark"></div>
                                <div className="mt-6 flow-root">
                                    <ul
                                        className="-my-4 divide-y divide-border-dark max-h-[calc(100vh-20rem)] overflow-y-auto pr-2"
                                        role="list"
                                    >
                                        {videos.map((video) => {
                                            const isCurrent =
                                                video.id === currentVideo.id;
                                            const isWatched = video.watched;
                                            const isLocked = false; // You can add logic for locked videos

                                            return (
                                                <li
                                                    key={video.id}
                                                    className="flex items-center gap-4 py-4"
                                                >
                                                    {isWatched ? (
                                                        <span className="material-symbols-outlined text-green-400 text-xl">
                                                            check_circle
                                                        </span>
                                                    ) : isCurrent ? (
                                                        <span className="material-symbols-outlined text-primary text-xl">
                                                            play_circle
                                                        </span>
                                                    ) : isLocked ? (
                                                        <span className="material-symbols-outlined text-text-secondary-dark text-xl">
                                                            lock
                                                        </span>
                                                    ) : (
                                                        <span className="material-symbols-outlined text-text-secondary-dark text-xl">
                                                            radio_button_unchecked
                                                        </span>
                                                    )}
                                                    <Link
                                                        href={`/courses/${course.id}/videos/${video.id}`}
                                                        className={`flex-1 ${
                                                            isCurrent
                                                                ? "bg-primary/10 -mx-4 px-4 rounded-lg py-2"
                                                                : ""
                                                        }`}
                                                    >
                                                        <p
                                                            className={`text-sm leading-tight ${
                                                                isCurrent
                                                                    ? "font-bold text-white"
                                                                    : isLocked
                                                                    ? "font-medium text-text-secondary-dark/70"
                                                                    : "font-medium text-text-secondary-dark"
                                                            }`}
                                                        >
                                                            {video.title}
                                                        </p>
                                                        {video.time_in_seconds && (
                                                            <p
                                                                className={`text-xs mt-1 ${
                                                                    isCurrent
                                                                        ? "text-text-secondary-dark"
                                                                        : isLocked
                                                                        ? "text-text-secondary-dark/50"
                                                                        : "text-text-secondary-dark/70"
                                                                }`}
                                                            >
                                                                {formatTime(
                                                                    video.time_in_seconds
                                                                )}
                                                            </p>
                                                        )}
                                                    </Link>
                                                </li>
                                            );
                                        })}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Course;
