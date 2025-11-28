import React, { useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, router } from "@inertiajs/react";
import { PremiumCourseCard } from "@/components/courses/PremiumCourseCard";

interface Course {
    id: number;
    title: string;
    description: string;
    thumbnail: string;
    icon: string;
    progress: number;
    total_videos?: number;
    tags?: Array<{ id: number; name: string }>;
}

interface Tag {
    id: number;
    name: string;
    courses_through_pivot_count: number;
}

interface CoursesIndexProps {
    auth: any;
    courses: Course[];
    categories: Tag[];
    filters: {
        search: string;
        category: string;
        level: string;
        sort: string;
    };
}

const CoursesIndex: React.FC<CoursesIndexProps> = ({ auth, courses, categories, filters }) => {
    const [search, setSearch] = useState(filters.search || "");
    const [category, setCategory] = useState(filters.category || "");
    const [level, setLevel] = useState(filters.level || "");
    const [sort, setSort] = useState(filters.sort || "recent");

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route("courses.index"),
            { search, category, level, sort },
            { preserveState: true, preserveScroll: true }
        );
    };

    const handleFilterChange = (filterType: string, value: string) => {
        const newFilters = { search, category, level, sort, [filterType]: value };
        router.get(route("courses.index"), newFilters, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Cursos" />

            <div className="layout-content-container flex flex-col w-full max-w-7xl flex-1 gap-10">
                {/* Header Section */}
                <section>
                    <div className="flex flex-col gap-3 mb-8 text-center">
                        <h1 className="text-white text-4xl font-black leading-tight tracking-[-0.033em] font-heading md:text-5xl">
                            Explore Nossos Cursos Premium
                        </h1>
                        <p className="text-[#A0A0A0] text-base font-normal leading-normal max-w-2xl mx-auto">
                            Encontre a formação ideal para impulsionar sua carreira com os melhores especialistas do mercado.
                        </p>
                    </div>

                    {/* Search and Filters */}
                    <div className="flex flex-col lg:flex-row items-center justify-between gap-4 mb-8">
                        <form onSubmit={handleSearch} className="relative w-full lg:max-w-md">
                            <span className="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-white/50">
                                search
                            </span>
                            <input
                                type="text"
                                className="w-full bg-surface-dark border border-white/10 rounded-lg h-12 pl-12 pr-4 text-base focus:ring-primary focus:border-primary placeholder:text-white/50"
                                placeholder="O que você quer aprender hoje?"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </form>
                        <div className="flex flex-wrap items-center justify-center gap-4">
                            <div className="relative min-w-40">
                                <select
                                    className="w-full bg-surface-dark border border-white/10 rounded-lg h-11 px-3 text-sm focus:ring-primary focus:border-primary appearance-none text-white"
                                    value={category}
                                    onChange={(e) => {
                                        setCategory(e.target.value);
                                        handleFilterChange("category", e.target.value);
                                    }}
                                >
                                    <option value="">Categoria</option>
                                    {categories.map((cat) => (
                                        <option key={cat.id} value={cat.name}>
                                            {cat.name}
                                        </option>
                                    ))}
                                </select>
                                <span className="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-white/50 pointer-events-none">
                                    expand_more
                                </span>
                            </div>
                            <div className="relative min-w-40">
                                <select
                                    className="w-full bg-surface-dark border border-white/10 rounded-lg h-11 px-3 text-sm focus:ring-primary focus:border-primary appearance-none text-white"
                                    value={level}
                                    onChange={(e) => {
                                        setLevel(e.target.value);
                                        handleFilterChange("level", e.target.value);
                                    }}
                                >
                                    <option value="">Nível</option>
                                    <option value="beginner">Iniciante</option>
                                    <option value="intermediate">Intermediário</option>
                                    <option value="advanced">Avançado</option>
                                </select>
                                <span className="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-white/50 pointer-events-none">
                                    expand_more
                                </span>
                            </div>
                            <div className="relative min-w-40">
                                <select
                                    className="w-full bg-surface-dark border border-white/10 rounded-lg h-11 px-3 text-sm focus:ring-primary focus:border-primary appearance-none text-white"
                                    value={sort}
                                    onChange={(e) => {
                                        setSort(e.target.value);
                                        handleFilterChange("sort", e.target.value);
                                    }}
                                >
                                    <option value="recent">Mais Recentes</option>
                                    <option value="popular">Mais Populares</option>
                                </select>
                                <span className="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-white/50 pointer-events-none">
                                    expand_more
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Courses Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {courses.length > 0 ? (
                            courses.map((course) => (
                                <PremiumCourseCard key={course.id} course={course} />
                            ))
                        ) : (
                            <div className="col-span-full bg-surface-dark rounded-xl p-8 text-center border border-white/10">
                                <p className="text-[#A0A0A0] text-lg mb-4">
                                    Nenhum curso encontrado com os filtros selecionados.
                                </p>
                                <button
                                    onClick={() => {
                                        setSearch("");
                                        setCategory("");
                                        setLevel("");
                                        setSort("recent");
                                        router.get(route("courses.index"), {}, { preserveState: false });
                                    }}
                                    className="gradient-button text-white px-6 py-2 rounded-lg transition-opacity"
                                >
                                    Limpar Filtros
                                </button>
                            </div>
                        )}
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
};

export default CoursesIndex;
