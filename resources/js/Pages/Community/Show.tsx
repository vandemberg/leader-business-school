import React, { useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, router, useForm } from "@inertiajs/react";

interface Post {
    id: number;
    title: string;
    content: string;
    tag: string | null;
    likes_count: number;
    comments_count: number;
    is_liked: boolean;
    created_at: string;
    user: {
        id: number;
        name: string;
    };
}

interface Comment {
    id: number;
    content: string;
    created_at: string;
    user: {
        id: number;
        name: string;
    };
    replies?: Comment[];
}

interface CommunityShowProps {
    auth: any;
    post: Post;
    comments: Comment[];
}

const CommunityShow: React.FC<CommunityShowProps> = ({ auth, post: initialPost, comments: initialComments }) => {
    const [post, setPost] = useState<Post>(initialPost);
    const [comments, setComments] = useState<Comment[]>(initialComments);
    const [replyingTo, setReplyingTo] = useState<number | null>(null);

    const { data, setData, post: submitComment, processing, errors, reset } = useForm({
        content: "",
        parent_id: null as number | null,
    });

    const handleLike = (e?: React.MouseEvent) => {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        router.post(route("community.posts.like", post.id), {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                // Update post from response
                const updatedPost = (page.props as any).post;
                if (updatedPost) {
                    setPost(updatedPost);
                } else {
                    // Fallback: reload if post not in response
                    router.reload({ only: ['post'] });
                }
            },
        });
    };

    const handleCommentSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        submitComment(route("community.posts.comments.store", post.id), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                reset();
                setReplyingTo(null);
                // Update comments and post from response
                const updatedComments = (page.props as any).comments;
                const updatedPost = (page.props as any).post;
                if (updatedComments) {
                    setComments(updatedComments);
                } else {
                    router.reload({ only: ['comments', 'post'] });
                }
                if (updatedPost) {
                    setPost(updatedPost);
                }
            },
        });
    };

    const handleReplyClick = (commentId: number) => {
        if (replyingTo === commentId) {
            setReplyingTo(null);
            setData("parent_id", null);
        } else {
            setReplyingTo(commentId);
            setData("parent_id", commentId);
        }
        reset();
    };

    const formatTimeAgo = (date: string) => {
        const now = new Date();
        const postDate = new Date(date);
        const diffInSeconds = Math.floor((now.getTime() - postDate.getTime()) / 1000);

        if (diffInSeconds < 60) return "agora";
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutos atrás`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} horas atrás`;
        return `${Math.floor(diffInSeconds / 86400)} dias atrás`;
    };

    const getUserInitial = (name: string) => {
        return name.charAt(0).toUpperCase();
    };

    const getUserAvatarColor = (userId: number) => {
        const colors = [
            "bg-gradient-to-br from-secondary to-primary",
            "bg-gradient-to-br from-teal-500 to-cyan-500",
            "bg-gradient-to-br from-green-500 to-emerald-500",
            "bg-gradient-to-br from-blue-500 to-indigo-500",
            "bg-gradient-to-br from-purple-500 to-pink-500",
        ];
        return colors[userId % colors.length];
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={post.title} />

            <div className="layout-content-container flex flex-col w-full max-w-4xl flex-1 gap-8">
                {/* Breadcrumbs */}
                <nav className="flex items-center gap-2 text-sm text-[#A0A0A0]">
                    <Link href={route("community.index")} className="hover:text-white transition-colors">
                        Comunidade
                    </Link>
                    <span className="material-symbols-outlined text-base">chevron_right</span>
                    <span className="text-white/70 truncate">{post.title}</span>
                </nav>

                {/* Main Post */}
                <article className="p-6 rounded-xl bg-surface-dark border border-white/10">
                    <h1 className="text-3xl font-bold font-heading mb-4 text-white">
                        {post.title}
                    </h1>

                    <div className="flex items-center gap-4 text-sm text-[#A0A0A0] mb-6">
                        <span>
                            Postado por <span className="font-medium text-white/90">{post.user.name}</span>
                        </span>
                        <span className="w-1 h-1 bg-white/20 rounded-full"></span>
                        <span>{formatTimeAgo(post.created_at)}</span>
                        {post.tag && (
                            <>
                                <span className="w-1 h-1 bg-white/20 rounded-full"></span>
                                <span>
                                    em{" "}
                                    <Link
                                        href={route("community.index", { tag: post.tag })}
                                        className="font-medium text-primary/80 hover:text-primary"
                                    >
                                        #{post.tag}
                                    </Link>
                                </span>
                            </>
                        )}
                    </div>

                    <div className="prose prose-invert max-w-none mb-6">
                        <p className="text-white/90 whitespace-pre-wrap leading-relaxed">
                            {post.content}
                        </p>
                    </div>

                    {/* Post Actions */}
                    <div className="flex items-center gap-6 pt-4 border-t border-white/10">
                        <button
                            onClick={(e) => handleLike(e)}
                            className={`flex items-center gap-2 hover:text-white transition-colors ${
                                post.is_liked ? "text-primary" : "text-[#A0A0A0]"
                            }`}
                        >
                            <span
                                className="material-symbols-outlined text-xl"
                                style={{
                                    fontVariationSettings: post.is_liked
                                        ? "'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24"
                                        : "'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24"
                                }}
                            >
                                thumb_up
                            </span>
                            <span className="font-medium">{post.likes_count}</span>
                        </button>
                    </div>
                </article>

                {/* Comments Section */}
                <section className="flex flex-col gap-6">
                    <h2 className="text-2xl font-bold font-heading text-white">
                        {post.comments_count} {post.comments_count === 1 ? "Comentário" : "Comentários"}
                    </h2>

                    {/* Add Comment Form */}
                    <form onSubmit={handleCommentSubmit} className="flex items-start gap-4">
                        <div className={`${getUserAvatarColor(auth.user.id)} rounded-full size-10 flex items-center justify-center text-white font-bold flex-shrink-0`}>
                            {getUserInitial(auth.user.name)}
                        </div>
                        <div className="flex-grow flex gap-3">
                            <input
                                type="text"
                                placeholder="Adicione seu comentário..."
                                className="flex-1 bg-surface-dark border border-white/10 rounded-lg h-12 px-4 text-base text-white focus:ring-primary focus:border-primary placeholder:text-white/50"
                                value={data.content}
                                onChange={(e) => setData("content", e.target.value)}
                                required
                            />
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-6 h-12 rounded-lg gradient-button text-white font-bold text-sm transition-opacity disabled:opacity-50 whitespace-nowrap"
                            >
                                {processing ? "Comentando..." : "Comentar"}
                            </button>
                        </div>
                    </form>
                    {errors.content && <p className="text-red-500 text-sm ml-14">{errors.content}</p>}

                    {/* Comments List */}
                    <div className="flex flex-col gap-6">
                        {comments.length > 0 ? (
                            comments.map((comment) => (
                                <div key={comment.id} className="flex flex-col gap-4">
                                    <div className="flex items-start gap-4">
                                        <div className={`${getUserAvatarColor(comment.user.id)} rounded-full size-10 flex items-center justify-center text-white font-bold flex-shrink-0`}>
                                            {getUserInitial(comment.user.name)}
                                        </div>
                                        <div className="flex-grow">
                                            <div className="flex items-center gap-3 mb-2">
                                                <span className="font-semibold text-white">
                                                    {comment.user.name}
                                                    {comment.user.id === post.user.id && (
                                                        <span className="ml-2 text-xs text-primary/80">(Autora)</span>
                                                    )}
                                                </span>
                                                <span className="text-sm text-[#A0A0A0]">
                                                    {formatTimeAgo(comment.created_at)}
                                                </span>
                                            </div>
                                            <p className="text-white/90 mb-3 whitespace-pre-wrap">
                                                {comment.content}
                                            </p>
                                            <div className="flex items-center gap-4">
                                                <button
                                                    className="flex items-center gap-1 text-sm text-[#A0A0A0] hover:text-white transition-colors"
                                                >
                                                    <span className="material-symbols-outlined text-lg">thumb_up</span>
                                                    <span>0</span>
                                                </button>
                                                <button
                                                    onClick={() => handleReplyClick(comment.id)}
                                                    className="text-sm text-[#A0A0A0] hover:text-white transition-colors"
                                                >
                                                    Responder
                                                </button>
                                            </div>

                                            {/* Reply Form */}
                                            {replyingTo === comment.id && (
                                                <form onSubmit={handleCommentSubmit} className="mt-4 flex items-start gap-4">
                                                    <div className={`${getUserAvatarColor(auth.user.id)} rounded-full size-8 flex items-center justify-center text-white font-bold text-sm flex-shrink-0`}>
                                                        {getUserInitial(auth.user.name)}
                                                    </div>
                                                    <div className="flex-grow flex gap-3">
                                                        <input
                                                            type="text"
                                                            placeholder="Adicione sua resposta..."
                                                            className="flex-1 bg-surface-dark border border-white/10 rounded-lg h-10 px-4 text-sm text-white focus:ring-primary focus:border-primary placeholder:text-white/50"
                                                            value={data.content}
                                                            onChange={(e) => setData("content", e.target.value)}
                                                            required
                                                        />
                                                        <button
                                                            type="submit"
                                                            disabled={processing}
                                                            className="px-4 h-10 rounded-lg gradient-button text-white font-bold text-xs transition-opacity disabled:opacity-50 whitespace-nowrap"
                                                        >
                                                            Responder
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => {
                                                                setReplyingTo(null);
                                                                setData("parent_id", null);
                                                                reset();
                                                            }}
                                                            className="px-4 h-10 rounded-lg bg-white/10 text-white/70 font-medium text-xs hover:bg-white/20 transition-colors whitespace-nowrap"
                                                        >
                                                            Cancelar
                                                        </button>
                                                    </div>
                                                </form>
                                            )}

                                            {/* Replies */}
                                            {comment.replies && comment.replies.length > 0 && (
                                                <div className="mt-4 ml-4 pl-4 border-l-2 border-white/10 flex flex-col gap-4">
                                                    {comment.replies.map((reply) => (
                                                        <div key={reply.id} className="flex items-start gap-3">
                                                            <div className={`${getUserAvatarColor(reply.user.id)} rounded-full size-8 flex items-center justify-center text-white font-bold text-sm flex-shrink-0`}>
                                                                {getUserInitial(reply.user.name)}
                                                            </div>
                                                            <div className="flex-grow">
                                                                <div className="flex items-center gap-2 mb-1">
                                                                    <span className="font-semibold text-white text-sm">
                                                                        {reply.user.name}
                                                                        {reply.user.id === post.user.id && (
                                                                            <span className="ml-2 text-xs text-primary/80">(Autora)</span>
                                                                        )}
                                                                    </span>
                                                                    <span className="text-xs text-[#A0A0A0]">
                                                                        {formatTimeAgo(reply.created_at)}
                                                                    </span>
                                                                </div>
                                                                <p className="text-white/80 text-sm whitespace-pre-wrap">
                                                                    {reply.content}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="text-center py-12 text-[#A0A0A0]">
                                Nenhum comentário ainda. Seja o primeiro a comentar!
                            </div>
                        )}
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
};

export default CommunityShow;

