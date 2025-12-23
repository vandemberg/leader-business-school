import React from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, useForm } from "@inertiajs/react";

interface Video {
    id: number;
    title: string;
    time_in_seconds?: number | null;
}

interface Module {
    id: number;
    name: string;
    videos: Video[];
}

interface Course {
    id: number;
    title: string;
    description?: string | null;
    modules: Module[];
    share_token: string;
}

interface PersonalCourseShareProps {
    auth: any;
    course: Course;
    isOwner: boolean;
    isEnrolled: boolean;
}

const PersonalCourseShare: React.FC<PersonalCourseShareProps> = ({ auth, course, isOwner, isEnrolled }) => {
    const { post, processing } = useForm({});
    const hasVideos = course.modules.some((module) => module.videos.length > 0);

    const enroll = () => {
        post(route("personal-courses.share.enroll", course.share_token), {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Curso: ${course.title}`} />

            <div className="layout-content-container flex flex-col w-full max-w-4xl flex-1 gap-8">
                <section className="flex flex-col gap-2">
                    <h1 className="text-white text-3xl font-black leading-tight tracking-[-0.033em] font-heading">
                        {course.title}
                    </h1>
                    {course.description && (
                        <p className="text-white/70 text-base font-normal leading-normal">
                            {course.description}
                        </p>
                    )}
                </section>

                <section className="bg-surface-dark border border-white/10 rounded-2xl p-6">
                    <h2 className="text-white text-xl font-bold mb-4">Conteúdo do curso</h2>
                    <div className="grid gap-4">
                        {course.modules.length === 0 ? (
                            <p className="text-white/60">Ainda não há módulos publicados.</p>
                        ) : (
                            course.modules.map((module) => (
                                <div key={module.id} className="border border-white/10 rounded-xl p-4">
                                    <h3 className="text-white font-semibold">{module.name}</h3>
                                    <ul className="mt-3 space-y-2 text-white/70 text-sm">
                                        {module.videos.length === 0 ? (
                                            <li>Nenhum vídeo cadastrado.</li>
                                        ) : (
                                            module.videos.map((video) => (
                                                <li key={video.id} className="flex items-center justify-between">
                                                    <span>{video.title}</span>
                                                    {video.time_in_seconds ? (
                                                        <span className="text-white/40">
                                                            {Math.round(video.time_in_seconds / 60)} min
                                                        </span>
                                                    ) : null}
                                                </li>
                                            ))
                                        )}
                                    </ul>
                                </div>
                            ))
                        )}
                    </div>
                </section>

                <section className="flex flex-wrap gap-3">
                    {isOwner ? (
                        <Link
                            href={route("personal-courses.edit", course.id)}
                            className="flex items-center gap-2 rounded-lg bg-primary px-6 py-2 text-sm font-semibold text-white hover:bg-primary/90"
                        >
                            Editar curso
                        </Link>
                    ) : isEnrolled ? (
                        hasVideos ? (
                            <Link
                                href={route("courses.watch", course.id)}
                                className="flex items-center gap-2 rounded-lg bg-primary px-6 py-2 text-sm font-semibold text-white hover:bg-primary/90"
                            >
                                Assistir curso
                            </Link>
                        ) : (
                            <span className="flex items-center gap-2 rounded-lg bg-white/10 px-6 py-2 text-sm text-white/70">
                                Conteúdo ainda não disponível
                            </span>
                        )
                    ) : (
                        <button
                            type="button"
                            onClick={enroll}
                            disabled={processing}
                            className="flex items-center gap-2 rounded-lg bg-primary px-6 py-2 text-sm font-semibold text-white hover:bg-primary/90 disabled:opacity-60"
                        >
                            Inscrever-se neste curso
                        </button>
                    )}
                    <Link
                        href={route("personal-courses.index")}
                        className="flex items-center gap-2 rounded-lg bg-white/10 px-6 py-2 text-sm text-white hover:bg-white/20"
                    >
                        Meus cursos pessoais
                    </Link>
                </section>
            </div>
        </AuthenticatedLayout>
    );
};

export default PersonalCourseShare;
