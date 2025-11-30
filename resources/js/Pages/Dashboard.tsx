import React, { useState, useEffect } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, router, usePage } from "@inertiajs/react";
import { PremiumCourseCard } from "@/components/courses/PremiumCourseCard";

interface Course {
    id: number;
    title: string;
    description: string;
    thumbnail: string;
    progress: number;
    totalVideos?: number;
    completedVideos?: number;
}

interface Badge {
    id: number;
    title: string;
    icon?: string;
    color: string;
    type: string;
    threshold: number;
    description?: string;
    unlocked: boolean;
    unlocked_at?: string | null;
}

interface DashboardProps {
    auth: any;
    coursesInProgress: Course[];
    allCourses: Course[];
    coursesInProgressForGrid: Course[];
    globalProgress: number;
    totalHoursWatched: number;
    filter?: string;
    streak?: {
        current_streak: number;
        longest_streak: number;
        last_activity_date: string | null;
        is_active: boolean;
    };
    badges?: Badge[];
}

const Dashboard: React.FC<DashboardProps> = ({
    auth,
    coursesInProgress,
    allCourses,
    coursesInProgressForGrid,
    globalProgress,
    totalHoursWatched,
    filter = 'in_progress',
    streak,
    badges = []
}) => {
    const userName = auth.user.name.split(' ')[0]; // Get first name
    const [selectedFilter, setSelectedFilter] = useState(filter);

    // Sincronizar estado quando o prop filter mudar (após nova busca)
    useEffect(() => {
        setSelectedFilter(filter);
    }, [filter]);

    // Mapear títulos e mensagens baseado no filtro
    const filterTitleMap: { [key: string]: string } = {
        'all': 'Todos os cursos',
        'in_progress': 'Em progresso',
        'completed': 'Concluídos',
        'not_started': 'Não iniciados'
    };

    const filterEmptyMessageMap: { [key: string]: string } = {
        'all': 'Nenhum curso encontrado.',
        'in_progress': 'Nenhum curso em progresso no momento.',
        'completed': 'Nenhum curso concluído ainda.',
        'not_started': 'Nenhum curso não iniciado.'
    };

    const handleFilterChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        const newFilter = e.target.value;
        setSelectedFilter(newFilter);
        
        router.get(route("dashboard"), { filter: newFilter }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Dashboard" />

            <div className="layout-content-container flex flex-col w-full max-w-7xl flex-1 gap-10">
                {/* Greeting Section */}
                <section>
                    <div className="flex flex-wrap justify-between gap-3 mb-8">
                        <div className="flex min-w-72 flex-col gap-2">
                            <p className="text-white text-4xl font-black leading-tight tracking-[-0.033em] font-heading">
                                Olá, {userName}
                            </p>
                            <p className="text-[#A0A0A0] text-base font-normal leading-normal">
                                Seu Painel de Aprendizagem
                            </p>
                        </div>
                    </div>

                    {/* Stats Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                        {/* Global Progress Card */}
                        <div className="md:col-span-2 xl:col-span-1 flex flex-col gap-6">
                            <div className="flex flex-col gap-4 rounded-xl bg-surface-dark p-6 border border-white/10 h-full justify-center">
                                <p className="text-white text-lg font-medium leading-normal">
                                    Progresso Global
                                </p>
                                <div className="relative w-40 h-40 mx-auto">
                                    <svg className="w-full h-full" viewBox="0 0 36 36">
                                        <path
                                            className="text-white/10"
                                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                            fill="none"
                                            stroke="currentColor"
                                            strokeWidth="3"
                                        ></path>
                                        <path
                                            className="text-primary"
                                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                            fill="none"
                                            stroke="url(#progressGradient)"
                                            strokeDasharray={`${globalProgress}, 100`}
                                            strokeDashoffset="0"
                                            strokeLinecap="round"
                                            strokeWidth="3"
                                        ></path>
                                        <defs>
                                            <linearGradient id="progressGradient" x1="0%" x2="100%" y1="0%" y2="0%">
                                                <stop offset="0%" style={{ stopColor: '#8E2DE2', stopOpacity: 1 }}></stop>
                                                <stop offset="100%" style={{ stopColor: '#4A00E0', stopOpacity: 1 }}></stop>
                                            </linearGradient>
                                        </defs>
                                    </svg>
                                    <div className="absolute inset-0 flex flex-col items-center justify-center">
                                        <span className="text-4xl font-bold font-heading text-white">
                                            {globalProgress}%
                                        </span>
                                        <span className="text-sm text-[#A0A0A0]">Concluído</span>
                                    </div>
                                </div>
                            </div>

                            {/* Hours Watched Card */}
                            <div className="flex flex-col gap-2 rounded-xl p-6 bg-surface-dark border border-white/10">
                                <p className="text-[#A0A0A0] text-base font-medium leading-normal">
                                    Horas Assistidas
                                </p>
                                <div className="flex items-end gap-2">
                                    <p className="text-white tracking-light text-5xl font-bold leading-none font-heading">
                                        {totalHoursWatched}
                                    </p>
                                    <span className="text-[#A0A0A0] text-xl font-medium pb-1">horas</span>
                                </div>
                            </div>

                            {/* Streak Card */}
                            {streak && (
                                <div className="flex flex-col gap-2 rounded-xl p-6 bg-gradient-to-br from-primary/20 to-secondary/20 border border-primary/30">
                                    <div className="flex items-center gap-2 mb-1">
                                        <span className="material-symbols-outlined text-primary text-xl" style={{ fontVariationSettings: "'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24" }}>
                                            local_fire_department
                                        </span>
                                        <p className="text-white text-base font-medium leading-normal">
                                            Sequência de Dias
                                        </p>
                                    </div>
                                    <div className="flex items-end gap-2">
                                        <p className="text-white tracking-light text-5xl font-bold leading-none font-heading">
                                            {streak.current_streak}
                                        </p>
                                        <span className="text-white/70 text-xl font-medium pb-1">
                                            {streak.current_streak === 1 ? 'dia' : 'dias'}
                                        </span>
                                    </div>
                                    {streak.longest_streak > streak.current_streak && (
                                        <p className="text-white/60 text-xs mt-1">
                                            Recorde: {streak.longest_streak} {streak.longest_streak === 1 ? 'dia' : 'dias'}
                                        </p>
                                    )}
                                </div>
                            )}
                        </div>

                        {/* Recent Courses */}
                        <div className="md:col-span-2 xl:col-span-2 flex flex-col rounded-xl bg-surface-dark p-6 border border-white/10">
                            <h3 className="text-white text-lg font-medium leading-normal mb-4">
                                Cursos Recentes
                            </h3>
                            <div className="flex flex-col gap-4">
                                {coursesInProgress.length > 0 ? (
                                    coursesInProgress.map((course) => (
                                        <Link
                                            key={course.id}
                                            href={`/courses/${course.id}/watch`}
                                            className="flex items-center gap-4 group p-3 -m-3 rounded-lg hover:bg-white/5 transition-colors"
                                        >
                                            <div
                                                className="flex-shrink-0 w-16 h-12 bg-cover bg-center rounded"
                                                style={{ backgroundImage: `url(${course.thumbnail})` }}
                                            ></div>
                                            <div className="flex-1">
                                                <p className="text-white font-semibold line-clamp-1">
                                                    {course.title}
                                                </p>
                                                <p className="text-sm text-[#A0A0A0]">
                                                    {course.progress}% concluído
                                                </p>
                                            </div>
                                            <div className="w-full h-1 bg-white/10 rounded-full max-w-24 hidden sm:block">
                                                <div
                                                    className={`h-1 rounded-full ${
                                                        course.progress === 100 ? 'bg-green-500' : 'bg-primary'
                                                    }`}
                                                    style={{ width: `${course.progress}%` }}
                                                ></div>
                                            </div>
                                            <span className="material-symbols-outlined text-white/50 group-hover:text-white transition-colors">
                                                chevron_right
                                            </span>
                                        </Link>
                                    ))
                                ) : (
                                    <div className="flex flex-col items-center justify-center py-8">
                                        <span className="material-symbols-outlined text-white/30 text-4xl mb-2">
                                            school
                                        </span>
                                        <p className="text-[#A0A0A0] text-sm text-center">
                                            Nenhum curso recente
                                        </p>
                                        <Link
                                            href={route("courses.index")}
                                            className="mt-4 text-primary hover:text-secondary text-sm font-medium transition-colors"
                                        >
                                            Explorar cursos →
                                        </Link>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Achievements */}
                        <div className="md:col-span-2 xl:col-span-1 flex flex-col rounded-xl bg-surface-dark p-6 border border-white/10">
                            <h3 className="text-white text-lg font-medium leading-normal mb-4">
                                Conquistas
                            </h3>
                            <div className="grid grid-cols-3 gap-4 text-center">
                                {badges.length > 0 ? (
                                    badges.slice(0, 6).map((badge) => (
                                        <div key={badge.id} className="flex flex-col items-center gap-2">
                                            <div
                                                className={`w-16 h-16 flex items-center justify-center rounded-full p-1 ${
                                                    badge.unlocked
                                                        ? 'bg-gradient-to-br from-secondary to-primary'
                                                        : 'bg-white/10'
                                                }`}
                                            >
                                                <div className="w-full h-full flex items-center justify-center bg-surface-dark rounded-full">
                                                    <span
                                                        className={`material-symbols-outlined text-3xl ${
                                                            badge.unlocked ? 'text-white' : 'text-white/50'
                                                        }`}
                                                        style={badge.unlocked ? { color: badge.color } : {}}
                                                    >
                                                        {badge.icon || 'workspace_premium'}
                                                    </span>
                                                </div>
                                            </div>
                                            <p
                                                className={`text-xs leading-tight ${
                                                    badge.unlocked ? 'text-white/80' : 'text-white/50'
                                                }`}
                                            >
                                                {badge.title}
                                            </p>
                                        </div>
                                    ))
                                ) : (
                                    // Placeholder quando não há badges
                                    Array.from({ length: 6 }).map((_, index) => (
                                        <div key={index} className="flex flex-col items-center gap-2">
                                            <div className="w-16 h-16 flex items-center justify-center rounded-full p-1 bg-white/10">
                                                <div className="w-full h-full flex items-center justify-center bg-surface-dark rounded-full">
                                                    <span className="material-symbols-outlined text-3xl text-white/50">
                                                        lock
                                                    </span>
                                                </div>
                                            </div>
                                            <p className="text-xs leading-tight text-white/50">
                                                Bloqueada
                                            </p>
                                        </div>
                                    ))
                                )}
                            </div>
                        </div>
                    </div>
                </section>

                {/* Courses Grid Section */}
                <section>
                    <div className="flex flex-wrap items-center justify-between gap-4 mb-6">
                        <h2 className="text-white text-[28px] font-bold leading-tight tracking-[-0.015em] font-heading">
                            {filterTitleMap[selectedFilter] || 'Em progresso'}
                        </h2>
                        <div className="flex flex-wrap items-center gap-4">
                            <div className="relative min-w-48">
                                <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-white/50">
                                    search
                                </span>
                                <input
                                    className="w-full bg-surface-dark border border-white/10 rounded-lg h-10 pl-10 pr-4 text-sm text-white placeholder:text-white/50 focus:ring-primary focus:border-primary"
                                    placeholder="Buscar cursos..."
                                    type="text"
                                />
                            </div>
                            <div className="relative min-w-48">
                                <select 
                                    className="w-full bg-surface-dark border border-white/10 rounded-lg h-10 px-3 text-sm text-white focus:ring-primary focus:border-primary appearance-none"
                                    value={selectedFilter}
                                    onChange={handleFilterChange}
                                >
                                    <option value="all">Todos os cursos</option>
                                    <option value="in_progress">Em andamento</option>
                                    <option value="completed">Concluídos</option>
                                    <option value="not_started">Não iniciados</option>
                                </select>
                                <span className="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-white/50 pointer-events-none">
                                    expand_more
                                </span>
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {coursesInProgressForGrid.length > 0 ? (
                            coursesInProgressForGrid.map((course) => (
                                <PremiumCourseCard key={course.id} course={course} />
                            ))
                        ) : (
                            <div className="col-span-full bg-surface-dark rounded-xl p-8 text-center border border-white/10">
                                <p className="text-[#A0A0A0] text-lg">
                                    {filterEmptyMessageMap[selectedFilter] || 'Nenhum curso encontrado.'}
                                </p>
                                <Link
                                    href={route("courses.index")}
                                    className="mt-4 inline-block gradient-button text-white px-6 py-2 rounded-lg transition-opacity"
                                >
                                    Explorar Cursos
                                </Link>
                            </div>
                        )}
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
};

export default Dashboard;
