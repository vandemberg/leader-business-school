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

interface PostTag {
    id: number;
    name: string;
    usage_count: number;
}

interface TopContributor {
    id: number;
    name: string;
    posts_count: number;
    points: number;
}

interface CommunityIndexProps {
    auth: any;
    posts: {
        data: Post[];
        links: any;
        current_page: number;
        last_page: number;
    };
    popularTags: PostTag[];
    topContributors: TopContributor[];
    filters: {
        search: string;
        tag: string;
    };
}

const CommunityIndex: React.FC<CommunityIndexProps> = ({
    auth,
    posts,
    popularTags,
    topContributors,
    filters,
}) => {
    const [search, setSearch] = useState(filters.search || "");
    const [showCreateModal, setShowCreateModal] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        title: "",
        content: "",
        tag: "",
    });

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route("community.index"), { search }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleTagClick = (tag: string) => {
        router.get(route("community.index"), { tag }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleCreatePost = (e: React.FormEvent) => {
        e.preventDefault();
        post(route("community.posts.store"), {
            onSuccess: () => {
                reset();
                setShowCreateModal(false);
            },
        });
    };

    const handleLike = (postId: number) => {
        router.post(route("community.posts.like", postId), {}, {
            preserveState: true,
            preserveScroll: true,
        });
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

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Comunidade" />

            <div className="layout-content-container flex flex-col w-full max-w-7xl flex-1 gap-10">
                {/* Header Section */}
                <section className="text-center">
                    <h1 className="text-4xl md:text-5xl font-black font-heading tracking-[-0.033em] mb-3">
                        Bem-vindo à <span className="gradient-text">Comunidade LBS</span>
                    </h1>
                    <p className="text-[#A0A0A0] text-lg max-w-3xl mx-auto">
                        Um espaço para conectar, aprender e crescer juntos. Faça perguntas, compartilhe insights e colabore com outros líderes.
                    </p>
                </section>

                {/* Search and Create Section */}
                <section className="flex flex-col gap-6 md:flex-row">
                    <form onSubmit={handleSearch} className="relative flex-grow">
                        <span className="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-white/50">
                            search
                        </span>
                        <input
                            type="text"
                            className="w-full bg-surface-dark border border-white/10 rounded-lg h-12 pl-12 pr-4 text-base focus:ring-primary focus:border-primary placeholder:text-white/50"
                            placeholder="Buscar tópicos, perguntas, pessoas..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                    </form>
                    <button
                        onClick={() => setShowCreateModal(true)}
                        className="w-full md:w-auto flex items-center justify-center gap-2 h-12 px-6 rounded-lg text-white font-bold text-sm gradient-button transition-opacity whitespace-nowrap"
                    >
                        <span className="material-symbols-outlined">add_circle</span>
                        Criar Nova Postagem
                    </button>
                </section>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-10">
                    {/* Main Content */}
                    <div className="lg:col-span-2 flex flex-col gap-8">
                        <section>
                            <h2 className="text-2xl font-bold font-heading mb-5">Discussões Recentes</h2>
                            <div className="flex flex-col gap-4">
                                {posts.data.length > 0 ? (
                                    posts.data.map((post) => (
                                        <div
                                            key={post.id}
                                            className="block p-5 rounded-xl bg-surface-dark border border-white/10 hover:border-primary/50 transition-all duration-300 group"
                                        >
                                            <div className="flex items-start gap-4">
                                                <div className="bg-gradient-to-br from-secondary to-primary rounded-full size-10 flex items-center justify-center text-white font-bold">
                                                    {post.user.name.charAt(0).toUpperCase()}
                                                </div>
                                                <div className="flex-grow">
                                                    <p className="font-semibold text-white mb-1 group-hover:text-secondary transition-colors">
                                                        {post.title}
                                                    </p>
                                                    <div className="flex items-center gap-4 text-sm text-[#A0A0A0]">
                                                        <span>
                                                            por <span className="font-medium text-white/90">{post.user.name}</span>
                                                        </span>
                                                        <span className="w-1 h-1 bg-white/20 rounded-full"></span>
                                                        <span>{formatTimeAgo(post.created_at)}</span>
                                                        {post.tag && (
                                                            <>
                                                                <span className="w-1 h-1 bg-white/20 rounded-full"></span>
                                                                <span>
                                                                    em{" "}
                                                                    <button
                                                                        onClick={() => handleTagClick(post.tag!)}
                                                                        className="font-medium text-primary/80 hover:text-primary"
                                                                    >
                                                                        #{post.tag}
                                                                    </button>
                                                                </span>
                                                            </>
                                                        )}
                                                    </div>
                                                </div>
                                                <div className="flex items-center gap-3 text-sm text-[#A0A0A0]">
                                                    <button
                                                        onClick={() => handleLike(post.id)}
                                                        className={`flex items-center gap-1 hover:text-white transition-colors ${
                                                            post.is_liked ? "text-primary" : ""
                                                        }`}
                                                    >
                                                        <span 
                                                            className={`material-symbols-outlined text-lg ${
                                                                post.is_liked ? "" : "opacity-60"
                                                            }`}
                                                            style={{
                                                                fontVariationSettings: post.is_liked 
                                                                    ? "'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24"
                                                                    : "'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24"
                                                            }}
                                                        >
                                                            thumb_up
                                                        </span>
                                                        <span>{post.likes_count}</span>
                                                    </button>
                                                    <div className="flex items-center gap-1">
                                                        <span className="material-symbols-outlined text-lg">forum</span>
                                                        <span>{post.comments_count}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="text-center py-12 text-[#A0A0A0]">
                                        Nenhuma discussão encontrada.
                                    </div>
                                )}
                            </div>

                            {/* Pagination */}
                            {posts.last_page > 1 && (
                                <div className="mt-6 flex justify-center gap-2">
                                    {Array.from({ length: posts.last_page }, (_, i) => i + 1).map((page) => (
                                        <button
                                            key={page}
                                            onClick={() => router.get(route("community.index"), { page }, { preserveState: true })}
                                            className={`px-4 py-2 rounded-lg ${
                                                page === posts.current_page
                                                    ? "bg-primary text-white"
                                                    : "bg-surface-dark text-white/70 hover:bg-white/10"
                                            }`}
                                        >
                                            {page}
                                        </button>
                                    ))}
                                </div>
                            )}
                        </section>
                    </div>

                    {/* Sidebar */}
                    <aside className="lg:col-span-1 flex flex-col gap-8">
                        {/* Popular Tags */}
                        <section>
                            <h2 className="text-2xl font-bold font-heading mb-5">Tópicos Populares</h2>
                            <div className="flex flex-wrap gap-2">
                                {popularTags.map((tag) => (
                                    <button
                                        key={tag.id}
                                        onClick={() => handleTagClick(tag.name)}
                                        className={`px-3 py-1.5 bg-surface-dark border border-white/10 text-white/80 rounded-full text-sm hover:border-primary hover:text-white transition-colors ${
                                            filters.tag === tag.name ? "border-primary text-white" : ""
                                        }`}
                                    >
                                        #{tag.name}
                                    </button>
                                ))}
                            </div>
                        </section>

                        {/* Top Contributors */}
                        <section className="p-6 rounded-xl bg-surface-dark border border-white/10">
                            <h2 className="text-2xl font-bold font-heading mb-5">Top Contribuidores</h2>
                            <div className="flex flex-col gap-4">
                                {topContributors.map((contributor, index) => (
                                    <div key={contributor.id} className="flex items-center gap-3">
                                        <div className="bg-gradient-to-br from-secondary to-primary rounded-full size-10 flex items-center justify-center text-white font-bold">
                                            {contributor.name.charAt(0).toUpperCase()}
                                        </div>
                                        <div className="flex-grow">
                                            <p className="font-semibold text-white">{contributor.name}</p>
                                            <p className="text-xs text-secondary">
                                                {index === 0 ? "Mestre da Liderança" : index === 1 ? "Expert em Marketing" : "Guru das Finanças"}
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <p className="font-bold text-white">{contributor.points}</p>
                                            <p className="text-xs text-[#A0A0A0]">Pontos</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </section>
                    </aside>
                </div>
            </div>

            {/* Create Post Modal */}
            {showCreateModal && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                    <div className="bg-surface-dark rounded-xl border border-white/10 p-6 max-w-2xl w-full">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-2xl font-bold font-heading text-white">Criar Nova Postagem</h3>
                            <button
                                onClick={() => {
                                    setShowCreateModal(false);
                                    reset();
                                }}
                                className="text-white/70 hover:text-white"
                            >
                                <span className="material-symbols-outlined">close</span>
                            </button>
                        </div>
                        <form onSubmit={handleCreatePost}>
                            <div className="space-y-4">
                                <div>
                                    <input
                                        type="text"
                                        placeholder="Título da postagem"
                                        className="w-full bg-background-dark border border-white/10 rounded-lg h-12 px-4 text-base focus:ring-primary focus:border-primary placeholder:text-white/50"
                                        value={data.title}
                                        onChange={(e) => setData("title", e.target.value)}
                                        required
                                    />
                                    {errors.title && <p className="text-red-500 text-sm mt-1">{errors.title}</p>}
                                </div>
                                <div>
                                    <textarea
                                        placeholder="Conteúdo da postagem"
                                        className="w-full bg-background-dark border border-white/10 rounded-lg p-4 text-base focus:ring-primary focus:border-primary placeholder:text-white/50 min-h-[200px]"
                                        value={data.content}
                                        onChange={(e) => setData("content", e.target.value)}
                                        required
                                    />
                                    {errors.content && <p className="text-red-500 text-sm mt-1">{errors.content}</p>}
                                </div>
                                <div>
                                    <input
                                        type="text"
                                        placeholder="Tag (opcional, ex: #Liderança)"
                                        className="w-full bg-background-dark border border-white/10 rounded-lg h-12 px-4 text-base focus:ring-primary focus:border-primary placeholder:text-white/50"
                                        value={data.tag}
                                        onChange={(e) => setData("tag", e.target.value.replace("#", ""))}
                                    />
                                </div>
                                <div className="flex gap-4">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="flex-1 gradient-button h-12 rounded-lg text-white font-bold text-sm transition-opacity disabled:opacity-50"
                                    >
                                        {processing ? "Publicando..." : "Publicar"}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setShowCreateModal(false);
                                            reset();
                                        }}
                                        className="px-6 h-12 rounded-lg bg-white/10 text-white font-medium text-sm hover:bg-white/20 transition-colors"
                                    >
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
};

export default CommunityIndex;

