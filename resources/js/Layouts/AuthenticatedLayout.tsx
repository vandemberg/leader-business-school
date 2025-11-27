import { PropsWithChildren } from 'react';
import Dropdown from '@/components/Dropdown';
import { Link, usePage } from "@inertiajs/react";
import { User } from "@/types";

export default function Authenticated({
    user,
    children,
}: PropsWithChildren<{ user: User }>) {
    const { platform } = usePage().props as any;

    return (
        <div className="relative flex h-auto min-h-screen w-full flex-col overflow-x-hidden bg-background-light dark:bg-background-dark">
            <div className="layout-container flex h-full grow flex-col">
                <header className="flex items-center justify-between whitespace-nowrap border-b border-solid border-white/10 px-4 sm:px-10 lg:px-20 py-4 sticky top-0 z-50 bg-background-dark/80 backdrop-blur-sm">
                    <div className="flex items-center gap-4 text-white">
                        <div className="size-6 text-primary">
                            <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 4H17.3334V17.3334H30.6666V30.6666H44V44H4V4Z" fill="currentColor"></path>
                            </svg>
                        </div>
                        <h2 className="text-white text-lg font-bold leading-tight tracking-[-0.015em] font-heading">
                            {platform?.current?.name || 'Leader Business School'}
                        </h2>
                    </div>
                    <nav className="hidden md:flex flex-1 justify-center items-center gap-9">
                        <Link
                            href={route("dashboard")}
                            className={`text-sm font-medium leading-normal transition-colors ${
                                route().current("dashboard")
                                    ? "text-white border-b-2 border-primary pb-1 font-bold"
                                    : "text-white/70 hover:text-white"
                            }`}
                        >
                            Home
                        </Link>
                        <Link
                            href={route("courses.index")}
                            className={`text-sm font-medium leading-normal transition-colors ${
                                route().current("courses.index")
                                    ? "text-white border-b-2 border-primary pb-1 font-bold"
                                    : "text-white/70 hover:text-white"
                            }`}
                        >
                            Meus Cursos
                        </Link>
                        <Link
                            href={route("teachers.index")}
                            className={`text-sm font-medium leading-normal transition-colors ${
                                route().current("teachers.index")
                                    ? "text-white border-b-2 border-primary pb-1 font-bold"
                                    : "text-white/70 hover:text-white"
                            }`}
                        >
                            Comunidade
                        </Link>
                        <a
                            href="#"
                            className="text-white/70 hover:text-white transition-colors text-sm font-medium leading-normal"
                        >
                            Ajuda
                        </a>
                    </nav>
                    <div className="flex items-center gap-4">
                        <div className="flex gap-2">
                            <button className="flex h-10 w-10 cursor-pointer items-center justify-center overflow-hidden rounded-full bg-white/5 text-white/70 hover:bg-white/10 hover:text-white transition-colors">
                                <span className="material-symbols-outlined text-xl">notifications</span>
                            </button>
                            <button className="flex h-10 w-10 cursor-pointer items-center justify-center overflow-hidden rounded-full bg-white/5 text-white/70 hover:bg-white/10 hover:text-white transition-colors">
                                <span className="material-symbols-outlined text-xl">settings</span>
                            </button>
                        </div>
                        <Dropdown>
                            <Dropdown.Trigger>
                                <button className="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10 bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold">
                                    {user.name.charAt(0).toUpperCase()}
                                </button>
                            </Dropdown.Trigger>
                            <Dropdown.Content>
                                <Dropdown.Link href={route("profile.edit")}>
                                    Perfil
                                </Dropdown.Link>
                                <Dropdown.Link
                                    href={route("logout")}
                                    method="post"
                                    as="button"
                                >
                                    Sair
                                </Dropdown.Link>
                            </Dropdown.Content>
                        </Dropdown>
                    </div>
                </header>

                <main className="px-4 sm:px-10 lg:px-20 flex flex-1 justify-center py-10">
                    {children}
                </main>
            </div>
        </div>
    );
}
