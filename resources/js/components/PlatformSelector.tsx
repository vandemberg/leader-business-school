import { useState, useRef, useEffect, useMemo } from "react";
import { router } from "@inertiajs/react";
import { Platform } from "@/types";

interface PlatformSelectorProps {
    platforms: Platform[];
    currentPlatform: Platform | null;
    showSelector: boolean;
}

export default function PlatformSelector({
    platforms,
    currentPlatform,
    showSelector,
}: PlatformSelectorProps) {
    const [isLoading, setIsLoading] = useState(false);
    const [isOpen, setIsOpen] = useState(false);
    const dropdownRef = useRef<HTMLDivElement>(null);

    // Remove duplicates by id to ensure unique platforms
    const uniquePlatforms = useMemo(() => {
        const seen = new Set<number>();
        return platforms.filter((platform) => {
            if (seen.has(platform.id)) {
                return false;
            }
            seen.add(platform.id);
            return true;
        });
    }, [platforms]);

    // Fecha o dropdown ao clicar fora
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (
                dropdownRef.current &&
                !dropdownRef.current.contains(event.target as Node)
            ) {
                setIsOpen(false);
            }
        };

        if (isOpen) {
            document.addEventListener("mousedown", handleClickOutside);
        }

        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, [isOpen]);

    if (!showSelector || uniquePlatforms.length <= 1) {
        return null;
    }

    const handlePlatformChange = async (platformId: number) => {
        if (isLoading || !platformId) return;
        if (currentPlatform && platformId === currentPlatform.id) {
            setIsOpen(false);
            return;
        }

        setIsLoading(true);
        setIsOpen(false);

        try {
            router.post(
                route("platforms.switch"),
                { platform_id: platformId },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        // Recarrega a página para aplicar as mudanças
                        window.location.reload();
                    },
                    onError: (errors) => {
                        console.error("Erro ao trocar plataforma:", errors);
                        alert(
                            errors.platform ||
                                "Erro ao trocar plataforma. Tente novamente."
                        );
                    },
                    onFinish: () => {
                        setIsLoading(false);
                    },
                }
            );
        } catch (error) {
            console.error("Erro na requisição:", error);
            alert("Erro ao trocar plataforma. Tente novamente.");
            setIsLoading(false);
        }
    };

    return (
        <div className="relative" ref={dropdownRef}>
            <button
                type="button"
                onClick={(e) => {
                    e.stopPropagation();
                    setIsOpen(!isOpen);
                }}
                disabled={isLoading}
                className="flex items-center gap-2 px-3 py-2 text-sm font-medium text-white/70 hover:text-white bg-white/5 hover:bg-white/10 rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span className="material-symbols-outlined text-lg">
                    {isOpen ? "expand_less" : "expand_more"}
                </span>
                <span className="max-w-[150px] truncate">
                    {currentPlatform?.name || "Plataforma"}
                </span>

                {isLoading && (
                    <span className="material-symbols-outlined text-lg animate-spin">
                        sync
                    </span>
                )}
            </button>

            {isOpen && (
                <div className="absolute z-50 mt-2 w-56 rounded-md bg-surface-dark border border-white/10 shadow-lg">
                    <div className="py-1 flex flex-col">
                        {uniquePlatforms.map((platform) => (
                            <button
                                key={platform.id}
                                type="button"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    handlePlatformChange(platform.id);
                                }}
                                disabled={isLoading}
                                className={`w-full text-left px-4 py-2 text-sm transition-colors ${
                                    currentPlatform?.id === platform.id
                                        ? "bg-white/10 text-white font-medium"
                                        : "text-white/70 hover:bg-white/10 hover:text-white"
                                } ${
                                    isLoading
                                        ? "opacity-50 cursor-not-allowed"
                                        : ""
                                }`}
                            >
                                <div className="flex items-center justify-between">
                                    <span>{platform.name}</span>
                                    {currentPlatform?.id === platform.id && (
                                        <span className="material-symbols-outlined text-sm">
                                            check
                                        </span>
                                    )}
                                </div>
                            </button>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
