import React, { useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, useForm } from "@inertiajs/react";

interface PersonalCourse {
    id: number;
    title: string;
    description?: string | null;
    share_token: string;
}

interface PersonalCoursesIndexProps {
    auth: any;
    courses: PersonalCourse[];
}

const PersonalCoursesIndex: React.FC<PersonalCoursesIndexProps> = ({ auth, courses }) => {
    const { data, setData, post, processing, errors, reset } = useForm({
        title: "",
        description: "",
    });
    const [copiedCourseId, setCopiedCourseId] = useState<number | null>(null);

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        post(route("personal-courses.store"), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
            },
        });
    };

    const handleCopy = async (course: PersonalCourse) => {
        const shareUrl = route("personal-courses.share", course.share_token);

        if (navigator?.clipboard?.writeText) {
            await navigator.clipboard.writeText(shareUrl);
        } else {
            window.prompt("Copie o link do curso:", shareUrl);
        }

        setCopiedCourseId(course.id);
        setTimeout(() => setCopiedCourseId(null), 2000);
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Cursos pessoais" />

            <div className="layout-content-container flex flex-col w-full max-w-5xl flex-1 gap-10">
                <section className="flex flex-col gap-2">
                    <h1 className="text-white text-4xl font-black leading-tight tracking-[-0.033em] font-heading">
                        Seus cursos pessoais
                    </h1>
                    <p className="text-[#A0A0A0] text-base font-normal leading-normal">
                        Crie cursos com módulos e vídeos para compartilhar com outras pessoas.
                    </p>
                </section>

                <section className="bg-surface-dark border border-white/10 rounded-2xl p-6">
                    <h2 className="text-white text-xl font-bold mb-4">Criar novo curso</h2>
                    <form onSubmit={submit} className="flex flex-col gap-4">
                        <div>
                            <label className="text-white/80 text-sm font-medium">Título</label>
                            <input
                                type="text"
                                className="w-full bg-background-dark border border-white/10 rounded-lg h-11 px-4 text-white focus:ring-primary focus:border-primary"
                                value={data.title}
                                onChange={(event) => setData("title", event.target.value)}
                                required
                            />
                            {errors.title && (
                                <p className="text-sm text-red-400 mt-1">{errors.title}</p>
                            )}
                        </div>
                        <div>
                            <label className="text-white/80 text-sm font-medium">Descrição</label>
                            <textarea
                                className="w-full bg-background-dark border border-white/10 rounded-lg min-h-[120px] px-4 py-3 text-white focus:ring-primary focus:border-primary"
                                value={data.description}
                                onChange={(event) => setData("description", event.target.value)}
                            />
                            {errors.description && (
                                <p className="text-sm text-red-400 mt-1">{errors.description}</p>
                            )}
                        </div>
                        <button
                            type="submit"
                            disabled={processing}
                            className="self-start flex items-center gap-2 rounded-lg bg-primary px-6 py-2 text-sm font-semibold text-white hover:bg-primary/90 disabled:opacity-60"
                        >
                            Criar curso
                        </button>
                    </form>
                </section>

                <section className="flex flex-col gap-4">
                    <h2 className="text-white text-xl font-bold">Cursos criados</h2>
                    {courses.length === 0 ? (
                        <div className="bg-surface-dark border border-white/10 rounded-2xl p-8 text-center text-white/70">
                            Você ainda não criou nenhum curso pessoal.
                        </div>
                    ) : (
                        <div className="grid gap-4">
                            {courses.map((course) => (
                                <div
                                    key={course.id}
                                    className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-surface-dark border border-white/10 rounded-2xl p-6"
                                >
                                    <div>
                                        <h3 className="text-white text-lg font-semibold">{course.title}</h3>
                                        {course.description && (
                                            <p className="text-white/70 text-sm mt-1">
                                                {course.description}
                                            </p>
                                        )}
                                    </div>
                                    <div className="flex flex-wrap gap-3">
                                        <Link
                                            href={route("personal-courses.edit", course.id)}
                                            className="flex items-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-sm text-white hover:border-primary hover:text-primary"
                                        >
                                            Editar curso
                                        </Link>
                                        <button
                                            type="button"
                                            onClick={() => handleCopy(course)}
                                            className="flex items-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-sm text-white hover:border-primary hover:text-primary"
                                        >
                                            {copiedCourseId === course.id ? "Link copiado" : "Compartilhar"}
                                        </button>
                                        <Link
                                            href={route("personal-courses.share", course.share_token)}
                                            className="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm text-white hover:bg-white/20"
                                        >
                                            Ver página
                                        </Link>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </AuthenticatedLayout>
    );
};

export default PersonalCoursesIndex;
