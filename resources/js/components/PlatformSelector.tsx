import { useState } from "react";
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

    if (!showSelector || platforms.length <= 1) {
        return null;
    }

    const handlePlatformChange = async (platformId: string) => {
        if (isLoading || !platformId) return;
        if (currentPlatform && parseInt(platformId) === currentPlatform.id)
            return;

        setIsLoading(true);

        try {
            // Obtém o token atual do cookie ou localStorage
            const authToken =
                document.cookie
                    .split("; ")
                    .find((row) => row.startsWith("admin_token="))
                    ?.split("=")[1] || localStorage.getItem("auth_token");

            const response = await fetch("/api/admin/platforms/switch", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${authToken}`,
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "include",
                body: JSON.stringify({
                    platform_id: parseInt(platformId),
                }),
            });

            const data = await response.json();

            if (response.ok) {
                // Atualiza o token se fornecido
                if (data.access_token) {
                    localStorage.setItem("auth_token", data.access_token);
                }

                // Mostra mensagem de sucesso
                if (data.message) {
                    console.log(data.message);
                }

                // Recarrega a página para aplicar as mudanças
                window.location.reload();
            } else {
                console.error("Erro ao trocar plataforma:", data.error);
                alert(data.error || "Erro ao trocar plataforma");
            }
        } catch (error) {
            console.error("Erro na requisição:", error);
            alert("Erro ao trocar plataforma. Tente novamente.");
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="flex items-center space-x-2">
            <label
                htmlFor="platform-selector"
                className="text-sm font-medium text-gray-700"
            >
                Plataforma:
            </label>
            <select
                id="platform-selector"
                value={currentPlatform?.id || ""}
                onChange={(e) => handlePlatformChange(e.target.value)}
                disabled={isLoading}
                className="block w-auto px-3 py-1 text-sm border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {platforms.map((platform) => (
                    <option key={platform.id} value={platform.id}>
                        {platform.name}
                    </option>
                ))}
            </select>
            {isLoading && (
                <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
            )}
        </div>
    );
}
