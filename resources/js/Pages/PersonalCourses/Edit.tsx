import React, { useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, useForm } from "@inertiajs/react";
import axios from "axios";

interface Video {
    id: number;
    title: string;
    description?: string | null;
    url: string;
    time_in_seconds?: number | null;
}

interface Module {
    id: number;
    name: string;
    description?: string | null;
    videos: Video[];
}

interface Course {
    id: number;
    title: string;
    description?: string | null;
    modules: Module[];
}

interface PersonalCoursesEditProps {
    auth: any;
    course: Course;
    shareUrl: string;
}

interface VideoDraft {
    title: string;
    description: string;
    url: string;
    time_in_seconds: string;
}

const emptyVideoDraft = (): VideoDraft => ({
    title: "",
    description: "",
    url: "",
    time_in_seconds: "",
});

const PersonalCoursesEdit: React.FC<PersonalCoursesEditProps> = ({ auth, course, shareUrl }) => {
    const { data, setData, put, processing } = useForm({
        title: course.title,
        description: course.description || "",
    });

    const [modules, setModules] = useState<Module[]>(course.modules || []);
    const [newModule, setNewModule] = useState({ name: "", description: "" });
    const [videoDrafts, setVideoDrafts] = useState<Record<number, VideoDraft>>({});
    const [copied, setCopied] = useState(false);

    const submitCourse = (event: React.FormEvent) => {
        event.preventDefault();
        put(route("personal-courses.update", course.id), { preserveScroll: true });
    };

    const handleCopy = async () => {
        if (navigator?.clipboard?.writeText) {
            await navigator.clipboard.writeText(shareUrl);
        } else {
            window.prompt("Copie o link do curso:", shareUrl);
        }

        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const addModule = async () => {
        if (!newModule.name.trim()) return;
        const response = await axios.post(route("personal-courses.modules.store", course.id), {
            name: newModule.name,
            description: newModule.description,
        });
        setModules((prev) => [...prev, { ...response.data, videos: [] }]);
        setNewModule({ name: "", description: "" });
    };

    const updateModule = async (moduleId: number) => {
        const module = modules.find((item) => item.id === moduleId);
        if (!module) return;

        const response = await axios.put(
            route("personal-courses.modules.update", [course.id, moduleId]),
            {
                name: module.name,
                description: module.description,
            }
        );

        setModules((prev) =>
            prev.map((item) =>
                item.id === moduleId ? { ...item, ...response.data, videos: item.videos } : item
            )
        );
    };

    const deleteModule = async (moduleId: number) => {
        await axios.delete(route("personal-courses.modules.destroy", [course.id, moduleId]));
        setModules((prev) => prev.filter((item) => item.id !== moduleId));
    };

    const handleVideoDraftChange = (moduleId: number, field: keyof VideoDraft, value: string) => {
        setVideoDrafts((prev) => ({
            ...prev,
            [moduleId]: {
                ...(prev[moduleId] || emptyVideoDraft()),
                [field]: value,
            },
        }));
    };

    const addVideo = async (moduleId: number) => {
        const draft = videoDrafts[moduleId] || emptyVideoDraft();
        if (!draft.title.trim() || !draft.url.trim()) return;

        const response = await axios.post(
            route("personal-courses.videos.store", [course.id, moduleId]),
            {
                title: draft.title,
                description: draft.description,
                url: draft.url,
                time_in_seconds: draft.time_in_seconds !== "" ? Number(draft.time_in_seconds) : null,
            }
        );

        setModules((prev) =>
            prev.map((module) =>
                module.id === moduleId
                    ? { ...module, videos: [...module.videos, response.data] }
                    : module
            )
        );
        setVideoDrafts((prev) => ({ ...prev, [moduleId]: emptyVideoDraft() }));
    };

    const updateVideo = async (moduleId: number, videoId: number) => {
        const module = modules.find((item) => item.id === moduleId);
        const video = module?.videos.find((item) => item.id === videoId);
        if (!module || !video) return;

        const response = await axios.put(
            route("personal-courses.videos.update", [course.id, moduleId, videoId]),
            {
                title: video.title,
                description: video.description,
                url: video.url,
                time_in_seconds:
                    video.time_in_seconds !== null && video.time_in_seconds !== undefined
                        ? Number(video.time_in_seconds)
                        : null,
            }
        );

        setModules((prev) =>
            prev.map((item) =>
                item.id === moduleId
                    ? {
                          ...item,
                          videos: item.videos.map((v) => (v.id === videoId ? response.data : v)),
                      }
                    : item
            )
        );
    };

    const deleteVideo = async (moduleId: number, videoId: number) => {
        await axios.delete(route("personal-courses.videos.destroy", [course.id, moduleId, videoId]));
        setModules((prev) =>
            prev.map((module) =>
                module.id === moduleId
                    ? { ...module, videos: module.videos.filter((video) => video.id !== videoId) }
                    : module
            )
        );
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Editar ${course.title}`} />

            <div className="layout-content-container flex flex-col w-full max-w-5xl flex-1 gap-8">
                <section className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="text-white text-3xl font-black leading-tight tracking-[-0.033em] font-heading">
                            Editar curso pessoal
                        </h1>
                        <p className="text-white/70 text-sm mt-2">
                            Configure módulos e vídeos do seu curso.
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-3">
                        <button
                            type="button"
                            onClick={handleCopy}
                            className="flex items-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-sm text-white hover:border-primary hover:text-primary"
                        >
                            {copied ? "Link copiado" : "Compartilhar"}
                        </button>
                        <Link
                            href={shareUrl}
                            className="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm text-white hover:bg-white/20"
                        >
                            Ver página compartilhada
                        </Link>
                        <Link
                            href={route("personal-courses.index")}
                            className="flex items-center gap-2 rounded-lg bg-white/5 px-4 py-2 text-sm text-white hover:bg-white/10"
                        >
                            Voltar
                        </Link>
                    </div>
                </section>

                <section className="bg-surface-dark border border-white/10 rounded-2xl p-6">
                    <h2 className="text-white text-xl font-bold mb-4">Informações do curso</h2>
                    <form onSubmit={submitCourse} className="flex flex-col gap-4">
                        <div>
                            <label className="text-white/80 text-sm font-medium">Título</label>
                            <input
                                type="text"
                                className="w-full bg-background-dark border border-white/10 rounded-lg h-11 px-4 text-white focus:ring-primary focus:border-primary"
                                value={data.title}
                                onChange={(event) => setData("title", event.target.value)}
                                required
                            />
                        </div>
                        <div>
                            <label className="text-white/80 text-sm font-medium">Descrição</label>
                            <textarea
                                className="w-full bg-background-dark border border-white/10 rounded-lg min-h-[120px] px-4 py-3 text-white focus:ring-primary focus:border-primary"
                                value={data.description}
                                onChange={(event) => setData("description", event.target.value)}
                            />
                        </div>
                        <button
                            type="submit"
                            disabled={processing}
                            className="self-start flex items-center gap-2 rounded-lg bg-primary px-6 py-2 text-sm font-semibold text-white hover:bg-primary/90 disabled:opacity-60"
                        >
                            Salvar curso
                        </button>
                    </form>
                </section>

                <section className="bg-surface-dark border border-white/10 rounded-2xl p-6">
                    <h2 className="text-white text-xl font-bold mb-4">Módulos</h2>

                    <div className="grid gap-4 mb-8">
                        <div className="grid gap-3">
                            <input
                                type="text"
                                placeholder="Nome do módulo"
                                className="w-full bg-background-dark border border-white/10 rounded-lg h-11 px-4 text-white focus:ring-primary focus:border-primary"
                                value={newModule.name}
                                onChange={(event) => setNewModule((prev) => ({ ...prev, name: event.target.value }))}
                            />
                            <textarea
                                placeholder="Descrição do módulo"
                                className="w-full bg-background-dark border border-white/10 rounded-lg min-h-[100px] px-4 py-3 text-white focus:ring-primary focus:border-primary"
                                value={newModule.description}
                                onChange={(event) =>
                                    setNewModule((prev) => ({ ...prev, description: event.target.value }))
                                }
                            />
                            <button
                                type="button"
                                onClick={addModule}
                                className="self-start flex items-center gap-2 rounded-lg bg-primary px-6 py-2 text-sm font-semibold text-white hover:bg-primary/90"
                            >
                                Adicionar módulo
                            </button>
                        </div>
                    </div>

                    <div className="grid gap-6">
                        {modules.length === 0 ? (
                            <p className="text-white/60">Nenhum módulo cadastrado ainda.</p>
                        ) : (
                            modules.map((module) => (
                                <div key={module.id} className="border border-white/10 rounded-xl p-4">
                                    <div className="flex flex-col gap-3">
                                        <input
                                            type="text"
                                            className="w-full bg-background-dark border border-white/10 rounded-lg h-11 px-4 text-white focus:ring-primary focus:border-primary"
                                            value={module.name}
                                            onChange={(event) =>
                                                setModules((prev) =>
                                                    prev.map((item) =>
                                                        item.id === module.id
                                                            ? { ...item, name: event.target.value }
                                                            : item
                                                    )
                                                )
                                            }
                                        />
                                        <textarea
                                            className="w-full bg-background-dark border border-white/10 rounded-lg min-h-[90px] px-4 py-3 text-white focus:ring-primary focus:border-primary"
                                            value={module.description || ""}
                                            onChange={(event) =>
                                                setModules((prev) =>
                                                    prev.map((item) =>
                                                        item.id === module.id
                                                            ? { ...item, description: event.target.value }
                                                            : item
                                                    )
                                                )
                                            }
                                        />
                                        <div className="flex flex-wrap gap-3">
                                            <button
                                                type="button"
                                                onClick={() => updateModule(module.id)}
                                                className="flex items-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-sm text-white hover:border-primary hover:text-primary"
                                            >
                                                Salvar módulo
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => deleteModule(module.id)}
                                                className="flex items-center gap-2 rounded-lg border border-red-500/60 px-4 py-2 text-sm text-red-300 hover:border-red-400 hover:text-red-200"
                                            >
                                                Remover módulo
                                            </button>
                                        </div>
                                    </div>

                                    <div className="mt-6">
                                        <h3 className="text-white text-base font-semibold mb-3">Vídeos</h3>
                                        <div className="grid gap-3 mb-6">
                                            <input
                                                type="text"
                                                placeholder="Título do vídeo"
                                                className="w-full bg-background-dark border border-white/10 rounded-lg h-11 px-4 text-white focus:ring-primary focus:border-primary"
                                                value={(videoDrafts[module.id] || emptyVideoDraft()).title}
                                                onChange={(event) =>
                                                    handleVideoDraftChange(module.id, "title", event.target.value)
                                                }
                                            />
                                            <input
                                                type="text"
                                                placeholder="URL do vídeo"
                                                className="w-full bg-background-dark border border-white/10 rounded-lg h-11 px-4 text-white focus:ring-primary focus:border-primary"
                                                value={(videoDrafts[module.id] || emptyVideoDraft()).url}
                                                onChange={(event) =>
                                                    handleVideoDraftChange(module.id, "url", event.target.value)
                                                }
                                            />
                                            <input
                                                type="text"
                                                placeholder="Duração em segundos"
                                                className="w-full bg-background-dark border border-white/10 rounded-lg h-11 px-4 text-white focus:ring-primary focus:border-primary"
                                                value={(videoDrafts[module.id] || emptyVideoDraft()).time_in_seconds}
                                                onChange={(event) =>
                                                    handleVideoDraftChange(
                                                        module.id,
                                                        "time_in_seconds",
                                                        event.target.value
                                                    )
                                                }
                                            />
                                            <textarea
                                                placeholder="Descrição do vídeo"
                                                className="w-full bg-background-dark border border-white/10 rounded-lg min-h-[90px] px-4 py-3 text-white focus:ring-primary focus:border-primary"
                                                value={(videoDrafts[module.id] || emptyVideoDraft()).description}
                                                onChange={(event) =>
                                                    handleVideoDraftChange(
                                                        module.id,
                                                        "description",
                                                        event.target.value
                                                    )
                                                }
                                            />
                                            <button
                                                type="button"
                                                onClick={() => addVideo(module.id)}
                                                className="self-start flex items-center gap-2 rounded-lg bg-primary px-6 py-2 text-sm font-semibold text-white hover:bg-primary/90"
                                            >
                                                Adicionar vídeo
                                            </button>
                                        </div>

                                        <div className="grid gap-4">
                                            {module.videos.length === 0 ? (
                                                <p className="text-white/60">Nenhum vídeo cadastrado.</p>
                                            ) : (
                                                module.videos.map((video) => (
                                                    <div
                                                        key={video.id}
                                                        className="border border-white/10 rounded-xl p-4 grid gap-3"
                                                    >
                                                        <input
                                                            type="text"
                                                            className="w-full bg-background-dark border border-white/10 rounded-lg h-11 px-4 text-white focus:ring-primary focus:border-primary"
                                                            value={video.title}
                                                            onChange={(event) =>
                                                                setModules((prev) =>
                                                                    prev.map((mod) =>
                                                                        mod.id === module.id
                                                                            ? {
                                                                                  ...mod,
                                                                                  videos: mod.videos.map((item) =>
                                                                                      item.id === video.id
                                                                                          ? { ...item, title: event.target.value }
                                                                                          : item
                                                                                  ),
                                                                              }
                                                                            : mod
                                                                    )
                                                                )
                                                            }
                                                        />
                                                        <input
                                                            type="text"
                                                            className="w-full bg-background-dark border border-white/10 rounded-lg h-11 px-4 text-white focus:ring-primary focus:border-primary"
                                                            value={video.url}
                                                            onChange={(event) =>
                                                                setModules((prev) =>
                                                                    prev.map((mod) =>
                                                                        mod.id === module.id
                                                                            ? {
                                                                                  ...mod,
                                                                                  videos: mod.videos.map((item) =>
                                                                                      item.id === video.id
                                                                                          ? { ...item, url: event.target.value }
                                                                                          : item
                                                                                  ),
                                                                              }
                                                                            : mod
                                                                    )
                                                                )
                                                            }
                                                        />
                                                        <input
                                                            type="text"
                                                            className="w-full bg-background-dark border border-white/10 rounded-lg h-11 px-4 text-white focus:ring-primary focus:border-primary"
                                                            value={video.time_in_seconds ?? ""}
                                                            onChange={(event) =>
                                                                setModules((prev) =>
                                                                    prev.map((mod) =>
                                                                        mod.id === module.id
                                                                            ? {
                                                                                  ...mod,
                                                                                  videos: mod.videos.map((item) =>
                                                                                      item.id === video.id
                                                                                          ? {
                                                                                                ...item,
                                                                                                time_in_seconds:
                                                                                                    event.target.value === ""
                                                                                                        ? null
                                                                                                        : Number(event.target.value),
                                                                                            }
                                                                                          : item
                                                                                  ),
                                                                              }
                                                                            : mod
                                                                    )
                                                                )
                                                            }
                                                        />
                                                        <textarea
                                                            className="w-full bg-background-dark border border-white/10 rounded-lg min-h-[90px] px-4 py-3 text-white focus:ring-primary focus:border-primary"
                                                            value={video.description || ""}
                                                            onChange={(event) =>
                                                                setModules((prev) =>
                                                                    prev.map((mod) =>
                                                                        mod.id === module.id
                                                                            ? {
                                                                                  ...mod,
                                                                                  videos: mod.videos.map((item) =>
                                                                                      item.id === video.id
                                                                                          ? {
                                                                                                ...item,
                                                                                                description: event.target.value,
                                                                                            }
                                                                                          : item
                                                                                  ),
                                                                              }
                                                                            : mod
                                                                    )
                                                                )
                                                            }
                                                        />
                                                        <div className="flex flex-wrap gap-3">
                                                            <button
                                                                type="button"
                                                                onClick={() => updateVideo(module.id, video.id)}
                                                                className="flex items-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-sm text-white hover:border-primary hover:text-primary"
                                                            >
                                                                Salvar vídeo
                                                            </button>
                                                            <button
                                                                type="button"
                                                                onClick={() => deleteVideo(module.id, video.id)}
                                                                className="flex items-center gap-2 rounded-lg border border-red-500/60 px-4 py-2 text-sm text-red-300 hover:border-red-400 hover:text-red-200"
                                                            >
                                                                Remover vídeo
                                                            </button>
                                                        </div>
                                                    </div>
                                                ))
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
};

export default PersonalCoursesEdit;
