import React from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, usePage } from "@inertiajs/react";
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

interface DashboardProps {
    auth: any;
    coursesInProgress: Course[];
    allCourses: Course[];
    coursesInProgressForGrid: Course[];
    globalProgress: number;
    totalHoursWatched: number;
    platform: PlatformProp;
}

interface PlatformProp {
    id: number;
    name: string;
    slug: string;
}

const Dashboard: React.FC<DashboardProps> = ({
    auth,
    coursesInProgress,
    allCourses,
    coursesInProgressForGrid,
    globalProgress,
    totalHoursWatched,
    platform
}) => {
    const userName = auth.user.name.split(' ')[0]; // Get first name

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
                                {[
                                    { icon: 'workspace_premium', label: 'Mestre do Foco', unlocked: true, color: 'text-secondary' },
                                    { icon: 'local_fire_department', label: '7 Dias de Fogo', unlocked: true, color: 'text-yellow-400' },
                                    { icon: 'school', label: 'Primeiro Curso', unlocked: true, color: 'text-blue-400' },
                                    { icon: 'military_tech', label: 'Líder Nato', unlocked: true, color: 'text-green-400' },
                                    { icon: 'lock', label: 'Bloqueada', unlocked: false, color: 'text-white/50' },
                                    { icon: 'lock', label: 'Bloqueada', unlocked: false, color: 'text-white/50' },
                                ].map((achievement, index) => (
                                    <div key={index} className="flex flex-col items-center gap-2">
                                        <div
                                            className={`w-16 h-16 flex items-center justify-center rounded-full p-1 ${
                                                achievement.unlocked
                                                    ? 'bg-gradient-to-br from-secondary to-primary'
                                                    : 'bg-white/10'
                                            }`}
                                        >
                                            <div className="w-full h-full flex items-center justify-center bg-surface-dark rounded-full">
                                                <span
                                                    className={`material-symbols-outlined text-3xl ${
                                                        achievement.unlocked ? achievement.color : 'text-white/50'
                                                    }`}
                                                >
                                                    {achievement.icon}
                                                </span>
                                            </div>
                                        </div>
                                        <p
                                            className={`text-xs leading-tight ${
                                                achievement.unlocked ? 'text-white/80' : 'text-white/50'
                                            }`}
                                        >
                                            {achievement.label}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>

                {/* Courses Grid Section */}
                <section>
                    <div className="flex flex-wrap items-center justify-between gap-4 mb-6">
                        <h2 className="text-white text-[28px] font-bold leading-tight tracking-[-0.015em] font-heading">
                            Em progresso
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
                                <select className="w-full bg-surface-dark border border-white/10 rounded-lg h-10 px-3 text-sm text-white focus:ring-primary focus:border-primary appearance-none">
                                    <option>Todos os cursos</option>
                                    <option>Em andamento</option>
                                    <option>Concluídos</option>
                                    <option>Não iniciados</option>
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
                                    Nenhum curso em progresso no momento.
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
